<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

// Edits an already-saved bill in place (same Trans_no, same Trans_date --
// this is a correction to the original sale, not a new one). The tricky
// part save_transaction.php never had to deal with: reversing the ORIGINAL
// stock effect before re-applying the edited item list, or a re-save would
// silently double-deduct (or under-deduct) stock.
//
// Reversal strategy per old line:
//   - If it has a recorded batch (trans_detail.Invoice_No, only true for
//     bills saved since batch-linking was added), put the quantity back
//     into that exact batch -- fully correct.
//   - If not (any older bill, or a batch that's since been deleted), put it
//     back into whichever remaining batch of that stock number has the
//     LATEST expiry date -- i.e. "back of the FEFO line", the least
//     disruptive place for stock whose real origin is unknown. If no batch
//     exists at all for that stock number, that line's reversal is skipped
//     and reported back in the response rather than silently failing.

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) { echo json_encode(['error' => 'No data received']); exit; }

$transNo   = intval($data['trans_no'] ?? 0);
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

if (!$transNo) { echo json_encode(['error' => 'No Trans_no given']); exit; }
if (!is_array($items) || !count($items)) { echo json_encode(['error' => 'No items in the bill']); exit; }

$gross = 0;
foreach ($items as $item) { $gross += floatval($item['price'] ?? 0) * intval($item['quantity'] ?? 0); }
$discAmt = $gross * $discPct / 100;
$net     = $gross - $discAmt;
$balance = $paid - $net;

if ($transType !== 'Credit' && $balance < 0) {
    echo json_encode(['error' => 'Cash/Card sales cannot be saved with a negative balance — collect full payment or set Type to Credit']);
    exit;
}

sqlsrv_begin_transaction($conn);

$stmtCheck = sqlsrv_query($conn, "SELECT Trans_no, Is_Cancelled FROM [Transaction] WHERE Trans_no = ?", [$transNo]);
$checkRow  = $stmtCheck ? sqlsrv_fetch_array($stmtCheck, SQLSRV_FETCH_ASSOC) : null;
if (!$checkRow) {
    sqlsrv_rollback($conn);
    echo json_encode(['error' => 'Bill #' . $transNo . ' not found']);
    exit;
}
if ($checkRow['Is_Cancelled']) {
    sqlsrv_rollback($conn);
    echo json_encode(['error' => 'Bill #' . $transNo . ' is cancelled and cannot be edited']);
    exit;
}

// --- Reverse the original stock effect of every existing line ---
$skippedReversals = [];
$stmtOld = sqlsrv_query($conn, "SELECT stock_number, quantity, Invoice_No FROM trans_detail WHERE Trans_no = ?", [$transNo]);
$oldLines = [];
if ($stmtOld) { while ($row = sqlsrv_fetch_array($stmtOld, SQLSRV_FETCH_ASSOC)) { $oldLines[] = $row; } }

foreach ($oldLines as $old) {
    $stockNo = $old['stock_number'];
    $qty     = intval($old['quantity']);
    $invNo   = $old['Invoice_No'];

    if ($invNo !== null) {
        $upd = sqlsrv_query($conn,
            "UPDATE ST_STOCKRECEIPTDETAIL SET ITEMS_AVAILABLE = ITEMS_AVAILABLE + ? WHERE Invoice_no = ? AND STOCK_NUMBER = ?",
            [$qty, $invNo, $stockNo]);
        if (!$upd) { $skippedReversals[] = $stockNo; }
        continue;
    }

    // No recorded batch -- reverse into the latest-expiry remaining batch.
    $stmtLatest = sqlsrv_query($conn,
        "SELECT TOP 1 Invoice_no FROM ST_STOCKRECEIPTDETAIL WHERE STOCK_NUMBER = ? ORDER BY EXPIRY_DATE DESC",
        [$stockNo]);
    $latest = $stmtLatest ? sqlsrv_fetch_array($stmtLatest, SQLSRV_FETCH_ASSOC) : null;
    if ($latest) {
        sqlsrv_query($conn,
            "UPDATE ST_STOCKRECEIPTDETAIL SET ITEMS_AVAILABLE = ITEMS_AVAILABLE + ? WHERE Invoice_no = ? AND STOCK_NUMBER = ?",
            [$qty, $latest['Invoice_no'], $stockNo]);
    } else {
        $skippedReversals[] = $stockNo;
    }
}

// --- Delete the old line items, insert the new ones (same as save_transaction.php) ---
sqlsrv_query($conn, "DELETE FROM trans_detail WHERE Trans_no = ?", [$transNo]);

