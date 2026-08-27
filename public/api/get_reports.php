<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';
$sql  = "SELECT
    FORMAT(t.Trans_date,'MMM-yyyy') AS Month,
    s.SIZE_DESC AS Size,
    COUNT(DISTINCT t.Trans_no) AS TxnCount,
    SUM(d.amount) AS TotalSales
FROM [Transaction] t
JOIN trans_detail d ON d.Trans_no = t.Trans_no
JOIN Item_Stock s   ON s.STOCK_NUMBER = d.stock_number
GROUP BY FORMAT(t.Trans_date,'MMM-yyyy'), s.SIZE_DESC, YEAR(t.Trans_date), MONTH(t.Trans_date)
ORDER BY YEAR(t.Trans_date), MONTH(t.Trans_date), s.SIZE_DESC";
$stmt = sqlsrv_query($conn, $sql);
$rows = [];
if ($stmt) { while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) $rows[] = $row; }
sqlsrv_close($conn);
echo json_encode($rows);
