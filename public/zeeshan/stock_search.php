<?php
// Items Management bucket, per Zeeshan's own role matrix.
require_once __DIR__ . '/../includes/access.php';
requireAccess('inventory');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AISellProduct - Group Wise Stock Search</title>
<script src="https://cdn.tailwindcss.com"></script>
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

.filter-select, select {
    border: 1px solid; border-color: #808080 #ffffff #ffffff #808080;
    background: #ffff99; padding: 2px 4px; height: 22px; font-size:12px;
    font-family: Tahoma, sans-serif; width:100%; border-radius:0 !important;
}
label.lbl { font-weight:bold; white-space:nowrap; display:block; margin-bottom:2px; }

.win-btn {
    background: #d4d0c8; border: 1px solid; border-color: #ffffff #808080 #808080 #ffffff;
    padding: 2px 10px; cursor:pointer; font-size:12px; height:23px;
    font-family: Tahoma, sans-serif; white-space:nowrap; display:inline-flex; align-items:center; gap:3px;
}
.win-btn:hover  { background: #e8e4d8; }

.win-table { width:100%; border-collapse:collapse; font-size:11px; }
.win-table thead th { border:1px solid #808080; padding:3px 5px; text-align:left; font-weight:bold; background:#d4d0c8; white-space:nowrap; position:sticky; top:0; z-index:1; }
.win-table tbody tr { background:#fff; }
.win-table tbody tr:nth-child(even) { background:#f5f3ee; }
.win-table tbody tr:hover td { background:#c5d5e8 !important; }
.win-table td, .win-table th { border:1px solid #d0ccc4 !important; padding:3px 5px !important; }

.win-section-label { background:#d4d0c8; font-weight:bold; padding:3px 8px; border-bottom:1px solid #808080; display:flex; align-items:center; justify-content:space-between; }
.win-statusbar { background:#d4d0c8; border-top:1px solid #808080; padding:3px 8px; display:flex; gap:14px; }
</style>
</head>
<body class="flex flex-col h-screen">

<div class="win-titlebar">
    <span>&#x1F4E6; AISellProduct &mdash; Group Wise Stock Search</span>
    <span id="live-clock" style="font-weight:normal;font-size:11px;"></span>
</div>

<div class="win-menubar">
    <span class="win-menu-item" onclick="window.location='../pos.php'">&#x2190; Back to Sale</span>
    <span class="win-menu-item nav-active">Group Wise Stock Search</span>
    <?php if (!empty($_SESSION['emp_is_admin'])): ?>
    <span class="win-menu-item" style="color:#5b3a8a;font-weight:bold;" onclick="window.location='../admin_users.php'">&#x1F464; Manage Users</span>
    <span class="win-menu-item" style="color:#5b3a8a;font-weight:bold;" onclick="window.location='../admin_dashboard.php'">&#x1F4CA; Dashboard</span>
    <span class="win-menu-item" style="color:#5b3a8a;font-weight:bold;" onclick="window.location='../reports/admin_reports.php'">&#x1F4C8; Profit Reports</span>
    <?php endif; ?>
    <span style="flex:1"></span>
    <span class="win-menu-item" style="color:#555;">Database: <b><?php echo htmlspecialchars($_SESSION['active_db_label'] ?? 'Water Distribution'); ?></b></span>
    <span class="win-menu-item" onclick="window.location='../login.php'" title="Pick a different database">&#x1F504; Switch Database</span>
    <span class="win-menu-item" style="color:#555;">User: <b><?php echo htmlspecialchars($_SESSION['emp_user_name'] ?? '—'); ?></b></span>
    <span class="win-menu-item" onclick="window.location='../logout.php'" title="Sign out" style="color:darkred;">&#x1F6AA; Logout</span>
</div>

<div style="display:flex;flex-direction:column;flex:1;padding:5px;gap:4px;min-height:0;">

    <!-- Filters -->
    <div class="win-panel" style="padding:8px;">
        <div style="display:flex;flex-wrap:wrap;gap:8px 16px;align-items:end;">
            <div style="width:220px;">
                <label class="lbl">Brand Name</label>
                <select id="filter-brand" class="filter-select"><option value="">All Brands</option></select>
            </div>
            <div style="width:220px;">
                <label class="lbl">Item Type</label>
                <select id="filter-item-type" class="filter-select"><option value="">All Item Types</option></select>
            </div>
            <div style="width:220px;">
                <label class="lbl">Stock Type</label>
                <select id="filter-stock-type" class="filter-select"><option value="">All Stock Types</option></select>
            </div>
            <button id="btn-reset" class="win-btn">&#x21BA; Reset Filters</button>
            <div style="margin-left:auto;font-weight:bold;color:#555;">Showing <span id="result-count">0</span> items</div>
        </div>
    </div>

    <!-- Results -->
    <div class="win-panel" style="flex:1;min-height:0;display:flex;flex-direction:column;position:relative;">
        <div id="loading-state" class="absolute inset-0 flex flex-col items-center justify-center" style="background:rgba(255,255,255,0.85);z-index:20;">
            <span style="color:#555;font-weight:bold;">Loading items…</span>
        </div>
        <div style="flex:1;overflow:auto;min-height:0;">
            <table class="win-table">
                <thead>
                    <tr>
                        <th style="width:70px;">Stock #</th><th>Brand Name</th><th>Item Name</th><th>Item Type</th>
                        <th>Stock Type</th><th style="text-align:center;width:90px;">Volume (L)</th><th style="text-align:center;width:100px;">Status</th>
                    </tr>
                </thead>
                <tbody id="results-tbody"></tbody>
            </table>
            <div id="empty-state" class="hidden" style="padding:40px;text-align:center;color:#888;">
                <div style="font-weight:bold;font-size:13px;margin-bottom:4px;">No items found</div>
                <div>Try adjusting your filters to find what you're looking for.</div>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div id="pagination-controls" class="win-panel hidden flex items-center justify-between" style="padding:6px 8px;">
        <div style="color:#555;">Showing <span id="page-start">0</span> to <span id="page-end">0</span> of <span id="page-total">0</span> items</div>
        <div style="display:flex;align-items:center;gap:6px;">
            <button id="btn-prev" class="win-btn">Previous</button>
            <div id="page-numbers" style="display:flex;align-items:center;gap:4px;"></div>
            <button id="btn-next" class="win-btn">Next</button>
        </div>
    </div>

</div>

<div class="win-statusbar"><span>Ready</span></div>

<script>
function clockTick() { document.getElementById('live-clock').textContent = new Date().toLocaleString('en-GB'); }
clockTick(); setInterval(clockTick, 1000);
</script>
<script src="assets/js/stock_search.js?v=2"></script>
</body>
</html>
