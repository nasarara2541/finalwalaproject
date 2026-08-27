<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/auth_check.php';
require_once __DIR__ . '/../../../includes/access.php';
require_once __DIR__ . '/../../config/database.php';
if (!canAccess('inventory')) { echo json_encode(['success' => false, 'error' => 'Access denied']); exit; }

// purchase_order.js calls this expecting {success, data:[...]} -- it's not
// one of Qasim's 3 given files (his JS references it, but it was never sent
// with the rest). Wired to this app's real Item_Stock, same fields his JS
// actually reads (STOCK_NUMBER/ITEM_NAME/ITEM_TYPE/QTY_INHAND), filtered
// client-side by his own existing code, so the query here just returns
// everything active rather than re-implementing his filter server-side.
$conn = getDbConnection();
$sql = "SELECT STOCK_NUMBER, ITEM_NAME, ITEM_TYPE, QTY_INHAND
        FROM Item_Stock
        WHERE AVAILABLE_STATUS = 'Active'
        ORDER BY ITEM_NAME";
$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    echo json_encode(['success' => false, 'error' => sqlsrv_errors()[0]['message'] ?? 'Query failed']);
    exit;
}
$rows = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $rows[] = $row;
}
echo json_encode(['success' => true, 'data' => $rows]);
