<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) { echo json_encode(['error'=>'No data']); exit; }

$invNo    = $data['invoice_no']    ?? null;
$supCode  = intval(trim($data['supplier_code'] ?? ''));
$invDate  = $data['invoice_date']  ?? null;
$recDate  = $data['received_date'] ?? null;
$recBy    = $data['received_by']   ?? '';
$discount = floatval($data['discount']     ?? 0);
$status   = $data['status']   ?? 'Y';
$userId   = $data['user_id']  ?? 'admin';
$lines    = $data['lines']    ?? [];

// Now persisted -- the schema migration (add_stock_receiving_fields.sql)
// has been confirmed run, so these are no longer ignored.
$paymentType   = $data['payment_type']        ?? 'Cash';
$supplierInvNo = trim($data['supplier_invoice_no'] ?? '') ?: null;
$loosePurchase = !empty($data['loose_purchase']) ? 1 : 0;
$addaCharges   = floatval($data['adda_charges']  ?? 0);
$otherCharges  = floatval($data['other_charges'] ?? 0);

// ---- Server-side validation (never trust the client alone) ----
if (!$supCode) { echo json_encode(['error'=>'Supplier Code is required and must be a valid numeric code']); exit; }
if (!$invDate) { echo json_encode(['error'=>'Invoice Date is required']); exit; }
if (!$recDate) { echo json_encode(['error'=>'Received Date is required']); exit; }
if (!is_array($lines) || !count($lines)) { echo json_encode(['error'=>'Add at least one item line']); exit; }

// Total Amount is recomputed from the lines server-side (never trusted from
// the client) so it can never drift from Qty Received × Purch. Price/Item.
$total = 0;
$seen  = [];
foreach ($lines as $i => $l) {
    $stockNo = intval($l['stock_number'] ?? 0);
    $batch   = trim($l['batch_no']     ?? '');
    $expiry  = $l['expiry_date'] ?? '';
    $qtyRec  = intval($l['qty_received'] ?? 0);
    $sPrice  = floatval($l['sale_price'] ?? 0);
    $pPrice  = floatval($l['purch_price'] ?? 0);

    if (!$stockNo) { echo json_encode(['error'=>'Line '.($i+1).': stock item is required']); exit; }
    if (!$batch)   { echo json_encode(['error'=>'Line '.($i+1).': Batch No. is required']); exit; }
    if (!$expiry)  { echo json_encode(['error'=>'Line '.($i+1).': Expiry Date is required']); exit; }
    if ($qtyRec <= 0) { echo json_encode(['error'=>'Line '.($i+1).': Qty Received must be > 0']); exit; }
    if ($sPrice <= 0) { echo json_encode(['error'=>'Line '.($i+1).': Sales Price must be > 0']); exit; }

    if (isset($seen[$stockNo])) { echo json_encode(['error'=>'Duplicate line: stock '.$stockNo.' appears more than once on this invoice']); exit; }
    $seen[$stockNo] = true;
    $total += $qtyRec * $pPrice;
}

sqlsrv_begin_transaction($conn);

// oldBatches[stock|batch] = ['received'=>int, 'available'=>int] - captured before
// the old rows are deleted, so we can tell how much of each batch already sold
// and carry that forward instead of resetting it back to "fully available".
$oldBatches = [];

