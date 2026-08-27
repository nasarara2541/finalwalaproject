<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/access.php';

if (!canAccess('inventory')) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

$month = trim($_GET['month'] ?? '');
$where = '';
$params = [];
// Half-open range on Created_On -- avoids off-by-one/timezone issues a plain
// <= comparison against a datetime column would have.
if (preg_match('/^\d{4}-\d{2}$/', $month)) {
    $from = $month . '-01';
    $to = date('Y-m-d', strtotime($from . ' +1 month'));
    $where = 'WHERE Created_On >= ? AND Created_On < ?';
    $params = [$from, $to];
}

$sql = "SELECT TOP (500)
            Ref_No AS ref_no, Description AS description, Doctor_Name AS doctor_name,
            Doctor_Contact AS doctor_contact, Doctor_Address AS doctor_address,
            Patient_Name AS patient_name, Patient_Age AS patient_age,
            Patient_Contact AS patient_contact, Patient_Address AS patient_address,
            Remarks AS remarks, Created_By AS created_by, Created_On AS created_on
        FROM ST_NARCOTICS_LOG
        $where
        ORDER BY Narcotics_ID DESC";

$stmt = sqlsrv_query($conn, $sql, $params);
$rows = [];
if ($stmt) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        if ($row['created_on'] instanceof DateTime) {
            $row['created_on'] = $row['created_on']->format('d M Y H:i');
        }
        $rows[] = $row;
    }
} else {
    http_response_code(500);
    echo json_encode(['error' => sqlsrv_errors()[0]['message'] ?? 'Query failed']);
    exit;
}
sqlsrv_close($conn);
echo json_encode($rows);
