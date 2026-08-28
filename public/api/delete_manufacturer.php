<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

$data = json_decode(file_get_contents('php://input'), true);
$manufactureNo = intval($data['manufacture_no'] ?? 0);
if (!$manufactureNo) { echo json_encode(['error' => 'No manufacturer selected']); exit; }

$sql  = "DELETE FROM Manufacture WHERE Manufacture_no=?";
$stmt = sqlsrv_query($conn, $sql, [$manufactureNo]);
if (!$stmt) {
    $err = sqlsrv_errors()[0]['message'] ?? 'Delete failed';
    if (stripos($err, 'REFERENCE constraint') !== false) {
        $err = 'Cannot remove this manufacturer - it is linked to existing stock items.';
    }
    echo json_encode(['error' => $err]);
    exit;
}
sqlsrv_close($conn);
echo json_encode(['success' => true]);