if (!$invNo) {
    // Invoice_no is no longer an IDENTITY column - the app must supply it.
    // TABLOCKX+HOLDLOCK for the rest of this transaction so two concurrent
    // saves can't compute the same "next" number.
    $sqlNext  = "SELECT ISNULL(MAX(Invoice_no), 0) + 1 AS next_no FROM ST_STOCKRECEIPT WITH (TABLOCKX, HOLDLOCK)";
    $stmtNext = sqlsrv_query($conn, $sqlNext);
    $nextRow  = $stmtNext ? sqlsrv_fetch_array($stmtNext, SQLSRV_FETCH_ASSOC) : null;
    $invNo    = intval($nextRow['next_no'] ?? 0);
    if (!$invNo) {
        sqlsrv_rollback($conn);
        echo json_encode(['error'=>'Could not generate an invoice number']);
        exit;
    }

    $sqlH  = "INSERT INTO ST_STOCKRECEIPT
                  (Invoice_no,INVOICE_DATE,SUPPLIER_CODE,RECEIVED_DATE,TOTAL_AMOUNT,DISCOUNT,STATUS,RECEIVED_BY,User_id,
                   PAYMENT_TYPE,SUPPLIER_INVOICE_NO,LOOSE_PURCHASE,ADDA_CHARGES,OTHER_CHARGES)
              VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
    $stmtH = sqlsrv_query($conn,$sqlH,[$invNo,$invDate,$supCode,$recDate,$total,$discount,$status,$recBy,$userId,
                   $paymentType,$supplierInvNo,$loosePurchase,$addaCharges,$otherCharges]);
    if (!$stmtH) { sqlsrv_rollback($conn); echo json_encode(['error'=>sqlsrv_errors()[0]['message'] ?? 'Could not save invoice (check Supplier Code exists)']); exit; }
} else {
    $invNo = intval($invNo);

    $sqlU  = "UPDATE ST_STOCKRECEIPT
              SET INVOICE_DATE=?,SUPPLIER_CODE=?,RECEIVED_DATE=?,TOTAL_AMOUNT=?,DISCOUNT=?,STATUS=?,RECEIVED_BY=?,
                  PAYMENT_TYPE=?,SUPPLIER_INVOICE_NO=?,LOOSE_PURCHASE=?,ADDA_CHARGES=?,OTHER_CHARGES=?
              WHERE Invoice_no=?";
    $stmtU = sqlsrv_query($conn,$sqlU,[$invDate,$supCode,$recDate,$total,$discount,$status,$recBy,
                  $paymentType,$supplierInvNo,$loosePurchase,$addaCharges,$otherCharges,$invNo]);
    if (!$stmtU) { sqlsrv_rollback($conn); echo json_encode(['error'=>sqlsrv_errors()[0]['message'] ?? 'Could not update invoice (check Supplier Code exists)']); exit; }

    $sqlOld  = "SELECT STOCK_NUMBER, BATCH_NO, ITEMS_RECEIVED, ITEMS_AVAILABLE FROM ST_STOCKRECEIPTDETAIL WHERE Invoice_no=?";
    $stmtOld = sqlsrv_query($conn,$sqlOld,[$invNo]);
    if ($stmtOld) {
        while ($old = sqlsrv_fetch_array($stmtOld,SQLSRV_FETCH_ASSOC)) {
            $oldKey = $old['STOCK_NUMBER'];
            $oldBatches[$oldKey] = [
                'received'  => intval($old['ITEMS_RECEIVED']),
                'available' => intval($old['ITEMS_AVAILABLE']),
            ];
        }
    }

    sqlsrv_query($conn,"DELETE FROM ST_STOCKRECEIPTDETAIL WHERE Invoice_no=?",[$invNo]);
}

