<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) { echo json_encode(['error' => 'No ID']); exit; }

$sqlH = "SELECT
             t.Trans_no,
             t.Invoice_reference,
             t.Cust_name,
             t.Cust_telno,
             CONVERT(VARCHAR(20), t.Trans_date, 103) AS Trans_date,
             t.Trans_type,
             t.Gross_amount,
             t.Disc_percentage,
             t.Trans_amount,
             t.Paid_amount,
             t.Balance_amount,
             t.User_id,
             t.Branch_code,
             t.Tax_status,
             t.Sale_Person_id,
             e.Full_Name AS Sale_Person_Name,
             t.Description,
             t.Remarks,
             t.Is_Cancelled,
             t.Cancelled_By,
             t.Cancel_Reason
         FROM [Transaction] t
         LEFT JOIN Employee e ON e.Emp_no = t.Sale_Person_id
         WHERE t.Trans_no = ?";

$stmtH = sqlsrv_query($conn, $sqlH, [$id]);
$header = $stmtH ? sqlsrv_fetch_array($stmtH, SQLSRV_FETCH_ASSOC) : null;

$sqlD = "SELECT
             d.stock_number,
             s.BRAND_NAME,
             s.ITEM_NAME,
             s.ITEM_TYPE,
             s.VOLUME_L,
             s.SIZE_DESC,
             d.quantity,
             d.Price_PerItem,
             d.amount,
             d.Status,
             d.Invoice_No
         FROM trans_detail d
         JOIN Item_Stock s ON s.STOCK_NUMBER = d.stock_number
         WHERE d.Trans_no = ?";

$stmtD  = sqlsrv_query($conn, $sqlD, [$id]);
$detail = [];
if ($stmtD) {
    while ($row = sqlsrv_fetch_array($stmtD, SQLSRV_FETCH_ASSOC)) {
        $detail[] = $row;
    }
}
sqlsrv_close($conn);
echo json_encode(['header' => $header, 'detail' => $detail]);
