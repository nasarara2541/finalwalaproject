<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

// db.php only confirms someone is logged in — this endpoint exposes every
// employee's contact details and role, so it additionally requires the
// Administrator group specifically.
if (empty($_SESSION['emp_is_admin'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Admin access required']);
    exit;
}

$sql = "SELECT u.User_id, u.User_name, u.Login_status, u.User_desc, u.EMP_ID,
               e.Full_Name, e.Address, e.City, e.Tel_no, e.Mobile_no,
               g.Group_id, g.Group_name, gu.Local_admin
        FROM Interface_User u
        LEFT JOIN Employee e ON e.Emp_no = u.EMP_ID
        OUTER APPLY (
            SELECT TOP 1 gu2.Group_id, gu2.Local_admin
            FROM Interface_GroupUser gu2
            WHERE gu2.User_id = u.User_id AND gu2.Authorize_Status = 'Y'
            ORDER BY CASE WHEN gu2.Local_admin = 'A' THEN 0 ELSE 1 END
        ) gu
        LEFT JOIN Interface_Group g ON g.Group_id = gu.Group_id
        ORDER BY u.User_name";

$stmt = sqlsrv_query($conn, $sql);
$rows = [];
if ($stmt) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $rows[] = $row;
    }
}
sqlsrv_close($conn);
echo json_encode($rows);
