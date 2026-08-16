<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';
$sql  = "SELECT TOP 200 ID,Item_name,Demand_qty,CONVERT(VARCHAR,Booking_date,103) AS Booking_date,CONVERT(VARCHAR,Demand_date,103) AS Demand_date,Supplier_code,Prod_Type,Status,Comments FROM Item_Booking ORDER BY ID DESC";
$stmt = sqlsrv_query($conn, $sql);
$rows = [];
if ($stmt) { while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) $rows[] = $row; }
sqlsrv_close($conn);
echo json_encode($rows);
