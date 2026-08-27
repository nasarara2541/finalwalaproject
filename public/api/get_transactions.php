<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

$field = $_GET['field'] ?? 'all';
$q     = trim($_GET['q'] ?? '');

$columnMap = [
    'Trans_no'   => 'CAST(Trans_no AS VARCHAR(20))',
    'Cust_name'  => 'Cust_name',
    'Cust_telno' => 'Cust_telno',
    'Trans_date' => "CONVERT(VARCHAR(20), Trans_date, 103)",
    'Trans_type' => 'Trans_type',
    'User_id'    => 'User_id',
];
if ($field !== 'all' && !isset($columnMap[$field])) { $field = 'all'; }

$baseSelect = "SELECT TOP 200
             Trans_no,
             Cust_name,
             Cust_telno,
             CONVERT(VARCHAR(20), Trans_date, 103) AS Trans_date,
             Trans_type,
             Gross_amount,
             Disc_percentage,
             Trans_amount,
             Paid_amount,
             Balance_amount,
             User_id,
             Branch_code
         FROM [Transaction]";

if ($q === '') {
    // No search — just the most recent transactions.
    $stmt = sqlsrv_query($conn, $baseSelect . " ORDER BY Trans_no DESC");
} else {
    $term = '%' . $q . '%';
    if ($field === 'all') {
        $sql = $baseSelect . "
            WHERE " . implode(' OR ', array_map(fn($c) => "$c LIKE ?", $columnMap)) . "
            ORDER BY Trans_no DESC";
        $stmt = sqlsrv_query($conn, $sql, array_fill(0, count($columnMap), $term));
    } else {
        $sql  = $baseSelect . " WHERE {$columnMap[$field]} LIKE ? ORDER BY Trans_no DESC";
        $stmt = sqlsrv_query($conn, $sql, [$term]);
    }
}

$rows = [];
if ($stmt) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $rows[] = $row;
    }
}
sqlsrv_close($conn);
echo json_encode($rows);
