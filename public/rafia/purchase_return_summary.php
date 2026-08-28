<?php
require_once __DIR__ . '/../includes/access.php';
requireAccess('inventory');
$currentMonth = date('Y-m');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AISellProduct - Purchased &amp; Return Invoice(s) Summary</title>
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

input[type=month] {
    border: 1px solid; border-color: #808080 #ffffff #ffffff #808080;
    background: #ffff99; padding: 1px 4px; height: 22px; font-size:12px;
    font-family: Tahoma, sans-serif;
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
.win-table tfoot td { border:1px solid #808080; padding:3px 5px; background:#e8e4d8; font-weight:bold; }

.win-section-label { background:#d4d0c8; font-weight:bold; padding:3px 8px; border-bottom:1px solid #808080; display:flex; align-items:center; justify-content:space-between; }
.win-statusbar { background:#d4d0c8; border-top:1px solid #808080; padding:3px 8px; display:flex; gap:14px; }
.win-statusbar span { border-right:1px solid #808080; padding-right:12px; }

#toast { position:fixed; bottom:16px; right:16px; z-index:9999; padding:8px 16px; font-weight:bold; font-size:12px; border:1px solid; display:none; }
</style>
</head>
<body class="flex flex-col h-screen">

<?php $SCREEN_NAME = 'Purchased & Return Invoice(s) Summary'; $SCREEN_ICON = 'truck'; require __DIR__ . '/../includes/navbar.php'; ?>

<div style="display:flex;flex-direction:column;flex:1;padding:5px;gap:4px;min-height:0;overflow:auto;">

    <div class="win-panel" style="padding:8px;display:flex;align-items:center;gap:8px;">
        <label style="font-weight:bold;">Month</label>
        <input id="f-month" type="month" value="<?php echo htmlspecialchars($currentMonth); ?>" onchange="loadSummary()">
    </div>

    <div class="win-panel" style="display:flex;flex-direction:column;min-height:150px;">
        <div class="win-section-label"><span>Purchase</span><span id="purchase-count" style="font-weight:normal;color:#555;"></span></div>
        <div style="overflow:auto;max-height:300px;">
            <table class="win-table">
                <thead>
                    <tr><th>Trans #</th><th>Inv #</th><th>Inv Date</th><th>Alias</th><th>Supplier</th><th style="text-align:right;">Bonus</th><th style="text-align:right;">Qty</th><th style="text-align:right;">Amount</th><th>Created By</th><th>Posted By</th><th>Posted On</th></tr>
                </thead>
                <tbody id="purchase-body"><tr><td colspan="11" style="text-align:center;padding:10px;color:#888;">Loading…</td></tr></tbody>
                <tfoot id="purchase-foot"></tfoot>
            </table>
        </div>
    </div>

    <div class="win-panel" style="display:flex;flex-direction:column;min-height:150px;">
        <div class="win-section-label">
            <span>Purchase Return</span>
            <span id="return-count" style="font-weight:normal;color:#555;"></span>
        </div>
        <div style="padding:4px 8px;color:#555;background:#fff8dc;border-bottom:1px solid #808080;">
            Reporting-only — always honestly empty right now. No entry screen exists yet to record a return; this table will populate once one is built.
        </div>
        <div style="overflow:auto;max-height:300px;">
            <table class="win-table">
                <thead>
                    <tr><th>Trans #</th><th>Inv #</th><th>Inv Date</th><th>Alias</th><th>Supplier</th><th style="text-align:right;">Bonus</th><th style="text-align:right;">Qty</th><th style="text-align:right;">Amount</th><th>Created By</th><th>Posted By</th><th>Posted On</th></tr>
                </thead>
                <tbody id="return-body"><tr><td colspan="11" style="text-align:center;padding:10px;color:#888;">Loading…</td></tr></tbody>
                <tfoot id="return-foot"></tfoot>
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

function renderTable(bodyId, footId, countId, rows, noun) {
    document.getElementById(countId).textContent = rows.length + ' invoice(s)';
    const body = document.getElementById(bodyId);
    const foot = document.getElementById(footId);
    if (!rows.length) {
        body.innerHTML = `<tr><td colspan="11" style="text-align:center;padding:10px;color:#888;">No ${noun} found for this range.</td></tr>`;
        foot.innerHTML = '';
        return;
    }
    body.innerHTML = rows.map(r => `
        <tr>
            <td>${r.trans_no || '—'}</td>
            <td>${r.invoice_no}</td>
            <td>${esc(r.invoice_date)}</td>
            <td>${esc(r.alias) || '—'}</td>
            <td>${esc(r.supplier_name) || '—'}</td>
            <td style="text-align:right;">${Number(r.bonus).toLocaleString()}</td>
            <td style="text-align:right;">${Number(r.qty).toLocaleString()}</td>
            <td style="text-align:right;">${esc(r.total_amount)}</td>
            <td>${esc(r.created_by) || '—'}</td>
            <td>${esc(r.posted_by) || '—'}</td>
            <td>${esc(r.posted_on) || '—'}</td>
        </tr>`).join('');
    let qtySum = 0, amtSum = 0;
    rows.forEach(r => { qtySum += Number(r.qty); amtSum += Number(String(r.total_amount).replace(/,/g,'')); });
    foot.innerHTML = `<tr><td colspan="6">Total</td><td style="text-align:right;">${qtySum.toLocaleString()}</td><td style="text-align:right;">${amtSum.toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2})}</td><td colspan="3"></td></tr>`;
}

function loadSummary() {
    setStatus('Loading…');
    const params = new URLSearchParams({ month: document.getElementById('f-month').value });
    fetch('api/purchase_return_summary.php?' + params.toString())
        .then(r => r.json())
        .then(res => {
            if (res && res.error) {
                toast('Error: ' + res.error, 'err');
                setStatus('Ready');
                return;
            }
            renderTable('purchase-body', 'purchase-foot', 'purchase-count', res.purchases, 'purchase invoices');
            renderTable('return-body', 'return-foot', 'return-count', res.returns, 'purchase returns');
            setStatus('Ready');
        })
        .catch(() => setStatus('Ready'));
}

loadSummary();
</script>
</body>
</html>
