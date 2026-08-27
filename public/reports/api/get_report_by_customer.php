<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../includes/db.php';

if (empty($_SESSION['emp_is_admin'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Admin access required']);
    exit;
}

// Same flat per-container-size rate as get_dashboard_summary.php, and same
// Med Stock exception -- see that file's comment for the full rationale.
// "Size" itself stays Water-specific grouping -- Med Stock items honestly
// fall into 'Other' there, same convention as Stock Search's Group column.
$isWaterDb = ($_SESSION['active_db_label'] ?? 'Water Distribution') === 'Water Distribution';
$costExpr = $isWaterDb
    ? "CASE
                WHEN s.ITEM_NAME LIKE '0.5L%' THEN td.quantity * 120
                WHEN s.ITEM_NAME LIKE '1.5L%' THEN td.quantity * 150
                WHEN s.ITEM_NAME LIKE '6L%'   THEN td.quantity * 60
                WHEN s.ITEM_NAME LIKE '12L%'  THEN td.quantity * 50
                WHEN s.ITEM_NAME LIKE '19L%'  THEN td.amount
                ELSE 0
            END"
    : "COALESCE(td.PPrice_amount, td.quantity * s.PURCHASE_PRICE, 0)";

$sql = "SELECT
            YEAR(t.Trans_date)                          AS Yr,
            MONTH(t.Trans_date)                          AS Mo,
            FORMAT(t.Trans_date, 'MMM-yyyy', 'en-US')    AS Month,
            ISNULL(t.Customer_id, -1)                    AS CustomerId,
            ISNULL(c.Cust_name, 'Walk-in / Unspecified') AS Customer,
            CASE
                WHEN s.ITEM_NAME LIKE '0.5L%' THEN '0.5L'
                WHEN s.ITEM_NAME LIKE '1.5L%' THEN '1.5L'
                WHEN s.ITEM_NAME LIKE '6L%'   THEN '6L'
                WHEN s.ITEM_NAME LIKE '12L%'  THEN '12L'
                WHEN s.ITEM_NAME LIKE '19L%'  THEN '19L'
                ELSE 'Other'
            END                                           AS Size,
            SUM(td.amount)                                AS Sale,
            SUM(td.amount - $costExpr)                     AS Cost
        FROM trans_detail td
        JOIN [Transaction] t ON td.Trans_no     = t.Trans_no
        JOIN Item_Stock  s   ON td.stock_number = s.STOCK_NUMBER
        LEFT JOIN Customer c ON t.Customer_id   = c.Customer_id
        GROUP BY
            YEAR(t.Trans_date), MONTH(t.Trans_date),
            FORMAT(t.Trans_date, 'MMM-yyyy', 'en-US'),
            ISNULL(t.Customer_id, -1),
            ISNULL(c.Cust_name, 'Walk-in / Unspecified'),
            CASE
                WHEN s.ITEM_NAME LIKE '0.5L%' THEN '0.5L'
                WHEN s.ITEM_NAME LIKE '1.5L%' THEN '1.5L'
                WHEN s.ITEM_NAME LIKE '6L%'   THEN '6L'
                WHEN s.ITEM_NAME LIKE '12L%'  THEN '12L'
                WHEN s.ITEM_NAME LIKE '19L%'  THEN '19L'
                ELSE 'Other'
            END
        ORDER BY Yr DESC, Mo DESC, Customer";

$stmt = sqlsrv_query($conn, $sql);
$rows = [];
if ($stmt) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $rows[] = $row;
    }
} else {
    http_response_code(500);
    echo json_encode(['error' => sqlsrv_errors()[0]['message'] ?? 'Query failed']);
    exit;
}
sqlsrv_close($conn);
echo json_encode($rows);
