<?php
require_once __DIR__ . '/includes/require_admin.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AISellProduct - Admin Dashboard</title>
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

select {
    border: 1px solid; border-color: #808080 #ffffff #ffffff #808080;
    background: #ffff99; padding: 2px 6px; height: 22px; font-size:12px;
    font-family: Tahoma, sans-serif;
}
select:focus { outline: 1px solid #0a246a; }

.win-btn {
    background: #d4d0c8; border: 1px solid; border-color: #ffffff #808080 #808080 #ffffff;
    padding: 2px 10px; cursor:pointer; font-size:12px; height:23px;
    font-family: Tahoma, sans-serif; white-space:nowrap; display:inline-flex; align-items:center; gap:3px;
}
.win-btn:hover { background: #e8e4d8; }

.win-table { width:100%; border-collapse:collapse; font-size:11px; }
.win-table thead th { border:1px solid #808080; padding:4px 6px; text-align:left; font-weight:bold; background:#d4d0c8; white-space:nowrap; position:sticky; top:0; z-index:1; }
.win-table tbody tr { background:#fff; }
.win-table tbody tr:nth-child(even) { background:#f5f3ee; }
.win-table tbody tr:hover { background:#c5d5e8 !important; }
.win-table td { border:1px solid #d0ccc4; padding:4px 6px; white-space:nowrap; }

.win-section-label { background:#d4d0c8; font-weight:bold; padding:3px 8px; border-bottom:1px solid #808080; display:flex; align-items:center; justify-content:space-between; }
.win-statusbar { background:#d4d0c8; border-top:1px solid #808080; padding:3px 8px; display:flex; gap:14px; }
.win-statusbar span { border-right:1px solid #808080; padding-right:12px; }

.stat-tile {
    flex:1; background:#fff; border:1px solid; border-color:#808080 #ffffff #ffffff #808080;
    padding:8px 14px; display:flex; flex-direction:column; gap:2px; min-width:140px;
}
.stat-value { font-size:19px; font-weight:bold; }
.stat-label { font-size:11px; color:#555; font-weight:bold; }
.stat-note  { font-size:10px; color:#999; }

.chart-area { display:flex; align-items:flex-end; gap:10px; height:170px; padding:6px 10px 0; overflow-x:auto; }
.chart-bar-col { display:flex; flex-direction:column; align-items:center; min-width:34px; cursor:default; }
.chart-bar-stack { width:26px; display:flex; flex-direction:column; justify-content:flex-end; }
.chart-seg-profit { background:#1a7a1a; width:100%; }
.chart-seg-cost   { background:#b03030; width:100%; }
.chart-bar-label { font-size:10px; color:#444; margin-top:4px; white-space:nowrap; transform:rotate(-40deg); transform-origin: top left; position:relative; left:10px; top:2px; }
.chart-legend { display:flex; gap:14px; padding:6px 10px 0; font-size:11px; }
.chart-legend span { display:inline-flex; align-items:center; gap:4px; }
.legend-swatch { width:10px; height:10px; display:inline-block; }

.empty-note { text-align:center; color:#888; padding:14px; font-size:11px; }
#toast { position:fixed; bottom:16px; right:16px; z-index:9999; padding:8px 16px; font-weight:bold; font-size:12px; border:1px solid; display:none; }
</style>
</head>
<body class="flex flex-col h-screen">

<div class="win-titlebar">
    <span>&#x1F4CA; AISellProduct &mdash; Admin Dashboard</span>
    <span id="live-clock" style="font-weight:normal;font-size:11px;"></span>
</div>

<div class="win-menubar">
    <span class="win-menu-item" onclick="window.location='pos.php'">&#x2190; Back to Sale</span>
    <span class="win-menu-item nav-active">Admin Dashboard</span>
    <span class="win-menu-item" style="color:#5b3a8a;font-weight:bold;" onclick="window.location='admin_reports.php'">&#x1F4C8; Profit Reports</span>
    <span class="win-menu-item" style="color:#5b3a8a;font-weight:bold;" onclick="window.location='admin_users.php'">&#x1F464; Manage Users</span>
    <span class="win-menu-item" style="color:#5b3a8a;font-weight:bold;" onclick="window.location='item_details.php'">&#x1F4E6; Item Details</span>
    <span style="flex:1"></span>
    <span class="win-menu-item" style="color:#555;">Database: <b><?php echo htmlspecialchars($_SESSION['active_db_label'] ?? 'Water Distribution'); ?></b></span>
    <span class="win-menu-item" onclick="window.location='login.php'" title="Pick a different database">&#x1F504; Switch Database</span>
    <span class="win-menu-item" style="color:#555;">User: <b><?php echo htmlspecialchars($_SESSION['emp_user_name'] ?? '—'); ?></b></span>
    <span class="win-menu-item" onclick="window.location='logout.php'" title="Sign out" style="color:darkred;">&#x1F6AA; Logout</span>
</div>

<div style="display:flex;flex-direction:column;flex:1;padding:8px;gap:8px;min-height:0;overflow-y:auto;">

    <!-- Stat tiles: most recent month -->
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <div class="stat-tile">
            <span class="stat-label" id="stat-month-label">Latest Month</span>
            <span class="stat-value" id="stat-packs" style="color:#0a246a;">—</span>
            <span class="stat-note">Packs Sold</span>
        </div>
        <div class="stat-tile">
            <span class="stat-label">&nbsp;</span>
            <span class="stat-value" id="stat-sale" style="color:#003087;">—</span>
            <span class="stat-note">Total Sale</span>
        </div>
        <div class="stat-tile">
            <span class="stat-label">&nbsp;</span>
            <span class="stat-value" id="stat-cost" style="color:#b03030;">—</span>
            <span class="stat-note">Total Cost</span>
        </div>
        <div class="stat-tile">
            <span class="stat-label">&nbsp;</span>
            <span class="stat-value" id="stat-profit" style="color:#1a7a1a;">—</span>
            <span class="stat-note">Profit</span>
        </div>
    </div>

    <!-- Net Profit calculator: Total Profit is already computed live from
         real data (see stat tiles above). Expenses auto-fills from
         SummarySalesExp when a saved row exists for the selected month
         (see np-source-note below the field); otherwise it's blank and
         freely editable. Either way nothing typed here gets written back to
         the table -- this only reads it, saving is a separate feature. -->
    <div class="win-panel" style="padding:8px;display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
        <span style="font-weight:bold;color:#0a246a;">Net Profit</span>
        <span style="display:flex;align-items:center;gap:5px;">
            <label for="np-month-select" style="font-weight:normal;font-size:11px;color:#555;">Month:</label>
            <select id="np-month-select" onchange="onNetProfitMonthChange()"></select>
        </span>
        <span style="display:flex;align-items:center;gap:5px;">
            <label for="np-expenses" style="font-weight:normal;font-size:11px;color:#555;">Expenses:</label>
            <input type="number" id="np-expenses" value="0" oninput="onNetProfitExpensesEdited()" style="width:110px;">
            <span id="np-source-note" style="font-size:10px;color:#999;"></span>
        </span>
        <span style="font-size:11px;color:#555;">Total Profit: <b id="np-total-profit" style="font-size:13px;color:#1a7a1a;">—</b></span>
        <span style="flex:1"></span>
        <span style="font-weight:bold;">Net Profit:</span>
        <span id="np-net-profit" style="font-size:17px;font-weight:bold;">—</span>
    </div>

    <div style="display:flex;gap:8px;flex:1;min-height:260px;">

        <!-- Chart -->
        <div class="win-panel" style="flex:1.3;display:flex;flex-direction:column;padding:8px;">
            <div class="win-section-label" style="margin:-8px -8px 0 -8px;">
                <span>Profit Trend</span>
                <span style="font-weight:normal;font-size:11px;color:#555;">Cost + Profit = Sale, by month</span>
            </div>
            <div class="chart-legend">
                <span><span class="legend-swatch" style="background:#1a7a1a;"></span> Profit</span>
                <span><span class="legend-swatch" style="background:#b03030;"></span> Cost</span>
            </div>
            <div class="chart-area" id="chart-area">
                <div class="empty-note">Loading chart…</div>
            </div>
        </div>

        <!-- Monthly summary table -->
        <div class="win-panel" style="flex:1;display:flex;flex-direction:column;padding:8px;">
            <div class="win-section-label" style="margin:-8px -8px 8px -8px;">
                <span>Total Profit Per Month</span>
            </div>
            <div style="flex:1;overflow:auto;">
                <table class="win-table">
                    <thead>
                        <tr><th>Month</th><th style="text-align:right;">Packs</th><th style="text-align:right;">Sale</th><th style="text-align:right;">Cost</th><th style="text-align:right;">Profit</th></tr>
                    </thead>
                    <tbody id="summary-body"><tr><td colspan="5" class="empty-note">Loading…</td></tr></tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Per-item breakdown -->
    <div class="win-panel" style="padding:8px;min-height:200px;">
        <div class="win-section-label" style="margin:-8px -8px 8px -8px;">
            <span>Breakdown by Item</span>
            <span style="display:flex;align-items:center;gap:5px;">
                <label for="item-month-select" style="font-weight:normal;font-size:11px;color:#555;">Month:</label>
                <select id="item-month-select" onchange="renderItemTable()"></select>
            </span>
        </div>
        <table class="win-table">
            <thead>
                <tr><th>Item</th><th style="text-align:right;">Packs</th><th style="text-align:right;">Sale</th><th style="text-align:right;">Cost</th><th style="text-align:right;">Profit</th></tr>
            </thead>
            <tbody id="item-body"><tr><td colspan="5" class="empty-note">Loading…</td></tr></tbody>
        </table>
    </div>

</div>

<div class="win-statusbar">
    <span id="status-msg">Ready</span>
    <span>Cost = recorded cost where available, otherwise quantity × current purchase price</span>
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

let summaryData = [];
let itemData = [];
let expenseData = [];

function loadDashboard() {
    setStatus('Loading…');
    Promise.all([
        fetch('api/get_dashboard_summary.php').then(r => r.json()),
        fetch('api/get_dashboard_by_item.php').then(r => r.json()),
        fetch('api/get_summary_sales_exp.php').then(r => r.json())
    ]).then(([summary, items, expenses]) => {
        if (summary && summary.error) { toast('Error: ' + summary.error, 'err'); return; }
        if (items && items.error) { toast('Error: ' + items.error, 'err'); return; }
        if (expenses && expenses.error) { toast('Error: ' + expenses.error, 'err'); return; }
        summaryData = summary;
        itemData = items;
        expenseData = Array.isArray(expenses) ? expenses : [];
        renderStatTiles();
        renderChart();
        renderSummaryTable();
        populateMonthSelect();
        renderItemTable();
        populateNetProfitMonthSelect();
        onNetProfitMonthChange();
        setStatus('Ready');
    }).catch(() => {
        document.getElementById('summary-body').innerHTML =
            '<tr><td colspan="5" style="text-align:center;color:darkred;padding:10px;">Could not load — check DB connection</td></tr>';
        document.getElementById('item-body').innerHTML =
            '<tr><td colspan="5" style="text-align:center;color:darkred;padding:10px;">Could not load — check DB connection</td></tr>';
        document.getElementById('chart-area').innerHTML =
            '<div class="empty-note" style="color:darkred;">Could not load chart data</div>';
    });
}

function renderStatTiles() {
    if (!summaryData.length) return;
    const m = summaryData[0]; // newest month, since API returns DESC
    const profit = m.Sale - m.Cost;
    document.getElementById('stat-month-label').textContent = m.Month;
    document.getElementById('stat-packs').textContent  = fmt(m.Packs);
    document.getElementById('stat-sale').textContent   = fmt(m.Sale);
    document.getElementById('stat-cost').textContent   = fmt(m.Cost);
    document.getElementById('stat-profit').textContent = fmt(profit);
}

function renderChart() {
    const box = document.getElementById('chart-area');
    if (!summaryData.length) { box.innerHTML = '<div class="empty-note">No data yet</div>'; return; }

    // Chronological order (oldest -> newest) for a left-to-right trend read,
    // capped to the most recent 12 months so the chart doesn't get crowded.
    const months = summaryData.slice(0, 12).slice().reverse();
    const maxSale = Math.max(...months.map(m => m.Sale), 1);
    const maxBarPx = 150;

    box.innerHTML = '';
    months.forEach(m => {
        const profit = m.Sale - m.Cost;
        const totalPx  = Math.max(2, (m.Sale / maxSale) * maxBarPx);
        const costPx   = m.Sale > 0 ? (m.Cost / m.Sale) * totalPx : 0;
        const profitPx = Math.max(0, totalPx - costPx);

        const col = document.createElement('div');
        col.className = 'chart-bar-col';
        col.title = `${m.Month}\nSale: ${fmt(m.Sale)}\nCost: ${fmt(m.Cost)}\nProfit: ${fmt(profit)}`;
        col.innerHTML = `
            <div class="chart-bar-stack" style="height:${maxBarPx}px;">
                <div class="chart-seg-profit" style="height:${profitPx}px;"></div>
                <div class="chart-seg-cost" style="height:${costPx}px;"></div>
            </div>
            <div class="chart-bar-label">${m.Month}</div>`;
        box.appendChild(col);
    });
}

function renderSummaryTable() {
    const tbody = document.getElementById('summary-body');
    tbody.innerHTML = '';
    if (!summaryData.length) { tbody.innerHTML = '<tr><td colspan="5" class="empty-note">No data yet</td></tr>'; return; }
    summaryData.forEach(m => {
        const profit = m.Sale - m.Cost;
        const tr = document.createElement('tr');
        tr.innerHTML = `<td style="font-weight:bold;">${m.Month}</td>
            <td style="text-align:right;">${fmt(m.Packs)}</td>
            <td style="text-align:right;">${fmt(m.Sale)}</td>
            <td style="text-align:right;color:#b03030;">${fmt(m.Cost)}</td>
            <td style="text-align:right;font-weight:bold;color:${profit>=0?'#1a7a1a':'#b03030'};">${fmt(profit)}</td>`;
        tbody.appendChild(tr);
    });
}

function monthKey(row) { return row.Yr + '-' + String(row.Mo).padStart(2,'0'); }

function populateMonthSelect() {
    const sel = document.getElementById('item-month-select');
    sel.innerHTML = '';
    summaryData.forEach(m => {
        const opt = document.createElement('option');
        opt.value = monthKey(m);
        opt.textContent = m.Month;
        sel.appendChild(opt);
    });
}

function renderItemTable() {
    const key = document.getElementById('item-month-select').value;
    const tbody = document.getElementById('item-body');
    tbody.innerHTML = '';
    const rows = itemData.filter(r => monthKey(r) === key);
    if (!rows.length) { tbody.innerHTML = '<tr><td colspan="5" class="empty-note">No data for this month</td></tr>'; return; }
    rows.forEach(r => {
        const profit = r.Sale - r.Cost;
        const tr = document.createElement('tr');
        tr.innerHTML = `<td style="font-weight:bold;color:#0a246a;">${r.Item}</td>
            <td style="text-align:right;">${fmt(r.Packs)}</td>
            <td style="text-align:right;">${fmt(r.Sale)}</td>
            <td style="text-align:right;color:#b03030;">${fmt(r.Cost)}</td>
            <td style="text-align:right;font-weight:bold;color:${profit>=0?'#1a7a1a':'#b03030'};">${fmt(profit)}</td>`;
        tbody.appendChild(tr);
    });
}

function populateNetProfitMonthSelect() {
    const sel = document.getElementById('np-month-select');
    sel.innerHTML = '';
    summaryData.forEach(m => {
        const opt = document.createElement('option');
        opt.value = monthKey(m);
        opt.textContent = m.Month;
        sel.appendChild(opt);
    });
}

// Month changed: look up SummarySalesExp for this month. A saved row
// auto-fills Expenses and labels where it came from. If there's no row yet,
// per Qasim -- Total_Sales must never be hardcoded, it has to be
// calculated live and inserted at runtime -- ask the server to compute it
// from real trans_detail data right now and create the row, rather than
// just leaving a gap. Total_Expenses always starts at 0 on a freshly
// created row and still needs a human to type the real figure in.
function onNetProfitMonthChange() {
    const key = document.getElementById('np-month-select').value;
    const input = document.getElementById('np-expenses');
    const note = document.getElementById('np-source-note');
    const saved = expenseData.find(r => monthKey(r) === key);

    if (saved) {
        input.value = saved.Total_Expenses;
        note.textContent = '(from database)';
        renderNetProfit();
        return;
    }

    const [yr, mo] = key.split('-').map(Number);
    input.value = '';
    note.textContent = 'Checking database…';
    renderNetProfit();

    fetch(`api/ensure_summary_sales_exp.php?year=${yr}&month=${mo}`)
        .then(r => r.json())
        .then(res => {
            if (document.getElementById('np-month-select').value !== key) return; // month changed again while waiting
            if (res.error) { note.textContent = '(no saved entry — enter manually)'; renderNetProfit(); return; }
            expenseData.push({ Yr: yr, Mo: mo, Total_Sales: res.Total_Sales, Total_Expenses: res.Total_Expenses });
            input.value = res.Total_Expenses;
            note.textContent = res.created ? '(new row created — enter expenses)' : '(from database)';
            renderNetProfit();
        })
        .catch(() => {
            if (document.getElementById('np-month-select').value !== key) return;
            note.textContent = '(no saved entry — enter manually)';
            renderNetProfit();
        });
}

// Typing into Expenses always overrides whatever was shown, whether that
// was a saved value or blank -- editing never writes back to the table,
// this only reads from it.
function onNetProfitExpensesEdited() {
    document.getElementById('np-source-note').textContent = '(edited, not saved)';
    renderNetProfit();
}

function renderNetProfit() {
    const key = document.getElementById('np-month-select').value;
    const m = summaryData.find(r => monthKey(r) === key);
    const totalProfitEl = document.getElementById('np-total-profit');
    const netProfitEl = document.getElementById('np-net-profit');
    if (!m) { totalProfitEl.textContent = '—'; netProfitEl.textContent = '—'; return; }
    const totalProfit = m.Sale - m.Cost;
    const expenses = Number(document.getElementById('np-expenses').value) || 0;
    const netProfit = totalProfit - expenses;
    totalProfitEl.textContent = fmt(totalProfit);
    netProfitEl.textContent = fmt(netProfit);
    netProfitEl.style.color = netProfit >= 0 ? '#1a7a1a' : '#b03030';
}

loadDashboard();
</script>
</body>
</html>
