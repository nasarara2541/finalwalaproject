<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/access.php';

if (!canAccess('inventory')) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true) ?: [];

// Every field but Created_By/Created_On is optional -- this is a logging
// tool for whatever's available at the counter, not a transactional record,
// so nothing is required beyond trimming/length-capping.
function nullableText($value, $maxLength) {
    $text = trim((string)($value ?? ''));
    if ($text === '') return null;
    return mb_substr($text, 0, $maxLength);
}

$params = [
    nullableText($data['ref_no'] ?? null, 30),
    nullableText($data['description'] ?? null, 200),
    nullableText($data['doctor_name'] ?? null, 100),
    nullableText($data['doctor_contact'] ?? null, 30),
    nullableText($data['doctor_address'] ?? null, 200),
    nullableText($data['patient_name'] ?? null, 100),
    nullableText($data['patient_age'] ?? null, 10),
    nullableText($data['patient_contact'] ?? null, 30),
    nullableText($data['patient_address'] ?? null, 200),
    nullableText($data['remarks'] ?? null, 200),
    // Created_By is always server-side from the logged-in session -- never
    // trust a client-supplied value for this.
    nullableText($_SESSION['emp_user_id'] ?? null, 50),
];

$sql = "INSERT INTO ST_NARCOTICS_LOG (
            Ref_No, Description, Doctor_Name, Doctor_Contact, Doctor_Address,
            Patient_Name, Patient_Age, Patient_Contact, Patient_Address,
            Remarks, Created_By
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = sqlsrv_query($conn, $sql, $params);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => sqlsrv_errors()[0]['message'] ?? 'Save failed']);
    exit;
}
sqlsrv_close($conn);
echo json_encode(['success' => true]);
