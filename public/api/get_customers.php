<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

$sql  = "SELECT Customer_id, Cust_name, Address, Contact_no FROM Customer ORDER BY Cust_name";
$stmt = sqlsrv_query($conn, $sql);
$rows = [];
if ($stmt) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $rows[] = $row;
    }
}
sqlsrv_close($conn);
echo json_encode($rows);
