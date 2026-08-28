<?php
require_once __DIR__ . '/../includes/access.php';
requireAccess('inventory');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AISellProduct - Narcotics Register</title>
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

input[type=text] {
    border: 1px solid; border-color: #808080 #ffffff #ffffff #808080;
    background: #ffff99; padding: 2px 4px; height: 22px; font-size:12px;
    font-family: Tahoma, sans-serif; width:100%;
}
input[type=month] {
    border: 1px solid; border-color: #808080 #ffffff #ffffff #808080;
    background: #ffff99; padding: 1px 4px; height: 22px; font-size:12px;
    font-family: Tahoma, sans-serif;
}
input[readonly], input[disabled] { background: #d4d0c8 !important; color:#555; }

.win-btn {
    background: #d4d0c8; border: 1px solid; border-color: #ffffff #808080 #808080 #ffffff;
    padding: 2px 10px; cursor:pointer; font-size:12px; height:23px;
    font-family: Tahoma, sans-serif; white-space:nowrap; display:inline-flex; align-items:center; gap:3px;
}
.win-btn:hover  { background: #e8e4d8; }
.win-btn:disabled { color:#999; cursor:default; background:#d4d0c8; }
.win-btn-green { background:#1a7a1a; color:white; border-color:#5ccc5c #0a500a #0a500a #5ccc5c; }
.win-btn-green:hover { background:#218c21; }

.win-table { width:100%; border-collapse:collapse; font-size:11px; }
.win-table thead th { border:1px solid #808080; padding:3px 5px; text-align:left; font-weight:bold; background:#d4d0c8; white-space:nowrap; position:sticky; top:0; z-index:1; }
.win-table tbody tr { background:#fff; }
.win-table tbody tr:nth-child(even) { background:#f5f3ee; }
.win-table td { border:1px solid #d0ccc4; padding:3px 5px; }

.win-section-label { background:#d4d0c8; font-weight:bold; padding:3px 8px; border-bottom:1px solid #808080; display:flex; align-items:center; justify-content:space-between; }
.win-statusbar { background:#d4d0c8; border-top:1px solid #808080; padding:3px 8px; display:flex; gap:14px; }
.win-statusbar span { border-right:1px solid #808080; padding-right:12px; }

label.lbl { font-weight:bold; white-space:nowrap; width:110px; flex-shrink:0; text-align:right; padding-right:4px; }
.field-cell { display:flex; align-items:center; gap:4px; flex:1; min-width:190px; }

#toast { position:fixed; bottom:16px; right:16px; z-index:9999; padding:8px 16px; font-weight:bold; font-size:12px; border:1px solid; display:none; }
</style>
</head>
<body class="flex flex-col h-screen">

<?php $SCREEN_NAME = 'Anti Narcotics Drugs Register'; $SCREEN_ICON = 'briefcase-medical'; require __DIR__ . '/../includes/navbar.php'; ?>

<div style="display:flex;flex-direction:column;flex:1;padding:5px;gap:4px;min-height:0;overflow:auto;">

    <!-- Entry form -->
    <div class="win-panel" style="padding:8px;">
        <div class="win-section-label" style="margin:-8px -8px 8px -8px;">
            <span>Log a Dispensing Entry</span>
            <span style="font-weight:normal;color:#555;">Every field except Created By/On is optional - fill in whatever's available at the counter</span>
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:8px 20px;margin-bottom:6px;">
            <div class="field-cell" style="min-width:140px;"><label class="lbl">Ref #</label><input id="f-ref" type="text"></div>
            <div class="field-cell" style="flex:3;"><label class="lbl">Description</label><input id="f-desc" type="text"></div>
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:8px 20px;margin-bottom:6px;">
            <div class="field-cell"><label class="lbl">Doctor Name</label><input id="f-doc-name" type="text"></div>
            <div class="field-cell"><label class="lbl">Doctor Contact #</label><input id="f-doc-contact" type="text"></div>
            <div class="field-cell" style="flex:2;"><label class="lbl">Doctor Address</label><input id="f-doc-address" type="text"></div>
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:8px 20px;margin-bottom:8px;">
            <div class="field-cell"><label class="lbl">Patient Name</label><input id="f-pat-name" type="text"></div>
            <div class="field-cell" style="min-width:90px;"><label class="lbl" style="width:70px;">Age</label><input id="f-pat-age" type="text"></div>
            <div class="field-cell"><label class="lbl">Patient Contact #</label><input id="f-pat-contact" type="text"></div>
            <div class="field-cell" style="flex:2;"><label class="lbl">Patient Address</label><input id="f-pat-address" type="text"></div>
            <div class="field-cell" style="flex:2;"><label class="lbl">Remarks</label><input id="f-remarks" type="text"></div>
        </div>
        <button class="win-btn win-btn-green" onclick="createEntry()">&#x2795; Add Entry</button>
    </div>

    <!-- Register list -->
    <div class="win-panel" style="flex:1;min-height:200px;display:flex;flex-direction:column;">
        <div class="win-section-label">
            <span>Register Entries - newest first</span>
            <div style="display:flex;align-items:center;gap:6px;">
                <label style="font-weight:normal;">Month</label>
                <input id="f-month" type="month" onchange="loadEntries()">
                <button class="win-btn" onclick="document.getElementById('f-month').value='';loadEntries();">Clear</button>
                <span id="result-count" style="font-weight:normal;color:#555;"></span>
            </div>
        </div>
        <div style="flex:1;overflow:auto;min-height:0;">
            <table class="win-table">
                <thead>
                    <tr>
                        <th>Ref #</th><th>Description</th><th>Doctor</th><th>Doctor Contact</th>
                        <th>Patient</th><th>Age</th><th>Patient Contact</th><th>Remarks</th>
                        <th>Created By</th><th>Created On</th>
                    </tr>
                </thead>
                <tbody id="results-body"><tr><td colspan="10" style="text-align:center;padding:10px;color:#888;">Pick a month above to load its entries, or Clear to load everything.</td></tr></tbody>
            </table>
        </div>
    </div>

</div>

<div class="win-statusbar">
    <span id="status-msg">Ready</span>
</div>

<div id="toast"></div>

<script>
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

const _nativeFetch = window.fetch;
window.fetch = function(...args) {
    return _nativeFetch.apply(this, args).catch(err => {
        toast('Network/Server error - check DB_SERVER in .env and that the database is reachable', 'err');
        throw err;
    });
};

function setStatus(msg) { document.getElementById('status-msg').textContent = msg; }
function esc(s) { const d = document.createElement('div'); d.textContent = s ?? ''; return d.innerHTML; }

function loadEntries() {
    setStatus('Loading…');
    const params = new URLSearchParams({ month: document.getElementById('f-month').value });
    fetch('api/list_narcotics.php?' + params.toString())
        .then(r => r.json())
        .then(rows => {
            if (rows && rows.error) {
                toast('Error: ' + rows.error, 'err');
                document.getElementById('results-body').innerHTML =
                    '<tr><td colspan="10" style="text-align:center;padding:10px;color:darkred;">' + esc(rows.error) + '</td></tr>';
                setStatus('Ready');
                return;
            }
            renderRows(rows);
            setStatus('Ready');
        })
        .catch(() => {
            document.getElementById('results-body').innerHTML =
                '<tr><td colspan="10" style="text-align:center;color:darkred;padding:10px;">Could not load register - check DB connection</td></tr>';
        });
}

function renderRows(rows) {
    const tbody = document.getElementById('results-body');
    document.getElementById('result-count').textContent = rows.length + ' entr' + (rows.length === 1 ? 'y' : 'ies');
    if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="10" style="text-align:center;padding:10px;color:#888;">No narcotics register entries yet.</td></tr>';
        return;
    }
    tbody.innerHTML = rows.map(r => `
        <tr>
            <td>${esc(r.ref_no) || '-'}</td>
            <td>${esc(r.description) || '-'}</td>
            <td>${esc(r.doctor_name) || '-'}</td>
            <td>${esc(r.doctor_contact) || '-'}</td>
            <td>${esc(r.patient_name) || '-'}</td>
            <td>${esc(r.patient_age) || '-'}</td>
            <td>${esc(r.patient_contact) || '-'}</td>
            <td>${esc(r.remarks) || '-'}</td>
            <td>${esc(r.created_by) || '-'}</td>
            <td>${esc(r.created_on)}</td>
        </tr>`).join('');
}

function createEntry() {
    const payload = {
        ref_no: document.getElementById('f-ref').value.trim(),
        description: document.getElementById('f-desc').value.trim(),
        doctor_name: document.getElementById('f-doc-name').value.trim(),
        doctor_contact: document.getElementById('f-doc-contact').value.trim(),
        doctor_address: document.getElementById('f-doc-address').value.trim(),
        patient_name: document.getElementById('f-pat-name').value.trim(),
        patient_age: document.getElementById('f-pat-age').value.trim(),
        patient_contact: document.getElementById('f-pat-contact').value.trim(),
        patient_address: document.getElementById('f-pat-address').value.trim(),
        remarks: document.getElementById('f-remarks').value.trim(),
    };
    setStatus('Saving…');
    fetch('api/save_narcotics.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
        .then(r => r.json())
        .then(res => {
            if (res && res.error) { toast('Error: ' + res.error, 'err'); setStatus('Ready'); return; }
            toast('Entry added', 'ok');
            ['f-ref','f-desc','f-doc-name','f-doc-contact','f-doc-address','f-pat-name','f-pat-age','f-pat-contact','f-pat-address','f-remarks']
                .forEach(id => document.getElementById(id).value = '');
            loadEntries();
        })
        .catch(() => setStatus('Ready'));
}

// Deliberately not calling loadEntries() here -- unlike Clear (an explicit
// "yes, show me everything" click), a bare page load shouldn't pull the
// whole register by default. Pick a month, or click Clear, to load it.
</script>
</body>
</html>
