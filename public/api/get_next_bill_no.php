<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

// Read-only preview of what Trans_no the NEXT sale will get. Trans_no isn't
// an IDENTITY column -- save_transaction.php only computes the real value
// at save time, under a table lock, which is the only moment it's
// authoritative (two cashiers mid-sale at once could both see the same
// preview here, but whoever actually saves first still gets it correctly,
// and the second save's own lock recomputes a fresh number for them). This
// exists purely so the POS screen has something honest to show before that,
// instead of a dash.
$sql = "SELECT ISNULL(MAX(Trans_no), 0) + 1 AS next_no FROM [Transaction]";
$stmt = sqlsrv_query($conn, $sql);
$row = $stmt ? sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC) : null;
sqlsrv_close($conn);
echo json_encode(['next_no' => intval($row['next_no'] ?? 1)]);
