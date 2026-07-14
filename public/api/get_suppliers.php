<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';
$sql  = "SELECT SUPPLIER_CODE,SUPPLIER_NAME,CONTACT_PERSON,CITY,TELEPHONE_NO,MOBILE_NO,EMAIL,REGION FROM ST_Supplier ORDER BY SUPPLIER_NAME";
$stmt = sqlsrv_query($conn, $sql);
$rows = [];
if ($stmt) { while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) $rows[] = $row; }
sqlsrv_close($conn);
echo json_encode($rows);
