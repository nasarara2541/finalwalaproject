<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

// db.php only confirms someone is logged in - creating/editing accounts and
// granting Administrator rights requires the Administrator group specifically.
if (empty($_SESSION['emp_is_admin'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Admin access required']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) { echo json_encode(['error' => 'No data received']); exit; }

$originalUserId = trim($data['original_user_id'] ?? '');
$userId    = trim($data['user_id']    ?? '');
$password  = $data['password']        ?? '';
$fullName  = trim($data['full_name']  ?? '');
$address   = trim($data['address']    ?? '');
$city      = trim($data['city']       ?? '');
$telNo     = trim($data['tel_no']     ?? '');
$mobileNo  = trim($data['mobile_no']  ?? '');
$userDesc  = trim($data['user_desc']  ?? '');
$role      = ($data['role'] ?? 'management') === 'admin' ? 'admin' : 'management';
$active    = !empty($data['active']);

$isNew        = ($originalUserId === '');
$targetUserId = $isNew ? $userId : $originalUserId;

if ($targetUserId === '') { echo json_encode(['error' => 'User ID is required']); exit; }
if (!preg_match('/^[A-Za-z0-9_.-]+$/', $targetUserId)) {
    echo json_encode(['error' => 'User ID may only contain letters, numbers, dot, dash and underscore']);
    exit;
}
if ($fullName === '') { echo json_encode(['error' => 'Full Name is required']); exit; }
if ($isNew && $password === '') { echo json_encode(['error' => 'Password is required for a new user']); exit; }

// Self-protection: an admin editing their own account can't lock themselves
// out by deactivating or demoting the very account they're currently using.
if (!$isNew && $originalUserId === ($_SESSION['emp_user_id'] ?? null)) {
    if (!$active)        { echo json_encode(['error' => 'You cannot deactivate your own account while logged in as it.']); exit; }
    if ($role !== 'admin') { echo json_encode(['error' => 'You cannot remove Administrator rights from your own account.']); exit; }
}

$groupId     = $role === 'admin' ? 'G01' : 'G02';
$localAdmin  = $role === 'admin' ? 'A'   : 'R';
$loginStatus = $active ? 'Y' : 'N';

sqlsrv_begin_transaction($conn);

if ($isNew) {
    // Reject a duplicate User_id up front with a friendly message instead of
    // letting the primary-key violation surface as a raw SQL error.
    $checkStmt = sqlsrv_query($conn, "SELECT COUNT(*) AS cnt FROM Interface_User WHERE User_id = ?", [$targetUserId]);
    $checkRow  = $checkStmt ? sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC) : null;
    if (!$checkRow || $checkRow['cnt'] > 0) {
        sqlsrv_rollback($conn);
        echo json_encode(['error' => 'That User ID is already taken']);
        exit;
    }
}

// Look up (or create) the linked Employee row. The seed users all have
// EMP_ID = NULL, so this also backfills an Employee record for them the
// first time they're edited here.
$empId = null;
if (!$isNew) {
    $empStmt = sqlsrv_query($conn, "SELECT EMP_ID FROM Interface_User WHERE User_id = ?", [$targetUserId]);
    $empRow  = $empStmt ? sqlsrv_fetch_array($empStmt, SQLSRV_FETCH_ASSOC) : null;
    $empId   = $empRow['EMP_ID'] ?? null;
}

if ($empId) {
    sqlsrv_query($conn,
        "UPDATE Employee SET Full_Name=?, Address=?, City=?, Tel_no=?, Mobile_no=? WHERE Emp_no=?",
        [$fullName, $address, $city, $telNo, $mobileNo, $empId]
    );
} else {
    $sqlEmp  = "INSERT INTO Employee (Full_Name, Address, City, Tel_no, Mobile_no) VALUES (?,?,?,?,?);
                SELECT SCOPE_IDENTITY() AS new_id;";
    $stmtEmp = sqlsrv_query($conn, $sqlEmp, [$fullName, $address, $city, $telNo, $mobileNo]);
    if (!$stmtEmp) {
        sqlsrv_rollback($conn);
        echo json_encode(['error' => sqlsrv_errors()[0]['message'] ?? 'Could not save employee details']);
        exit;
    }
    sqlsrv_next_result($stmtEmp);
    $row   = sqlsrv_fetch_array($stmtEmp, SQLSRV_FETCH_ASSOC);
    $empId = intval($row['new_id']);
}

if ($isNew) {
    // Plain text, not password_hash() -- per explicit user instruction, to
    // match user_login.php's plain-text comparison and Zeeshan's screen.
    $stmtU = sqlsrv_query($conn,
        "INSERT INTO Interface_User (User_id, User_name, User_password, Login_status, User_desc, EMP_ID) VALUES (?,?,?,?,?,?)",
        [$targetUserId, $fullName, $password, $loginStatus, $userDesc, $empId]
    );
    if (!$stmtU) {
        sqlsrv_rollback($conn);
        echo json_encode(['error' => sqlsrv_errors()[0]['message'] ?? 'Could not create user']);
        exit;
    }
} else {
    $stmtU = sqlsrv_query($conn,
        "UPDATE Interface_User SET User_name=?, Login_status=?, User_desc=?, EMP_ID=? WHERE User_id=?",
        [$fullName, $loginStatus, $userDesc, $empId, $targetUserId]
    );
    if (!$stmtU) {
        sqlsrv_rollback($conn);
        echo json_encode(['error' => sqlsrv_errors()[0]['message'] ?? 'Could not update user']);
        exit;
    }
    // A blank password field means "keep the current password" - only
    // touch User_password when the admin actually typed a new one.
    if ($password !== '') {
        sqlsrv_query($conn, "UPDATE Interface_User SET User_password=? WHERE User_id=?", [$password, $targetUserId]);
    }
}

// Replace this user's group membership rather than trying to patch it in
// place - simplest correct way to let the role change to/from Admin.
sqlsrv_query($conn, "DELETE FROM Interface_GroupUser WHERE User_id = ?", [$targetUserId]);
$stmtG = sqlsrv_query($conn,
    "INSERT INTO Interface_GroupUser (Group_id, User_id, Local_admin, Authorize_Status) VALUES (?,?,?,'Y')",
    [$groupId, $targetUserId, $localAdmin]
);
if (!$stmtG) {
    sqlsrv_rollback($conn);
    echo json_encode(['error' => sqlsrv_errors()[0]['message'] ?? 'Could not set role']);
    exit;
}

sqlsrv_commit($conn);
sqlsrv_close($conn);
echo json_encode(['success' => true, 'user_id' => $targetUserId, 'mode' => $isNew ? 'inserted' : 'updated']);
