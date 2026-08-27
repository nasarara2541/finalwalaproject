<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) { echo json_encode(['error' => 'No data received']); exit; }

$manufactureNo = (isset($data['manufacture_no']) && $data['manufacture_no']) ? intval($data['manufacture_no']) : null;
$name          = trim($data['m_name']      ?? '');
$shortName     = trim($data['m_shortname'] ?? '');
$address       = trim($data['address']     ?? '');
$city          = trim($data['city']        ?? '');
$telNo         = trim($data['tel_no']      ?? '');

if (!$name) { echo json_encode(['error' => 'Manufacturer Name is required']); exit; }

if ($manufactureNo) {
    $sql  = "UPDATE Manufacture SET M_Name=?, M_ShortName=?, Address=?, City=?, Tel_no=? WHERE Manufacture_no=?";
    $stmt = sqlsrv_query($conn, $sql, [$name, $shortName, $address, $city, $telNo, $manufactureNo]);
    if (!$stmt) {
        echo json_encode(['error' => sqlsrv_errors()[0]['message'] ?? 'Update failed']);
        exit;
    }
    sqlsrv_close($conn);
    echo json_encode(['success' => true, 'manufacture_no' => $manufactureNo]);
} else {
    $sql  = "INSERT INTO Manufacture (M_Name, M_ShortName, Address, City, Tel_no) VALUES (?,?,?,?,?); SELECT SCOPE_IDENTITY() AS new_id;";
    $stmt = sqlsrv_query($conn, $sql, [$name, $shortName, $address, $city, $telNo]);
    if (!$stmt) {
        echo json_encode(['error' => sqlsrv_errors()[0]['message'] ?? 'Insert failed']);
        exit;
    }
    sqlsrv_next_result($stmt);
    $row   = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    $newId = intval($row['new_id']);
    sqlsrv_close($conn);
    echo json_encode(['success' => true, 'new_id' => $newId]);
}
