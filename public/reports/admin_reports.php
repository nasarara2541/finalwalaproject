<?php
require_once __DIR__ . '/../includes/access.php';
requireAccess('admin_area');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AISellProduct - Profit Reports</title>
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

.report-tabbar { background:#ece9d8; border-bottom:1px solid #808080; display:flex; gap:2px; padding:4px 8px 0; }
.report-tab { padding:5px 16px; cursor:pointer; font-size:12px; font-weight:bold; border:1px solid #808080; border-bottom:none; background:#d4d0c8; border-radius:3px 3px 0 0; color:#333; }
.report-tab.active { background:#ece9d8; color:#0a246a; border-bottom:1px solid #ece9d8; position:relative; top:1px; }
.report-panel { display:none; }
.report-panel.active { display:flex; flex-direction:column; flex:1; min-height:0; }

select, input[type=text] {
    border: 1px solid; border-color: #808080 #ffffff #ffffff #808080;
    background: #ffff99; padding: 2px 6px; height: 22px; font-size:12px;
    font-family: Tahoma, sans-serif;
}
select:focus, input[type=text]:focus { outline: 1px solid #0a246a; }

.win-table { width:100%; border-collapse:collapse; font-size:11px; }
.win-table thead th { border:1px solid #808080; padding:4px 6px; text-align:left; font-weight:bold; background:#d4d0c8; white-space:nowrap; position:sticky; top:0; z-index:1; }
.win-table tbody tr { background:#fff; }
.win-table tbody tr:nth-child(even) { background:#f5f3ee; }
.win-table tbody tr:hover { background:#c5d5e8 !important; }
.win-table td { border:1px solid #d0ccc4; padding:4px 6px; white-space:nowrap; }
.win-table tr.grand-total td { background:#d4d0c8 !important; font-weight:bold; border-top:2px solid #808080; }

.win-section-label { background:#d4d0c8; font-weight:bold; padding:3px 8px; border-bottom:1px solid #808080; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:6px; }
.win-statusbar { background:#d4d0c8; border-top:1px solid #808080; padding:3px 8px; display:flex; gap:14px; }
.win-statusbar span { border-right:1px solid #808080; padding-right:12px; }

.empty-note { text-align:center; color:#888; padding:14px; font-size:11px; }
#toast { position:fixed; bottom:16px; right:16px; z-index:9999; padding:8px 16px; font-weight:bold; font-size:12px; border:1px solid; display:none; }
</style>
</head>
<body class="flex flex-col h-screen">

<div class="win-titlebar">
    <span>&#x1F4C8; AISellProduct &mdash; Profit Reports</span>
    <span id="live-clock" style="font-weight:normal;font-size:11px;"></span>
</div>

<div class="win-menubar">
    <span class="win-menu-item" onclick="window.location='../pos.php'">&#x2190; Back to Sale</span>
    <span class="win-menu-item" style="color:#5b3a8a;font-weight:bold;" onclick="window.location='../admin_dashboard.php'">&#x1F4CA; Dashboard</span>
    <span class="win-menu-item nav-active">Profit Reports</span>
    <span class="win-menu-item" style="color:#5b3a8a;font-weight:bold;" onclick="window.location='../admin_users.php'">&#x1F464; Manage Users</span>
    <span class="win-menu-item" style="color:#5b3a8a;font-weight:bold;" onclick="window.location='../item_details.php'">&#x1F4E6; Item Details</span>
    <span style="flex:1"></span>
    <span class="win-menu-item" style="color:#555;">Database: <b><?php echo htmlspecialchars($_SESSION['active_db_label'] ?? 'Water Distribution'); ?></b></span>
    <span class="win-menu-item" onclick="window.location='../login.php'" title="Pick a different database">&#x1F504; Switch Database</span>
    <span class="win-menu-item" style="color:#555;">User: <b><?php echo htmlspecialchars($_SESSION['emp_user_name'] ?? '—'); ?></b></span>
    <span class="win-menu-item" onclick="window.location='../logout.php'" title="Sign out" style="color:darkred;">&#x1F6AA; Logout</span>
</div>

<div class="report-tabbar">
    <div class="report-tab active" id="tab-btn-product" onclick="switchReport('product')">By Product</div>
    <div class="report-tab" id="tab-btn-region" onclick="switchReport('region')">By Region</div>
    <div class="report-tab" id="tab-btn-customer" onclick="switchReport('customer')">By Customer</div>
</div>

<div style="flex:1;min-height:0;padding:8px;">

    <!-- By Product -->
    <div class="report-panel active" id="panel-product">
        <div class="win-panel" style="flex:1;display:flex;flex-direction:column;padding:8px;min-height:0;">
            <div class="win-section-label" style="margin:-8px -8px 8px -8px;">
                <span>Profit Report by Product</span>
                <span style="display:flex;align-items:center;gap:5px;">
                    <label for="product-month" style="font-weight:normal;font-size:11px;color:#555;">Month:</label>
                    <select id="product-month" onchange="renderProductTable()"></select>
                </span>
            </div>
            <div style="flex:1;overflow:auto;min-height:0;">
                <table class="win-table">
                    <thead>
                        <tr><th>Product</th><th style="text-align:right;">Packs</th><th style="text-align:right;">Sale</th><th style="text-align:right;">Cost</th><th style="text-align:right;">Profit</th></tr>
                    </thead>
                    <tbody id="product-body"><tr><td colspan="5" class="empty-note">Loading…</td></tr></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- By Region -->
    <div class="report-panel" id="panel-region">
        <div class="win-panel" style="flex:1;display:flex;flex-direction:column;padding:8px;min-height:0;">
            <div class="win-section-label" style="margin:-8px -8px 8px -8px;">
                <span>Profit Report by Region</span>
                <span style="display:flex;align-items:center;gap:5px;">
                    <label for="region-month" style="font-weight:normal;font-size:11px;color:#555;">Month:</label>
                    <select id="region-month" onchange="renderRegionTable()"></select>
                </span>
            </div>
            <div style="flex:1;overflow:auto;min-height:0;">
                <table class="win-table" id="region-table">
                    <thead><tr id="region-head"></tr></thead>
                    <tbody id="region-body"><tr><td class="empty-note">Loading…</td></tr></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- By Customer -->
    <div class="report-panel" id="panel-customer">
        <div class="win-panel" style="flex:1;display:flex;flex-direction:column;padding:8px;min-height:0;">
            <div class="win-section-label" style="margin:-8px -8px 8px -8px;">
                <span>Profit Report by Customer</span>
                <span style="display:flex;align-items:center;gap:5px;">
                    <input type="text" id="customer-filter" placeholder="Filter by name…" oninput="renderCustomerTable()" style="width:160px;">
                    <label for="customer-month" style="font-weight:normal;font-size:11px;color:#555;">Month:</label>
                    <select id="customer-month" onchange="renderCustomerTable()"></select>
                </span>
            </div>
            <div style="flex:1;overflow:auto;min-height:0;">
                <table class="win-table" id="customer-table">
                    <thead><tr id="customer-head"></tr></thead>
                    <tbody id="customer-body"><tr><td class="empty-note">Loading…</td></tr></tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<div class="win-statusbar">
    <span id="status-msg">Ready</span>
    <span>Profit = Sale &minus; Cost. Region/Customer figures grouped by product size (Large A / Small B variants of the same size combined).</span>
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
function setStatus(msg) { document.getElementById('status-msg').textContent = msg; }

const _nativeFetch = window.fetch;
window.fetch = function(...args) {
    return _nativeFetch.apply(this, args).catch(err => {
        toast('Network/Server error — check DB_SERVER in .env and that the database is reachable', 'err');
        throw err;
    });
};

const fmt = n => Number(n||0).toLocaleString('en-US', {minimumFractionDigits:0, maximumFractionDigits:0});
const SIZES = ['0.5L','1.5L','6L','12L','19L'];

let productData = [];
let regionData = [];
let customerData = [];

function switchReport(name) {
    ['product','region','customer'].forEach(n => {
        document.getElementById('panel-'+n).classList.toggle('active', n===name);
        document.getElementById('tab-btn-'+n).classList.toggle('active', n===name);
    });
}

function monthKey(row) { return row.Yr + '-' + String(row.Mo).padStart(2,'0'); }

function populateMonthSelect(selectId, data) {
    const sel = document.getElementById(selectId);
    const seen = new Set();
    sel.innerHTML = '';
    data.forEach(m => {
        const key = monthKey(m);
        if (seen.has(key)) return;
        seen.add(key);
        const opt = document.createElement('option');
        opt.value = key;
        opt.textContent = m.Month;
        sel.appendChild(opt);
    });
}

function loadReports() {
    setStatus('Loading…');
    Promise.all([
        fetch('../api/get_dashboard_by_item.php').then(r => r.json()),
        fetch('api/get_report_by_region.php').then(r => r.json()),
        fetch('api/get_report_by_customer.php').then(r => r.json())
    ]).then(([product, region, customer]) => {
        if (product && product.error) { toast('Error: ' + product.error, 'err'); return; }
        if (region  && region.error)  { toast('Error: ' + region.error, 'err'); return; }
        if (customer&& customer.error){ toast('Error: ' + customer.error, 'err'); return; }
        productData = product;
        regionData = region;
        customerData = customer;

        populateMonthSelect('product-month', productData);
        populateMonthSelect('region-month', regionData);
        populateMonthSelect('customer-month', customerData);

        renderProductTable();
        renderRegionTable();
        renderCustomerTable();
        setStatus('Ready');
    }).catch(() => {
        document.getElementById('product-body').innerHTML =
            '<tr><td colspan="5" style="text-align:center;color:darkred;padding:10px;">Could not load — check DB connection</td></tr>';
        document.getElementById('region-body').innerHTML =
            '<tr><td style="text-align:center;color:darkred;padding:10px;">Could not load — check DB connection</td></tr>';
        document.getElementById('customer-body').innerHTML =
            '<tr><td style="text-align:center;color:darkred;padding:10px;">Could not load — check DB connection</td></tr>';
    });
}

function renderProductTable() {
    const key = document.getElementById('product-month').value;
    const tbody = document.getElementById('product-body');
    tbody.innerHTML = '';
    const rows = productData.filter(r => monthKey(r) === key);
    if (!rows.length) { tbody.innerHTML = '<tr><td colspan="5" class="empty-note">No data for this month</td></tr>'; return; }

    let tPacks=0, tSale=0, tCost=0;
    rows.forEach(r => {
        const profit = r.Sale - r.Cost;
        tPacks += Number(r.Packs); tSale += Number(r.Sale); tCost += Number(r.Cost);
        const tr = document.createElement('tr');
        tr.innerHTML = `<td style="font-weight:bold;color:#0a246a;">${r.Item}</td>
            <td style="text-align:right;">${fmt(r.Packs)}</td>
            <td style="text-align:right;">${fmt(r.Sale)}</td>
            <td style="text-align:right;color:#b03030;">${fmt(r.Cost)}</td>
            <td style="text-align:right;font-weight:bold;color:${profit>=0?'#1a7a1a':'#b03030'};">${fmt(profit)}</td>`;
        tbody.appendChild(tr);
    });
    const totalTr = document.createElement('tr');
    totalTr.className = 'grand-total';
    const tProfit = tSale - tCost;
    totalTr.innerHTML = `<td>Total</td>
        <td style="text-align:right;">${fmt(tPacks)}</td>
        <td style="text-align:right;">${fmt(tSale)}</td>
        <td style="text-align:right;">${fmt(tCost)}</td>
        <td style="text-align:right;color:${tProfit>=0?'#1a7a1a':'#b03030'};">${fmt(tProfit)}</td>`;
    tbody.appendChild(totalTr);
}

function pivotByRow(rows, rowKeyField, rowLabelField) {
    const byKey = new Map();
    rows.forEach(r => {
        const key = r[rowKeyField];
        if (!byKey.has(key)) byKey.set(key, { label: r[rowLabelField], sizeProfit: {}, total: 0 });
        const entry = byKey.get(key);
        const profit = Number(r.Sale) - Number(r.Cost);
        const size = SIZES.includes(r.Size) ? r.Size : 'Other';
        entry.sizeProfit[size] = (entry.sizeProfit[size] || 0) + profit;
        entry.total += profit;
    });
    return Array.from(byKey.values()).sort((a,b) => b.total - a.total);
}

function hasOtherColumn(rows) {
    return rows.some(r => !SIZES.includes(r.Size));
}

function renderMatrixHead(theadRowId, firstColLabel, includeOther) {
    const cols = SIZES.concat(includeOther ? ['Other'] : []);
    const tr = document.getElementById(theadRowId);
    tr.innerHTML = `<th>${firstColLabel}</th>` + cols.map(s => `<th style="text-align:right;">${s}</th>`).join('') + `<th style="text-align:right;">Total</th>`;
    return cols;
}

function renderRegionTable() {
    const key = document.getElementById('region-month').value;
    const rows = regionData.filter(r => monthKey(r) === key);
    const includeOther = hasOtherColumn(rows);
    const cols = renderMatrixHead('region-head', 'Region', includeOther);
    const tbody = document.getElementById('region-body');
    tbody.innerHTML = '';
    if (!rows.length) { tbody.innerHTML = `<tr><td colspan="${cols.length+2}" class="empty-note">No data for this month</td></tr>`; return; }

    const pivoted = pivotByRow(rows, 'Region', 'Region');
    const colTotals = {}; cols.forEach(c => colTotals[c] = 0);
    let grandTotal = 0;

    pivoted.forEach(entry => {
        const tr = document.createElement('tr');
        let html = `<td style="font-weight:bold;color:#0a246a;">${entry.label}</td>`;
        cols.forEach(c => {
            const v = entry.sizeProfit[c] || 0;
            colTotals[c] += v;
            html += `<td style="text-align:right;color:${v>=0?'#1a7a1a':'#b03030'};">${fmt(v)}</td>`;
        });
        grandTotal += entry.total;
        html += `<td style="text-align:right;font-weight:bold;color:${entry.total>=0?'#1a7a1a':'#b03030'};">${fmt(entry.total)}</td>`;
        tr.innerHTML = html;
        tbody.appendChild(tr);
    });

    const totalTr = document.createElement('tr');
    totalTr.className = 'grand-total';
    let html = `<td>Total</td>`;
    cols.forEach(c => { html += `<td style="text-align:right;">${fmt(colTotals[c])}</td>`; });
    html += `<td style="text-align:right;">${fmt(grandTotal)}</td>`;
    totalTr.innerHTML = html;
    tbody.appendChild(totalTr);
}

function renderCustomerTable() {
    const key = document.getElementById('customer-month').value;
    const filterText = (document.getElementById('customer-filter').value || '').toLowerCase().trim();
    const rows = customerData.filter(r => monthKey(r) === key);
    const includeOther = hasOtherColumn(rows);
    const cols = renderMatrixHead('customer-head', 'Customer', includeOther);
    const tbody = document.getElementById('customer-body');
    tbody.innerHTML = '';
    if (!rows.length) { tbody.innerHTML = `<tr><td colspan="${cols.length+2}" class="empty-note">No data for this month</td></tr>`; return; }

    let pivoted = pivotByRow(rows, 'CustomerId', 'Customer');
    if (filterText) pivoted = pivoted.filter(e => e.label.toLowerCase().includes(filterText));
    if (!pivoted.length) { tbody.innerHTML = `<tr><td colspan="${cols.length+2}" class="empty-note">No matching customer</td></tr>`; return; }

    const colTotals = {}; cols.forEach(c => colTotals[c] = 0);
    let grandTotal = 0;

    pivoted.forEach(entry => {
        const tr = document.createElement('tr');
        let html = `<td style="font-weight:bold;color:#0a246a;">${entry.label}</td>`;
        cols.forEach(c => {
            const v = entry.sizeProfit[c] || 0;
            colTotals[c] += v;
            html += `<td style="text-align:right;color:${v>=0?'#1a7a1a':'#b03030'};">${fmt(v)}</td>`;
        });
        grandTotal += entry.total;
        html += `<td style="text-align:right;font-weight:bold;color:${entry.total>=0?'#1a7a1a':'#b03030'};">${fmt(entry.total)}</td>`;
        tr.innerHTML = html;
        tbody.appendChild(tr);
    });

    const totalTr = document.createElement('tr');
    totalTr.className = 'grand-total';
    let html = `<td>Total${filterText ? ' (filtered)' : ''}</td>`;
    cols.forEach(c => { html += `<td style="text-align:right;">${fmt(colTotals[c])}</td>`; });
    html += `<td style="text-align:right;">${fmt(grandTotal)}</td>`;
    totalTr.innerHTML = html;
    tbody.appendChild(totalTr);
}

loadReports();
</script>
</body>
</html>
