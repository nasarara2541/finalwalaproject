<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/auth_check.php';
require_once __DIR__ . '/../../../includes/access.php';
require_once __DIR__ . '/../../config/database.php';
if (!canAccess('inventory')) { echo json_encode(['success' => false, 'error' => 'Access denied']); exit; }

// purchase_order.js calls this (?stock_no=X) expecting {success, data:{...}}
// with exactly these 6 fields -- same situation as available_products.php,
// referenced by his JS but never included among the given files.
$stockNo = isset($_GET['stock_no']) ? (int)$_GET['stock_no'] : 0;
if (!$stockNo) {
    echo json_encode(['success' => false, 'error' => 'No stock number given']);
    exit;
}

$conn = getDbConnection();
$sql = "SELECT STOCK_NUMBER, ITEM_NAME, QTY_INHAND, PRICE, PURCHASE_PRICE, UNITS_PERITEM
        FROM Item_Stock WHERE STOCK_NUMBER = ?";
$stmt = sqlsrv_query($conn, $sql, [$stockNo]);
if ($stmt === false) {
    echo json_encode(['success' => false, 'error' => sqlsrv_errors()[0]['message'] ?? 'Query failed']);
    exit;
}
$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
if (!$row) {
    echo json_encode(['success' => false, 'error' => 'Item not found']);
    exit;
}
echo json_encode(['success' => true, 'data' => $row]);
