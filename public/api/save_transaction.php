<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) { echo json_encode(['error' => 'No data received']); exit; }

$custName  = $data['cust_name']       ?? '';
$custTel   = $data['cust_telno']      ?? '';
$custId    = (isset($data['customer_id']) && $data['customer_id']) ? intval($data['customer_id']) : null;
$invoiceRef= trim($data['invoice_reference'] ?? '');
$transType = $data['trans_type']      ?? 'Cash';
$discPct   = floatval($data['disc_percentage'] ?? 0);
$paid      = floatval($data['paid_amount']     ?? 0);
$userId    = $data['user_id']         ?? 'admin';
$taxStatus = $data['tax_status']      ?? 'N';
$items     = $data['items']           ?? [];
$salePersonId = (isset($data['sale_person_id']) && $data['sale_person_id']) ? intval($data['sale_person_id']) : null;
$description  = trim($data['description'] ?? '');
$remarks      = trim($data['remarks']      ?? '');

if (!is_array($items) || !count($items)) {
    echo json_encode(['error' => 'No items in the bill']);
    exit;
}

// Recompute the money authoritatively from the line items server-side -
// never trust the client's pre-computed totals for what gets written to
// the ledger, so a future client-side bug can't corrupt financial records.
$gross = 0;
foreach ($items as $item) {
    $gross += floatval($item['price'] ?? 0) * intval($item['quantity'] ?? 0);
}
$discAmt = $gross * $discPct / 100;
$net     = $gross - $discAmt;
$balance = $paid - $net;

// Cash/Card sales must be paid in full - only Credit may carry an
// outstanding (negative) balance, since that's the whole point of credit.
if ($transType !== 'Credit' && $balance < 0) {
    echo json_encode(['error' => 'Cash/Card sales cannot be saved with a negative balance - collect full payment or set Type to Credit']);
    exit;
}

sqlsrv_begin_transaction($conn);

// Trans_no is no longer an IDENTITY column in the current schema - the app
// must supply it. TABLOCKX+HOLDLOCK for the rest of this transaction so two
// concurrent sales can't both compute the same "next" number.
$sqlNext  = "SELECT ISNULL(MAX(Trans_no), 0) + 1 AS next_no FROM [Transaction] WITH (TABLOCKX, HOLDLOCK)";
$stmtNext = sqlsrv_query($conn, $sqlNext);
$nextRow  = $stmtNext ? sqlsrv_fetch_array($stmtNext, SQLSRV_FETCH_ASSOC) : null;
$transNo  = intval($nextRow['next_no'] ?? 0);
if (!$transNo) {
    sqlsrv_rollback($conn);
    echo json_encode(['error' => 'Could not generate a transaction number']);
    exit;
}

