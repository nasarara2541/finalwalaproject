<?php
require_once __DIR__ . '/includes/require_login.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manufacture List — AISellProduct</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
* { font-family: Tahoma, 'Segoe UI', sans-serif; font-size: 12px; box-sizing: border-box; }
body { background: #d4d0c8; margin: 0; padding: 0; }

.win-titlebar { background: linear-gradient(to right, #0a246a, #3a6ea5); color:white; font-weight:bold; padding:5px 10px; display:flex; align-items:center; justify-content:space-between; }
.win-inset  { border: 1px solid; border-color: #808080 #ffffff #ffffff #808080; background:#fff; }
.win-panel  { border: 1px solid; border-color: #808080 #ffffff #ffffff #808080; background:#ece9d8; }
.win-section-label { background:#d4d0c8; font-weight:bold; padding:3px 8px; border-bottom:1px solid #808080; display:flex; align-items:center; justify-content:space-between; }

input[type=text] {
    border: 1px solid; border-color: #808080 #ffffff #ffffff #808080;
    background: #ffff99; padding: 2px 5px; height: 22px; font-size:12px;
    font-family: Tahoma, sans-serif; width: 100%;
}
input[readonly] { background: #d4d0c8 !important; }
input:focus { outline: 2px solid #0a246a; }

.win-btn { background:#d4d0c8; border:1px solid; border-color:#ffffff #808080 #808080 #ffffff; padding:3px 14px; cursor:pointer; font-size:12px; height:24px; font-family:Tahoma,sans-serif; white-space:nowrap; display:inline-flex; align-items:center; gap:3px; }
.win-btn:hover { background:#e8e4d8; }
.win-btn-green { background:#1a7a1a; color:white; border-color:#44aa44 #0a500a #0a500a #44aa44; }
.win-btn-green:hover { background:#1e8c1e; }
.win-btn-red { background:#8b0000; color:white; border-color:#cc4444 #550000 #550000 #cc4444; }
.win-btn-red:hover { background:#a00000; }

label.lbl { font-weight:bold; display:block; margin-bottom:2px; }
.field-col { display:flex; flex-direction:column; }

.mfg-list-row { padding:2px 6px; cursor:pointer; white-space:nowrap; }
.mfg-list-row:hover { background:#c5d5e8; }
.mfg-list-row.row-selected { background:#0a246a; color:#fff; }

#toast { position:fixed; bottom:12px; right:12px; z-index:9999; padding:7px 14px; font-weight:bold; font-size:12px; border:1px solid; display:none; }
</style>
</head>
<body class="flex flex-col" style="min-height:100vh;">

<div class="win-titlebar">
    <span>Manufacture List</span>
    <button onclick="window.close()" style="background:transparent;border:none;color:white;cursor:pointer;font-size:14px;font-weight:bold;">&#x2716;</button>
</div>

<div style="padding:8px;flex:1;overflow-y:auto;">

    <div class="win-panel" style="padding:8px;margin-bottom:6px;">
        <div class="win-section-label" style="margin:-8px -8px 8px -8px;">
            <span>Manufacturer Details</span>
            <span id="form-mode" style="font-weight:normal;font-size:11px;color:#555;">New Record</span>
        </div>
        <div style="display:grid;grid-template-columns:100px 1fr 1fr;gap:8px;">
            <div class="field-col">
                <label class="lbl">Manufacture No</label>
                <input id="mfg-no" type="text" readonly value="(New)" tabindex="-1">
            </div>
            <div class="field-col">
                <label class="lbl">Manufacturer Name <span style="color:darkred;">*</span></label>
                <input id="mfg-name" type="text" placeholder="e.g. Nestle Pure Life" maxlength="100">
            </div>
            <div class="field-col">
                <label class="lbl">Short Name</label>
                <input id="mfg-shortname" type="text" placeholder="e.g. NESTLE" maxlength="50">
            </div>
            <div class="field-col" style="grid-column:span 3;">
                <label class="lbl">Address</label>
                <input id="mfg-address" type="text" placeholder="Street address" maxlength="255">
            </div>
            <div class="field-col">
                <label class="lbl">City</label>
                <input id="mfg-city" type="text" placeholder="e.g. Islamabad" maxlength="100">
            </div>
            <div class="field-col">
                <label class="lbl">Tel No</label>
                <input id="mfg-tel" type="text" placeholder="e.g. 051-1234567" maxlength="20">
            </div>
        </div>
        <div style="display:flex;gap:6px;margin-top:8px;">
            <button class="win-btn" onclick="newManufacturer()">New</button>
            <button class="win-btn win-btn-green" onclick="saveManufacturer()">Save</button>
            <button class="win-btn win-btn-red" onclick="removeManufacturer()">Remove</button>
        </div>
    </div>

    <div class="win-panel" style="padding:8px;">
        <div class="win-section-label" style="margin:-8px -8px 8px -8px;">
            <span>Manufacturer List</span>
            <button class="win-btn" style="height:18px;font-size:11px;padding:0 8px;" onclick="loadManufacturers()">Refresh</button>
        </div>
        <div style="display:flex;gap:4px;margin-bottom:6px;align-items:center;">
            <label class="lbl" style="margin-bottom:0;">Manufacturer:</label>
            <input id="mfg-search" type="text" placeholder="Enter Name for search" oninput="filterManufacturers(this.value)">
        </div>
        <div class="win-inset" id="mfg-listbox" style="height:220px;overflow-y:auto;"></div>
        <div style="display:flex;align-items:center;gap:6px;margin-top:6px;">
            <label class="lbl" style="margin-bottom:0;">Selected:</label>
            <input id="mfg-selected-display" type="text" readonly value="" tabindex="-1">
        </div>
    </div>

    <div style="color:#8b0000;font-size:11px;padding:5px 0;">
        <span style="color:darkred;">*</span> Required field.
    </div>
</div>

<div id="toast"></div>

<script>
let allManufacturers = [];
let currentList = [];
let selectedManufactureNo = null;

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

function loadManufacturers() {
    fetch('api/get_manufacturers.php')
        .then(r => r.json())
        .then(rows => {
            if (rows && rows.error) { toast('Error: ' + rows.error, 'err'); return; }
            allManufacturers = rows;
            currentList = rows;
            renderList();
            if (window.opener && !window.opener.closed) window.opener.refreshManufacturersFromChild && window.opener.refreshManufacturersFromChild();
        })
        .catch(() => {
            toast('Network error loading manufacturers', 'err');
            document.getElementById('mfg-listbox').innerHTML =
                '<div style="padding:6px;color:darkred;">Could not load — check DB connection</div>';
        });
}

function renderList() {
    const box = document.getElementById('mfg-listbox');
    box.innerHTML = '';
    if (!currentList.length) {
        box.innerHTML = '<div style="padding:6px;color:#888;">No manufacturers found</div>';
        return;
    }
    currentList.forEach(m => {
        const row = document.createElement('div');
        row.setAttribute('tabindex', '0');
        row.className = 'mfg-list-row' + (m.Manufacture_no === selectedManufactureNo ? ' row-selected' : '');
        row.textContent = m.Manufacture_no + ':' + m.M_Name + '-' + (m.M_ShortName || '');
        row.onclick = () => selectManufacturer(m);
        box.appendChild(row);
    });
}

function filterManufacturers(q) {
    q = q.trim().toLowerCase();
    currentList = !q ? allManufacturers : allManufacturers.filter(m =>
        (m.M_Name || '').toLowerCase().includes(q) || (m.M_ShortName || '').toLowerCase().includes(q)
    );
    renderList();
}

function selectManufacturer(m) {
    selectedManufactureNo = m.Manufacture_no;
    document.getElementById('mfg-no').value = m.Manufacture_no;
    document.getElementById('mfg-name').value = m.M_Name || '';
    document.getElementById('mfg-shortname').value = m.M_ShortName || '';
    document.getElementById('mfg-address').value = m.Address || '';
    document.getElementById('mfg-city').value = m.City || '';
    document.getElementById('mfg-tel').value = m.Tel_no || '';
    document.getElementById('mfg-selected-display').value = m.Manufacture_no + ': ' + m.M_Name;
    document.getElementById('form-mode').textContent = 'Editing Record';
    renderList();
}

function newManufacturer() {
    selectedManufactureNo = null;
    document.getElementById('mfg-no').value = '(New)';
    document.getElementById('mfg-name').value = '';
    document.getElementById('mfg-shortname').value = '';
    document.getElementById('mfg-address').value = '';
    document.getElementById('mfg-city').value = '';
    document.getElementById('mfg-tel').value = '';
    document.getElementById('mfg-selected-display').value = '';
    document.getElementById('form-mode').textContent = 'New Record';
    renderList();
    document.getElementById('mfg-name').focus();
}

function saveManufacturer() {
    const name = document.getElementById('mfg-name').value.trim();
    if (!name) { toast('Manufacturer Name is required', 'warn'); document.getElementById('mfg-name').focus(); return; }

    const payload = {
        manufacture_no: selectedManufactureNo,
        m_name: name,
        m_shortname: document.getElementById('mfg-shortname').value.trim(),
        address: document.getElementById('mfg-address').value.trim(),
        city: document.getElementById('mfg-city').value.trim(),
        tel_no: document.getElementById('mfg-tel').value.trim()
    };

    fetch('api/save_manufacturer.php', { method: 'POST', body: JSON.stringify(payload) })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                toast(selectedManufactureNo ? 'Manufacturer updated' : 'Manufacturer added', 'ok');
                if (!selectedManufactureNo && res.new_id) selectedManufactureNo = res.new_id;
                loadManufacturers();
            } else {
                toast('Error: ' + (res.error || 'Save failed'), 'err');
            }
        })
        .catch(() => toast('Network error saving manufacturer', 'err'));
}

function removeManufacturer() {
    if (!selectedManufactureNo) { toast('Select a manufacturer from the list first', 'warn'); return; }
    const name = document.getElementById('mfg-name').value;
    if (!confirm('Remove manufacturer "' + name + '"? This cannot be undone.')) return;

    fetch('api/delete_manufacturer.php', { method: 'POST', body: JSON.stringify({ manufacture_no: selectedManufactureNo }) })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                toast('Manufacturer removed', 'ok');
                newManufacturer();
                loadManufacturers();
            } else {
                toast('Error: ' + (res.error || 'Remove failed'), 'err');
            }
        })
        .catch(() => toast('Network error removing manufacturer', 'err'));
}

const mfgFieldOrder = ['mfg-name','mfg-shortname','mfg-address','mfg-city','mfg-tel'];

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { window.close(); return; }
    if (e.key !== 'Enter') return;

    const active = document.activeElement;
    const id = active.id;

    if (id === 'mfg-search') {
        e.preventDefault();
        if (currentList.length) selectManufacturer(currentList[0]);
        document.getElementById('mfg-name').focus();
        return;
    }

    if (active.tagName === 'DIV' && active.closest('#mfg-listbox')) {
        e.preventDefault();
        const no = parseInt(active.textContent.split(':')[0]);
        const m  = currentList.find(x => x.Manufacture_no === no);
        if (m) selectManufacturer(m);
        document.getElementById('mfg-name').focus();
        return;
    }

    if (id === 'mfg-tel') {
        e.preventDefault();
        saveManufacturer();
        return;
    }

    const idx = mfgFieldOrder.indexOf(id);
    if (idx !== -1 && idx < mfgFieldOrder.length - 1) {
        e.preventDefault();
        document.getElementById(mfgFieldOrder[idx + 1]).focus();
    }
});

loadManufacturers();
</script>
</body>
</html>
