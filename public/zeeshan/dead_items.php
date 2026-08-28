<?php
// Previously gated admin-only (same class as Dashboard/Profit Reports).
// Under Zeeshan's own 5-role matrix this actually belongs to the Inventory
// role's remit (grouped with Items Management/Purchase Orders), not
// Administration -- Admin/Management/Inventory can all see it now.
require_once __DIR__ . '/../includes/access.php';
requireAccess('inventory');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AISellProduct - Dead Items Report</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- His JS injects fa-solid icon markup dynamically (spinner, export icon) -- kept just for that, not used in this file's own static markup -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

    .win-btn {
        background: #d4d0c8; border: 1px solid; border-color: #ffffff #808080 #808080 #ffffff;
        padding: 2px 10px; cursor:pointer; font-size:12px; height:23px;
        font-family: Tahoma, sans-serif; white-space:nowrap; display:inline-flex; align-items:center; gap:6px;
    }
    .win-btn:hover  { background: #e8e4d8; }
    .win-btn-green { background:#1a7a1a; color:white; border-color:#5ccc5c #0a500a #0a500a #5ccc5c; }
    .win-btn-green:hover { background:#218c21; }

    .win-table { width:100%; border-collapse:collapse; font-size:11px; }
    .win-table thead th { border:1px solid #808080; padding:3px 5px; text-align:left; font-weight:bold; background:#d4d0c8; white-space:nowrap; position:sticky; top:0; z-index:1; }
    .win-table tbody tr { background:#fff; }
    .win-table tbody tr:nth-child(even) { background:#f5f3ee; }
    .win-table td, .win-table th { border:1px solid #d0ccc4 !important; padding:3px 5px !important; }

    .win-section-label { background:#d4d0c8; font-weight:bold; padding:3px 8px; border-bottom:1px solid #808080; display:flex; align-items:center; justify-content:space-between; }
    .win-statusbar { background:#d4d0c8; border-top:1px solid #808080; padding:3px 8px; display:flex; gap:14px; }
    </style>
</head>
<body class="flex flex-col h-screen">

<?php $SCREEN_NAME = 'Dead Items Report'; $SCREEN_ICON = 'triangle-exclamation'; require __DIR__ . '/../includes/navbar.php'; ?>

<div style="display:flex;flex-direction:column;flex:1;padding:5px;gap:4px;min-height:0;">

    <div class="win-panel" style="padding:6px 8px;display:flex;align-items:center;gap:10px;">
        <span style="color:#555;">Items with no sales in the last 60 days</span>
        <span style="flex:1"></span>
        <button id="btn-run" class="win-btn win-btn-green">&#x1F504; Generate</button>
        <button id="btn-export" class="win-btn">&#x1F4C4; Download PDF</button>
    </div>

    <div class="win-panel" style="flex:1;min-height:0;display:flex;flex-direction:column;">
        <div class="win-section-label">
            <span>Report Data</span>
            <span id="record-count" style="font-weight:normal;color:#555;">0 items found</span>
        </div>
        <div style="flex:1;overflow:auto;min-height:0;">
            <table class="win-table" id="dead-items-table">
                <thead>
                    <tr>
                        <th style="width:80px;">Stock No.</th><th>Item Name</th><th>Brand Name</th>
                        <th style="text-align:right;width:90px;">Retail Price</th>
                        <th style="text-align:center;width:90px;">Qty In Hand</th>
                        <th style="width:110px;">Last Sold Date</th>
                    </tr>
                </thead>
                <tbody id="dead-items-tbody">
                    <tr><td colspan="6" style="text-align:center;padding:20px;color:#888;">Click Generate to load report.</td></tr>
                </tbody>
            </table>
        </div>
        <div style="background:#d4d0c8;border-top:1px solid #808080;padding:6px 8px;display:flex;justify-content:space-between;align-items:center;">
            <div id="pagination-info" style="color:#555;">Showing 0 to 0 of 0 items</div>
            <div id="pagination-controls" style="display:flex;align-items:center;gap:4px;"></div>
        </div>
    </div>

</div>

<div class="win-statusbar"><span>Ready</span></div>

<div id="toast-msg" class="opacity-0" style="position:fixed;bottom:16px;right:16px;z-index:9999;font-weight:bold;transition:all 0.3s;">Notification</div>

<script>
function clockTick() { document.getElementById('live-clock').textContent = new Date().toLocaleString('en-GB'); }
clockTick(); setInterval(clockTick, 1000);
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script src="assets/js/dead_items.js?v=4"></script>
</body>
</html>
