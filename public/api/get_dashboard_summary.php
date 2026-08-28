<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

// Dashboard figures are sensitive (money/profit) - require the Administrator
// group specifically, not just "logged in".
if (empty($_SESSION['emp_is_admin'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Admin access required']);
    exit;
}

// Total Sale trusts the stored trans_detail.amount directly (explicit
// business decision) rather than recomputing quantity * Price_PerItem - a
// handful of historical rows have an amount that doesn't match qty*price
// (data-entry typos from hand transcription), but the recorded amount is
// kept as the source of truth rather than silently overridden.
//
// Profit is a flat, hardcoded Rs.-per-bottle rate by size (120/0.5L,
// 150/1.5L, 60/6L, 50/12L), not derived from Item_Stock.PURCHASE_PRICE --
// explicit instruction, verified line-for-line against the teacher-provided
// Summarization.xlsx "Profit" sheet (every month, exact match). 19L is a
// refillable container with negligible per-refill cost, so its entire sale
// amount counts as profit rather than using a per-bottle rate. "Cost" here
// is *implied* (Sale - Profit) purely so the existing UI (which always
// computes Profit as Sale - Cost, and the chart which stacks Cost+Profit to
// equal Sale) keeps working unchanged -- it is not a real recorded cost, and
// can legitimately go negative for an individual item/month if that flat
// rate exceeds the actual sale amount for a low-price-per-bottle month.
// This flat rate only makes sense for the 5 real water container sizes, so
// it's specific to the Water Distribution database. Med Stock has no such
// per-size rate -- there, cost uses trans_detail.PPrice_amount (the actual
// recorded cost per line, populated on 100% of its historical sale rows),
// falling back to Item_Stock.PURCHASE_PRICE only if that's ever missing.
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
            YEAR(t.Trans_date)                                                                   AS Yr,
            MONTH(t.Trans_date)                                                                  AS Mo,
            FORMAT(t.Trans_date, 'MMM-yyyy', 'en-US')                                             AS Month,
            SUM(td.quantity)                                                                     AS Packs,
            SUM(td.amount)                                                                       AS Sale,
            SUM(td.amount - $costExpr)                                                            AS Cost
        FROM trans_detail td
        JOIN [Transaction] t ON td.Trans_no     = t.Trans_no
        JOIN Item_Stock  s   ON td.stock_number = s.STOCK_NUMBER
        GROUP BY
            YEAR(t.Trans_date), MONTH(t.Trans_date),
            FORMAT(t.Trans_date, 'MMM-yyyy', 'en-US')
        ORDER BY
            YEAR(t.Trans_date) DESC, MONTH(t.Trans_date) DESC";

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
