<?php
// Qasim's own entry point was public/index.php, a router for his whole app
// (17 pages, most never sent) that pulled in a shared navbar.php/layout also
// never included. This is a fresh, minimal shell built just for the one
// screen actually being integrated -- his real business logic (JS/API/Models)
// is untouched; only the visual layer (this shell + his fragment's markup)
// was restyled to match this app's own house look.
require_once __DIR__ . '/../config/auth_check.php';
// Purchase Order falls under Zeeshan's "Inventory" role bucket, alongside
// Stock Receiving and Items Management -- Cashier/Booking are blocked.
require_once __DIR__ . '/../../includes/access.php';
requireAccess('inventory');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AISellProduct - Purchase Order</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<script src="assets/js/toast.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
* { font-family: Tahoma, 'Segoe UI', sans-serif; font-size: 12px; box-sizing: border-box; }
html, body { height: 100%; margin: 0; }
body { background: #d4d0c8; overflow: hidden; }
[class*="rounded"] { border-radius: 0 !important; }
[class*="shadow"]  { box-shadow: none !important; }

.win-panel  { border: 1px solid; border-color: #808080 #ffffff #ffffff #808080; background:#ece9d8; }
.win-titlebar { background: linear-gradient(to right, #0a246a, #3a6ea5); color: white; font-weight: bold; font-size: 12px; padding: 4px 8px; display:flex; align-items:center; justify-content:space-between; }
.win-menubar { background: #d4d0c8; border-bottom: 1px solid #808080; display:flex; gap:0; padding: 2px 2px; }
.win-menu-item { padding: 3px 10px; cursor:pointer; font-size:12px; }
.win-menu-item:hover, .win-menu-item.nav-active { background:#0a246a; color:white; }

input[type=text], input[type=number], input[type=date], select {
    border: 1px solid; border-color: #808080 #ffffff #ffffff #808080;
    background: #ffff99; padding: 2px 4px; height: 22px; font-size:12px;
    font-family: Tahoma, sans-serif;
}
input[readonly], input:disabled { background: #d4d0c8 !important; color:#333; }
input.field-blue { background: #cce0ff !important; }
input:focus, select:focus { outline: 2px solid #0a246a; }

.win-btn {
    background: #d4d0c8; border: 1px solid; border-color: #ffffff #808080 #808080 #ffffff;
    padding: 2px 10px; cursor:pointer; font-size:12px; height:23px;
    font-family: Tahoma, sans-serif; white-space:nowrap; display:inline-flex; align-items:center; gap:3px;
}
.win-btn:hover  { background: #e8e4d8; }
.win-btn:disabled { opacity:0.5; cursor:default; }
.win-btn-blue  { background:#003087; color:white; border-color:#5599cc #002266 #002266 #5599cc; }
.win-btn-blue:hover { background:#004499; }
.win-btn-red   { background:#8b0000; color:white; border-color:#cc4444 #550000 #550000 #cc4444; }

.win-table { width:100%; border-collapse:collapse; font-size:11px; }
.win-table thead th { border:1px solid #808080; padding:3px 5px; text-align:left; font-weight:bold; background:#d4d0c8; white-space:nowrap; position:sticky; top:0; z-index:1; }
.win-table tbody tr { background:#fff; }
.win-table tbody tr:nth-child(even) { background:#f5f3ee; }
.win-table td, .win-table th { border:1px solid #d0ccc4 !important; padding:3px 5px !important; }

.win-section-label { background:#d4d0c8; font-weight:bold; padding:3px 8px; border-bottom:1px solid #808080; display:flex; align-items:center; justify-content:space-between; }
.win-statusbar { background:#d4d0c8; border-top:1px solid #808080; padding:3px 8px; display:flex; gap:14px; }

label.lbl { font-weight:bold; white-space:nowrap; flex-shrink:0; }
.field-cell { display:flex; align-items:center; gap:4px; }
</style>
</head>
<body class="flex flex-col h-screen">

<div class="win-titlebar">
    <span>&#x1F4E5; AISellProduct &mdash; Purchase Order</span>
    <span id="live-clock" style="font-weight:normal;font-size:11px;"></span>
</div>

<div class="win-menubar">
    <span class="win-menu-item" onclick="window.location='../../pos.php'">&#x2190; Back to Sale</span>
    <span class="win-menu-item nav-active">Purchase Order</span>
    <?php if (!empty($_SESSION['emp_is_admin'])): ?>
    <span class="win-menu-item" style="color:#5b3a8a;font-weight:bold;" onclick="window.location='../../admin_users.php'">&#x1F464; Manage Users</span>
    <span class="win-menu-item" style="color:#5b3a8a;font-weight:bold;" onclick="window.location='../../admin_dashboard.php'">&#x1F4CA; Dashboard</span>
    <span class="win-menu-item" style="color:#5b3a8a;font-weight:bold;" onclick="window.location='../../reports/admin_reports.php'">&#x1F4C8; Profit Reports</span>
    <?php endif; ?>
    <span style="flex:1"></span>
    <span class="win-menu-item" style="color:#555;">Database: <b><?php echo htmlspecialchars($_SESSION['active_db_label'] ?? 'Water Distribution'); ?></b></span>
    <span class="win-menu-item" onclick="window.location='../../login.php'" title="Pick a different database">&#x1F504; Switch Database</span>
    <span class="win-menu-item" style="color:#555;">User: <b><?php echo htmlspecialchars($_SESSION['emp_user_name'] ?? '—'); ?></b></span>
    <span class="win-menu-item" onclick="window.location='../../logout.php'" title="Sign out" style="color:darkred;">&#x1F6AA; Logout</span>
</div>

<main class="flex-1" style="min-height:0;overflow:auto;display:flex;flex-direction:column;">
    <?php require_once __DIR__ . '/../src/Views/Pages/purchase_order.php'; ?>
</main>

<div class="win-statusbar"><span>Ready</span></div>

<script>
function clockTick() { document.getElementById('live-clock').textContent = new Date().toLocaleString('en-GB'); }
clockTick(); setInterval(clockTick, 1000);
</script>
<script src="assets/js/purchase_order.js"></script>
</body>
</html>
