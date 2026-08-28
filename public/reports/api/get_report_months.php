<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../includes/db.php';

if (empty($_SESSION['emp_is_admin'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Admin access required']);
    exit;
}

// Just the distinct months that actually have sales -- cheap (one row per
// month, not per sale line) so it's fine to load this eagerly to populate
// the Month dropdowns. The actual per-month report rows (get_dashboard_by_item/
// get_report_by_region/get_report_by_customer) only get fetched once a
// specific month is picked -- see admin_reports.php.
$sql = "SELECT
            YEAR(Trans_date)                       AS Yr,
            MONTH(Trans_date)                      AS Mo,
            FORMAT(Trans_date, 'MMM-yyyy', 'en-US') AS Month
        FROM [Transaction]
        GROUP BY YEAR(Trans_date), MONTH(Trans_date), FORMAT(Trans_date, 'MMM-yyyy', 'en-US')
        ORDER BY Yr DESC, Mo DESC";

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
