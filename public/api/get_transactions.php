<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

$sql  = "SELECT TOP 100
             Trans_no,
             Cust_name,
             Cust_telno,
             CONVERT(VARCHAR(20), Trans_date, 103) AS Trans_date,
             Trans_type,
             Gross_amount,
             Disc_percentage,
             Trans_amount,
             Paid_amount,
             Balance_amount,
             User_id,
             Branch_code
         FROM [Transaction]
         ORDER BY Trans_no DESC";

$stmt = sqlsrv_query($conn, $sql);
$rows = [];
if ($stmt) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $rows[] = $row;
    }
}
sqlsrv_close($conn);
echo json_encode($rows);
