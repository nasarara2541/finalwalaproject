<?php
require '../config/db.php';
require_once __DIR__ . '/../../includes/access.php';
header('Content-Type: application/json');
if (!canAccess('inventory')) { http_response_code(403); echo json_encode(['error' => 'Access denied']); exit; }

$from = isset($_GET['from']) ? $_GET['from'] : null;
$to   = isset($_GET['to'])   ? $_GET['to']   : null;

// show_unavailable: '1' (default) includes items that are ONLY unavailable
// (qty >= threshold but status = 'N'). '0' excludes those, but items with
// qty below the threshold always show regardless of this flag.
$showUnavailable = isset($_GET['show_unavailable']) ? $_GET['show_unavailable'] : '1';
$showUnavailable = ($showUnavailable === '0') ? 0 : 1;

// qty_less_than: threshold for "low qty" (strictly less than). Must be a
// positive integer; default 1 (i.e. QTY_INHAND < 1, meaning <= 0).
$qtyLessThan = isset($_GET['qty_less_than']) ? (int)$_GET['qty_less_than'] : 1;
if ($qtyLessThan < 1) $qtyLessThan = 1;

// "Short" = qty in hand below threshold, OR (if show_unavailable) manually marked unavailable
$sql = "SELECT
            i.STOCK_NUMBER,
            ISNULL(i.BRAND_NAME,'')        AS BRAND_NAME,
            ISNULL(i.ITEM_NAME,'')         AS ITEM_NAME,
            ISNULL(i.ITEM_TYPE,'')         AS ITEM_TYPE,
            ISNULL(i.AVAILABLE_STATUS,'Y') AS AVAILABLE_STATUS,
            ISNULL(i.QTY_INHAND,0)         AS QTY_INHAND,
            ISNULL(m.M_Name,'')            AS MANUFACTURER_NAME,
            ISNULL(i.LOCATION,'')          AS LOCATION,
            (SELECT MAX(d.Record_date) FROM ST_STOCKRECEIPTDETAIL d WHERE d.STOCK_NUMBER = i.STOCK_NUMBER) AS LAST_RECEIVED
        FROM Item_Stock i
        LEFT JOIN Manufacture m ON m.Manufacture_no = i.MANUFACTURE_NO
        WHERE (ISNULL(i.QTY_INHAND,0) < ?" . ($showUnavailable ? " OR ISNULL(i.AVAILABLE_STATUS,'Y') = 'N'" : "") . ")";

$params = [$qtyLessThan];
if ($from) {
    $sql .= " AND (SELECT MAX(d.Record_date) FROM ST_STOCKRECEIPTDETAIL d WHERE d.STOCK_NUMBER = i.STOCK_NUMBER) >= ?";
    $params[] = $from;
}
if ($to) {
    $sql .= " AND (SELECT MAX(d.Record_date) FROM ST_STOCKRECEIPTDETAIL d WHERE d.STOCK_NUMBER = i.STOCK_NUMBER) <= ?";
    $params[] = $to;
}
$sql .= " ORDER BY TRY_CAST(i.STOCK_NUMBER AS INT), i.STOCK_NUMBER";

$stmt = sqlsrv_query($conn, $sql, $params);
if ($stmt === false) {
    echo json_encode(["success" => false, "message" => "Query failed", "errors" => sqlsrv_errors()]);
    exit;
}
$items = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    if ($row['LAST_RECEIVED'] instanceof DateTime) $row['LAST_RECEIVED'] = $row['LAST_RECEIVED']->format('Y-m-d');
    $items[] = $row;
}
echo json_encode(["success" => true, "items" => $items], JSON_INVALID_UTF8_SUBSTITUTE);
?>