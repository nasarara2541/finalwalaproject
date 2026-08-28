<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

// The OPTION hint at the end is the same query-resource cap Anoosha found
// for her own item search -- keeps this fast without capping row count,
// which would matter here: pos.php/stock_receiving.php both use every row
// of this to look up a SPECIFIC item's nearest-expiry batch, so truncating
// it could silently make some items' expiry info vanish.
$sql = "SELECT
            d.Invoice_no,
            d.STOCK_NUMBER,
            s.BRAND_NAME,
            s.ITEM_NAME,
            d.ITEMS_RECEIVED,
            d.ITEMS_AVAILABLE,
            CONVERT(VARCHAR(10), d.EXPIRY_DATE, 23) AS EXPIRY_DATE,
            d.BATCH_NO,
            d.UNITS_PERITEM,
            d.PRICE_PERITEM,
            d.PPRICE_PERITEM
        FROM ST_STOCKRECEIPTDETAIL d
        JOIN Item_Stock s ON s.STOCK_NUMBER = d.STOCK_NUMBER
        WHERE d.ITEMS_AVAILABLE > 0
          AND d.EXPIRY_DATE IS NOT NULL
        ORDER BY d.EXPIRY_DATE ASC
        OPTION (MAXDOP 1, MIN_GRANT_PERCENT = 0, MAX_GRANT_PERCENT = 1)";

$stmt = sqlsrv_query($conn, $sql);
$rows = [];
if ($stmt) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $rows[] = $row;
    }
}
sqlsrv_close($conn);
echo json_encode($rows);