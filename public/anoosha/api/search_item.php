<?php
require '../config/db.php';
require_once __DIR__ . '/../../includes/access.php';
header('Content-Type: application/json');
if (!canAccess('inventory')) { http_response_code(403); echo json_encode(['error' => 'Access denied']); exit; }

$q = isset($_GET['q']) ? $_GET['q'] : '';
$sql = "SELECT STOCK_NUMBER, ITEM_NAME, ITEM_TYPE, PRICE, QTY_INHAND, PURCHASE_PRICE, UNITS_PERITEM FROM Item_Stock WHERE ITEM_NAME LIKE ? OPTION (MAXDOP 1, MIN_GRANT_PERCENT = 0, MAX_GRANT_PERCENT = 1)";
$params = ["%$q%"];
$stmt = sqlsrv_query($conn, $sql, $params);
if ($stmt === false) {
    echo json_encode(["success" => false, "message" => "Query failed", "errors" => sqlsrv_errors()]);
    exit;
}
$results = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $results[] = $row;
}
$json = json_encode($results, JSON_INVALID_UTF8_SUBSTITUTE);
echo $json !== false ? $json : json_encode(["success" => false, "message" => json_last_error_msg()]);
?>