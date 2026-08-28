<?php
require_once __DIR__ . '/includes/access.php';
requireAccess('daily_sale');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AISellProduct - Sale Items</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
* { font-family: Tahoma, 'Segoe UI', sans-serif; font-size: 12px; box-sizing: border-box; }
html, body { height: 100%; margin: 0; }
body { background: #d4d0c8; overflow: hidden; }

.win-inset  { border: 1px solid; border-color: #808080 #ffffff #ffffff #808080; background:#fff; }
.win-panel  { border: 1px solid; border-color: #808080 #ffffff #ffffff #808080; background:#ece9d8; }
.win-titlebar { background: linear-gradient(to right, #0a246a, #3a6ea5); color: white; font-weight: bold; padding: 4px 8px; display:flex; align-items:center; justify-content:space-between; }
.win-menubar { background: #d4d0c8; border-bottom: 1px solid #808080; display:flex; padding: 2px; }
.win-menu-item { padding: 3px 10px; cursor:pointer; font-size:12px; }
.win-menu-item:hover, .nav-active { background:#0a246a; color:white; }

input[type=text], input[type=number], input[type=date], select {
    border: 1px solid; border-color: #808080 #ffffff #ffffff #808080;
    background: #ffff99; padding: 2px 4px; height: 22px; font-size:12px; font-family: Tahoma, sans-serif;
}
input[readonly], input[disabled], select[disabled] { background: #d4d0c8 !important; color:#666; }

.win-btn { background:#d4d0c8; border:1px solid; border-color:#ffffff #808080 #808080 #ffffff; padding:2px 10px; cursor:pointer; height:23px; display:inline-flex; align-items:center; gap:3px; }
.win-btn:hover { background:#e8e4d8; }
.win-btn:disabled { color:#999; cursor:default; background:#d4d0c8; }
.win-btn:disabled:hover { background:#d4d0c8; }
.win-btn-blue  { background:#003087; color:white; border-color:#5599cc #002266 #002266 #5599cc; }
.win-btn-green { background:#1a7a1a; color:white; border-color:#44aa44 #0a500a #0a500a #44aa44; }
.win-btn-red   { background:#8b0000; color:white; border-color:#cc4444 #550000 #550000 #cc4444; }

.toolbar-btn { flex:1; height:38px; font-size:13px; font-weight:bold; border-color:#ffffff #808080 #808080 #ffffff; }
.toolbar-btn:disabled { color:#999; }

.win-table { width:100%; border-collapse:collapse; font-size:11px; }
.win-table thead th { border:1px solid #808080; padding:3px 5px; background:#d4d0c8; font-weight:bold; text-align:left; position:sticky; top:0; }
.win-table tbody tr { background:#fff; }
.win-table tbody tr:nth-child(even) { background:#f5f3ee; }
.win-table tbody tr:hover { background:#c5d5e8 !important; cursor:pointer; }
.win-table tbody tr.row-selected { background:#0a246a !important; color:white; }
.win-table tbody tr.row-selected td { color:white !important; }
.win-table td { border:1px solid #d0ccc4; padding:3px 5px; white-space:nowrap; }

.win-section-label { background:#d4d0c8; font-weight:bold; padding:3px 8px; border-bottom:1px solid #808080; display:flex; align-items:center; justify-content:space-between; }
.win-scroll { overflow:auto; min-height:0; }
.win-statusbar { background:#d4d0c8; border-top:1px solid #808080; padding:3px 8px; display:flex; gap:14px; font-size:12px; }

.popup-overlay { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:8000; justify-content:center; align-items:center; }
.popup-overlay.open { display:flex; }
.popup-box { background:#ece9d8; border:2px solid #0a246a; box-shadow:4px 4px 16px rgba(0,0,0,0.5); display:flex; flex-direction:column; min-width:320px; max-height:85vh; }
.popup-titlebar { background:linear-gradient(to right,#0a246a,#3a6ea5); color:white; font-weight:bold; padding:4px 8px; display:flex; align-items:center; justify-content:space-between; cursor:move; user-select:none; }
.popup-titlebar span.close-x { cursor:pointer; font-size:14px; padding:0 4px; font-weight:bold; }
.popup-body { overflow:auto; flex:1; padding:8px; }
.popup-footer { padding:6px 8px; border-top:1px solid #ccc; display:flex; gap:6px; background:#ece9d8; }

#toast { position:fixed; bottom:16px; right:16px; z-index:9999; padding:8px 16px; font-weight:bold; font-size:12px; border:1px solid; display:none; }
</style>
</head>
<body class="flex flex-col h-screen">

<?php $SCREEN_NAME = 'Sale Items'; require __DIR__ . '/includes/navbar.php'; ?>

<div style="display:flex;flex-direction:column;flex:1;padding:5px;gap:4px;min-height:0;">

    <!-- Toolbar -->
    <div style="display:flex;gap:4px;">
        <button class="win-btn toolbar-btn win-btn-green" onclick="saveBill()">Save</button>
        <button class="win-btn toolbar-btn" onclick="clearSaleForm()">Clear Sale</button>
        <button class="win-btn toolbar-btn" onclick="openEditPopup()">Edit</button>
        <button class="win-btn toolbar-btn" disabled title="No returns/refunds concept exists anywhere in this app yet — you said this is coming as its own screen. Not wired up yet, noted for follow-up.">Return</button>
        <button class="win-btn toolbar-btn win-btn-blue" onclick="printBill()">Print</button>
        <button class="win-btn toolbar-btn" onclick="holdBill()">Hold</button>
        <button class="win-btn toolbar-btn" onclick="cancelCurrentBill()" title="Voids the currently loaded bill and reverses its stock — load one via Edit first">Cancellation</button>
        <button class="win-btn toolbar-btn" style="color:darkred;" onclick="clearSaleForm()">Cancel</button>
    </div>

    <!-- Bill info row -->
    <div class="win-panel" style="display:flex;align-items:center;gap:10px;padding:5px 8px;flex-wrap:wrap;">
        <div style="display:flex;align-items:center;gap:4px;">
            <label style="font-weight:bold;">Bill No</label>
            <input id="bill-no" type="text" readonly value="—" style="width:80px;font-weight:bold;color:#0a246a;" tabindex="-1">
        </div>
        <div style="display:flex;align-items:center;gap:4px;">
            <label style="font-weight:bold;">Date &amp; Time</label>
            <input id="bill-datetime" type="text" readonly style="width:190px;background:#0a246a !important;color:white !important;font-weight:bold;text-align:center;" tabindex="-1">
        </div>
        <div style="display:flex;align-items:center;gap:4px;">
            <label style="font-weight:bold;">Payment Type</label>
            <select id="trans-type" style="width:80px;">
                <option value="Cash">Cash</option>
                <option value="Credit">Credit</option>
                <option value="Card">Card</option>
            </select>
        </div>
        <div style="display:flex;align-items:center;gap:4px;flex:1;min-width:200px;">
            <label style="font-weight:bold;">Client Name</label>
            <input id="cust-name" type="text" list="cust-datalist" placeholder="Walk-in / Type to search…" style="flex:1;" oninput="onClientInput()" autocomplete="off">
            <datalist id="cust-datalist"></datalist>
        </div>
        <div style="display:flex;align-items:center;gap:6px;margin-left:auto;">
            <span class="win-menu-item" style="border:1px solid #808080;" onclick="openHeldPopup()" title="View/resume bills set aside with Hold">&#x1F4C2; Held (<span id="held-count">0</span>)</span>
            <span id="cart-item-badge" style="background:#0a246a;color:white;font-weight:bold;font-size:20px;padding:2px 14px;border:1px solid #000;">0</span>
            <span style="font-weight:bold;">Items:</span>
        </div>
    </div>

    <!-- Second info row — not shown in the reference crop, but these fields
         appear as real filters on Sale Reports (Sale Person/Description/
         Remarks), so they need somewhere to actually be entered or those
         filters would forever return nothing. -->
    <div class="win-panel" style="display:flex;align-items:center;gap:10px;padding:4px 8px;flex-wrap:wrap;">
        <div style="display:flex;align-items:center;gap:4px;">
            <label style="font-weight:bold;" title="Who gets sales credit for this bill — distinct from the logged-in cashier">Sale Person</label>
            <input id="sale-person" type="text" list="employee-datalist" placeholder="Optional" style="width:150px;" oninput="onSalePersonInput()" autocomplete="off">
            <datalist id="employee-datalist"></datalist>
        </div>
        <div style="display:flex;align-items:center;gap:4px;flex:1;min-width:150px;">
            <label style="font-weight:bold;">Description</label>
            <input id="bill-description" type="text" placeholder="Optional" style="flex:1;">
        </div>
        <div style="display:flex;align-items:center;gap:4px;flex:1;min-width:150px;">
            <label style="font-weight:bold;">Remarks</label>
            <input id="bill-remarks" type="text" placeholder="Optional" style="flex:1;">
        </div>
    </div>

    <!-- Main area: cart | entry + enriched results -->
    <div style="display:flex;gap:4px;flex:1;min-height:0;">

        <!-- LEFT: Bill Items cart (same proven pattern as pos.php — not
             visible in the reference crop, but a sale screen can't function
             without seeing what's actually in the bill before saving). -->
        <div class="win-panel" style="flex:1.2;display:flex;flex-direction:column;min-height:0;">
            <div class="win-section-label"><span>Bill Items</span><span id="cart-count" style="font-weight:normal;color:#555;">0 item(s)</span></div>
            <div class="win-scroll" style="flex:1;">
                <table class="win-table" id="cart-table">
                    <thead><tr><th>#</th><th>Stock No.</th><th>Product</th><th style="text-align:right;">Qty</th><th style="text-align:right;">Price</th><th style="text-align:right;">Amount</th><th>Del</th></tr></thead>
                    <tbody id="cart-body"><tr><td colspan="7" style="text-align:center;color:#888;padding:14px;">No items added yet</td></tr></tbody>
                </table>
            </div>
        </div>

        <!-- RIGHT: entry row + enriched search results -->
        <div style="flex:2;display:flex;flex-direction:column;gap:4px;min-height:0;">

            <div class="win-panel" style="padding:6px 8px;">
                <div style="display:flex;align-items:flex-end;gap:6px;flex-wrap:wrap;">
                    <div style="display:flex;flex-direction:column;gap:1px;"><label style="font-weight:bold;">Code</label><input id="sel-stock" type="text" readonly style="width:75px;" tabindex="-1"></div>
                    <div style="display:flex;flex-direction:column;gap:1px;position:relative;min-width:200px;"><label style="font-weight:bold;">Name</label><input id="item-search" type="text" placeholder="Type to search…" oninput="searchItems(this.value)" autocomplete="off"></div>
                    <div style="display:flex;flex-direction:column;gap:1px;">
                        <label style="font-weight:bold;color:#888;" title="No item-level bonus-on-sale column exists (BONUS_QTY only exists on the receiving side) — not wired up yet, noted for follow-up">Bonus</label>
                        <input type="text" disabled style="width:55px;" title="Not wired up yet — noted for follow-up">
                    </div>
                    <div style="display:flex;flex-direction:column;gap:1px;"><label style="font-weight:bold;">Qty</label><input id="sel-qty" type="number" min="1" value="1" style="width:55px;" oninput="recalcLine()"></div>
                    <div style="display:flex;flex-direction:column;gap:1px;">
                        <label style="font-weight:bold;color:#888;" title="No per-line discount column exists on trans_detail (only one overall bill-level discount is tracked, in the Calculator below) — not wired up yet, noted for follow-up">Disc %</label>
                        <input type="text" disabled style="width:50px;" title="Not wired up yet — noted for follow-up">
                    </div>
                    <div style="display:flex;flex-direction:column;gap:1px;"><label style="font-weight:bold;">U P</label><input id="sel-price" type="number" min="0" step="0.01" style="width:75px;" oninput="recalcLine()"></div>
                    <div style="display:flex;flex-direction:column;gap:1px;"><label style="font-weight:bold;">Amount</label><input id="sel-amount" type="text" readonly style="width:80px;font-weight:bold;" tabindex="-1"></div>
                    <div style="display:flex;flex-direction:column;gap:1px;"><label>&nbsp;</label><button class="win-btn win-btn-blue" onclick="addItemToCart()">+ Add</button></div>
                </div>
            </div>

            <div class="win-panel" style="flex:1;display:flex;flex-direction:column;min-height:0;">
                <div class="win-section-label"><span>Items</span><span id="result-count" style="font-weight:normal;color:#555;"></span></div>
                <div class="win-scroll" style="flex:1;">
                    <table class="win-table">
                        <thead><tr>
                            <th>Bar Code</th><th>Product Name</th>
                            <th>Generic</th>
                            <th>Type</th><th>Company</th><th>Manu</th>
                            <th style="text-align:right;">QTY</th><th style="text-align:right;">S Price</th>
                            <th style="text-align:right;">Pack</th>
                            <th style="text-align:right;" title="Inferred as S Price × Pack — not a defined concept in the schema, flag if this should mean something else">Pack Rate</th>
                            <th>Location</th>
                        </tr></thead>
                        <tbody id="results-body"><tr><td colspan="11" style="text-align:center;color:#888;padding:10px;">Loading…</td></tr></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Calculator / totals -->
    <div class="win-panel" style="padding:6px 10px;display:flex;align-items:center;gap:20px;flex-wrap:wrap;">
        <div style="display:flex;align-items:center;gap:4px;"><label style="font-weight:bold;">Total:</label><input id="gross-total" type="text" readonly value="0.00" style="width:100px;font-weight:bold;" tabindex="-1"></div>
        <div style="display:flex;align-items:center;gap:4px;"><label style="font-weight:bold;">Disc %:</label><input id="disc-pct" type="number" min="0" max="100" value="0" style="width:55px;" oninput="recalcTotals()"></div>
        <div style="display:flex;align-items:center;gap:4px;"><label style="font-weight:bold;">Disc Amt:</label><input id="disc-amt" type="text" readonly value="0.00" style="width:85px;" tabindex="-1"></div>
        <div style="display:flex;align-items:center;gap:4px;"><label style="font-weight:bold;">Net Total:</label><input id="net-total" type="text" readonly value="0.00" style="width:110px;font-weight:bold;" tabindex="-1"></div>
        <div style="display:flex;align-items:center;gap:4px;"><label style="font-weight:bold;">Cash:</label><input id="cash-paid" type="number" min="0" value="0" style="width:90px;font-weight:bold;" oninput="recalcBalance()"></div>
        <div style="display:flex;align-items:center;gap:4px;"><label style="font-weight:bold;">Balance:</label><input id="balance-amt" type="text" readonly value="0.00" style="width:110px;font-weight:bold;" tabindex="-1"></div>
    </div>

</div>

<div class="win-statusbar">
    <span id="status-msg">Ready</span>
    <span>AISellProduct v1.0</span>
</div>

<!-- Edit popup — search + pick a saved bill to load back into the form -->
<div id="edit-popup-overlay" class="popup-overlay">
    <div class="popup-box" id="edit-popup-box" style="width:560px;">
        <div class="popup-titlebar" id="edit-popup-titlebar">
            <span>&#x270E; Edit — pick a bill to load</span>
            <span class="close-x" onclick="closeEditPopup()">&#x2716;</span>
        </div>
        <div class="popup-body">
            <input id="edit-search-input" type="text" placeholder="Search by Bill#, customer, date…" style="width:100%;margin-bottom:6px;" oninput="filterEditList(this.value)">
            <table class="win-table">
                <thead><tr><th>Bill#</th><th>Date</th><th>Customer</th><th style="text-align:right;">Net</th></tr></thead>
                <tbody id="edit-list-body"><tr><td colspan="4" style="text-align:center;padding:10px;color:#888;">Loading…</td></tr></tbody>
            </table>
        </div>
        <div class="popup-footer"><button class="win-btn" onclick="closeEditPopup()" style="color:darkred;">Close</button></div>
    </div>
</div>

<!-- Held bills popup — same pattern as pos.php -->
<div id="held-popup-overlay" class="popup-overlay">
    <div class="popup-box" id="held-popup-box" style="width:520px;">
        <div class="popup-titlebar" id="held-popup-titlebar">
            <span>&#x23F8; Held Bills — click a row to resume</span>
            <span class="close-x" onclick="closeHeldPopup()">&#x2716;</span>
        </div>
        <div class="popup-body">
            <table class="win-table">
                <thead><tr><th>Held At</th><th>Customer</th><th style="text-align:right;">Items</th><th style="text-align:right;">Amount</th><th></th></tr></thead>
                <tbody id="held-bills-body"><tr><td colspan="5" style="text-align:center;padding:10px;color:#888;">No held bills</td></tr></tbody>
            </table>
        </div>
        <div class="popup-footer"><button class="win-btn" onclick="closeHeldPopup()" style="color:darkred;">Close</button></div>
    </div>
</div>

<div id="print-area"></div>
<div id="toast"></div>

<script>
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

function clockTick() {
    document.getElementById('live-clock').textContent = new Date().toLocaleString('en-GB');
    document.getElementById('bill-datetime').value = new Date().toLocaleString('en-GB', { hour12: true });
}
clockTick();
setInterval(clockTick, 1000);

let cart = [];
let selectedItem = null;
let allCustomers = [];
let selectedCustomerId = null;
let allEmployees = [];
let selectedSalePersonId = null;
let editingTransNo = null; // set while an existing bill is loaded via Edit
let editingIsCancelled = false; // true if the bill loaded via Edit is voided
let heldBills = [];

function loadCustomers() {
    fetch('api/get_customers.php').then(r=>r.json()).then(rows => {
        allCustomers = rows;
        const dl = document.getElementById('cust-datalist');
        dl.innerHTML = '';
        rows.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.Customer_id + ' - ' + c.Cust_name;
            dl.appendChild(opt);
        });
    });
}
function onClientInput() {
    const val = document.getElementById('cust-name').value;
    const match = val.match(/^(\d+)\s*-\s*/);
    const cust = match ? allCustomers.find(c => c.Customer_id === parseInt(match[1])) : null;
    selectedCustomerId = cust ? cust.Customer_id : null;
}

function loadEmployees() {
    fetch('api/get_employees.php').then(r=>r.json()).then(rows => {
        allEmployees = rows;
        const dl = document.getElementById('employee-datalist');
        dl.innerHTML = '';
        rows.forEach(e => {
            const opt = document.createElement('option');
            opt.value = e.Full_Name;
            dl.appendChild(opt);
        });
    });
}

let myEmployee = null;
function loadMyEmployee() {
    fetch('api/get_my_employee.php').then(r=>r.json()).then(emp => {
        myEmployee = emp;
        applyDefaultSalePerson();
    });
}
function applyDefaultSalePerson() {
    if (!myEmployee || editingTransNo !== null) return;
    if (document.getElementById('sale-person').value.trim() !== '') return;
    document.getElementById('sale-person').value = myEmployee.Full_Name;
    selectedSalePersonId = myEmployee.Emp_no;
}
function onSalePersonInput() {
    const val = document.getElementById('sale-person').value.trim();
    const emp = allEmployees.find(e => e.Full_Name === val);
    selectedSalePersonId = emp ? emp.Emp_no : null;
}

let allResults = [];
let searchTimer = null;
function searchItems(q) {
    clearTimeout(searchTimer);
    if (!q.trim()) { renderResults(allResults); return; }
    searchTimer = setTimeout(() => {
        fetch('api/search_items_enriched.php?q=' + encodeURIComponent(q)).then(r=>r.json()).then(renderResults);
    }, 250);
}
function loadAllItems() {
    fetch('api/search_items_enriched.php?q=').then(r=>r.json()).then(data => { allResults = data; renderResults(data); });
}
function renderResults(rows) {
    const tbody = document.getElementById('results-body');
    document.getElementById('result-count').textContent = rows.length + ' item(s)';
    tbody.innerHTML = '';
    if (!rows.length) { tbody.innerHTML = '<tr><td colspan="11" style="text-align:center;color:#888;padding:10px;">No items found</td></tr>'; return; }
    rows.forEach(item => {
        const pack = parseInt(item.UNITS_PERITEM) || 0;
        const price = parseFloat(item.PRICE) || 0;
        const packRate = pack * price;
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${item.BARCODE || '—'}</td>
            <td style="font-weight:bold;">${(item.BRAND_NAME||'') + ' ' + (item.ITEM_NAME||'')}</td>
            <td>${item.ITEM_NAME || '—'}</td>
            <td>${item.ITEM_TYPE || '—'}</td>
            <td>${item.COMPANY_NAME || '—'}</td>
            <td>${item.MANUFACTURE_NAME || '—'}</td>
            <td style="text-align:right;">${item.QTY_INHAND ?? 0}</td>
            <td style="text-align:right;">${price.toFixed(2)}</td>
            <td style="text-align:right;">${pack || '—'}</td>
            <td style="text-align:right;">${packRate ? packRate.toFixed(2) : '—'}</td>
            <td>${item.LOCATION || '—'}</td>`;
        tr.onclick = () => {
            document.querySelectorAll('#results-body tr').forEach(r => r.classList.remove('row-selected'));
            tr.classList.add('row-selected');
            selectItem(item);
        };
        tbody.appendChild(tr);
    });
}
function selectItem(item) {
    selectedItem = item;
    document.getElementById('sel-stock').value = item.STOCK_NUMBER;
    document.getElementById('sel-price').value = parseFloat(item.PRICE||0).toFixed(2);
    document.getElementById('sel-qty').value = 1;
    recalcLine();
    setStatus('Selected: ' + (item.BRAND_NAME||'') + ' ' + (item.ITEM_NAME||''));
}
function recalcLine() {
    const qty = parseFloat(document.getElementById('sel-qty').value) || 0;
    const price = parseFloat(document.getElementById('sel-price').value) || 0;
    document.getElementById('sel-amount').value = (qty*price).toFixed(2);
}
function addItemToCart() {
    if (!selectedItem) { toast('Select an item first', 'warn'); return; }
    const qty = parseInt(document.getElementById('sel-qty').value) || 0;
    const price = parseFloat(document.getElementById('sel-price').value) || 0;
    if (qty <= 0) { toast('Quantity must be > 0', 'warn'); return; }
    if (price <= 0) { toast('Price must be > 0', 'warn'); return; }
    const existing = cart.find(c => c.stock_number === selectedItem.STOCK_NUMBER);
    const alreadyInCart = existing ? existing.quantity : 0;
    const inHand = parseInt(selectedItem.QTY_INHAND) || 0;
    if (alreadyInCart + qty > inHand) {
        toast('Only ' + inHand + ' in hand' + (alreadyInCart ? ' (' + alreadyInCart + ' already in this bill)' : ''), 'warn');
        return;
    }
    if (existing) { existing.quantity += qty; existing.amount = existing.quantity * existing.price; }
    else cart.push({ stock_number: selectedItem.STOCK_NUMBER, name: (selectedItem.BRAND_NAME||'')+' '+(selectedItem.ITEM_NAME||''), quantity: qty, price: price, amount: qty*price });
    renderCart(); recalcTotals();
    selectedItem = null;
    document.getElementById('item-search').value = '';
    document.getElementById('sel-stock').value = '';
    document.getElementById('sel-price').value = '';
    document.getElementById('sel-qty').value = 1;
    document.getElementById('sel-amount').value = '';
    document.querySelectorAll('#results-body tr').forEach(r => r.classList.remove('row-selected'));
    setStatus('Item added to bill');
}
function removeFromCart(i) { cart.splice(i,1); renderCart(); recalcTotals(); }
function renderCart() {
    const tbody = document.getElementById('cart-body');
    document.getElementById('cart-count').textContent = cart.length + ' item(s)';
    tbody.innerHTML = '';
    if (!cart.length) { tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#888;padding:14px;">No items added yet</td></tr>'; return; }
    cart.forEach((c,i) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td>${i+1}</td><td style="font-weight:bold;color:#0a246a;">${c.stock_number}</td><td>${c.name}</td>
            <td style="text-align:right;font-weight:bold;">${c.quantity}</td><td style="text-align:right;">${c.price.toFixed(2)}</td>
            <td style="text-align:right;font-weight:bold;">${c.amount.toFixed(2)}</td>
            <td style="text-align:center;"><button class="win-btn" onclick="removeFromCart(${i})" style="height:18px;font-size:11px;padding:0 6px;color:darkred;">X</button></td>`;
        tbody.appendChild(tr);
    });
}
function recalcTotals() {
    const gross = cart.reduce((s,c)=>s+c.amount,0);
    const discPct = parseFloat(document.getElementById('disc-pct').value) || 0;
    const discAmt = gross * discPct / 100;
    const net = gross - discAmt;
    document.getElementById('gross-total').value = gross.toFixed(2);
    document.getElementById('disc-amt').value = discAmt.toFixed(2);
    document.getElementById('net-total').value = net.toFixed(2);
    document.getElementById('cart-item-badge').textContent = cart.length;
    recalcBalance();
}
function recalcBalance() {
    const net = parseFloat(document.getElementById('net-total').value) || 0;
    const cash = parseFloat(document.getElementById('cash-paid').value) || 0;
    const bal = cash - net;
    document.getElementById('balance-amt').value = bal.toFixed(2);
    document.getElementById('balance-amt').style.color = bal < 0 ? 'darkred' : 'darkgreen';
}

function buildPayload() {
    const gross = cart.reduce((s,c)=>s+c.amount,0);
    const discPct = parseFloat(document.getElementById('disc-pct').value) || 0;
    const net = gross - (gross*discPct/100);
    const paid = parseFloat(document.getElementById('cash-paid').value) || 0;
    return {
        trans_no: editingTransNo,
        cust_name: document.getElementById('cust-name').value,
        cust_telno: '',
        customer_id: selectedCustomerId,
        invoice_reference: '',
        trans_type: document.getElementById('trans-type').value,
        disc_percentage: discPct, gross_amount: gross, trans_amount: net,
        paid_amount: paid, balance_amount: paid-net,
        user_id: '<?php echo htmlspecialchars($_SESSION['emp_user_id'] ?? 'admin'); ?>',
        tax_status: 'N',
        sale_person_id: selectedSalePersonId,
        description: document.getElementById('bill-description').value,
        remarks: document.getElementById('bill-remarks').value,
        items: cart.map(c => ({ stock_number: c.stock_number, quantity: c.quantity, price: c.price, amount: c.amount }))
    };
}

function performSave() {
    if (editingIsCancelled) { toast('This bill is cancelled — it cannot be saved/edited', 'warn'); return Promise.resolve(null); }
    if (!cart.length) { toast('Add items to the bill first', 'warn'); return Promise.resolve(null); }
    const payload = buildPayload();
    const transType = document.getElementById('trans-type').value;
    if (transType !== 'Credit' && (payload.paid_amount - payload.trans_amount) < 0) {
        toast('Cash/Card sales cannot be saved with a negative balance — collect full payment or switch Type to Credit', 'warn');
        return Promise.resolve(null);
    }
    setStatus('Saving…');
    const endpoint = editingTransNo ? 'api/update_transaction.php' : 'api/save_transaction.php';
    return fetch(endpoint, { method:'POST', body: JSON.stringify(payload) }).then(r=>r.json());
}

function saveBill() {
    performSave().then(res => {
        if (!res) return;
        if (res.success) {
            editingTransNo = res.trans_no;
            document.getElementById('bill-no').value = res.trans_no;
            if (res.skipped_reversals && res.skipped_reversals.length) {
                toast('Saved — but stock for item(s) ' + res.skipped_reversals.join(', ') + " couldn't be fully reversed (no batch left to restore into)", 'warn');
            } else {
                toast('Bill #' + res.trans_no + ' saved', 'ok');
            }
            setStatus('Saved — Bill #' + res.trans_no);
            loadAllItems();
        } else {
            toast('Error: ' + (res.error||'Unknown'), 'err');
            setStatus('Save failed');
        }
    });
}

// Voids the currently loaded bill (must be loaded via Edit first) and
// reverses its stock effect server-side (same strategy as editing). Does
// not delete the bill — it stays visible in Cancelled Bills for audit.
function cancelCurrentBill() {
    if (!editingTransNo) { toast('Load a bill via Edit first, then click Cancellation', 'warn'); return; }
    if (editingIsCancelled) { toast('Bill #' + editingTransNo + ' is already cancelled', 'warn'); return; }
    if (!confirm('Cancel Bill #' + editingTransNo + '? This reverses its stock and cannot be undone.')) return;
    const reason = prompt('Reason for cancelling (optional):', '') || '';
    setStatus('Cancelling…');
    fetch('api/cancel_transaction.php', {
        method: 'POST',
        body: JSON.stringify({ trans_no: editingTransNo, reason: reason, user_id: '<?php echo htmlspecialchars($_SESSION['emp_user_id'] ?? 'admin'); ?>' })
    }).then(r=>r.json()).then(res => {
        if (!res.success) { toast('Error: ' + (res.error||'Unknown'), 'err'); setStatus('Cancel failed'); return; }
        if (res.skipped_reversals && res.skipped_reversals.length) {
            toast('Bill #' + res.trans_no + ' cancelled — but stock for item(s) ' + res.skipped_reversals.join(', ') + " couldn't be fully reversed (no batch left to restore into)", 'warn');
        } else {
            toast('Bill #' + res.trans_no + ' cancelled — stock reversed', 'ok');
        }
        setStatus('Bill #' + res.trans_no + ' cancelled');
        clearSaleForm();
    });
}

function printBill() {
    performSave().then(res => {
        if (!res) return;
        if (!res.success) { toast('Error: ' + (res.error||'Unknown'), 'err'); return; }
        editingTransNo = res.trans_no;
        document.getElementById('bill-no').value = res.trans_no;
        toast('Bill #' + res.trans_no + ' saved — printing', 'ok');
        loadAllItems();
        const w = window.open('', '_blank', 'width=380,height=600');
        const custName = document.getElementById('cust-name').value || 'Walk-in';
        let rows = cart.map(c => `${c.name} x${c.quantity} @ ${c.price.toFixed(2)} = ${c.amount.toFixed(2)}`).join('\n');
        w.document.write(`<pre style="font-family:'Courier New',monospace;font-size:12px;padding:10px;">Bill #${res.trans_no}\nCustomer: ${custName}\n${'-'.repeat(30)}\n${rows}\n${'-'.repeat(30)}\nNet Total: ${document.getElementById('net-total').value}\nCash: ${document.getElementById('cash-paid').value}\nBalance: ${document.getElementById('balance-amt').value}\n</pre>`);
        w.document.close();
        setTimeout(() => { w.print(); }, 300);
    });
}

function persistHeldBills() {
    const key = 'sale_items_held_' + '<?php echo htmlspecialchars($_SESSION['active_db'] ?? 'default'); ?>';
    localStorage.setItem(key, JSON.stringify(heldBills));
    document.getElementById('held-count').textContent = heldBills.length;
}
function loadHeldBills() {
    const key = 'sale_items_held_' + '<?php echo htmlspecialchars($_SESSION['active_db'] ?? 'default'); ?>';
    heldBills = JSON.parse(localStorage.getItem(key) || '[]');
    document.getElementById('held-count').textContent = heldBills.length;
}
function holdBill() {
    if (!cart.length) { toast('Add items to the bill first', 'warn'); return; }
    heldBills.push({ heldAt: new Date().toLocaleString('en-GB'), cart, custName: document.getElementById('cust-name').value,
        selectedCustomerId, transType: document.getElementById('trans-type').value,
        discPct: document.getElementById('disc-pct').value, cashPaid: document.getElementById('cash-paid').value });
    persistHeldBills();
    clearSaleForm();
    toast('Bill held — resume from Held popup', 'ok');
}
function openHeldPopup() { renderHeldList(); document.getElementById('held-popup-overlay').classList.add('open'); }
function closeHeldPopup() { document.getElementById('held-popup-overlay').classList.remove('open'); }
function renderHeldList() {
    const tbody = document.getElementById('held-bills-body');
    tbody.innerHTML = '';
    if (!heldBills.length) { tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:10px;color:#888;">No held bills</td></tr>'; return; }
    heldBills.forEach((h,i) => {
        const amount = h.cart.reduce((s,c)=>s+c.amount,0);
        const tr = document.createElement('tr');
        tr.innerHTML = `<td>${h.heldAt}</td><td>${h.custName||'Walk-in'}</td><td style="text-align:right;">${h.cart.length}</td>
            <td style="text-align:right;font-weight:bold;">${amount.toFixed(2)}</td>
            <td style="text-align:center;"><button class="win-btn win-btn-red" onclick="deleteHeld(${i},event)" style="height:18px;font-size:11px;">X</button></td>`;
        tr.onclick = (e) => { if (e.target.tagName==='BUTTON') return; recallHeld(i); };
        tbody.appendChild(tr);
    });
}
function recallHeld(i) {
    const h = heldBills[i];
    cart = h.cart;
    document.getElementById('cust-name').value = h.custName || '';
    selectedCustomerId = h.selectedCustomerId || null;
    document.getElementById('trans-type').value = h.transType || 'Cash';
    document.getElementById('disc-pct').value = h.discPct || 0;
    document.getElementById('cash-paid').value = h.cashPaid || 0;
    renderCart(); recalcTotals();
    heldBills.splice(i,1); persistHeldBills();
    closeHeldPopup();
    toast('Held bill resumed', 'ok');
}
function deleteHeld(i,e) { e.stopPropagation(); heldBills.splice(i,1); persistHeldBills(); renderHeldList(); }

function clearSaleForm() {
    cart = []; selectedItem = null; editingTransNo = null; editingIsCancelled = false;
    document.getElementById('cust-name').value = '';
    selectedCustomerId = null;
    document.getElementById('sale-person').value = '';
    selectedSalePersonId = null;
    document.getElementById('bill-description').value = '';
    document.getElementById('bill-remarks').value = '';
    document.getElementById('disc-pct').value = '0';
    document.getElementById('cash-paid').value = '0';
    document.getElementById('bill-no').value = '—';
    document.getElementById('item-search').value = '';
    document.getElementById('sel-stock').value = '';
    document.getElementById('sel-price').value = '';
    document.getElementById('sel-amount').value = '';
    renderCart(); recalcTotals();
    loadAllItems();
    applyDefaultSalePerson();
    setStatus('Ready');
}

// --- Edit: load a saved bill back into the form for correction ---
let editListCache = [];
function openEditPopup() {
    document.getElementById('edit-popup-overlay').classList.add('open');
    document.getElementById('edit-list-body').innerHTML = '<tr><td colspan="4" style="text-align:center;padding:10px;color:#888;">Loading…</td></tr>';
    fetch('api/get_transactions.php').then(r=>r.json()).then(rows => {
        editListCache = rows;
        renderEditList(rows);
    });
}
function closeEditPopup() { document.getElementById('edit-popup-overlay').classList.remove('open'); }
function renderEditList(rows) {
    const tbody = document.getElementById('edit-list-body');
    tbody.innerHTML = '';
    if (!rows.length) { tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;padding:10px;color:#888;">No bills found</td></tr>'; return; }
    rows.slice(0,200).forEach(row => {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td style="font-weight:bold;color:#0a246a;">${row.Trans_no}</td><td>${row.Trans_date}</td><td>${row.Cust_name||'Walk-in'}</td><td style="text-align:right;">${parseFloat(row.Trans_amount||0).toFixed(2)}</td>`;
        tr.onclick = () => loadBillForEdit(row.Trans_no);
        tbody.appendChild(tr);
    });
}
function filterEditList(q) {
    q = q.trim().toLowerCase();
    if (!q) { renderEditList(editListCache); return; }
    renderEditList(editListCache.filter(r => String(r.Trans_no).includes(q) || (r.Cust_name||'').toLowerCase().includes(q) || (r.Trans_date||'').includes(q)));
}
function loadBillForEdit(transNo) {
    fetch('api/get_transaction_detail.php?id=' + transNo).then(r=>r.json()).then(res => {
        if (!res.header) { toast('Could not load that bill', 'err'); return; }
        editingTransNo = res.header.Trans_no;
        editingIsCancelled = !!res.header.Is_Cancelled;
        document.getElementById('bill-no').value = res.header.Trans_no;
        document.getElementById('cust-name').value = res.header.Cust_name || '';
        selectedCustomerId = null;
        document.getElementById('sale-person').value = res.header.Sale_Person_Name || '';
        selectedSalePersonId = res.header.Sale_Person_id || null;
        document.getElementById('bill-description').value = res.header.Description || '';
        document.getElementById('bill-remarks').value = res.header.Remarks || '';
        document.getElementById('trans-type').value = res.header.Trans_type || 'Cash';
        document.getElementById('disc-pct').value = res.header.Disc_percentage || 0;
        document.getElementById('cash-paid').value = res.header.Paid_amount || 0;
        cart = res.detail.map(d => ({ stock_number: d.stock_number, name: (d.BRAND_NAME||'')+' '+(d.ITEM_NAME||''), quantity: d.quantity, price: parseFloat(d.Price_PerItem||0), amount: parseFloat(d.amount||0) }));
        renderCart(); recalcTotals();
        closeEditPopup();
        if (editingIsCancelled) {
            toast('Bill #' + transNo + ' is CANCELLED (' + (res.header.Cancel_Reason || 'no reason given') + ') — view only, Save is blocked', 'warn');
            setStatus('Viewing cancelled Bill #' + transNo);
        } else {
            toast('Bill #' + transNo + ' loaded — edit items/amounts, then Save to update it', 'ok');
            setStatus('Editing Bill #' + transNo);
        }
    });
}

function makeDraggable(boxId, barId) {
    const box = document.getElementById(boxId), bar = document.getElementById(barId);
    let dragging=false, ox=0, oy=0;
    bar.addEventListener('mousedown', e => { dragging=true; ox=e.clientX-box.offsetLeft; oy=e.clientY-box.offsetTop; });
    document.addEventListener('mousemove', e => {
        if (!dragging) return;
        box.style.position='fixed'; box.style.left=(e.clientX-ox)+'px'; box.style.top=(e.clientY-oy)+'px'; box.style.margin='0';
    });
    document.addEventListener('mouseup', () => dragging=false);
}
makeDraggable('edit-popup-box','edit-popup-titlebar');
makeDraggable('held-popup-box','held-popup-titlebar');
document.getElementById('edit-popup-overlay').addEventListener('click', e => { if (e.target === e.currentTarget) closeEditPopup(); });
document.getElementById('held-popup-overlay').addEventListener('click', e => { if (e.target === e.currentTarget) closeHeldPopup(); });

loadCustomers();
loadEmployees();
loadMyEmployee();
loadAllItems();
loadHeldBills();
</script>
</body>
</html>