foreach ($lines as $l) {
    $stockNo  = trim($l['stock_number']);
    $qtyRec   = intval($l['qty_received']);
    $expiry   = $l['expiry_date'] ?: null;
    $batch    = trim($l['batch_no']);
    $units    = intval($l['units_peritem']) ?: 1;
    $sPrice   = floatval($l['sale_price']);
    $pPrice   = floatval($l['purch_price']);

    // Bonus boxes are free stock from the supplier, on top of the paid
    // quantity -- physically real inventory that can be sold, so they're
    // added to what actually lands in the batch ledger. They do NOT affect
    // cost: $total above (and Amount below) is still qty_received x price
    // only, since bonus units cost nothing. Recomputed here server-side,
    // not trusted from the client, same as everything else in this file.
    $bonusQty = intval($l['bonus_qty'] ?? 0);
    $discPct  = floatval($l['disc_pct'] ?? 0);
    $gstPct   = floatval($l['gst_pct']  ?? 0);
    $lineAmount = $qtyRec * $pPrice;
    $discAmount = $lineAmount * $discPct / 100;
    $gstAmount  = ($lineAmount - $discAmount) * $gstPct / 100;

    // QTY Received on the form is entered in BOXES/CARTONS; everything actually
    // sold and tracked in the batch ledger (ITEMS_RECEIVED/ITEMS_AVAILABLE) is in
    // pieces/bottles, since that's the unit POS sells and deducts in. Convert
    // once here so the two stay in the same unit for the rest of this file.
    $qtyRecPieces = ($qtyRec + $bonusQty) * $units;

    // ITEMS_AVAILABLE is the live remaining quantity of THIS batch (decreases as
    // it sells via POS). A brand new batch starts fully available; if this line
    // matches a batch that existed before this save (a Modify), carry forward
    // however much of it has already sold instead of resetting to full.
    $oldKey = $stockNo;
    if (isset($oldBatches[$oldKey])) {
        $sold = max(0, $oldBatches[$oldKey]['received'] - $oldBatches[$oldKey]['available']);
        $qtyAvail = max(0, $qtyRecPieces - $sold);
    } else {
        $qtyAvail = $qtyRecPieces;
    }

    // Always derive these server-side from the validated price/units - never
    // trust the client's copy, which only recomputes them when the item is
    // first selected and goes stale the moment the price is edited afterward.
    // Sales/Purch Price entered on the form are PER BOX; dividing by units
    // gives the per-bottle price. PRICE_PERITEM/PPRICE_PERITEM store this
    // per-bottle value (matching Item_Stock.PRICE/PURCHASE_PRICE and the
    // schema's own sample data), NOT the raw per-box price - ITEMS_RECEIVED
    // is in pieces, so Amount everywhere must be pieces x per-bottle price.
    // Price_PerUnit/PPrice_PerUnit are a further per-item subdivision,
    // matching the same convention the sample data uses.
    $pricePerItem  = $units > 0 ? $sPrice/$units : 0;
    $ppricePerItem = $units > 0 ? $pPrice/$units : 0;
    $pricePerUnit  = $units > 0 ? $pricePerItem/$units : 0;
    $ppricePerUnit = $units > 0 ? $ppricePerItem/$units : 0;
    $sqlD  = "INSERT INTO ST_STOCKRECEIPTDETAIL
                  (Invoice_no,STOCK_NUMBER,PRICE_PERITEM,ITEMS_RECEIVED,ITEMS_AVAILABLE,EXPIRY_DATE,BATCH_NO,UNITS_PERITEM,PPRICE_PERITEM,Update_Status,Record_date,Price_PerUnit,PPrice_PerUnit,
                   BONUS_QTY,LINE_DISC_PERCENT,LINE_DISC_AMOUNT,Tax_Percentage,Tax_amount)
              VALUES (?,?,?,?,?,?,?,?,?,'Saved',GETDATE(),?,?,?,?,?,?,?)";
    $stmtD = sqlsrv_query($conn,$sqlD,[$invNo,$stockNo,$pricePerItem,$qtyRecPieces,$qtyAvail,$expiry,$batch,$units,$ppricePerItem,$pricePerUnit,$ppricePerUnit,
                   $bonusQty,$discPct,$discAmount,$gstPct,$gstAmount]);
    if (!$stmtD) { sqlsrv_rollback($conn); $err = sqlsrv_errors(); echo json_encode(['error'=>$err[0]['message'] ?? 'Detail insert failed (this stock number may already exist on this invoice)']); exit; }

    // POS charges Item_Stock.PRICE per bottle sold - must be the per-bottle
    // price, not the per-box price the receiving form was filled in with.
    // PURCHASE_PRICE is kept in sync the same way so save_transaction.php's
    // margin tracking (PPrice_amount) reflects the latest batch's cost.
    //
    // Log to Item_Price_History only when the sale price actually changes -
    // reading the old value first so a re-save at the same price (e.g.
    // Modify-with-no-changes) doesn't write a pointless "changed from X to
    // X" row.
    $stmtOldPrice = sqlsrv_query($conn, "SELECT PRICE FROM Item_Stock WHERE STOCK_NUMBER = ?", [$stockNo]);
    $oldPriceRow  = $stmtOldPrice ? sqlsrv_fetch_array($stmtOldPrice, SQLSRV_FETCH_ASSOC) : null;
    $oldPrice     = $oldPriceRow ? floatval($oldPriceRow['PRICE']) : null;

    // Whoever we most recently received this item from is who currently
    // supplies it -- same most-recent-wins convention as PRICE/PURCHASE_PRICE
    // just above, using the already-validated header Supplier Code.
    sqlsrv_query($conn,
        "UPDATE Item_Stock SET PRICE = ?, PURCHASE_PRICE = ?, SUPPLIER_CODE = ? WHERE STOCK_NUMBER = ?",
        [$pricePerItem, $ppricePerItem, $supCode, $stockNo]
    );

    if ($oldPrice === null || round($oldPrice, 2) !== round($pricePerItem, 2)) {
        sqlsrv_query($conn,
            "INSERT INTO Item_Price_History (STOCK_NUMBER, Old_Price, New_Price, Changed_Date, Source) VALUES (?, ?, ?, GETDATE(), 'Stock Receiving')",
            [$stockNo, $oldPrice, $pricePerItem]
        );
    }
}

// Reconcile Item_Stock.QTY_INHAND for every stock number touched by this
// invoice (new/edited lines, plus any lines removed during a Modify) to the
// batch ledger's true total, rather than trusting an independently
// incremented/decremented counter that can drift from the real batch data.
$affectedStockNumbers = [];
foreach ($lines as $l) { $affectedStockNumbers[trim($l['stock_number'])] = true; }
foreach ($oldBatches as $stockNo => $info) { $affectedStockNumbers[$stockNo] = true; }
foreach (array_keys($affectedStockNumbers) as $stockNo) {
    sqlsrv_query($conn,
        "UPDATE Item_Stock SET QTY_INHAND = ISNULL((SELECT SUM(ITEMS_AVAILABLE) FROM ST_STOCKRECEIPTDETAIL WHERE STOCK_NUMBER = ?),0) WHERE STOCK_NUMBER = ?",
        [$stockNo, $stockNo]
    );
}

sqlsrv_commit($conn);
sqlsrv_close($conn);
echo json_encode(['success'=>true,'invoice_no'=>$invNo]);
