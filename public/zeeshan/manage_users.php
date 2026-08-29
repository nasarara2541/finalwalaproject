<?php
// This screen manages Interface_User directly (same table every login
// authenticates against), so it needs the same admin-only gate as this
// app's own equivalent (admin_users.php), not just a plain login check.
require_once __DIR__ . '/../includes/require_admin.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AISellProduct - Manage Users</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- His JS injects fa-solid icon markup dynamically -- kept just for that, not used in this file's own static markup -->
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
    .win-btn-green { background:#1a7a1a !important; color:white !important; border-color:#5ccc5c #0a500a #0a500a #5ccc5c !important; }
    .win-btn-green:hover { background:#218c21 !important; }
    .win-btn-red { color:#8b0000; }

    .win-table { width:100%; border-collapse:collapse; font-size:11px; }
    .win-table thead th { border:1px solid #808080; padding:3px 5px; text-align:left; font-weight:bold; background:#d4d0c8; white-space:nowrap; position:sticky; top:0; z-index:1; }
    .win-table tbody tr { background:#fff; }
    .win-table tbody tr:nth-child(even) { background:#f5f3ee; }
    .win-table td, .win-table th { border:1px solid #d0ccc4 !important; padding:3px 5px !important; }

    .win-section-label { background:#d4d0c8; font-weight:bold; padding:3px 8px; border-bottom:1px solid #808080; display:flex; align-items:center; justify-content:space-between; }
    .win-statusbar { background:#d4d0c8; border-top:1px solid #808080; padding:3px 8px; display:flex; gap:14px; }

    .xp-input {
        border: 1px solid; border-color: #808080 #ffffff #ffffff #808080 !important;
        background: #ffff99 !important; padding: 3px 5px; font-size:12px;
        font-family: Tahoma, sans-serif; width:100%; border-radius:0 !important;
    }
    .xp-label { font-weight:bold; display:block; margin-bottom:2px; }
    </style>
</head>
<body class="flex flex-col h-screen">

<?php $SCREEN_NAME = 'Manage Users'; $SCREEN_ICON = 'users'; require __DIR__ . '/../includes/navbar.php'; ?>

<div style="display:flex;flex-direction:column;flex:1;padding:5px;gap:4px;min-height:0;">

    <div class="win-panel" style="padding:6px 8px;display:flex;align-items:center;">
        <span style="font-weight:bold;">Employee &amp; Login Accounts</span>
        <span style="flex:1"></span>
        <button id="btn-new-user" class="win-btn win-btn-green">&#x2795; New User</button>
    </div>

    <div class="win-panel" style="flex:1;min-height:0;display:flex;flex-direction:column;">
        <div style="flex:1;overflow:auto;min-height:0;">
            <table class="win-table">
                <thead>
                    <tr><th>User Name</th><th>Role / Description</th><th>Status</th><th style="text-align:right;">Actions</th></tr>
                </thead>
                <tbody id="users-tbody">
                    <tr><td colspan="4" style="text-align:center;padding:20px;color:#888;">Loading users…</td></tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

<div class="win-statusbar"><span>Ready</span></div>

<!-- User Modal -->
<div id="user-modal" class="fixed inset-0 hidden flex items-center justify-center" style="background:rgba(0,0,0,0.4);z-index:999;">
    <div class="win-panel" style="width:400px;max-width:90vw;">
        <div class="win-titlebar" style="cursor:default;">
            <span id="modal-title">Add New User</span>
            <span style="cursor:pointer;font-weight:bold;" onclick="closeUserModal()">&#x2715;</span>
        </div>
        <form id="user-form" style="padding:14px;display:flex;flex-direction:column;gap:10px;">
            <input type="hidden" id="is-new-user" value="true">

            <div>
                <label class="xp-label">User ID / Login</label>
                <input type="text" id="user-id" class="xp-input" required>
                <p style="font-size:10px;color:#555;margin-top:2px;">Must be unique (e.g. 'admin', 'john_d'). Cannot be changed later.</p>
            </div>

            <div>
                <label class="xp-label">Display Name</label>
                <input type="text" id="user-name" class="xp-input" required>
            </div>

            <div>
                <label class="xp-label">Role / Description</label>
                <select id="user-role" class="xp-input" required>
                    <option value="">Select Role</option>
                    <option value="Admin">Admin</option>
                    <option value="Management">Management</option>
                    <option value="Cashier">Cashier</option>
                    <option value="Inventory">Inventory</option>
                    <option value="Booking">Booking</option>
                </select>
            </div>

            <div>
                <label class="xp-label">Password</label>
                <input type="password" id="user-password" class="xp-input">
                <p id="password-help" style="font-size:10px;color:#555;margin-top:2px;">Required for new users.</p>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:8px;padding-top:6px;">
                <button type="button" class="win-btn" onclick="closeUserModal()">Cancel</button>
                <button type="submit" class="win-btn win-btn-green"><i class="fa-solid fa-floppy-disk"></i> Save</button>
            </div>
        </form>
    </div>
</div>

<div id="toast-msg" class="fixed opacity-0" style="bottom:16px;right:16px;z-index:9999;font-weight:bold;transition:all 0.3s;">Notification</div>

<script>
function clockTick() { document.getElementById('live-clock').textContent = new Date().toLocaleString('en-GB'); }
clockTick(); setInterval(clockTick, 1000);
</script>
<script src="assets/js/administration.js?v=4"></script>
</body>
</html>
