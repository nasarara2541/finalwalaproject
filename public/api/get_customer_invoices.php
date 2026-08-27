<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

$customerId = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : 0;
if (!$customerId) { echo json_encode([]); exit; }

$sql = "SELECT
            Trans_no,
            Invoice_reference,
            CONVERT(VARCHAR(20), Trans_date, 103) AS Trans_date,
            Trans_type,
            Trans_amount,
            Paid_amount,
            Balance_amount
        FROM [Transaction]
        WHERE Customer_id = ?
        ORDER BY Trans_no DESC";

$stmt = sqlsrv_query($conn, $sql, [$customerId]);
$rows = [];
if ($stmt) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $rows[] = $row;
    }
}
sqlsrv_close($conn);
echo json_encode($rows);
