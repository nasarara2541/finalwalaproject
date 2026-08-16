<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

$sql  = "SELECT TOP 500
             s.STOCK_NUMBER, s.BRAND_NAME, s.ITEM_NAME, s.ITEM_TYPE, s.STOCK_TYPE,
             s.VOLUME_L, s.UNITS_PERITEM, s.BARCODE, s.SIZE_DESC, s.AVAILABLE_STATUS,
             s.UNIT_TYPE, s.LOCATION, s.MANUFACTURE_NO, m.M_Name
         FROM Item_Stock s
         LEFT JOIN Manufacture m ON s.MANUFACTURE_NO = m.Manufacture_no
         ORDER BY s.BRAND_NAME";

$stmt = sqlsrv_query($conn, $sql);
$rows = [];
if ($stmt) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $rows[] = $row;
    }
} else {
    echo json_encode(['error' => sqlsrv_errors()[0]['message'] ?? 'Query failed']);
    sqlsrv_close($conn);
    exit;
}
sqlsrv_close($conn);
echo json_encode($rows);
