<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

$q    = isset($_GET['q']) ? trim($_GET['q']) : '';
$term = '%' . $q . '%';

// BARCODE is a real Item_Stock column (BIGINT) that was never wired into any
// search before -- matched here too so a barcode scan/typed value finds the
// item via the exact same endpoint as the normal name search, no separate
// API needed. TRY_CONVERT so a non-numeric name search still works (returns
// NULL instead of erroring), never matching BARCODE for those queries.
// BRAND_NAME is matched alongside ITEM_NAME (not ITEM_NAME alone) so a Med
// Stock cashier can find an item by its commercial name (e.g. "Panadol")
// and not just its generic/chemical name (e.g. "Paracetamol") -- same
// convention already used by search_stock_search.php and
// get_all_stock_items.php.
$sql  = "SELECT TOP 500
             STOCK_NUMBER,
             BRAND_NAME,
             ITEM_NAME,
             ITEM_TYPE,
             VOLUME_L,
             PRICE,
             QTY_INHAND,
             UNITS_PERITEM,
             UNIT_TYPE,
             PERCENTAGE_DISC,
             SIZE_DESC
         FROM Item_Stock
         WHERE AVAILABLE_STATUS = 'Active'
           AND (ITEM_NAME LIKE ? OR BRAND_NAME LIKE ? OR BARCODE = TRY_CONVERT(BIGINT, ?))
         ORDER BY CASE WHEN QTY_INHAND > 0 THEN 0 ELSE 1 END, BRAND_NAME";

$stmt   = sqlsrv_query($conn, $sql, [$term, $term, $q]);
$rows   = [];
if ($stmt) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $rows[] = $row;
    }
}
sqlsrv_close($conn);
echo json_encode($rows);