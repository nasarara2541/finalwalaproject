<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

if (empty($_SESSION['emp_is_admin'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Admin access required']);
    exit;
}

// Same figures as get_dashboard_summary.php (see its comment for the Sale
// source-of-truth decision and the flat-rate Profit/implied-Cost model),
// just broken down per item within each month instead of combined — small
// result set (months x items), so the frontend filters to one month at a
// time client-side rather than making a separate request per month. The
// per-item rate is the same as its size's rate (e.g. both 0.5L Large A and
// 0.5L Small B use 120/bottle), so summing this across an item's two
// variants reproduces the size-bucket total exactly.
$sql = "SELECT
            YEAR(t.Trans_date)                                                                   AS Yr,
            MONTH(t.Trans_date)                                                                  AS Mo,
            FORMAT(t.Trans_date, 'MMM-yyyy', 'en-US')                                             AS Month,
            td.stock_number                                                                      AS StockNo,
            COALESCE(NULLIF(s.SIZE_DESC, ''), s.ITEM_NAME)                                        AS Item,
            SUM(td.quantity)                                                                     AS Packs,
            SUM(td.amount)                                                                       AS Sale,
            SUM(td.amount - CASE
                WHEN s.ITEM_NAME LIKE '0.5L%' THEN td.quantity * 120
                WHEN s.ITEM_NAME LIKE '1.5L%' THEN td.quantity * 150
                WHEN s.ITEM_NAME LIKE '6L%'   THEN td.quantity * 60
                WHEN s.ITEM_NAME LIKE '12L%'  THEN td.quantity * 50
                WHEN s.ITEM_NAME LIKE '19L%'  THEN td.amount
                ELSE 0
            END)                                                                                  AS Cost
        FROM trans_detail td
        JOIN [Transaction] t ON td.Trans_no     = t.Trans_no
        JOIN Item_Stock  s   ON td.stock_number = s.STOCK_NUMBER
        GROUP BY
            YEAR(t.Trans_date), MONTH(t.Trans_date),
            FORMAT(t.Trans_date, 'MMM-yyyy', 'en-US'),
            td.stock_number, COALESCE(NULLIF(s.SIZE_DESC, ''), s.ITEM_NAME)
        ORDER BY
            Yr DESC, Mo DESC, Sale DESC";

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
