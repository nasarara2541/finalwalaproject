<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';
$sql  = "SELECT TOP 100 Invoice_no, SUPPLIER_CODE, STATUS, TOTAL_AMOUNT,
          CONVERT(VARCHAR,INVOICE_DATE,103) AS INVOICE_DATE,
          CONVERT(VARCHAR,RECEIVED_DATE,103) AS RECEIVED_DATE,
          RECEIVED_BY
         FROM ST_STOCKRECEIPT ORDER BY Invoice_no DESC";
$stmt = sqlsrv_query($conn,$sql);
$rows = [];
if ($stmt) { while($r=sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC)) $rows[]=$r; }
sqlsrv_close($conn);
echo json_encode($rows);
