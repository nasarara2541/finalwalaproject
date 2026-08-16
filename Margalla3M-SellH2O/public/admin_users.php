<?php
require_once __DIR__ . '/includes/require_admin.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AISellProduct - Manage Users</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
* { font-family: Tahoma, 'Segoe UI', sans-serif; font-size: 12px; box-sizing: border-box; }
html, body { height: 100%; margin: 0; }
body { background: #d4d0c8; overflow: hidden; }

.win-inset  { border: 1px solid; border-color: #808080 #ffffff #ffffff #808080; background:#fff; }
.win-panel  { border: 1px solid; border-color: #808080 #ffffff #ffffff #808080; background:#ece9d8; }
.win-titlebar { background: linear-gradient(to right, #0a246a, #3a6ea5); color: white; font-weight: bold; font-size: 12px; padding: 4px 8px; display:flex; align-items:center; justify-content:space-between; }
.win-menubar { background: #d4d0c8; border-bottom: 1px solid #808080; display:flex; gap:0; padding: 2px 2px; }
.win-menu-item { padding: 3px 10px; cursor:pointer; font-size:12px; }
.win-menu-item:hover, .win-menu-item.active { background:#0a246a; color:white; }
.nav-active { background:#0a246a !important; color:white !important; }

input[type=text], input[type=password] {
    border: 1px solid; border-color: #808080 #ffffff #ffffff #808080;
    background: #ffff99; padding: 2px 4px; height: 22px; font-size:12px;
    font-family: Tahoma, sans-serif; width:100%;
}
input.field-white { background:#fff !important; }
input[readonly] { background: #d4d0c8 !important; color:#333; }
input:focus { outline: 2px solid #0a246a; }

.win-btn {
    background: #d4d0c8; border: 1px solid; border-color: #ffffff #808080 #808080 #ffffff;
    padding: 2px 10px; cursor:pointer; font-size:12px; height:23px;
    font-family: Tahoma, sans-serif; white-space:nowrap; display:inline-flex; align-items:center; gap:3px;
}
.win-btn:hover  { background: #e8e4d8; }
.win-btn-green { background:#1a7a1a; color:white; border-color:#44aa44 #0a500a #0a500a #44aa44; }
.win-btn-green:hover { background:#1e8c1e; }
.win-btn-red   { background:#8b0000; color:white; border-color:#cc4444 #550000 #550000 #cc4444; }
.win-btn-red:hover { background:#a00000; }

.win-table { width:100%; border-collapse:collapse; font-size:11px; }
.win-table thead th { border:1px solid #808080; padding:3px 5px; text-align:left; font-weight:bold; background:#d4d0c8; white-space:nowrap; position:sticky; top:0; z-index:1; }
.win-table tbody tr { background:#fff; }
.win-table tbody tr:nth-child(even) { background:#f5f3ee; }
.win-table tbody tr:hover { background:#c5d5e8 !important; cursor:pointer; }
.win-table tbody tr.row-selected { background:#0a246a !important; color:white; }
.win-table tbody tr.row-selected .pill { outline: 1px solid white; }
.win-table td { border:1px solid #d0ccc4; padding:3px 5px; white-space:nowrap; }

.win-section-label { background:#d4d0c8; font-weight:bold; padding:3px 8px; border-bottom:1px solid #808080; display:flex; align-items:center; justify-content:space-between; }
.win-statusbar { background:#d4d0c8; border-top:1px solid #808080; padding:3px 8px; display:flex; gap:14px; }
.win-statusbar span { border-right:1px solid #808080; padding-right:12px; }

label.lbl { font-weight:bold; white-space:nowrap; width:110px; flex-shrink:0; }
.field-row { display:flex; align-items:center; gap:6px; margin-bottom:6px; }
.legend-text { color:#333; font-size:11px; white-space:nowrap; }
.required-star { color:darkred; }

.pill { display:inline-block; padding:1px 8px; border-radius:9px; font-size:10px; font-weight:bold; color:white; }
.pill-admin  { background:#0a246a; }
.pill-mgmt   { background:#3a6ea5; }
.pill-active { background:#1a7a1a; }
.pill-inactive { background:#8b0000; }

#toast { position:fixed; bottom:16px; right:16px; z-index:9999; padding:8px 16px; font-weight:bold; font-size:12px; border:1px solid; display:none; }
</style>
</head>
<body class="flex flex-col h-screen">

<div class="win-titlebar">
    <span>&#x1F464; AISellProduct &mdash; Manage Users</span>
    <span id="live-clock" style="font-weight:normal;font-size:11px;"></span>
</div>

<div class="win-menubar">
    <span class="win-menu-item" onclick="window.location='pos.php'">&#x2190; Back to Sale</span>
    <span class="win-menu-item nav-active">Manage Users</span>
    <span class="win-menu-item" style="color:#5b3a8a;font-weight:bold;" onclick="window.location='admin_dashboard.php'">&#x1F4CA; Dashboard</span>
    <span class="win-menu-item" style="color:#5b3a8a;font-weight:bold;" onclick="window.location='admin_reports.php'">&#x1F4C8; Profit Reports</span>
    <span class="win-menu-item" style="color:#5b3a8a;font-weight:bold;" onclick="window.location='item_details.php'">&#x1F4E6; Item Details</span>
    <span style="flex:1"></span>
    <span class="win-menu-item" style="color:#555;">Database: <b><?php echo htmlspecialchars($_SESSION['active_db_label'] ?? 'Water Distribution'); ?></b></span>
    <span class="win-menu-item" onclick="window.location='login.php'" title="Pick a different database">&#x1F504; Switch Database</span>
    <span class="win-menu-item" style="color:#555;">User: <b><?php echo htmlspecialchars($_SESSION['emp_user_name'] ?? '—'); ?></b></span>
    <span class="win-menu-item" onclick="window.location='logout.php'" title="Sign out" style="color:darkred;">&#x1F6AA; Logout</span>
</div>

<div style="display:flex;flex-direction:column;flex:1;padding:5px;gap:4px;min-height:0;">

    <div style="display:flex;gap:4px;align-items:stretch;">

        <div class="win-panel" style="padding:6px;flex:1.4;">
            <div class="win-section-label" style="margin:-6px -6px 6px -6px;">
                <span>Employee Details</span>
                <span id="form-mode" style="font-weight:normal;font-size:11px;color:#555;">New User</span>
            </div>

            <div class="field-row">
                <label class="lbl">User ID <span class="required-star">*</span></label>
                <input id="user-id-field" type="text" class="field-white" placeholder="Login username, e.g. sara" style="flex:1;">
            </div>

            <div class="field-row">
                <label class="lbl">Full Name <span class="required-star">*</span></label>
                <input id="user-fullname" type="text" placeholder="e.g. Sara Ahmed" style="flex:1;">
            </div>

            <div class="field-row">
                <label class="lbl">Address</label>
                <input id="user-address" type="text" placeholder="Optional" style="flex:1;">
            </div>

            <div class="field-row">
                <label class="lbl">City</label>
                <input id="user-city" type="text" placeholder="Optional" style="width:150px;flex:none;">
                <label class="lbl" style="width:auto;margin-left:8px;">Tel No</label>
                <input id="user-telno" type="text" placeholder="Optional" style="flex:1;">
            </div>

            <div class="field-row" style="margin-bottom:0;">
                <label class="lbl">Mobile No</label>
                <input id="user-mobileno" type="text" placeholder="Optional" style="flex:1;">
            </div>
        </div>

        <div class="win-panel" style="padding:6px;flex:1;display:flex;flex-direction:column;">
            <div class="win-section-label" style="margin:-6px -6px 6px -6px;">
                <span>Login &amp; Access</span>
            </div>

            <div class="field-row">
                <label class="lbl">Password <span class="required-star">*</span></label>
                <input id="user-password" type="password" placeholder="Required for new user" style="flex:1;" autocomplete="new-password">
            </div>
            <div class="field-row">
                <label class="lbl">Confirm</label>
                <input id="user-password-confirm" type="password" placeholder="Re-type password" style="flex:1;" autocomplete="new-password">
            </div>
            <div class="field-row" style="margin-top:-2px;">
                <span class="legend-text" style="margin-left:116px;" id="password-hint">Leave both blank to keep the current password when editing.</span>
            </div>

            <div class="field-row">
                <label class="lbl">Description</label>
                <input id="user-desc" type="text" placeholder="e.g. Cashier, Store Manager" style="flex:1;">
            </div>

            <div class="field-row">
                <label class="lbl">Role</label>
                <label style="display:flex;align-items:center;gap:3px;font-weight:normal;"><input type="radio" name="user-role" value="admin" style="width:auto;height:auto;"> Administrator</label>
                <label style="display:flex;align-items:center;gap:3px;font-weight:normal;margin-left:10px;"><input type="radio" name="user-role" value="management" checked style="width:auto;height:auto;"> Management</label>
            </div>

            <div class="field-row">
                <label class="lbl">Active</label>
                <label style="display:flex;align-items:center;gap:5px;font-weight:normal;">
                    <input type="checkbox" id="user-active" checked style="width:auto;height:auto;">
                    <span class="legend-text">Unchecking blocks this user from logging in</span>
                </label>
            </div>

            <div style="flex:1;"></div>
            <div style="display:flex;gap:5px;flex-wrap:wrap;margin-top:2px;">
                <button class="win-btn" onclick="newUser()">New</button>
                <button class="win-btn win-btn-green" onclick="saveUser()">Save</button>
            </div>
        </div>

    </div>

    <div class="win-panel" style="flex:1;display:flex;flex-direction:column;padding:8px;min-height:0;">
        <div class="win-section-label" style="margin:-8px -8px 8px -8px;">
            <span>All Users</span>
            <span style="display:flex;align-items:center;gap:6px;">
                <input id="user-filter-text" type="text" class="field-white" placeholder="Search by name or user ID…" style="width:220px;height:20px;" oninput="filterUsers()">
                <button class="win-btn" style="height:18px;font-size:11px;padding:0 8px;" onclick="loadUsers()">Refresh</button>
            </span>
        </div>
        <div style="flex:1;overflow:auto;min-height:0;">
            <table class="win-table">
                <thead>
                    <tr>
                        <th>User ID</th><th>Full Name</th><th>Role</th><th>Status</th>
                        <th>Description</th><th>Mobile No</th><th>City</th>
                    </tr>
                </thead>
                <tbody id="users-grid-body"><tr><td colspan="7" style="text-align:center;padding:8px;color:#888;">Loading…</td></tr></tbody>
            </table>
        </div>
    </div>

</div>

<div class="win-statusbar">
    <span id="status-msg">Ready</span>
    <span id="user-count"></span>
</div>

<div id="toast"></div>

<script>
let allUsers = [];
let userCurrentList = [];
let selectedUserId = null;

function clockTick() {
    document.getElementById('live-clock').textContent = new Date().toLocaleString('en-GB');
}
clockTick();
setInterval(clockTick, 1000);

function toast(msg, type) {
    const el = document.getElementById('toast');
    el.textContent = msg;
    el.style.display = 'block';
    el.style.background = type==='ok'?'#1a7a1a':type==='warn'?'#b8860b':'#990000';
    el.style.color = 'white';
    el.style.borderColor = type==='ok'?'#0a500a':type==='warn'?'#8b6508':'#660000';
    setTimeout(()=>{ el.style.display='none'; }, 3000);
}

// Any fetch() call below now surfaces a network/server failure (DB
// unreachable, wrong DB_SERVER in .env, connection dropped) as a toast
// instead of leaving the screen silently stuck on "Loading…" forever — the
// original rejection still propagates so each caller's existing .then()
// chain behaves exactly as it did before.
const _nativeFetch = window.fetch;
window.fetch = function(...args) {
    return _nativeFetch.apply(this, args).catch(err => {
        toast('Network/Server error — check DB_SERVER in .env and that the database is reachable', 'err');
        throw err;
    });
};

function setStatus(msg) { document.getElementById('status-msg').textContent = msg; }

function roleOf(u) { return u.Local_admin === 'A' ? 'admin' : 'management'; }

function loadUsers() {
    setStatus('Loading users…');
    fetch('api/get_users.php')
        .then(r => r.json())
        .then(rows => {
            if (rows && rows.error) { toast('Error: ' + rows.error, 'err'); return; }
            allUsers = rows;
            filterUsers();
            setStatus('Ready');
        })
        .catch(() => {
            toast('Network error loading users', 'err');
            document.getElementById('users-grid-body').innerHTML =
                '<tr><td colspan="7" style="text-align:center;color:darkred;padding:8px;">Could not load users — check DB connection</td></tr>';
        });
}

function renderUsersGrid() {
    const tbody = document.getElementById('users-grid-body');
    tbody.innerHTML = '';
    document.getElementById('user-count').textContent = userCurrentList.length + ' user(s)';
    if (!userCurrentList.length) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:8px;color:#888;">No users found</td></tr>';
        return;
    }
    userCurrentList.forEach(u => {
        const tr = document.createElement('tr');
        tr.setAttribute('tabindex', '0');
        tr.className = u.User_id === selectedUserId ? 'row-selected' : '';
        const rolePill   = roleOf(u) === 'admin' ? '<span class="pill pill-admin">Administrator</span>' : '<span class="pill pill-mgmt">Management</span>';
        const statusPill = u.Login_status === 'Y' ? '<span class="pill pill-active">Active</span>' : '<span class="pill pill-inactive">Inactive</span>';
        tr.innerHTML = `<td style="font-weight:bold;color:#0a246a;">${u.User_id}</td><td>${u.Full_Name || u.User_name || ''}</td>
            <td>${rolePill}</td><td>${statusPill}</td>
            <td>${u.User_desc || ''}</td><td>${u.Mobile_no || ''}</td><td>${u.City || ''}</td>`;
        tr.onclick = () => selectUser(u);
        tbody.appendChild(tr);
    });
}

function filterUsers() {
    const q = document.getElementById('user-filter-text').value.trim().toLowerCase();
    userCurrentList = !q ? allUsers : allUsers.filter(u => {
        const name = (u.Full_Name || u.User_name || '').toLowerCase();
        const id   = (u.User_id || '').toLowerCase();
        return name.includes(q) || id.includes(q);
    });
    renderUsersGrid();
}

function selectUser(u) {
    selectedUserId = u.User_id;
    const idField = document.getElementById('user-id-field');
    idField.value = u.User_id;
    idField.readOnly = true;

    document.getElementById('user-fullname').value  = u.Full_Name || '';
    document.getElementById('user-address').value   = u.Address || '';
    document.getElementById('user-city').value      = u.City || '';
    document.getElementById('user-telno').value     = u.Tel_no || '';
    document.getElementById('user-mobileno').value  = u.Mobile_no || '';
    document.getElementById('user-desc').value      = u.User_desc || '';
    document.getElementById('user-password').value  = '';
    document.getElementById('user-password-confirm').value = '';
    document.querySelector('input[name="user-role"][value="' + roleOf(u) + '"]').checked = true;
    document.getElementById('user-active').checked  = (u.Login_status === 'Y');

    document.getElementById('password-hint').textContent = 'Leave both blank to keep the current password.';
    document.getElementById('form-mode').textContent = 'Editing: ' + u.User_id;
    renderUsersGrid();
    setStatus('Selected: ' + u.User_id);
}

function newUser() {
    selectedUserId = null;
    const idField = document.getElementById('user-id-field');
    idField.readOnly = false;
    idField.value = '';

    document.getElementById('user-fullname').value  = '';
    document.getElementById('user-address').value   = '';
    document.getElementById('user-city').value      = '';
    document.getElementById('user-telno').value     = '';
    document.getElementById('user-mobileno').value  = '';
    document.getElementById('user-desc').value      = '';
    document.getElementById('user-password').value  = '';
    document.getElementById('user-password-confirm').value = '';
    document.querySelector('input[name="user-role"][value="management"]').checked = true;
    document.getElementById('user-active').checked  = true;

    document.getElementById('password-hint').textContent = 'Leave both blank to keep the current password when editing.';
    document.getElementById('form-mode').textContent = 'New User';
    renderUsersGrid();
    idField.focus();
    setStatus('Ready for new user');
}

function saveUser() {
    const isNew    = !selectedUserId;
    const userId   = document.getElementById('user-id-field').value.trim();
    const fullName = document.getElementById('user-fullname').value.trim();
    const password = document.getElementById('user-password').value;
    const confirm  = document.getElementById('user-password-confirm').value;
    const role     = document.querySelector('input[name="user-role"]:checked').value;
    const active   = document.getElementById('user-active').checked;

    if (!userId)   { toast('User ID is required', 'warn'); document.getElementById('user-id-field').focus(); return; }
    if (!fullName) { toast('Full Name is required', 'warn'); document.getElementById('user-fullname').focus(); return; }
    if (isNew && !password) { toast('Password is required for a new user', 'warn'); document.getElementById('user-password').focus(); return; }
    if (password !== confirm) { toast('Password and Confirm Password do not match', 'warn'); document.getElementById('user-password-confirm').focus(); return; }
    if (password && password.length < 4) { toast('Password must be at least 4 characters', 'warn'); document.getElementById('user-password').focus(); return; }

    const payload = {
        original_user_id: selectedUserId || '',
        user_id:    userId,
        password:   password,
        full_name:  fullName,
        address:    document.getElementById('user-address').value.trim(),
        city:       document.getElementById('user-city').value.trim(),
        tel_no:     document.getElementById('user-telno').value.trim(),
        mobile_no:  document.getElementById('user-mobileno').value.trim(),
        user_desc:  document.getElementById('user-desc').value.trim(),
        role:       role,
        active:     active
    };

    fetch('api/save_user.php', { method: 'POST', body: JSON.stringify(payload) })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                toast(res.mode === 'inserted' ? 'User added — ' + res.user_id : 'User updated', 'ok');
                selectedUserId = res.user_id;
                document.getElementById('user-id-field').readOnly = true;
                document.getElementById('form-mode').textContent = 'Editing: ' + res.user_id;
                loadUsers();
            } else {
                toast('Error: ' + (res.error || 'Save failed'), 'err');
            }
        })
        .catch(() => toast('Network error saving user', 'err'));
}

const userFieldOrder = ['user-id-field','user-fullname','user-address','user-city','user-telno','user-mobileno',
    'user-password','user-password-confirm','user-desc'];

document.addEventListener('keydown', e => {
    if (e.key !== 'Enter') return;
    const active = document.activeElement;
    const id = active.id;

    if (id === 'user-filter-text') {
        e.preventDefault();
        if (userCurrentList.length) selectUser(userCurrentList[0]);
        return;
    }

    if (active.tagName === 'TR' && active.closest('#users-grid-body')) {
        e.preventDefault();
        const u = userCurrentList.find(x => x.User_id === active.children[0].textContent);
        if (u) selectUser(u);
        document.getElementById('user-fullname').focus();
        return;
    }

    if (id === 'user-desc') {
        e.preventDefault();
        saveUser();
        return;
    }

    const idx = userFieldOrder.indexOf(id);
    if (idx !== -1 && idx < userFieldOrder.length - 1) {
        e.preventDefault();
        document.getElementById(userFieldOrder[idx + 1]).focus();
    }
});

loadUsers();
</script>
</body>
</html>
