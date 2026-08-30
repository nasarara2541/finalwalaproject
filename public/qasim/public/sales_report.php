<?php
// Qasim's own gate was true-Admin-only ($_SESSION['user_role'] === 'Admin').
// Under Zeeshan's 5-role matrix this belongs in the broader Administration
// bucket (Admin + Management), same as Dashboard/Profit Reports/Item Details.
require_once __DIR__ . '/../../includes/access.php';
requireAccess('admin_area');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AISellProduct - Sales Report</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<style>
* { font-family: Tahoma, 'Segoe UI', sans-serif; font-size: 12px; box-sizing: border-box; }
html, body { height: 100%; margin: 0; }
body { background: #d4d0c8; overflow: hidden; }
[class*="rounded"] { border-radius: 0 !important; }
[class*="shadow"]  { box-shadow: none !important; }

.win-panel  { border: 1px solid; border-color: #808080 #ffffff #ffffff #808080; background:#ece9d8; }
.win-white-panel { background:#fff; }
.win-titlebar { background: linear-gradient(to right, #0a246a, #3a6ea5); color: white; font-weight: bold; font-size: 12px; padding: 4px 8px; display:flex; align-items:center; justify-content:space-between; }
.win-menubar { background: #d4d0c8; border-bottom: 1px solid #808080; display:flex; gap:0; padding: 2px 2px; }
.win-menu-item { padding: 3px 10px; cursor:pointer; font-size:12px; }
.win-menu-item:hover, .win-menu-item.nav-active { background:#0a246a; color:white; }

input[type=date] {
    border: 1px solid; border-color: #808080 #ffffff #ffffff #808080;
    background: #ffff99; padding: 2px 4px; height: 22px; font-size:12px;
    font-family: Tahoma, sans-serif;
}

.win-btn {
    background: #d4d0c8; border: 1px solid; border-color: #ffffff #808080 #808080 #ffffff;
    padding: 4px 10px; cursor:pointer; font-size:12px;
    font-family: Tahoma, sans-serif; white-space:nowrap; display:inline-flex; align-items:center; justify-content:center; gap:6px;
}
.win-btn:hover  { background: #e8e4d8; }
.win-btn-blue  { background:#003087; color:white; border-color:#5599cc #002266 #002266 #5599cc; }
.win-btn-blue:hover { background:#004499; }

.win-table { width:100%; border-collapse:collapse; font-size:11px; }
.win-table thead th { border:1px solid #808080; padding:3px 5px; text-align:left; font-weight:bold; background:#d4d0c8; white-space:nowrap; }
.win-table tbody tr { background:#fff; }
.win-table tbody tr:nth-child(even) { background:#f5f3ee; }
.win-table td, .win-table th { border:1px solid #d0ccc4 !important; padding:3px 5px !important; }

label.lbl { font-weight:bold; }
</style>
</head>
<body class="flex flex-col h-screen">

<?php $SCREEN_NAME = 'Sales Report'; $SCREEN_ICON = 'chart-line'; require __DIR__ . '/../../includes/navbar.php'; ?>

<main class="flex-1" style="min-height:0;overflow:hidden;display:flex;flex-direction:column;">
    <?php require_once __DIR__ . '/../src/Views/Pages/Administration/sales_report.php'; ?>
</main>

<div class="win-statusbar" style="background:#d4d0c8;border-top:1px solid #808080;padding:3px 8px;"><span>Ready</span></div>

<script>
function clockTick() { document.getElementById('live-clock').textContent = new Date().toLocaleString('en-GB'); }
clockTick(); setInterval(clockTick, 1000);
</script>
<script src="assets/js/sales_report.js"></script>
</body>
</html>
