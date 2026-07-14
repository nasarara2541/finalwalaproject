<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

$q    = isset($_GET['q']) ? trim($_GET['q']) : '';
$term = '%' . $q . '%';

$sql  = "SELECT TOP 500
             STOCK_NUMBER, BRAND_NAME, ITEM_NAME, ITEM_TYPE,
             VOLUME_ML, PRICE, QTY_INHAND, UNITS_PERITEM,
             UNIT_TYPE, PERCENTAGE_DISC, SIZE_DESC, LOCATION
         FROM Item_Stock
         WHERE AVAILABLE_STATUS = 'Active'
           AND (
               STOCK_NUMBER LIKE ?
            OR BRAND_NAME   LIKE ?
            OR ITEM_NAME    LIKE ?
           )
         ORDER BY BRAND_NAME";

$stmt = sqlsrv_query($conn, $sql, [$term, $term, $term]);
$rows = [];
if ($stmt) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $rows[] = $row;
    }
}
sqlsrv_close($conn);
echo json_encode($rows);