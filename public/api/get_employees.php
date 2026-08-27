<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

// Backs the Sale Person picker on sale_items.php — deliberately not
// admin-gated like get_users.php (which exposes login/role data), this only
// returns the employee list itself.
$sql  = "SELECT Emp_no, Full_Name FROM Employee ORDER BY Full_Name";
$stmt = sqlsrv_query($conn, $sql);
$rows = [];
if ($stmt) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $rows[] = $row;
    }
}
sqlsrv_close($conn);
echo json_encode($rows);
