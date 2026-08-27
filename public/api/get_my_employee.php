<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

// Interface_User.EMP_ID links a login to a real Employee row -- only some
// users have it set (a real, honest gap, not every login has been mapped to
// an employee yet). Returns null rather than guessing when there's no link.
$sql  = "SELECT e.Emp_no, e.Full_Name
         FROM Interface_User u
         JOIN Employee e ON e.Emp_no = u.EMP_ID
         WHERE u.User_id = ?";
$stmt = sqlsrv_query($conn, $sql, [$_SESSION['emp_user_id']]);
$row  = $stmt ? sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC) : null;
sqlsrv_close($conn);
echo json_encode($row ?: null);
