<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

// Distinct non-blank locations already used on stock items - feeds the
// Location autocomplete on manufacture.php so users pick instead of retype.
$sql  = "SELECT DISTINCT LOCATION
         FROM Item_Stock
         WHERE LOCATION IS NOT NULL AND LTRIM(RTRIM(LOCATION)) <> ''
         ORDER BY LOCATION";
$stmt = sqlsrv_query($conn, $sql);
$rows = [];
if ($stmt) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $rows[] = $row['LOCATION'];
    }
} else {
    echo json_encode(['error' => sqlsrv_errors()[0]['message'] ?? 'Query failed']);
    sqlsrv_close($conn);
    exit;
}
sqlsrv_close($conn);
echo json_encode($rows);
