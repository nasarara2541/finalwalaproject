<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/access.php';

if (!canAccess('inventory')) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

$term = trim($_GET['q'] ?? '');
// Escape LIKE wildcards in the user's own input so a literal % or _ in a
// search term doesn't act as a wildcard.
$escaped = str_replace(['%', '_'], ['[%]', '[_]'], $term);
$like = '%' . $escaped . '%';

$sql = "SELECT TOP (500)
            STOCK_NUMBER AS stock_number,
            LTRIM(RTRIM(COALESCE(BRAND_NAME, '') + ' ' + COALESCE(ITEM_NAME, ''))) AS item_name,
            COALESCE(QTY_INHAND, 0) AS qty_inhand
        FROM Item_Stock
        WHERE
            ? = ''
            OR CONVERT(VARCHAR(20), STOCK_NUMBER) LIKE ?
            OR ITEM_NAME LIKE ?
            OR BRAND_NAME LIKE ?
        ORDER BY item_name";

$stmt = sqlsrv_query($conn, $sql, [$term, $like, $like, $like]);
$rows = [];
if ($stmt) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        if ($row['item_name'] === '') {
            $row['item_name'] = (string)$row['stock_number'];
        }
        $rows[] = $row;
    }
} else {
    http_response_code(500);
    echo json_encode(['error' => sqlsrv_errors()[0]['message'] ?? 'Query failed']);
    exit;
}
sqlsrv_close($conn);
echo json_encode($rows);
