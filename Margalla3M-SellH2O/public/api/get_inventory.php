<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';
$sql  = "SELECT STOCK_NUMBER,BRAND_NAME,ITEM_NAME,ITEM_TYPE,VOLUME_L,SIZE_DESC,PRICE,PURCHASE_PRICE,QTY_INHAND,AVAILABLE_STATUS,BARCODE FROM Item_Stock ORDER BY BRAND_NAME";
$stmt = sqlsrv_query($conn, $sql);
$rows = [];
if ($stmt) { while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) $rows[] = $row; }
sqlsrv_close($conn);
echo json_encode($rows);