$touchedStock = [];
foreach ($items as $item) {
    $stockNo = intval($item['stock_number']);
    $qty     = intval($item['quantity']);
    $price   = floatval($item['price']);
    $amount  = $price * $qty;
    $touchedStock[$stockNo] = true;

    $sqlCost  = "SELECT PURCHASE_PRICE FROM Item_Stock WHERE STOCK_NUMBER = ?";
    $stmtCost = sqlsrv_query($conn, $sqlCost, [$stockNo]);
    $costRow  = $stmtCost ? sqlsrv_fetch_array($stmtCost, SQLSRV_FETCH_ASSOC) : null;
    $pPriceAmount = (floatval($costRow['PURCHASE_PRICE'] ?? 0)) * $qty;

    $sqlAvail  = "SELECT ISNULL(SUM(ITEMS_AVAILABLE),0) AS total_avail
                  FROM ST_STOCKRECEIPTDETAIL WITH (UPDLOCK, HOLDLOCK)
                  WHERE STOCK_NUMBER = ?";
    $stmtAvail = sqlsrv_query($conn, $sqlAvail, [$stockNo]);
    $availRow  = $stmtAvail ? sqlsrv_fetch_array($stmtAvail, SQLSRV_FETCH_ASSOC) : null;
    $totalAvail = intval($availRow['total_avail'] ?? 0);
    if ($totalAvail < $qty) {
        sqlsrv_rollback($conn);
        echo json_encode(['error' => 'Not enough stock in hand for ' . $stockNo . ' after reversing the original bill — edit cancelled']);
        exit;
    }

    $stmtD = sqlsrv_query($conn,
        "INSERT INTO trans_detail (Trans_no, stock_number, quantity, Price_PerItem, amount, User_id, Status, PPrice_amount)
         VALUES (?, ?, ?, ?, ?, ?, 'Active', ?)",
        [$transNo, $stockNo, $qty, $price, $amount, $userId, $pPriceAmount]);
    if (!$stmtD) {
        sqlsrv_rollback($conn);
        $err = sqlsrv_errors();
        echo json_encode(['error' => $err[0]['message'] ?? 'Detail insert failed']);
        exit;
    }

    $remaining = $qty;
    $primaryInvoiceNo = null;
    $stmtBatches = sqlsrv_query($conn,
        "SELECT Invoice_no, STOCK_NUMBER, BATCH_NO, ITEMS_AVAILABLE FROM ST_STOCKRECEIPTDETAIL
         WHERE STOCK_NUMBER = ? AND ITEMS_AVAILABLE > 0 ORDER BY EXPIRY_DATE ASC", [$stockNo]);
    if ($stmtBatches) {
        while ($remaining > 0 && ($batch = sqlsrv_fetch_array($stmtBatches, SQLSRV_FETCH_ASSOC))) {
            $deduct = min($remaining, intval($batch['ITEMS_AVAILABLE']));
            if ($deduct > 0) {
                if ($primaryInvoiceNo === null) { $primaryInvoiceNo = $batch['Invoice_no']; }
                sqlsrv_query($conn,
                    "UPDATE ST_STOCKRECEIPTDETAIL SET ITEMS_AVAILABLE = ITEMS_AVAILABLE - ?
                     WHERE Invoice_no = ? AND STOCK_NUMBER = ? AND BATCH_NO = ?",
                    [$deduct, $batch['Invoice_no'], $batch['STOCK_NUMBER'], $batch['BATCH_NO']]);
                $remaining -= $deduct;
            }
        }
    }
    if ($primaryInvoiceNo !== null) {
        sqlsrv_query($conn, "UPDATE trans_detail SET Invoice_No = ? WHERE Trans_no = ? AND stock_number = ?",
            [$primaryInvoiceNo, $transNo, $stockNo]);
    }
}

// Reconcile QTY_INHAND for every stock number touched by either the old or
// new line items -- an item removed entirely during the edit still needs
// its QTY_INHAND refreshed after the reversal above.
foreach ($oldLines as $old) { $touchedStock[$old['stock_number']] = true; }
foreach (array_keys($touchedStock) as $stockNo) {
    sqlsrv_query($conn,
        "UPDATE Item_Stock SET QTY_INHAND = ISNULL((SELECT SUM(ITEMS_AVAILABLE) FROM ST_STOCKRECEIPTDETAIL WHERE STOCK_NUMBER = ?),0) WHERE STOCK_NUMBER = ?",
        [$stockNo, $stockNo]);
}

// --- Update the bill header (Trans_no and Trans_date stay the same) ---
$sqlU = "UPDATE [Transaction] SET
             Invoice_reference = ?, Customer_id = ?, Cust_name = ?, Cust_telno = ?, Trans_type = ?,
             Trans_amount = ?, Disc_percentage = ?, Tax_status = ?,
             Gross_amount = ?, Paid_amount = ?, Balance_amount = ?, User_id = ?,
             Sale_Person_id = ?, Description = ?, Remarks = ?,
             Modified_By = ?, Modified_Date = GETDATE()
         WHERE Trans_no = ?";
$stmtU = sqlsrv_query($conn, $sqlU, [
    $invoiceRef, $custId, $custName, $custTel, $transType,
    $net, $discPct, $taxStatus,
    $gross, $paid, $balance, $userId,
    $salePersonId, $description, $remarks,
    $userId,
    $transNo
]);
if (!$stmtU) {
    sqlsrv_rollback($conn);
    $err = sqlsrv_errors();
    echo json_encode(['error' => $err[0]['message'] ?? 'Header update failed']);
    exit;
}

sqlsrv_commit($conn);
sqlsrv_close($conn);
echo json_encode(['success' => true, 'trans_no' => $transNo, 'skipped_reversals' => $skippedReversals]);
