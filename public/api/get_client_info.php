<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

$sql  = "SELECT TOP 1 Client_name, Address, City, Email, Contact_no FROM Client ORDER BY Client_id";
$stmt = sqlsrv_query($conn, $sql);
$row  = $stmt ? sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC) : null;
sqlsrv_close($conn);
echo json_encode($row ?: []);
