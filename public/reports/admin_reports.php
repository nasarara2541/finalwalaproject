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

select, input[type=text], input[type=date] {
    border: 1px solid; border-color: #808080 #ffffff #ffffff #808080;
    background: #ffff99; padding: 2px 6px; height: 22px; font-size:12px;
    font-family: Tahoma, sans-serif;
}
select:focus, input[type=text]:focus, input[type=date]:focus { outline: 1px solid #0a246a; }

.win-btn {
    background: #d4d0c8; border: 1px solid; border-color: #ffffff #808080 #808080 #ffffff;
    padding: 2px 10px; cursor:pointer; font-size:12px; height:23px;
    font-family: Tahoma, sans-serif; white-space:nowrap; display:inline-flex; align-items:center; gap:3px;
}
.win-btn:hover { background: #e8e4d8; }
.win-btn:active { border-color: #808080 #ffffff #ffffff #808080; }
.win-btn-blue { background:#003087; color:white; border-color:#5599cc #002266 #002266 #5599cc; }
.win-btn-blue:hover { background:#0040ad; }

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

<?php $SCREEN_NAME = 'Profit Reports'; $SCREEN_ICON = 'chart-line'; require __DIR__ . '/../includes/navbar.php'; ?>

<div class="report-tabbar">
    <div class="report-tab active" id="tab-btn-product" onclick="switchReport('product')">By Product</div>
    <div class="report-tab" id="tab-btn-region" onclick="switchReport('region')">By Region</div>
    <div class="report-tab" id="tab-btn-customer" onclick="switchReport('customer')">By Customer</div>
</div>

<!-- display:flex is load-bearing here, not decorative -- .report-panel.active
     below relies on flex:1/min-height:0 to size itself, which only takes
     effect inside an actual flex container. Without it this div silently
     falls back to display:block, the panel (and the table inside it) just
     grows to fit its content instead of being capped to the viewport, and
     the table's own overflow:auto never has anything to scroll -- rows past
     what fits on screen were simply clipped, with no scrollbar anywhere. -->
<div style="flex:1;min-height:0;padding:8px;display:flex;flex-direction:column;">

    <!-- By Product -->
    <div class="report-panel active" id="panel-product">
        <div class="win-panel" style="flex:1;display:flex;flex-direction:column;padding:8px;min-height:0;">
            <div class="win-section-label" style="margin:-8px -8px 8px -8px;">
                <span>Profit Report by Product</span>
                <span style="display:flex;align-items:center;gap:5px;">
                    <label for="product-month" style="font-weight:normal;font-size:11px;color:#555;">Month:</label>
                    <select id="product-month" onchange="ensureProductLoaded()"></select>
                </span>
            </div>
            <div style="flex:1;overflow:auto;min-height:0;" tabindex="0">
                <table class="win-table">
                    <thead>
                        <tr><th>Product</th><th style="text-align:right;">Packs</th><th style="text-align:right;">Sale</th><th style="text-align:right;">Cost</th><th style="text-align:right;">Profit</th></tr>
                    </thead>
                    <tbody id="product-body"><tr><td colspan="5" class="empty-note">Loading months…</td></tr></tbody>
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
                    <select id="region-month" onchange="ensureRegionLoaded()"></select>
                </span>
            </div>
            <div style="flex:1;overflow:auto;min-height:0;" tabindex="0">
                <table class="win-table" id="region-table">
                    <thead><tr id="region-head"></tr></thead>
                    <tbody id="region-body"><tr><td class="empty-note">Loading months…</td></tr></tbody>
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
                    <input type="text" id="customer-filter" placeholder="Filter by name…" oninput="visibleCount.customer = PAGE_SIZE; renderCustomerTable();" style="width:160px;">
                    <label for="customer-month" style="font-weight:normal;font-size:11px;color:#555;">Month:</label>
                    <select id="customer-month" onchange="ensureCustomerLoaded()"></select>
                </span>
            </div>
            <div style="flex:1;overflow:auto;min-height:0;" tabindex="0">
                <table class="win-table" id="customer-table">
                    <thead><tr id="customer-head"></tr></thead>
                    <tbody id="customer-body"><tr><td class="empty-note">Loading months…</td></tr></tbody>
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
        toast('Network/Server error - check DB_SERVER in .env and that the database is reachable', 'err');
        throw err;
    });
};

const fmt = n => Number(n||0).toLocaleString('en-US', {minimumFractionDigits:0, maximumFractionDigits:0});
const SIZES = ['0.5L','1.5L','6L','12L','19L'];

let productData = [];
let regionData = [];
let customerData = [];

// Rendering all of a big month's rows at once is what was actually slow --
// each table starts at 100 rows and grows 100 at a time on request, same
// idea Anoosha used on her own screen.
const PAGE_SIZE = 100;
let visibleCount = { product: PAGE_SIZE, region: PAGE_SIZE, customer: PAGE_SIZE };

function loadMoreRow(key, totalRows, colspan, onClick) {
    const remaining = totalRows - visibleCount[key];
    if (remaining <= 0) return '';
    return `<tr><td colspan="${colspan}" style="text-align:center;padding:8px;background:#ece9d8;">
        <button class="win-btn win-btn-blue" onclick="${onClick}">
            <i class="fa-solid fa-angles-down"></i> Load ${Math.min(PAGE_SIZE, remaining)} More (${remaining} remaining)
        </button>
    </td></tr>`;
}
// Which month's rows are currently sitting in each of the arrays above --
// null until that tab's first load. Lets ensureXLoaded() skip re-fetching
// when the month picked is already what's loaded, and lets switching tabs
// lazily fetch a tab's data only the first time you actually look at it.
let loadedMonth = { product: null, region: null, customer: null };
let availableMonths = [];

function switchReport(name) {
    ['product','region','customer'].forEach(n => {
        document.getElementById('panel-'+n).classList.toggle('active', n===name);
        document.getElementById('tab-btn-'+n).classList.toggle('active', n===name);
    });
    if (name === 'product')  ensureProductLoaded();
    if (name === 'region')   ensureRegionLoaded();
    if (name === 'customer') ensureCustomerLoaded();
}

function monthKey(row) { return row.Yr + '-' + String(row.Mo).padStart(2,'0'); }

// First/last calendar day of a "YYYY-M" key, as YYYY-MM-DD, for the report
// APIs' from/to params. Built from local parts, not Date#toISOString() --
// that converts to UTC first, which silently shifts the date by a day in
// any timezone ahead of UTC (e.g. PKT).
function monthKeyToRange(key) {
    const [y, m] = key.split('-').map(Number);
    const pad = n => String(n).padStart(2, '0');
    const lastDay = new Date(y, m, 0).getDate(); // day 0 of next month = last day of this one
    return { from: `${y}-${pad(m)}-01`, to: `${y}-${pad(m)}-${pad(lastDay)}` };
}

function populateMonthSelects() {
    ['product-month','region-month','customer-month'].forEach(id => {
        const sel = document.getElementById(id);
        sel.innerHTML = '';
        availableMonths.forEach(m => {
            const opt = document.createElement('option');
            opt.value = monthKey(m);
            opt.textContent = m.Month;
            sel.appendChild(opt);
        });
    });
}

// Loads the small "which months actually have sales" list (cheap -- one row
// per month, not per sale) so all 3 Month dropdowns work immediately, same
// as before. Only the selected month's actual report rows get fetched from
// there, one tab at a time, so this screen still never pulls the whole
// sales history at once.
function loadMonthList() {
    setStatus('Loading…');
    fetch('api/get_report_months.php').then(r => r.json()).then(months => {
        if (months && months.error) { toast('Error: ' + months.error, 'err'); return; }
        availableMonths = months;
        if (!months.length) {
            ['product-body','region-body','customer-body'].forEach(id => {
                document.getElementById(id).innerHTML = '<tr><td class="empty-note">No sales recorded yet</td></tr>';
            });
            setStatus('Ready');
            return;
        }
        populateMonthSelects();
        ensureProductLoaded(); // land on the most recent month for the tab that's open by default
        setStatus('Ready');
    }).catch(() => {
        document.getElementById('product-body').innerHTML =
            '<tr><td colspan="5" style="text-align:center;color:darkred;padding:10px;">Could not load - check DB connection</td></tr>';
    });
}

function ensureProductLoaded() {
    const key = document.getElementById('product-month').value;
    if (!key) return;
    if (loadedMonth.product === key) { renderProductTable(); return; }
    setStatus('Loading…');
    const { from, to } = monthKeyToRange(key);
    fetch('../api/get_dashboard_by_item.php?' + new URLSearchParams({ from, to }))
        .then(r => r.json())
        .then(rows => {
            if (rows && rows.error) { toast('Error: ' + rows.error, 'err'); return; }
            productData = rows;
            loadedMonth.product = key;
            visibleCount.product = PAGE_SIZE;
            renderProductTable();
            setStatus('Ready');
        })
        .catch(() => {
            document.getElementById('product-body').innerHTML =
                '<tr><td colspan="5" style="text-align:center;color:darkred;padding:10px;">Could not load - check DB connection</td></tr>';
        });
}

function ensureRegionLoaded() {
    const key = document.getElementById('region-month').value;
    if (!key) return;
    if (loadedMonth.region === key) { renderRegionTable(); return; }
    setStatus('Loading…');
    const { from, to } = monthKeyToRange(key);
    fetch('api/get_report_by_region.php?' + new URLSearchParams({ from, to }))
        .then(r => r.json())
        .then(rows => {
            if (rows && rows.error) { toast('Error: ' + rows.error, 'err'); return; }
            regionData = rows;
            loadedMonth.region = key;
            visibleCount.region = PAGE_SIZE;
            renderRegionTable();
            setStatus('Ready');
        })
        .catch(() => {
            document.getElementById('region-body').innerHTML =
                '<tr><td style="text-align:center;color:darkred;padding:10px;">Could not load - check DB connection</td></tr>';
        });
}

function ensureCustomerLoaded() {
    const key = document.getElementById('customer-month').value;
    if (!key) return;
    if (loadedMonth.customer === key) { renderCustomerTable(); return; }
    setStatus('Loading…');
    const { from, to } = monthKeyToRange(key);
    fetch('api/get_report_by_customer.php?' + new URLSearchParams({ from, to }))
        .then(r => r.json())
        .then(rows => {
            if (rows && rows.error) { toast('Error: ' + rows.error, 'err'); return; }
            customerData = rows;
            loadedMonth.customer = key;
            visibleCount.customer = PAGE_SIZE;
            renderCustomerTable();
            setStatus('Ready');
        })
        .catch(() => {
            document.getElementById('customer-body').innerHTML =
                '<tr><td style="text-align:center;color:darkred;padding:10px;">Could not load - check DB connection</td></tr>';
        });
}

function renderProductTable() {
    const key = document.getElementById('product-month').value;
    const tbody = document.getElementById('product-body');
    tbody.innerHTML = '';
    const rows = productData.filter(r => monthKey(r) === key);
    if (!rows.length) { tbody.innerHTML = '<tr><td colspan="5" class="empty-note">No data for this month</td></tr>'; return; }

    // Totals are always computed over every matching row for the month, not
    // just the ones currently visible -- Load More should never change the
    // Total line, only reveal more detail above it.
    let tPacks=0, tSale=0, tCost=0;
    rows.forEach(r => { tPacks += Number(r.Packs); tSale += Number(r.Sale); tCost += Number(r.Cost); });

    rows.slice(0, visibleCount.product).forEach(r => {
        const profit = r.Sale - r.Cost;
        const tr = document.createElement('tr');
        tr.innerHTML = `<td style="font-weight:bold;color:#0a246a;">${r.Item}</td>
            <td style="text-align:right;">${fmt(r.Packs)}</td>
            <td style="text-align:right;">${fmt(r.Sale)}</td>
            <td style="text-align:right;color:#b03030;">${fmt(r.Cost)}</td>
            <td style="text-align:right;font-weight:bold;color:${profit>=0?'#1a7a1a':'#b03030'};">${fmt(profit)}</td>`;
        tbody.appendChild(tr);
    });
    tbody.insertAdjacentHTML('beforeend', loadMoreRow('product', rows.length, 5, 'visibleCount.product += PAGE_SIZE; renderProductTable();'));

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
    pivoted.forEach(entry => { cols.forEach(c => { colTotals[c] += entry.sizeProfit[c] || 0; }); grandTotal += entry.total; });

    pivoted.slice(0, visibleCount.region).forEach(entry => {
        const tr = document.createElement('tr');
        let html = `<td style="font-weight:bold;color:#0a246a;">${entry.label}</td>`;
        cols.forEach(c => {
            const v = entry.sizeProfit[c] || 0;
            html += `<td style="text-align:right;color:${v>=0?'#1a7a1a':'#b03030'};">${fmt(v)}</td>`;
        });
        html += `<td style="text-align:right;font-weight:bold;color:${entry.total>=0?'#1a7a1a':'#b03030'};">${fmt(entry.total)}</td>`;
        tr.innerHTML = html;
        tbody.appendChild(tr);
    });
    tbody.insertAdjacentHTML('beforeend', loadMoreRow('region', pivoted.length, cols.length+2, 'visibleCount.region += PAGE_SIZE; renderRegionTable();'));

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
    pivoted.forEach(entry => { cols.forEach(c => { colTotals[c] += entry.sizeProfit[c] || 0; }); grandTotal += entry.total; });

    pivoted.slice(0, visibleCount.customer).forEach(entry => {
        const tr = document.createElement('tr');
        let html = `<td style="font-weight:bold;color:#0a246a;">${entry.label}</td>`;
        cols.forEach(c => {
            const v = entry.sizeProfit[c] || 0;
            html += `<td style="text-align:right;color:${v>=0?'#1a7a1a':'#b03030'};">${fmt(v)}</td>`;
        });
        html += `<td style="text-align:right;font-weight:bold;color:${entry.total>=0?'#1a7a1a':'#b03030'};">${fmt(entry.total)}</td>`;
        tr.innerHTML = html;
        tbody.appendChild(tr);
    });
    tbody.insertAdjacentHTML('beforeend', loadMoreRow('customer', pivoted.length, cols.length+2, 'visibleCount.customer += PAGE_SIZE; renderCustomerTable();'));

    const totalTr = document.createElement('tr');
    totalTr.className = 'grand-total';
    let html = `<td>Total${filterText ? ' (filtered)' : ''}</td>`;
    cols.forEach(c => { html += `<td style="text-align:right;">${fmt(colTotals[c])}</td>`; });
    html += `<td style="text-align:right;">${fmt(grandTotal)}</td>`;
    totalTr.innerHTML = html;
    tbody.appendChild(totalTr);
}

loadMonthList();
</script>
</body>
</html>
