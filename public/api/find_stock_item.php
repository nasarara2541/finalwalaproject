<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

$stockNo = isset($_GET['stock_number']) ? intval($_GET['stock_number']) : 0;
if (!$stockNo) { echo json_encode(['found' => false]); exit; }

$sql  = "SELECT s.STOCK_NUMBER, s.BRAND_NAME, s.ITEM_NAME, s.ITEM_TYPE, s.STOCK_TYPE,
                 s.VOLUME_L, s.UNITS_PERITEM, s.BARCODE, s.SIZE_DESC, s.AVAILABLE_STATUS,
                 s.UNIT_TYPE, s.LOCATION, s.MANUFACTURE_NO, m.M_Name
          FROM Item_Stock s
          LEFT JOIN Manufacture m ON s.MANUFACTURE_NO = m.Manufacture_no
          WHERE s.STOCK_NUMBER = ?";

$stmt = sqlsrv_query($conn, $sql, [$stockNo]);
if (!$stmt) {
    echo json_encode(['found' => false, 'error' => sqlsrv_errors()[0]['message'] ?? 'Query failed']);
    sqlsrv_close($conn);
    exit;
}
$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
sqlsrv_close($conn);
if ($row) {
    echo json_encode(['found' => true, 'item' => $row]);
} else {
    echo json_encode(['found' => false]);
}
