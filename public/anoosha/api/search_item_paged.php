<?php
require '../config/db.php';
require_once __DIR__ . '/../../includes/access.php';
header('Content-Type: application/json');
if (!canAccess('inventory')) { http_response_code(403); echo json_encode(['error' => 'Access denied']); exit; }

$q      = isset($_GET['q']) ? $_GET['q'] : '';
$offset = isset($_GET['offset']) ? max(0, (int)$_GET['offset']) : 0;
$limit  = 100;
if (trim($q) === '') { echo json_encode(['results' => [], 'hasMore' => false]); exit; }

$sql = "SELECT STOCK_NUMBER, ITEM_NAME, ITEM_TYPE, PRICE, QTY_INHAND, PURCHASE_PRICE, UNITS_PERITEM
        FROM Item_Stock WHERE ITEM_NAME LIKE ?
        ORDER BY ITEM_NAME
        OFFSET ? ROWS FETCH NEXT ? ROWS ONLY
        OPTION (MAXDOP 1, MIN_GRANT_PERCENT = 0, MAX_GRANT_PERCENT = 1)";
$params = ["%$q%", $offset, $limit + 1];
$stmt = sqlsrv_query($conn, $sql, $params);
if ($stmt === false) {
    echo json_encode(["success" => false, "message" => "Query failed", "errors" => sqlsrv_errors()]);
    exit;
}
$results = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) { $results[] = $row; }
$hasMore = count($results) > $limit;
if ($hasMore) { array_pop($results); }
$json = json_encode(['results' => $results, 'hasMore' => $hasMore], JSON_INVALID_UTF8_SUBSTITUTE);
echo $json !== false ? $json : json_encode(["success" => false, "message" => json_last_error_msg()]);
?>