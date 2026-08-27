<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

$sql  = "SELECT Manufacture_no, M_Name, M_ShortName, Address, City, Tel_no FROM Manufacture ORDER BY M_Name";
$stmt = sqlsrv_query($conn, $sql);
$rows = [];
if ($stmt) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $rows[] = $row;
    }
} else {
    echo json_encode(['error' => sqlsrv_errors()[0]['message'] ?? 'Query failed']);
    sqlsrv_close($conn);
    exit;
}
sqlsrv_close($conn);
echo json_encode($rows);
