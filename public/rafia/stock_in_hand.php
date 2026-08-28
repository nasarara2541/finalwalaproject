<?php
require_once __DIR__ . '/../includes/access.php';
requireAccess('inventory');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AISellProduct - Stock In Hand</title>
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
.win-table td { border:1px solid #d0ccc4; padding:3px 5px; }

.win-section-label { background:#d4d0c8; font-weight:bold; padding:3px 8px; border-bottom:1px solid #808080; display:flex; align-items:center; justify-content:space-between; }
.win-statusbar { background:#d4d0c8; border-top:1px solid #808080; padding:3px 8px; display:flex; gap:14px; }
.win-statusbar span { border-right:1px solid #808080; padding-right:12px; }

label.lbl { font-weight:bold; white-space:nowrap; padding-right:4px; }
.field-cell { display:flex; align-items:center; gap:4px; }

#toast { position:fixed; bottom:16px; right:16px; z-index:9999; padding:8px 16px; font-weight:bold; font-size:12px; border:1px solid; display:none; }
</style>
</head>
<body class="flex flex-col h-screen">

<?php $SCREEN_NAME = 'Stock In Hand'; $SCREEN_ICON = 'boxes-stacked'; require __DIR__ . '/../includes/navbar.php'; ?>

<div style="display:flex;flex-direction:column;flex:1;padding:5px;gap:4px;min-height:0;">

    <div class="win-panel" style="padding:8px;display:flex;align-items:center;gap:8px;">
        <div class="field-cell" style="flex:1;max-width:400px;">
            <label class="lbl">Search</label>
            <input id="f-search" type="text" placeholder="Item name or stock number" oninput="debouncedSearch()">
        </div>
        <span style="color:#555;">Live snapshot of <code>QTY_INHAND</code> at this instant — not a point-in-time report; no stock-movement history table exists for that yet.</span>
    </div>

    <div class="win-panel" style="flex:1;min-height:0;display:flex;flex-direction:column;">
        <div class="win-section-label">
            <span>Stock In Hand</span>
            <span id="result-count" style="font-weight:normal;color:#555;"></span>
        </div>
        <div style="flex:1;overflow:auto;min-height:0;">
            <table class="win-table">
                <thead><tr><th>Stock Number</th><th>Item Name</th><th style="text-align:right;">Qty In Hand</th></tr></thead>
                <tbody id="results-body"><tr><td colspan="3" style="text-align:center;padding:10px;color:#888;">Type an item name or stock number above to search.</td></tr></tbody>
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
        toast('Network/Server error — check DB_SERVER in .env and that the database is reachable', 'err');
        throw err;
    });
};

function setStatus(msg) { document.getElementById('status-msg').textContent = msg; }
function esc(s) { const d = document.createElement('div'); d.textContent = s ?? ''; return d.innerHTML; }

let searchTimer = null;
function debouncedSearch() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(runSearch, 250);
}

function runSearch() {
    const q = document.getElementById('f-search').value.trim();
    if (!q) {
        // The API treats a blank q as "match everything", which is exactly
        // the full-table load this screen shouldn't do by default -- so
        // just don't call it until there's something to actually search for.
        document.getElementById('results-body').innerHTML =
            '<tr><td colspan="3" style="text-align:center;padding:10px;color:#888;">Type an item name or stock number above to search.</td></tr>';
        document.getElementById('result-count').textContent = '';
        setStatus('Ready');
        return;
    }
    setStatus('Searching…');
    const params = new URLSearchParams({ q });
    fetch('api/stock_in_hand.php?' + params.toString())
        .then(r => r.json())
        .then(rows => {
            if (rows && rows.error) {
                toast('Error: ' + rows.error, 'err');
                document.getElementById('results-body').innerHTML =
                    '<tr><td colspan="3" style="text-align:center;padding:10px;color:darkred;">' + esc(rows.error) + '</td></tr>';
                setStatus('Ready');
                return;
            }
            renderRows(rows);
            setStatus('Ready');
        })
        .catch(() => {
            document.getElementById('results-body').innerHTML =
                '<tr><td colspan="3" style="text-align:center;color:darkred;padding:10px;">Could not load stock — check DB connection</td></tr>';
        });
}

function renderRows(rows) {
    const tbody = document.getElementById('results-body');
    document.getElementById('result-count').textContent = rows.length + ' item(s)';
    if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;padding:10px;color:#888;">No matching items found.</td></tr>';
        return;
    }
    tbody.innerHTML = rows.map(r => `
        <tr>
            <td>${esc(r.stock_number)}</td>
            <td>${esc(r.item_name)}</td>
            <td style="text-align:right;">${Number(r.qty_inhand).toLocaleString()}</td>
        </tr>`).join('');
}

</script>
</body>
</html>
