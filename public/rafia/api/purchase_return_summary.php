<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/access.php';

if (!canAccess('inventory')) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

$currentMonth = date('Y-m');
$month = trim($_GET['month'] ?? $currentMonth);
if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
    $month = $currentMonth;
}
$from = $month . '-01';
$to   = date('Y-m-d', strtotime($from . ' +1 month'));

function fetchRows($conn, $sql, $params) {
    $stmt = sqlsrv_query($conn, $sql, $params);
    if (!$stmt) {
        return ['error' => sqlsrv_errors()[0]['message'] ?? 'Query failed'];
    }
    $rows = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        foreach (['invoice_date', 'posted_on'] as $k) {
            if (isset($row[$k]) && $row[$k] instanceof DateTime) {
                $row[$k] = $row[$k]->format($k === 'invoice_date' ? 'Y-m-d' : 'd M Y H:i');
            }
        }
        $row['total_amount'] = number_format((float)($row['total_amount'] ?? 0), 2, '.', ',');
        $rows[] = $row;
    }
    return $rows;
}

// Qty is a correlated subquery, not a JOIN + GROUP BY -- TOTAL_AMOUNT/Bonus/
// audit columns on the header row must not be aggregated.
$purchaseSql = "
    SELECT
        r.Trans_no AS trans_no,
        r.Invoice_no AS invoice_no,
        r.INVOICE_DATE AS invoice_date,
        r.Alias AS alias,
        s.SUPPLIER_NAME AS supplier_name,
        r.TOTAL_BONUS AS bonus,
        r.TOTAL_AMOUNT AS total_amount,
        r.User_id AS created_by,
        r.Posted_By AS posted_by,
        r.Posted_On AS posted_on,
        COALESCE((SELECT SUM(d.ITEMS_RECEIVED) FROM ST_STOCKRECEIPTDETAIL d WHERE d.Invoice_no = r.Invoice_no), 0) AS qty
    FROM ST_STOCKRECEIPT r
    LEFT JOIN ST_Supplier s ON s.SUPPLIER_CODE = r.SUPPLIER_CODE
    WHERE r.INVOICE_DATE >= ? AND r.INVOICE_DATE < ?
    ORDER BY r.INVOICE_DATE DESC, r.Invoice_no DESC";

$returnSql = "
    SELECT
        r.Trans_No AS trans_no,
        r.Invoice_no AS invoice_no,
        r.Invoice_Date AS invoice_date,
        r.Alias AS alias,
        s.SUPPLIER_NAME AS supplier_name,
        r.Bonus AS bonus,
        r.TOTAL_AMOUNT AS total_amount,
        r.Created_By AS created_by,
        r.Posted_By AS posted_by,
        r.Posted_On AS posted_on,
        COALESCE((SELECT SUM(d.QTY_RETURNED) FROM ST_STOCKRECEIPT_RETURN_DETAIL d WHERE d.Trans_No = r.Trans_No), 0) AS qty
    FROM ST_STOCKRECEIPT_RETURN r
    LEFT JOIN ST_Supplier s ON s.SUPPLIER_CODE = r.SUPPLIER_CODE
    WHERE r.Invoice_Date >= ? AND r.Invoice_Date < ?
    ORDER BY r.Invoice_Date DESC, r.Trans_No DESC";

$purchases = fetchRows($conn, $purchaseSql, [$from, $to]);
if (isset($purchases['error'])) { http_response_code(500); echo json_encode($purchases); exit; }

$returns = fetchRows($conn, $returnSql, [$from, $to]);
if (isset($returns['error'])) { http_response_code(500); echo json_encode($returns); exit; }

sqlsrv_close($conn);
echo json_encode(['purchases' => $purchases, 'returns' => $returns]);
