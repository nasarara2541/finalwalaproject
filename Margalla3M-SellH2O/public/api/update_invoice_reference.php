<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

$data    = json_decode(file_get_contents('php://input'), true);
$transNo = intval($data['trans_no'] ?? 0);
$ref     = trim($data['invoice_reference'] ?? '');
if (!$transNo) { echo json_encode(['error' => 'No transaction ID']); exit; }

$stmt = sqlsrv_query($conn, "UPDATE [Transaction] SET Invoice_reference=? WHERE Trans_no=?", [$ref, $transNo]);
sqlsrv_close($conn);
echo json_encode($stmt ? ['success' => true] : ['error' => sqlsrv_errors()[0]['message'] ?? 'Update failed']);
