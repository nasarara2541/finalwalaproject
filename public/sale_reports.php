<?php
require_once __DIR__ . '/includes/access.php';
requireAccess('daily_sale');
$isWaterDb = ($_SESSION['active_db_label'] ?? 'Water Distribution') === 'Water Distribution';
$groupLabel = $isWaterDb ? 'Water' : 'Medicine';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AISellProduct - Sale Reports</title>
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

input[type=text], input[type=date], input[type=time], select {
    border: 1px solid; border-color: #808080 #ffffff #ffffff #808080;
    background: #ffff99; padding: 2px 4px; height: 22px; font-size:12px;
    font-family: Tahoma, sans-serif; width:100%;
}
input[readonly], input[disabled], select[disabled] { background: #d4d0c8 !important; color:#888; }

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
.report-btn { width:100%; justify-content:flex-start; height:26px; border-bottom:1px solid #c8c4ba; }
.report-btn:last-child { border-bottom:none; }

.win-table { width:100%; border-collapse:collapse; font-size:11px; }
.win-table thead th { border:1px solid #808080; padding:3px 5px; text-align:left; font-weight:bold; background:#d4d0c8; white-space:nowrap; position:sticky; top:0; z-index:1; }
.win-table tbody tr { background:#fff; }
.win-table tbody tr:nth-child(even) { background:#f5f3ee; }
.win-table td { border:1px solid #d0ccc4; padding:3px 5px; white-space:nowrap; }

.win-section-label { background:#d4d0c8; font-weight:bold; padding:3px 8px; border-bottom:1px solid #808080; display:flex; align-items:center; justify-content:space-between; }
.win-statusbar { background:#d4d0c8; border-top:1px solid #808080; padding:3px 8px; display:flex; gap:14px; }

.field-row { display:flex; align-items:center; gap:6px; margin-bottom:5px; }
label.lbl { font-weight:bold; white-space:nowrap; width:100px; flex-shrink:0; text-align:right; }

/* Report results popup — same visual pattern used everywhere else in this app */
#report-popup-overlay {
    display:none; position:fixed; top:0; left:0; width:100%; height:100%;
    background:rgba(0,0,0,0.55); z-index:8000;
    justify-content:center; align-items:center;
}
#report-popup-overlay.open { display:flex; }
#report-popup-box {
    background:#fff; width:640px; max-height:80vh; display:flex; flex-direction:column;
    border:2px solid #0a246a; box-shadow:4px 4px 18px rgba(0,0,0,0.5);
}
#report-popup-titlebar {
    background:linear-gradient(to right,#0a246a,#3a6ea5); color:white;
    font-weight:bold; font-size:12px; padding:4px 8px;
    display:flex; align-items:center; justify-content:space-between; cursor:move; user-select:none;
}
#report-popup-titlebar span.close-x { cursor:pointer; font-size:14px; padding:0 4px; font-weight:bold; }
#report-popup-titlebar span.close-x:hover { color:#ffaaaa; }
#report-popup-body { overflow:auto; flex:1; padding:8px; }
#report-popup-footer { padding:6px 8px; border-top:1px solid #ccc; display:flex; gap:6px; background:#ece9d8; }

#toast { position:fixed; bottom:16px; right:16px; z-index:9999; padding:8px 16px; font-weight:bold; font-size:12px; border:1px solid; display:none; }
</style>
</head>
<body class="flex flex-col h-screen">

<div class="win-titlebar">
    <span>&#x1F4CA; AISellProduct &mdash; Sale Reports</span>
    <span id="live-clock" style="font-weight:normal;font-size:11px;"></span>
</div>

<div class="win-menubar">
    <span class="win-menu-item" onclick="window.location='pos.php'">&#x2190; Back to Sale</span>
    <span class="win-menu-item nav-active">Sale Reports</span>
    <?php if (!empty($_SESSION['emp_is_admin'])): ?>
    <span class="win-menu-item" style="color:#5b3a8a;font-weight:bold;" onclick="window.location='admin_users.php'">&#x1F464; Manage Users</span>
    <span class="win-menu-item" style="color:#5b3a8a;font-weight:bold;" onclick="window.location='admin_dashboard.php'">&#x1F4CA; Dashboard</span>
    <span class="win-menu-item" style="color:#5b3a8a;font-weight:bold;" onclick="window.location='reports/admin_reports.php'">&#x1F4C8; Profit Reports</span>
    <?php endif; ?>
    <span style="flex:1"></span>
    <span class="win-menu-item" style="color:#555;">Database: <b><?php echo htmlspecialchars($_SESSION['active_db_label'] ?? 'Water Distribution'); ?></b></span>
    <span class="win-menu-item" onclick="window.location='login.php'" title="Pick a different database">&#x1F504; Switch Database</span>
    <span class="win-menu-item" style="color:#555;">User: <b><?php echo htmlspecialchars($_SESSION['emp_user_name'] ?? '—'); ?></b></span>
    <span class="win-menu-item" onclick="window.location='logout.php'" title="Sign out" style="color:darkred;">&#x1F6AA; Logout</span>
</div>

<div style="display:flex;flex-direction:column;flex:1;padding:5px;gap:4px;min-height:0;">

    <!-- Top: date/time range + counter -->
    <div class="win-panel" style="padding:8px;">
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:6px;">
            <label style="display:flex;align-items:center;gap:4px;font-weight:bold;" title="Unchecked by default so a fresh search shows all-time-up-to-today instead of only today (which would hide almost everything, since this data is historical) — check this and pick a date to actually narrow it down">
                <input id="chk-from" type="checkbox" style="width:auto;height:auto;"> From
            </label>
            <input id="f-date-from" type="date" style="width:140px;">
            <label style="font-weight:bold;">To</label>
            <input id="f-date-to" type="date" style="width:140px;">

            <label style="display:flex;align-items:center;gap:4px;font-weight:bold;margin-left:20px;" title="Trans_date already stores a full timestamp — GETDATE() has always recorded real time-of-day for every sale made through the live app. Historical bulk-loaded bills just never had time data, so they'll all show 00:00:00.">
                <input id="chk-time" type="checkbox" style="width:auto;height:auto;"> Time From
            </label>
            <input id="f-time-from" type="time" value="07:00" style="width:110px;">
            <label style="font-weight:bold;">To</label>
            <input id="f-time-to" type="time" value="16:00" style="width:110px;">
        </div>
        <div class="field-row" style="max-width:340px;">
            <label class="lbl" style="color:#888;" title="Closest real concept is Branch_code (always &quot;HQ&quot; today) — not wired up yet, noted for follow-up">Counter Name</label>
            <select disabled title="Not wired up yet — noted for follow-up"><option>ALL</option></select>
        </div>
    </div>

    <!-- Bills | Items -->
    <div style="display:flex;gap:4px;flex:1;min-height:0;">

        <div class="win-panel" style="flex:1;display:flex;flex-direction:column;min-height:0;padding:8px;overflow:auto;">
            <div class="win-section-label" style="margin:-8px -8px 8px -8px;"><span>Bills</span></div>

            <label style="display:flex;align-items:center;gap:5px;font-weight:bold;margin-bottom:8px;">
                <input type="radio" name="bills-mode" checked style="width:auto;height:auto;"> Summary
            </label>

            <div class="field-row">
                <label class="lbl">Sale Type</label>
                <select id="f-sale-type">
                    <option value="">All</option>
                    <option value="Cash">Cash</option>
                    <option value="Credit">Credit</option>
                    <option value="Card">Card</option>
                </select>
            </div>
            <div class="field-row"><label class="lbl">Created By</label><input id="f-created-by" type="text"></div>
            <div class="field-row"><label class="lbl">Customer</label><input id="f-customer" type="text" list="customer-datalist" autocomplete="off"></div>
            <div class="field-row"><label class="lbl">Address</label><input id="f-address" type="text"></div>
            <div class="field-row">
                <label class="lbl" title="Who gets sales credit — distinct from Created By (the cashier's login). Filters by name against the Employee table.">Sale Person</label>
                <input id="f-sale-person" type="text" list="employee-datalist">
            </div>
            <div class="field-row"><label class="lbl">Ref #</label><input id="f-ref" type="text"></div>
            <div class="field-row"><label class="lbl">Description</label><input id="f-description" type="text"></div>
            <div class="field-row"><label class="lbl">Remarks</label><input id="f-remarks" type="text"></div>
            <div class="field-row"><label class="lbl">Modify By</label><input id="f-modify-by" type="text"></div>
            <div class="field-row">
                <label class="lbl" title="Matches via each line item's Item_Stock.SUPPLIER_CODE — a bill counts if ANY item on it came from this supplier. No real item has a supplier assigned yet, so this returns nothing until one does.">Supplier</label>
                <input id="f-supplier" type="text">
            </div>
            <div class="field-row">
                <label class="lbl" style="color:#888;" title="Same situation as Counter Name — needs a predefined list of shift codes/times, which is a business decision for your professor, not something to invent. Not wired up yet, noted for follow-up.">Shift</label>
                <select disabled title="Not wired up yet — noted for follow-up"><option>T</option></select>
            </div>

            <div class="win-inset" style="margin-top:auto;">
                <button class="win-btn report-btn" disabled title="No counter/register concept exists — not wired up yet, noted for follow-up">Counter Wise Cash</button>
                <button class="win-btn report-btn" onclick="runReport('total_sale')">Total Sale</button>
                <button class="win-btn report-btn" onclick="runReport('users_bill_count')">Users Bill(s) Count</button>
                <button class="win-btn report-btn" onclick="runReport('day_wise_sale')">Day Wise Sale &amp; Return Summary</button>
                <button class="win-btn report-btn" onclick="runReport('cancelled_bills')">Cancelled Bills</button>
            </div>
        </div>

        <div class="win-panel" style="flex:1;display:flex;flex-direction:column;min-height:0;padding:8px;overflow:auto;">
            <div class="win-section-label" style="margin:-8px -8px 8px -8px;"><span>Items</span></div>

            <div class="field-row"><label class="lbl">Code</label><input id="f-item-code" type="text"></div>
            <div class="field-row"><label class="lbl">Name</label><input id="f-item-name" type="text"></div>
            <div class="field-row"><label class="lbl">Type</label><input id="f-item-type" type="text"></div>
            <div class="field-row"><label class="lbl">Manufacture</label><input id="f-manufacture" type="text"></div>
            <div class="field-row"><label class="lbl">Company</label><input id="f-company" type="text"></div>
            <div class="field-row">
                <label class="lbl" title="No product-category column exists — this database is entirely one category, shown here rather than offered as a real filter">Group</label>
                <input type="text" value="<?php echo htmlspecialchars($groupLabel); ?>" readonly tabindex="-1">
            </div>
            <div class="field-row">
                <label class="lbl" title="Now written at sale time — FEFO already picks a specific batch per sale, save_transaction.php just records which one into trans_detail.Invoice_No. Only true going forward: every pre-existing historical sale still has no batch link.">Batch #</label>
                <input id="f-batch" type="text">
            </div>
            <div class="field-row"><label class="lbl">Customer</label><input id="f-item-customer" type="text" list="customer-datalist" autocomplete="off"></div>
            <div class="field-row">
                <label class="lbl" title="Same field/meaning as the Bills panel's Sale Person">Sale Person</label>
                <input id="f-item-sale-person" type="text" list="employee-datalist">
            </div>
            <div class="field-row"><label class="lbl">Created By</label><input id="f-item-created-by" type="text"></div>
            <div class="field-row"><label class="lbl">Modify By</label><input id="f-item-modify-by" type="text"></div>

            <div class="win-inset" style="margin-top:auto;">
                <button class="win-btn report-btn" onclick="runReport('item_group_summary')" title="Group is a hardcoded Water/Medicine constant (no real category column), so this always returns exactly one summary row">Item Group Summary</button>
                <button class="win-btn report-btn" onclick="runReport('all_sold_items')">All Sold Items Detail</button>
                <button class="win-btn report-btn" onclick="runReport('price_changed')" title="Only populated when an item's price actually changes via Stock Receiving — empty until that happens for an item">Price Changed Item(s)</button>
            </div>
        </div>
        <datalist id="employee-datalist"></datalist>
        <datalist id="customer-datalist"></datalist>

    </div>

    <div style="display:flex;justify-content:flex-end;">
        <button class="win-btn win-btn-blue" style="padding:4px 24px;" onclick="window.location='pos.php'">eXit</button>
    </div>

</div>

<div class="win-statusbar">
    <span id="status-msg">Ready</span>
</div>

<!-- ===== REPORT RESULTS POPUP ===== -->
<div id="report-popup-overlay">
    <div id="report-popup-box">
        <div id="report-popup-titlebar">
            <span id="report-popup-title">Report</span>
            <span class="close-x" onclick="closeReportPopup()">&#x2716;</span>
        </div>
        <div id="report-popup-body">
            <table class="win-table">
                <thead id="report-table-head"></thead>
                <tbody id="report-table-body"></tbody>
            </table>
        </div>
        <div id="report-popup-footer">
            <button class="win-btn" onclick="closeReportPopup()" style="color:darkred;">Close</button>
        </div>
    </div>
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

function runReport(report) {
    if (!document.getElementById('chk-from').checked) {
        // From/To always sent regardless, but this mirrors the reference's
        // checkbox — unchecking it just means "no date restriction".
    }
    const timeOn = document.getElementById('chk-time').checked;
    const itemsSide = (report==='all_sold_items'||report==='item_group_summary'||report==='price_changed');
    const params = new URLSearchParams({
        report: report,
        from: document.getElementById('chk-from').checked ? document.getElementById('f-date-from').value : '',
        to:   document.getElementById('f-date-to').value,
        time_from: timeOn ? document.getElementById('f-time-from').value : '',
        time_to:   timeOn ? document.getElementById('f-time-to').value   : '',
        sale_type:   document.getElementById('f-sale-type')  ? document.getElementById('f-sale-type').value  : '',
        created_by:  itemsSide ? document.getElementById('f-item-created-by').value : document.getElementById('f-created-by').value,
        customer:    itemsSide ? document.getElementById('f-item-customer').value    : document.getElementById('f-customer').value,
        sale_person: itemsSide ? document.getElementById('f-item-sale-person').value : document.getElementById('f-sale-person').value,
        modify_by:   itemsSide ? document.getElementById('f-item-modify-by').value   : document.getElementById('f-modify-by').value,
        address:     document.getElementById('f-address') ? document.getElementById('f-address').value : '',
        ref:         document.getElementById('f-ref') ? document.getElementById('f-ref').value : '',
        description: document.getElementById('f-description') ? document.getElementById('f-description').value : '',
        remarks:     document.getElementById('f-remarks') ? document.getElementById('f-remarks').value : '',
        supplier:    document.getElementById('f-supplier') ? document.getElementById('f-supplier').value : '',
        item_code:    document.getElementById('f-item-code').value,
        item_name:    document.getElementById('f-item-name').value,
        item_type:    document.getElementById('f-item-type').value,
        manufacture:  document.getElementById('f-manufacture').value,
        company:      document.getElementById('f-company').value,
        batch:        document.getElementById('f-batch') ? document.getElementById('f-batch').value : ''
    });
    setStatus('Running ' + report + '…');
    fetch('api/sale_reports.php?' + params.toString())
        .then(r => r.json())
        .then(res => {
            if (res.error) { toast('Error: ' + res.error, 'err'); setStatus('Ready'); return; }
            openReportPopup(res);
            setStatus('Ready');
        });
}

function openReportPopup(res) {
    document.getElementById('report-popup-title').textContent = res.title;
    document.getElementById('report-table-head').innerHTML =
        '<tr>' + res.columns.map(c => `<th>${c}</th>`).join('') + '</tr>';
    const tbody = document.getElementById('report-table-body');
    tbody.innerHTML = '';
    if (!res.rows.length) {
        tbody.innerHTML = `<tr><td colspan="${res.columns.length}" style="text-align:center;padding:10px;color:#888;">No data for this filter/date range</td></tr>`;
    } else {
        // Rows come back from the API as arrays of already-formatted display
        // strings (money pre-formatted to 2dp, counts as plain integers) —
        // no type-guessing needed here, just print each cell right-aligned
        // if it's numeric-looking.
        res.rows.forEach(row => {
            const tr = document.createElement('tr');
            const cells = row.map(v => {
                const display = (v === null || v === undefined || v === '') ? '—' : v;
                const isNumeric = typeof display === 'number' || (typeof display === 'string' && /^-?[\d,]+(\.\d+)?$/.test(display));
                return `<td${isNumeric ? ' style="text-align:right;"' : ''}>${display}</td>`;
            }).join('');
            tr.innerHTML = cells;
            tbody.appendChild(tr);
        });
    }
    document.getElementById('report-popup-overlay').classList.add('open');
}

function closeReportPopup() { document.getElementById('report-popup-overlay').classList.remove('open'); }

document.getElementById('report-popup-overlay').addEventListener('click', function(e) {
    if (e.target === this) closeReportPopup();
});

function makeDraggable(boxId, barId) {
    const box = document.getElementById(boxId);
    const bar = document.getElementById(barId);
    let dragging = false, ox = 0, oy = 0;
    bar.addEventListener('mousedown', e => {
        dragging = true; ox = e.clientX - box.offsetLeft; oy = e.clientY - box.offsetTop;
    });
    document.addEventListener('mousemove', e => {
        if (!dragging) return;
        box.style.position = 'fixed';
        box.style.left = (e.clientX - ox) + 'px';
        box.style.top  = (e.clientY - oy) + 'px';
        box.style.margin = '0';
    });
    document.addEventListener('mouseup', () => { dragging = false; });
}
makeDraggable('report-popup-box', 'report-popup-titlebar');

// Default date range: today only (matches the reference's From/To both
// defaulting to the same date in the screenshot). Built from local date
// parts, not toISOString() (which is UTC and can land on the wrong day
// depending on the machine's timezone/time of day).
function localDateStr(d) {
    return d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
}
const today = localDateStr(new Date());
document.getElementById('f-date-from').value = today;
document.getElementById('f-date-to').value   = today;

fetch('api/get_employees.php').then(r=>r.json()).then(rows => {
    const dl = document.getElementById('employee-datalist');
    rows.forEach(e => {
        const opt = document.createElement('option');
        opt.value = e.Full_Name;
        dl.appendChild(opt);
    });
});

fetch('api/get_customers.php').then(r=>r.json()).then(rows => {
    const dl = document.getElementById('customer-datalist');
    rows.forEach(c => {
        const opt = document.createElement('option');
        opt.value = c.Cust_name;
        dl.appendChild(opt);
    });
});
</script>
</body>
</html>
