<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

if (empty($_SESSION['emp_is_admin'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Admin access required']);
    exit;
}

// Same Sale/Cost figures as get_dashboard_summary.php -- Sale trusts the
// stored amount column directly, and "Cost" is Sale minus the flat
// hardcoded per-bottle Profit rate (120/0.5L, 150/1.5L, 60/6L, 50/12L,
// 100% of Sale for 19L), not Item_Stock.PURCHASE_PRICE. See that file's
// comment for the full rationale (verified against Summarization.xlsx).
// Broken down per month x Region x size-bucket. Region comes from Customer,
// so a walk-in sale (no Customer_id) has no Region either -- bucketed as
// 'Unspecified' rather than silently dropped from the report. Size is
// derived from the leading "0.5L"/"1.5L"/"6L"/"12L"/"19L" in ITEM_NAME
// (matches how Large A/Small B variants of the same size are meant to be
// combined per the report's column layout) rather than hardcoding
// STOCK_NUMBERs, so a newly added item still buckets correctly.
$sql = "SELECT
            YEAR(t.Trans_date)                          AS Yr,
            MONTH(t.Trans_date)                          AS Mo,
            FORMAT(t.Trans_date, 'MMM-yyyy', 'en-US')    AS Month,
            ISNULL(c.Region, 'Unspecified')              AS Region,
            CASE
                WHEN s.ITEM_NAME LIKE '0.5L%' THEN '0.5L'
                WHEN s.ITEM_NAME LIKE '1.5L%' THEN '1.5L'
                WHEN s.ITEM_NAME LIKE '6L%'   THEN '6L'
                WHEN s.ITEM_NAME LIKE '12L%'  THEN '12L'
                WHEN s.ITEM_NAME LIKE '19L%'  THEN '19L'
                ELSE 'Other'
            END                                           AS Size,
            SUM(td.amount)                                AS Sale,
            SUM(td.amount - CASE
                WHEN s.ITEM_NAME LIKE '0.5L%' THEN td.quantity * 120
                WHEN s.ITEM_NAME LIKE '1.5L%' THEN td.quantity * 150
                WHEN s.ITEM_NAME LIKE '6L%'   THEN td.quantity * 60
                WHEN s.ITEM_NAME LIKE '12L%'  THEN td.quantity * 50
                WHEN s.ITEM_NAME LIKE '19L%'  THEN td.amount
                ELSE 0
            END)                                          AS Cost
        FROM trans_detail td
        JOIN [Transaction] t ON td.Trans_no     = t.Trans_no
        JOIN Item_Stock  s   ON td.stock_number = s.STOCK_NUMBER
        LEFT JOIN Customer c ON t.Customer_id   = c.Customer_id
        GROUP BY
            YEAR(t.Trans_date), MONTH(t.Trans_date),
            FORMAT(t.Trans_date, 'MMM-yyyy', 'en-US'),
            ISNULL(c.Region, 'Unspecified'),
            CASE
                WHEN s.ITEM_NAME LIKE '0.5L%' THEN '0.5L'
                WHEN s.ITEM_NAME LIKE '1.5L%' THEN '1.5L'
                WHEN s.ITEM_NAME LIKE '6L%'   THEN '6L'
                WHEN s.ITEM_NAME LIKE '12L%'  THEN '12L'
                WHEN s.ITEM_NAME LIKE '19L%'  THEN '19L'
                ELSE 'Other'
            END
        ORDER BY Yr DESC, Mo DESC, Region";

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
