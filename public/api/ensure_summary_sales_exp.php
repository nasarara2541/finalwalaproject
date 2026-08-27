<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

if (empty($_SESSION['emp_is_admin'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Admin access required']);
    exit;
}

$year  = isset($_GET['year'])  ? (int)$_GET['year']  : 0;
$month = isset($_GET['month']) ? (int)$_GET['month'] : 0;
if ($year < 2000 || $month < 1 || $month > 12) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid year/month']);
    exit;
}
$saleDate = sprintf('%04d-%02d-01', $year, $month);

// Per Qasim: Total_Sales must never be a value someone typed in once --
// it's always the live SUM from trans_detail, computed right now, at the
// moment a row is needed. This only ever WRITES Total_Sales; Total_Expenses
// defaults to 0 (matching how the untracked early months already read) and
// is left for manual entry -- this endpoint does not touch Total_Expenses
// on a row that already exists.
$existing = sqlsrv_query($conn, "SELECT Sale_ID, Total_Sales, Total_Expenses FROM SummarySalesExp WHERE Sale_Date = ?", [$saleDate]);
if ($existing === false) {
    $errors = sqlsrv_errors();
    $isMissingTable = false;
    foreach ($errors ?? [] as $e) { if (($e['SQLSTATE'] ?? '') === '42S02') { $isMissingTable = true; break; } }
    http_response_code($isMissingTable ? 404 : 500);
    echo json_encode(['error' => $isMissingTable ? 'SummarySalesExp table does not exist' : ($errors[0]['message'] ?? 'Query failed')]);
    exit;
}
$row = sqlsrv_fetch_array($existing, SQLSRV_FETCH_ASSOC);
if ($row) {
    echo json_encode(['created' => false, 'Total_Sales' => $row['Total_Sales'], 'Total_Expenses' => $row['Total_Expenses']]);
    sqlsrv_close($conn);
    exit;
}

$sumStmt = sqlsrv_query($conn, "
    SELECT ISNULL(SUM(td.amount), 0) AS TotalSales
    FROM trans_detail td
    JOIN [Transaction] t ON t.Trans_no = td.Trans_no
    WHERE YEAR(t.Trans_date) = ? AND MONTH(t.Trans_date) = ?
", [$year, $month]);
$sumRow = $sumStmt ? sqlsrv_fetch_array($sumStmt, SQLSRV_FETCH_ASSOC) : null;
$totalSales = $sumRow ? $sumRow['TotalSales'] : 0;

$ins = sqlsrv_query($conn, "INSERT INTO SummarySalesExp (Sale_Date, Total_Sales, Total_Expenses) VALUES (?, ?, 0)", [$saleDate, $totalSales]);
if ($ins === false) {
    http_response_code(500);
    echo json_encode(['error' => sqlsrv_errors()[0]['message'] ?? 'Insert failed']);
    exit;
}

echo json_encode(['created' => true, 'Total_Sales' => $totalSales, 'Total_Expenses' => 0]);
sqlsrv_close($conn);
