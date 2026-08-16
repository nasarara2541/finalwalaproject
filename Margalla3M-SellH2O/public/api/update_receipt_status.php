<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

$data = json_decode(file_get_contents('php://input'), true);
$id     = intval($data['invoice_no'] ?? 0);
$status = trim($data['status'] ?? 'Y');
if (!$id) { echo json_encode(['error'=>'No invoice ID']); exit; }

$stmt = sqlsrv_query($conn, "UPDATE ST_STOCKRECEIPT SET STATUS=? WHERE Invoice_no=?", [$status, $id]);
sqlsrv_close($conn);
echo json_encode($stmt ? ['success'=>true] : ['error'=>sqlsrv_errors()[0]['message'] ?? 'Update failed']);
