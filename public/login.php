<?php
if (session_status() === PHP_SESSION_NONE) session_start();
// Relative to this file, not a hardcoded absolute path — see includes/db.php.
$env = parse_ini_file(__DIR__ . '/../.env');

if (isset($_GET['select'])) {
    $choice = $_GET['select'];
    if ($choice === 'water') {
        $_SESSION['active_db']       = $env['DB_NAME_WATER'];
        $_SESSION['active_db_label'] = 'Water Distribution';
    } elseif ($choice === 'medstock') {
        $_SESSION['active_db']       = $env['DB_NAME_MEDSTOCK'];
        $_SESSION['active_db_label'] = 'Med Stock Testing';
    }
    if (isset($_SESSION['active_db'])) {
        // Interface_User lives inside the database itself, so switching
        // databases always requires signing in again against that DB's users.
        unset($_SESSION['emp_user_id'], $_SESSION['emp_user_name'], $_SESSION['emp_is_admin'], $_SESSION['emp_group_name']);
        header('Location: user_login.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AISellProduct, Choose Your Database</title>
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

.win-btn {
    background: #d4d0c8; border: 1px solid; border-color: #ffffff #808080 #808080 #ffffff;
    padding: 2px 10px; cursor:pointer; font-size:12px; height:23px;
    font-family: Tahoma, sans-serif; white-space:nowrap; display:inline-flex; align-items:center; gap:3px;
}
.win-btn:hover  { background: #e8e4d8; }
.win-btn:active { border-color: #808080 #ffffff #ffffff #808080; }
.win-btn-blue  { background:#003087; color:white; border-color:#5599cc #002266 #002266 #5599cc; }
.win-btn-blue:hover { background:#004499; }

.center-wrap { flex:1; display:flex; align-items:center; justify-content:center; padding:24px; }

.db-card {
    width: 300px;
    text-decoration: none;
    color: inherit;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 22px 20px;
    cursor: pointer;
}
.db-card:hover { background:#c5d5e8; border-color:#3a6ea5 !important; }
.db-emoji { font-size: 40px; line-height:1; }
.db-name { font-size: 15px; font-weight: bold; color:#0a246a; margin:0; }
.db-desc { font-size: 11px; color:#444; margin:0; line-height:1.5; }
</style>
</head>
<body class="flex flex-col h-screen">

<div class="win-titlebar">
    <span>&#x1F4A7; AISellProduct &mdash; Choose Your Database</span>
    <span id="live-clock" style="font-weight:normal;font-size:11px;"></span>
</div>

<div class="center-wrap">
<div class="win-panel" style="padding:24px 32px;max-width:760px;width:100%;">
    <div style="text-align:center;margin-bottom:20px;">
        <div style="font-size:15px;font-weight:bold;color:#0a246a;">Welcome to AISellProduct</div>
        <div style="font-size:12px;color:#555;margin-top:4px;">Pick which database you would like to work with today. You can switch back any time.</div>
    </div>

    <div style="display:flex;gap:18px;flex-wrap:wrap;justify-content:center;">
        <a class="db-card win-inset" href="login.php?select=water">
            <div class="db-emoji">&#x1F4A7;</div>
            <p class="db-name">Water Distribution</p>
            <p class="db-desc">The live Margalla 3M Industries data. Use this for real sales and stock work.</p>
            <span class="win-btn win-btn-blue">Continue &#x2192;</span>
        </a>
        <a class="db-card win-inset" href="login.php?select=medstock">
            <div class="db-emoji">&#x1F48A;</div>
            <p class="db-name">Med Stock Testing</p>
            <p class="db-desc">A separate sample dataset with pharmacy style test data. Use this for testing and demos.</p>
            <span class="win-btn win-btn-blue">Continue &#x2192;</span>
        </a>
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
</script>
</body>
</html>
