<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['active_db'])) { header('Location: login.php'); exit; }

$SKIP_LOGIN_CHECK = true;
require_once __DIR__ . '/includes/db.php';

$error = '';
$prefillUserId = '';
$focusPassword = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId   = trim($_POST['user_id'] ?? '');
    $password = $_POST['password'] ?? '';
    $prefillUserId = $userId;

    if ($userId === '' || $password === '') {
        $error = 'Please enter both your User ID and Password.';
    } else {
        $sql = "SELECT u.User_id, u.User_name, u.User_password, u.Login_status, u.User_desc,
                       g.Group_name, gu.Local_admin
                FROM Interface_User u
                LEFT JOIN Interface_GroupUser gu ON gu.User_id = u.User_id AND gu.Authorize_Status = 'Y'
                LEFT JOIN Interface_Group g ON g.Group_id = gu.Group_id
                WHERE u.User_id = ?
                ORDER BY CASE WHEN gu.Local_admin = 'A' THEN 0 ELSE 1 END";
        $stmt = sqlsrv_query($conn, $sql, [$userId]);
        $row  = $stmt ? sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC) : null;

        // Plain-text comparison, not password_verify() -- per explicit user
        // instruction, matching how Zeeshan's Manage Users screen already
        // stores passwords. Every real User_password value in the database
        // must be plain text for login to work at all now, not a bcrypt hash.
        if (!$row || $row['Login_status'] !== 'Y' || $password !== ($row['User_password'] ?? '')) {
            $error = 'Invalid User ID or Password.';
            $focusPassword = true;
        } else {
            $_SESSION['emp_user_id']    = $row['User_id'];
            $_SESSION['emp_user_name']  = $row['User_name'];
            $_SESSION['emp_is_admin']   = ($row['Local_admin'] === 'A');
            $_SESSION['emp_group_name'] = $row['Group_name'] ?? 'Unassigned';
            // Interface_User.User_desc is the "Role" field Manage Users actually
            // edits (Admin/Management/Cashier/Inventory/Booking) -- previously
            // never read at login, only Local_admin/Group_name were. This is
            // now the source of truth for the 5-role screen-access matrix in
            // includes/access.php, kept alongside (not replacing) emp_is_admin
            // since existing true-admin-only gates already depend on it.
            $_SESSION['emp_role'] = trim($row['User_desc'] ?? '') ?: 'Management';

            $redirect = $_SESSION['redirect_after_login'] ?? 'pos.php';
            unset($_SESSION['redirect_after_login']);
            sqlsrv_close($conn);
            header('Location: ' . $redirect);
            exit;
        }
    }
}
sqlsrv_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AISellProduct, Employee Login</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
* { font-family: Tahoma, 'Segoe UI', sans-serif; font-size: 12px; box-sizing: border-box; }
html, body { height: 100%; margin: 0; }
body { background: #d4d0c8; overflow: hidden; }

.win-inset  { border: 1px solid; border-color: #808080 #ffffff #ffffff #808080; background:#fff; }
.win-panel  { border: 1px solid; border-color: #808080 #ffffff #ffffff #808080; background:#ece9d8; }
.win-titlebar { background: linear-gradient(to right, #0a246a, #3a6ea5); color: white; font-weight: bold; font-size: 12px; padding: 4px 8px; display:flex; align-items:center; justify-content:space-between; }
.win-statusbar { background:#d4d0c8; border-top:1px solid #808080; padding:3px 8px; display:flex; gap:14px; }
.win-statusbar span { border-right:1px solid #808080; padding-right:12px; }

input[type=text], input[type=password] {
    border: 1px solid; border-color: #808080 #ffffff #ffffff #808080;
    background: #ffff99; padding: 2px 6px; height: 26px; font-size:13px;
    font-family: Tahoma, sans-serif; width:100%;
}
input:focus { outline: 2px solid #0a246a; }

.win-btn {
    background: #d4d0c8; border: 1px solid; border-color: #ffffff #808080 #808080 #ffffff;
    padding: 2px 10px; cursor:pointer; font-size:12px; height:28px;
    font-family: Tahoma, sans-serif; white-space:nowrap; display:inline-flex; align-items:center; justify-content:center; gap:3px;
}
.win-btn:hover  { background: #e8e4d8; }
.win-btn:active { border-color: #808080 #ffffff #ffffff #808080; }
.win-btn-blue  { background:#003087; color:white; border-color:#5599cc #002266 #002266 #5599cc; font-weight:bold; }
.win-btn-blue:hover { background:#004499; }

.center-wrap { flex:1; display:flex; align-items:center; justify-content:center; padding:24px; }
.err-box { background:#fff0f0; border:1px solid #cc0000; color:#8b0000; padding:6px 10px; font-size:12px; margin-bottom:12px; }
label.lbl { font-weight:bold; display:block; margin-bottom:3px; }
</style>
</head>
<body class="flex flex-col h-screen">

<div class="win-titlebar">
    <span>&#x1F512; AISellProduct &mdash; Employee Login</span>
    <span id="live-clock" style="font-weight:normal;font-size:11px;"></span>
</div>

<div class="center-wrap">
<div class="win-panel" style="padding:24px 32px;width:360px;">
    <div style="text-align:center;margin-bottom:18px;">
        <div style="font-size:32px;line-height:1;">&#x1F464;</div>
        <div style="font-size:14px;font-weight:bold;color:#0a246a;margin-top:6px;">Sign In</div>
        <div style="font-size:11px;color:#555;margin-top:2px;">
            Database: <b><?php echo htmlspecialchars($_SESSION['active_db_label'] ?? ''); ?></b>
        </div>
    </div>

    <?php if ($error): ?>
    <div class="err-box"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="user_login.php" id="login-form" autocomplete="off">
        <div style="margin-bottom:12px;">
            <label class="lbl" for="login-userid">User ID</label>
            <input type="text" id="login-userid" name="user_id" value="<?php echo htmlspecialchars($prefillUserId); ?>" autocomplete="off">
        </div>
        <div style="margin-bottom:8px;">
            <label class="lbl" for="login-password">Password</label>
            <input type="password" id="login-password" name="password" autocomplete="off">
        </div>
        <div style="margin-bottom:16px;display:flex;align-items:center;gap:5px;">
            <input type="checkbox" id="show-pw" style="width:auto;height:auto;">
            <label for="show-pw" style="font-size:11px;color:#444;cursor:pointer;">Show password</label>
        </div>
        <button type="submit" class="win-btn win-btn-blue" style="width:100%;">&#x1F512; Login</button>
    </form>

    <div style="text-align:center;margin-top:14px;">
        <a href="login.php" style="font-size:11px;color:#0a246a;text-decoration:underline;">&#x2190; Choose a different database</a>
    </div>
</div>
</div>

<div class="win-statusbar">
    <span>Ready</span>
    <span>AISellProduct v1.0</span>
    <span>Margalla 3M Industries &mdash; Islamabad</span>
</div>

<script>
function clockTick() {
    document.getElementById('live-clock').textContent = new Date().toLocaleString('en-GB');
}
clockTick(); setInterval(clockTick, 1000);

const userIdField   = document.getElementById('login-userid');
const passwordField = document.getElementById('login-password');

userIdField.addEventListener('keydown', function(e){
    if (e.key === 'Enter') { e.preventDefault(); passwordField.focus(); }
});

document.getElementById('show-pw').addEventListener('change', function(){
    passwordField.type = this.checked ? 'text' : 'password';
});

<?php if ($focusPassword): ?>
passwordField.focus();
<?php else: ?>
userIdField.focus();
<?php endif; ?>
</script>
</body>
</html>
