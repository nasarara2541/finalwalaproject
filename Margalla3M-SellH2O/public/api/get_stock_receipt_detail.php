<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';
$id = intval($_GET['id'] ?? 0);
if (!$id) { echo json_encode(['error'=>'No ID']); exit; }

$sqlH = "SELECT Invoice_no, SUPPLIER_CODE, STATUS, TOTAL_AMOUNT, DISCOUNT, RECEIVED_BY,
          CONVERT(VARCHAR(10),INVOICE_DATE,23)  AS INVOICE_DATE,
          CONVERT(VARCHAR(10),RECEIVED_DATE,23) AS RECEIVED_DATE,
          PAYMENT_TYPE, SUPPLIER_INVOICE_NO, LOOSE_PURCHASE, ADDA_CHARGES, OTHER_CHARGES
         FROM ST_STOCKRECEIPT WHERE Invoice_no=?";
$stmtH = sqlsrv_query($conn,$sqlH,[$id]);
$header = $stmtH ? sqlsrv_fetch_array($stmtH,SQLSRV_FETCH_ASSOC) : null;

$sqlD = "SELECT d.STOCK_NUMBER, d.PRICE_PERITEM, d.ITEMS_RECEIVED, d.ITEMS_AVAILABLE,
          CONVERT(VARCHAR(10),d.EXPIRY_DATE,23) AS EXPIRY_DATE,
          d.BATCH_NO, d.UNITS_PERITEM, d.PPRICE_PERITEM, d.Update_Status,
          d.BONUS_QTY, d.LINE_DISC_PERCENT, d.LINE_DISC_AMOUNT, d.Tax_Percentage, d.Tax_amount,
          s.BRAND_NAME, s.ITEM_NAME, s.VOLUME_L, s.QTY_INHAND
         FROM ST_STOCKRECEIPTDETAIL d
         JOIN Item_Stock s ON s.STOCK_NUMBER=d.STOCK_NUMBER
         WHERE d.Invoice_no=?
         ORDER BY d.EXPIRY_DATE ASC";
$stmtD = sqlsrv_query($conn,$sqlD,[$id]);
$detail = [];
if ($stmtD) { while($r=sqlsrv_fetch_array($stmtD,SQLSRV_FETCH_ASSOC)) $detail[]=$r; }
sqlsrv_close($conn);
echo json_encode(['header'=>$header,'detail'=>$detail]);
