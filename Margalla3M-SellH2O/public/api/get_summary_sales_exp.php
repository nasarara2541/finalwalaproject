<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

if (empty($_SESSION['emp_is_admin'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Admin access required']);
    exit;
}

// Small, slow-growing table (one row per month) -- fetch everything once,
// the frontend matches by year/month against whichever month is selected in
// the Net Profit panel, same pattern as the other dashboard data.
//
// This table only exists in MargallaProd so far (created for the Water
// dataset, not run against MargallaTesting). Rather than erroring the whole
// dashboard load when it's missing, treat "table doesn't exist" as "no
// saved expenses yet" -- an empty list, so Net Profit just falls back to
// manual entry for that database instead of breaking everything else.
$sql = "SELECT Sale_ID, Sale_Date, Total_Sales, Total_Expenses FROM SummarySalesExp ORDER BY Sale_Date";
$stmt = sqlsrv_query($conn, $sql);
$rows = [];
if ($stmt) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $rows[] = [
            'Sale_ID' => $row['Sale_ID'],
            'Yr' => (int)$row['Sale_Date']->format('Y'),
            'Mo' => (int)$row['Sale_Date']->format('n'),
            'Total_Sales' => $row['Total_Sales'],
            'Total_Expenses' => $row['Total_Expenses'],
        ];
    }
} else {
    $errors = sqlsrv_errors();
    $isMissingTable = false;
    foreach ($errors ?? [] as $e) {
        if (($e['SQLSTATE'] ?? '') === '42S02') { $isMissingTable = true; break; }
    }
    if (!$isMissingTable) {
        http_response_code(500);
        echo json_encode(['error' => $errors[0]['message'] ?? 'Query failed']);
        exit;
    }
    // else: table missing, fall through with $rows = []
}
sqlsrv_close($conn);
echo json_encode($rows);
