<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

// Same matching behaviour as api/search_items.php (name/barcode, Active +
// in-stock only), but joined out to Manufacture/ST_Supplier and including
// LOCATION/UNITS_PERITEM/SAFETY_LEVEL - the extra columns sale_items.php's
// reference layout shows that the plain Available Products grid on pos.php
// never needed. Deliberately a separate endpoint (not a shared query with
// search_items.php) so nothing here can ever affect pos.php's Sale tab.
// BRAND_NAME matched alongside ITEM_NAME for the same reason as
// search_items.php -- a Med Stock cashier searching "Panadol" (commercial
// name) shouldn't need to know the generic name "Paracetamol" instead.
$q    = isset($_GET['q']) ? trim($_GET['q']) : '';
$term = '%' . $q . '%';

$sql  = "SELECT TOP 500
             s.STOCK_NUMBER,
             s.BARCODE,
             s.BRAND_NAME,
             s.ITEM_NAME,
             s.ITEM_TYPE,
             s.VOLUME_L,
             s.SIZE_DESC,
             s.PRICE,
             s.QTY_INHAND,
             s.UNITS_PERITEM,
             s.UNIT_TYPE,
             s.LOCATION,
             sup.SUPPLIER_NAME AS COMPANY_NAME,
             m.M_Name           AS MANUFACTURE_NAME
         FROM Item_Stock s
         LEFT JOIN ST_Supplier sup ON sup.SUPPLIER_CODE = s.SUPPLIER_CODE
         LEFT JOIN Manufacture m   ON m.Manufacture_no  = s.MANUFACTURE_NO
         WHERE s.AVAILABLE_STATUS = 'Active'
           AND s.QTY_INHAND > 0
           AND (s.ITEM_NAME LIKE ? OR s.BRAND_NAME LIKE ? OR s.BARCODE = TRY_CONVERT(BIGINT, ?))
         ORDER BY s.BRAND_NAME";

$stmt = sqlsrv_query($conn, $sql, [$term, $term, $q]);
$rows = [];
if ($stmt) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $rows[] = $row;
    }
}
sqlsrv_close($conn);
echo json_encode($rows);
