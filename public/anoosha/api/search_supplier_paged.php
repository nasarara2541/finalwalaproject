<?php
require '../config/db.php';
require_once __DIR__ . '/../../includes/access.php';
header('Content-Type: application/json');
if (!canAccess('inventory')) { http_response_code(403); echo json_encode(['error' => 'Access denied']); exit; }

$q      = isset($_GET['q']) ? trim($_GET['q']) : '';
$offset = isset($_GET['offset']) ? max(0, (int)$_GET['offset']) : 0;
$limit  = 100;
if ($q === '') { echo json_encode(['results' => [], 'hasMore' => false]); exit; }

$sql = "SELECT SUPPLIER_CODE, SUPPLIER_NAME FROM ST_Supplier WHERE SUPPLIER_NAME LIKE ?
        ORDER BY SUPPLIER_NAME
        OFFSET ? ROWS FETCH NEXT ? ROWS ONLY";
$stmt = sqlsrv_query($conn, $sql, ["%$q%", $offset, $limit + 1]);
if ($stmt === false) {
    echo json_encode(["success" => false, "message" => "Query failed", "errors" => sqlsrv_errors()]);
    exit;
}
$results = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) { $results[] = $row; }
$hasMore = count($results) > $limit;
if ($hasMore) { array_pop($results); }
echo json_encode(['results' => $results, 'hasMore' => $hasMore], JSON_INVALID_UTF8_SUBSTITUTE);
?>