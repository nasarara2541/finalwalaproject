<?php
require_once __DIR__ . '/includes/require_login.php';
$isWaterDb = ($_SESSION['active_db_label'] ?? 'Water Distribution') === 'Water Distribution';
$groupLabel = $isWaterDb ? 'Water' : 'Medicine';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AISellProduct - Stock Search</title>
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
input[readonly], input[disabled] { background: #d4d0c8 !important; color:#555; }

.win-btn {
    background: #d4d0c8; border: 1px solid; border-color: #ffffff #808080 #808080 #ffffff;
    padding: 2px 10px; cursor:pointer; font-size:12px; height:23px;
    font-family: Tahoma, sans-serif; white-space:nowrap; display:inline-flex; align-items:center; gap:3px;
}
.win-btn:hover  { background: #e8e4d8; }
.win-btn:disabled { color:#999; cursor:default; background:#d4d0c8; }
.win-btn:disabled:hover { background:#d4d0c8; }
.win-btn-blue { background:#003087; color:white; border-color:#5599cc #002266 #002266 #5599cc; }
.win-btn-blue:hover { background:#0040ad; }
.win-btn.filter-active { background:#0a246a; color:white; border-color:#3a6ea5 #061a4d #061a4d #3a6ea5; }

.win-table { width:100%; border-collapse:collapse; font-size:11px; }
.win-table thead th { border:1px solid #808080; padding:3px 5px; text-align:left; font-weight:bold; background:#d4d0c8; white-space:nowrap; position:sticky; top:0; z-index:1; }
.win-table tbody tr { background:#fff; }
.win-table tbody tr:nth-child(even) { background:#f5f3ee; }
.win-table tbody tr:hover { background:#c5d5e8 !important; cursor:pointer; }
.win-table tbody tr.row-selected { background:#0a246a !important; color:white; }
.win-table td { border:1px solid #d0ccc4; padding:3px 5px; white-space:nowrap; }

.win-section-label { background:#d4d0c8; font-weight:bold; padding:3px 8px; border-bottom:1px solid #808080; display:flex; align-items:center; justify-content:space-between; }
.win-statusbar { background:#d4d0c8; border-top:1px solid #808080; padding:3px 8px; display:flex; gap:14px; }
.win-statusbar span { border-right:1px solid #808080; padding-right:12px; }

label.lbl { font-weight:bold; white-space:nowrap; width:80px; flex-shrink:0; text-align:right; padding-right:4px; }
.field-cell { display:flex; align-items:center; gap:4px; flex:1; min-width:190px; }

#toast { position:fixed; bottom:16px; right:16px; z-index:9999; padding:8px 16px; font-weight:bold; font-size:12px; border:1px solid; display:none; }
</style>
</head>
<body class="flex flex-col h-screen">

<div class="win-titlebar">
    <span>&#x1F50D; AISellProduct &mdash; Stock Search</span>
    <span id="live-clock" style="font-weight:normal;font-size:11px;"></span>
</div>

<div class="win-menubar">
    <span class="win-menu-item" onclick="window.location='pos.php'">&#x2190; Back to Sale</span>
    <span class="win-menu-item nav-active">Stock Search</span>
    <?php if (!empty($_SESSION['emp_is_admin'])): ?>
    <span class="win-menu-item" style="color:#5b3a8a;font-weight:bold;" onclick="window.location='admin_users.php'">&#x1F464; Manage Users</span>
    <span class="win-menu-item" style="color:#5b3a8a;font-weight:bold;" onclick="window.location='admin_dashboard.php'">&#x1F4CA; Dashboard</span>
    <span class="win-menu-item" style="color:#5b3a8a;font-weight:bold;" onclick="window.location='admin_reports.php'">&#x1F4C8; Profit Reports</span>
    <?php endif; ?>
    <span style="flex:1"></span>
    <span class="win-menu-item" style="color:#555;">Database: <b><?php echo htmlspecialchars($_SESSION['active_db_label'] ?? 'Water Distribution'); ?></b></span>
    <span class="win-menu-item" onclick="window.location='login.php'" title="Pick a different database">&#x1F504; Switch Database</span>
    <span class="win-menu-item" style="color:#555;">User: <b><?php echo htmlspecialchars($_SESSION['emp_user_name'] ?? '—'); ?></b></span>
    <span class="win-menu-item" onclick="window.location='logout.php'" title="Sign out" style="color:darkred;">&#x1F6AA; Logout</span>
</div>

<div style="display:flex;flex-direction:column;flex:1;padding:5px;gap:4px;min-height:0;">

    <!-- Search filters -->
    <div class="win-panel" style="padding:8px;">
        <div style="display:flex;flex-wrap:wrap;gap:8px 20px;margin-bottom:6px;">
            <div class="field-cell"><label class="lbl">Item Code</label><input id="f-item-code" type="text" oninput="debouncedSearch()"></div>
            <div class="field-cell"><label class="lbl">Type</label><input id="f-type" type="text" oninput="debouncedSearch()"></div>
            <div class="field-cell"><label class="lbl">Company</label><input id="f-company" type="text" oninput="debouncedSearch()"></div>
            <div class="field-cell"><label class="lbl">Location</label><input id="f-location" type="text" oninput="debouncedSearch()"></div>
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:8px 20px;">
            <div class="field-cell"><label class="lbl">Item Name</label><input id="f-item-name" type="text" oninput="debouncedSearch()"></div>
            <div class="field-cell">
                <label class="lbl" title="No product-category column exists in the schema yet — this database is entirely one category, shown here rather than offered as a real filter">Group</label>
                <input type="text" value="<?php echo htmlspecialchars($groupLabel); ?>" readonly tabindex="-1">
            </div>
            <div class="field-cell"><label class="lbl">Manufacture By</label><input id="f-manufacture" type="text" oninput="debouncedSearch()"></div>
            <div class="field-cell">
                <label class="lbl" title="No matching column yet — waiting on the professor to define what this should be for a water business">Generic</label>
                <input type="text" value="—" readonly disabled tabindex="-1" title="Not defined yet — pending professor's clarification">
            </div>
        </div>
    </div>

    <!-- Results grid -->
    <div class="win-panel" style="flex:1;min-height:0;display:flex;flex-direction:column;">
        <div class="win-section-label">
            <span>Results</span>
            <span id="result-count" style="font-weight:normal;color:#555;"></span>
        </div>
        <div style="flex:1;overflow:auto;min-height:0;">
            <table class="win-table">
                <thead>
                    <tr>
                        <th>Bar Code</th>
                        <th>Product Name</th>
                        <th>Type</th>
                        <th>Company</th>
                        <th>Manufacture By</th>
                        <th>Group</th>
                        <th>Generic</th>
                        <th>Location</th>
                        <th style="text-align:right;">Pack</th>
                        <th style="text-align:right;">S.L</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="results-body"><tr><td colspan="11" style="text-align:center;padding:10px;color:#888;">Loading…</td></tr></tbody>
            </table>
        </div>
    </div>

    <!-- Filter buttons -->
    <div class="win-panel" style="padding:6px 8px;display:flex;gap:6px;flex-wrap:wrap;">
        <button class="win-btn filter-active" id="btn-all"      onclick="setView('all')">All Record</button>
        <button class="win-btn"               id="btn-inactive" onclick="setView('inactive')">All Inactive</button>
        <button class="win-btn"               id="btn-disc"     onclick="setView('disc')">Disc. Items</button>
        <button class="win-btn"               id="btn-bonus"    onclick="setView('bonus')">Bonus QTY</button>
        <button class="win-btn"               id="btn-narcotics" onclick="setView('narcotics')" title="Filters to items with NARCOTICS_STATUS = 1 — naturally empty for real water items, none of them are narcotics">Anti Nar</button>
    </div>

</div>

<div class="win-statusbar">
    <span id="status-msg">Ready</span>
    <span id="result-count-status"></span>
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

const GROUP_LABEL = <?php echo json_encode($groupLabel); ?>;
let currentView = 'all';
let selectedStock = null;
let searchTimer = null;

function debouncedSearch() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(runSearch, 250);
}

function setView(v) {
    currentView = v;
    ['all','inactive','disc','bonus','narcotics'].forEach(x => {
        document.getElementById('btn-' + x).classList.toggle('filter-active', x === v);
    });
    runSearch();
}

function runSearch() {
    setStatus('Searching…');
    const params = new URLSearchParams({
        item_code:      document.getElementById('f-item-code').value.trim(),
        item_name:      document.getElementById('f-item-name').value.trim(),
        type:           document.getElementById('f-type').value.trim(),
        company:        document.getElementById('f-company').value.trim(),
        manufacture_by: document.getElementById('f-manufacture').value.trim(),
        location:       document.getElementById('f-location').value.trim(),
        view:           currentView
    });
    fetch('api/search_stock_search.php?' + params.toString())
        .then(r => r.json())
        .then(rows => {
            if (rows && rows.error) {
                toast('Error: ' + rows.error, 'err');
                document.getElementById('results-body').innerHTML =
                    '<tr><td colspan="11" style="text-align:center;padding:10px;color:darkred;">' + rows.error + '</td></tr>';
                document.getElementById('result-count').textContent = '';
                setStatus('Ready');
                return;
            }
            renderResults(rows);
            setStatus('Ready');
        })
        .catch(() => {
            document.getElementById('results-body').innerHTML =
                '<tr><td colspan="11" style="text-align:center;color:darkred;padding:10px;">Could not load stock — check DB connection</td></tr>';
        });
}

function renderResults(rows) {
    const tbody = document.getElementById('results-body');
    tbody.innerHTML = '';
    document.getElementById('result-count').textContent = rows.length + ' item(s)';
    document.getElementById('result-count-status').textContent = rows.length + ' result(s)';
    if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="11" style="text-align:center;padding:10px;color:#888;">No items found</td></tr>';
        return;
    }
    rows.forEach(row => {
        const tr = document.createElement('tr');
        tr.className = selectedStock === row.STOCK_NUMBER ? 'row-selected' : '';
        const slDisplay = (row.SAFETY_LEVEL !== null && row.SAFETY_LEVEL !== undefined && row.SAFETY_LEVEL !== '') ? row.SAFETY_LEVEL : '—';
        // Per the migration script's own comments: NARCOTICS_STATUS=1 -> "*"
        // suffix, DISC_STATUS=1 -> "**" suffix, appended to the item name.
        let nameSuffix = '';
        if (row.NARCOTICS_STATUS === '1') nameSuffix += ' *';
        if (row.DISC_STATUS === '1')      nameSuffix += ' **';
        tr.innerHTML = `
            <td>${row.BARCODE || '—'}</td>
            <td style="font-weight:bold;">${(row.BRAND_NAME||'') + ' ' + (row.ITEM_NAME||'') + nameSuffix}</td>
            <td>${row.ITEM_TYPE || '—'}</td>
            <td>${row.COMPANY_NAME || '—'}</td>
            <td>${row.MANUFACTURE_NAME || '—'}</td>
            <td>${GROUP_LABEL}</td>
            <td style="color:#999;">—</td>
            <td>${row.LOCATION || '—'}</td>
            <td style="text-align:right;">${row.UNITS_PERITEM ?? '—'}</td>
            <td style="text-align:right;">${slDisplay}</td>
            <td>${row.AVAILABLE_STATUS || '—'}</td>`;
        tr.onclick = () => {
            selectedStock = row.STOCK_NUMBER;
            document.querySelectorAll('#results-body tr').forEach(r => r.classList.remove('row-selected'));
            tr.classList.add('row-selected');
        };
        tbody.appendChild(tr);
    });
}

runSearch();
</script>
</body>
</html>
