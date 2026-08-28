<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

$data    = json_decode(file_get_contents('php://input'), true);
$stockNo = intval($data['stock_number'] ?? 0);
if (!$stockNo) { echo json_encode(['error' => 'No item selected']); exit; }

$sql  = "DELETE FROM Item_Stock WHERE STOCK_NUMBER=?";
$stmt = sqlsrv_query($conn, $sql, [$stockNo]);
if (!$stmt) {
    $err = sqlsrv_errors()[0]['message'] ?? 'Delete failed';
    if (stripos($err, 'REFERENCE constraint') !== false) {
        $err = 'Cannot remove this item - it is linked to existing transactions or stock receipts.';
    }
    echo json_encode(['error' => $err]);
    exit;
}
sqlsrv_close($conn);
echo json_encode(['success' => true]);
