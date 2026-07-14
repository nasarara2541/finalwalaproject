<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) { echo json_encode(['error'=>'No data']); exit; }

$invNo    = $data['invoice_no']    ?? null;
$supCode  = $data['supplier_code'] ?? '';
$invDate  = $data['invoice_date']  ?? null;
$recDate  = $data['received_date'] ?? null;
$recBy    = $data['received_by']   ?? '';
$total    = floatval($data['total_amount'] ?? 0);
$discount = floatval($data['discount']     ?? 0);
$status   = $data['status']   ?? 'Y';
$userId   = $data['user_id']  ?? 'admin';
$lines    = $data['lines']    ?? [];

sqlsrv_begin_transaction($conn);

if (!$invNo) {
    $sqlH  = "INSERT INTO ST_STOCKRECEIPT
                  (INVOICE_DATE,SUPPLIER_CODE,RECEIVED_DATE,TOTAL_AMOUNT,DISCOUNT,STATUS,RECEIVED_BY,User_id)
              VALUES (?,?,?,?,?,?,?,?);
              SELECT SCOPE_IDENTITY() AS new_id;";
    $stmtH = sqlsrv_query($conn,$sqlH,[$invDate,$supCode,$recDate,$total,$discount,$status,$recBy,$userId]);
    if (!$stmtH) { sqlsrv_rollback($conn); echo json_encode(['error'=>sqlsrv_errors()[0]['message']]); exit; }
    sqlsrv_next_result($stmtH);
    $row   = sqlsrv_fetch_array($stmtH,SQLSRV_FETCH_ASSOC);
    $invNo = intval($row['new_id']);
} else {
    $invNo = intval($invNo);

    $sqlU  = "UPDATE ST_STOCKRECEIPT
              SET INVOICE_DATE=?,SUPPLIER_CODE=?,RECEIVED_DATE=?,TOTAL_AMOUNT=?,DISCOUNT=?,STATUS=?,RECEIVED_BY=?
              WHERE Invoice_no=?";
    $stmtU = sqlsrv_query($conn,$sqlU,[$invDate,$supCode,$recDate,$total,$discount,$status,$recBy,$invNo]);
    if (!$stmtU) { sqlsrv_rollback($conn); echo json_encode(['error'=>sqlsrv_errors()[0]['message']]); exit; }

    $sqlOld  = "SELECT STOCK_NUMBER, ITEMS_RECEIVED FROM ST_STOCKRECEIPTDETAIL WHERE Invoice_no=?";
    $stmtOld = sqlsrv_query($conn,$sqlOld,[$invNo]);
    if ($stmtOld) {
        while ($old = sqlsrv_fetch_array($stmtOld,SQLSRV_FETCH_ASSOC)) {
            sqlsrv_query($conn,
                "UPDATE Item_Stock SET QTY_INHAND = QTY_INHAND - ? WHERE STOCK_NUMBER = ?",
                [intval($old['ITEMS_RECEIVED']), $old['STOCK_NUMBER']]
            );
        }
    }

    sqlsrv_query($conn,"DELETE FROM ST_STOCKRECEIPTDETAIL WHERE Invoice_no=?",[$invNo]);
}

foreach ($lines as $l) {
    $stockNo  = $l['stock_number'];
    $qtyRec   = intval($l['qty_received']);
    $expiry   = $l['expiry_date'] ?: null;
    $batch    = $l['batch_no'];
    $units    = intval($l['units_peritem']) ?: 1;
    $sPrice   = floatval($l['sale_price']);
    $pPrice   = floatval($l['purch_price']);
    $qtyAvail = intval($l['qty_available']);

    $pricePerUnit  = floatval($l['price_perunit']  ?? ($units > 0 ? $sPrice/$units : 0));
    $ppricePerUnit = floatval($l['pprice_perunit'] ?? ($units > 0 ? $pPrice/$units : 0));
    $sqlD  = "INSERT INTO ST_STOCKRECEIPTDETAIL
                  (Invoice_no,STOCK_NUMBER,PRICE_PERITEM,ITEMS_RECEIVED,ITEMS_AVAILABLE,EXPIRY_DATE,BATCH_NO,UNITS_PERITEM,PPRICE_PERITEM,Update_Status,Record_date,Price_PerUnit,PPrice_PerUnit)
              VALUES (?,?,?,?,?,?,?,?,?,'Saved',GETDATE(),?,?)";
    $stmtD = sqlsrv_query($conn,$sqlD,[$invNo,$stockNo,$sPrice,$qtyRec,$qtyAvail,$expiry,$batch,$units,$pPrice,$pricePerUnit,$ppricePerUnit]);
    if (!$stmtD) { sqlsrv_rollback($conn); echo json_encode(['error'=>sqlsrv_errors()[0]['message']]); exit; }

    sqlsrv_query($conn,
        "UPDATE Item_Stock SET QTY_INHAND = QTY_INHAND + ?, PRICE = ? WHERE STOCK_NUMBER = ?",
        [$qtyRec, $sPrice, $stockNo]
    );
}

sqlsrv_commit($conn);
sqlsrv_close($conn);
echo json_encode(['success'=>true,'invoice_no'=>$invNo]);