$sqlT = "INSERT INTO [Transaction]
             (Trans_no, Invoice_reference, Customer_id, Cust_name, Cust_telno, Trans_date, Trans_type,
              Trans_amount, Disc_percentage, Tax_status,
              Gross_amount, Paid_amount, Balance_amount, User_id,
              Sale_Person_id, Description, Remarks)
         VALUES (?, ?, ?, ?, ?, GETDATE(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmtT = sqlsrv_query($conn, $sqlT, [
    $transNo, $invoiceRef, $custId, $custName, $custTel, $transType,
    $net, $discPct, $taxStatus,
    $gross, $paid, $balance, $userId,
    $salePersonId, $description, $remarks
]);

if (!$stmtT) {
    sqlsrv_rollback($conn);
    $err = sqlsrv_errors();
    echo json_encode(['error' => $err[0]['message'] ?? 'Insert failed']);
    exit;
}

foreach ($items as $item) {
    $stockNo = intval($item['stock_number']);
    $qty     = intval($item['quantity']);
    $price   = floatval($item['price']);
    $amount  = $price * $qty;

    // Purchase-price cost basis for this line, read from the item master so
    // margin/profit can be tracked (PPrice_amount is new in this schema).
    $sqlCost  = "SELECT PURCHASE_PRICE FROM Item_Stock WHERE STOCK_NUMBER = ?";
    $stmtCost = sqlsrv_query($conn, $sqlCost, [$stockNo]);
    $costRow  = $stmtCost ? sqlsrv_fetch_array($stmtCost, SQLSRV_FETCH_ASSOC) : null;
    $pPriceAmount = (floatval($costRow['PURCHASE_PRICE'] ?? 0)) * $qty;

    // Check stock against the batch ledger (the source of truth), not a
    // separately-tracked counter. WITH (UPDLOCK, HOLDLOCK) holds a lock on
    // this item's batch rows for the rest of the transaction, so a concurrent
    // sale of the same item can't both pass this check and oversell.
    $sqlAvail  = "SELECT ISNULL(SUM(ITEMS_AVAILABLE),0) AS total_avail
                  FROM ST_STOCKRECEIPTDETAIL WITH (UPDLOCK, HOLDLOCK)
                  WHERE STOCK_NUMBER = ?";
    $stmtAvail = sqlsrv_query($conn, $sqlAvail, [$stockNo]);
    $availRow  = $stmtAvail ? sqlsrv_fetch_array($stmtAvail, SQLSRV_FETCH_ASSOC) : null;
    $totalAvail = intval($availRow['total_avail'] ?? 0);
    if ($totalAvail < $qty) {
        sqlsrv_rollback($conn);
        echo json_encode(['error' => 'Not enough stock in hand for ' . $stockNo . ' - sale cancelled']);
        exit;
    }

    $sqlD  = "INSERT INTO trans_detail
                  (Trans_no, stock_number, quantity, Price_PerItem, amount, User_id, Status, PPrice_amount)
              VALUES (?, ?, ?, ?, ?, ?, 'Active', ?)";
    $stmtD = sqlsrv_query($conn, $sqlD, [
        $transNo,
        $stockNo,
        $qty,
        $price,
        $amount,
        $userId,
        $pPriceAmount,
    ]);
    if (!$stmtD) {
        sqlsrv_rollback($conn);
        $err = sqlsrv_errors();
        echo json_encode(['error' => $err[0]['message'] ?? 'Detail insert failed']);
        exit;
    }

    // FEFO: deduct the sold quantity from the earliest-expiring batch(es) of this
    // stock number first, so ST_STOCKRECEIPTDETAIL.ITEMS_AVAILABLE reflects what's
    // actually left on the shelf per batch (and a depleted batch drops off the
    // expiry ledger without its historical row ever being deleted).
    //
    // trans_detail.Invoice_No exists specifically to link a sold line back to
    // the stock receipt it came from, but was never actually written here --
    // recording the FIRST (earliest-expiring) batch touched, since that's
    // the one FEFO actually prioritizes. A line split across multiple
    // batches (first batch ran out mid-sale) only gets attributed to that
    // first/primary one -- trans_detail is one row per (Trans_no,
    // stock_number), it can't represent a multi-batch split without a
    // structural change, so this is an honest simplification, not a bug.
    $remaining = $qty;
    $primaryInvoiceNo = null;
    $sqlBatches = "SELECT Invoice_no, STOCK_NUMBER, BATCH_NO, ITEMS_AVAILABLE
                   FROM ST_STOCKRECEIPTDETAIL
                   WHERE STOCK_NUMBER = ? AND ITEMS_AVAILABLE > 0
                   ORDER BY EXPIRY_DATE ASC";
    $stmtBatches = sqlsrv_query($conn, $sqlBatches, [$stockNo]);
    if ($stmtBatches) {
        while ($remaining > 0 && ($batch = sqlsrv_fetch_array($stmtBatches, SQLSRV_FETCH_ASSOC))) {
            $deduct = min($remaining, intval($batch['ITEMS_AVAILABLE']));
            if ($deduct > 0) {
                if ($primaryInvoiceNo === null) { $primaryInvoiceNo = $batch['Invoice_no']; }
                sqlsrv_query($conn,
                    "UPDATE ST_STOCKRECEIPTDETAIL SET ITEMS_AVAILABLE = ITEMS_AVAILABLE - ?
                     WHERE Invoice_no = ? AND STOCK_NUMBER = ? AND BATCH_NO = ?",
                    [$deduct, $batch['Invoice_no'], $batch['STOCK_NUMBER'], $batch['BATCH_NO']]
                );
                $remaining -= $deduct;
            }
        }
    }
    if ($primaryInvoiceNo !== null) {
        sqlsrv_query($conn, "UPDATE trans_detail SET Invoice_No = ? WHERE Trans_no = ? AND stock_number = ?",
            [$primaryInvoiceNo, $transNo, $stockNo]);
    }

    // Reconcile Item_Stock.QTY_INHAND to the batch ledger's new total instead
    // of trusting a separately incremented/decremented counter that can drift.
    sqlsrv_query($conn,
        "UPDATE Item_Stock SET QTY_INHAND = ISNULL((SELECT SUM(ITEMS_AVAILABLE) FROM ST_STOCKRECEIPTDETAIL WHERE STOCK_NUMBER = ?),0) WHERE STOCK_NUMBER = ?",
        [$stockNo, $stockNo]
    );
}

sqlsrv_commit($conn);
sqlsrv_close($conn);
echo json_encode(['success' => true, 'trans_no' => $transNo]);