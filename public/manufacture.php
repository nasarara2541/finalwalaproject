<?php
require_once __DIR__ . '/includes/access.php';
requireAccess('inventory');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AISellProduct - Manufacture</title>
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

input[type=text], input[type=number] {
    border: 1px solid; border-color: #808080 #ffffff #ffffff #808080;
    background: #ffff99; padding: 2px 4px; height: 22px; font-size:12px;
    font-family: Tahoma, sans-serif; width:100%;
}
input.field-white { background:#fff !important; }
input[readonly] { background: #d4d0c8 !important; color:#333; }
input:focus { outline: 2px solid #0a246a; }

.win-btn {
    background: #d4d0c8; border: 1px solid; border-color: #ffffff #808080 #808080 #ffffff;
    padding: 2px 10px; cursor:pointer; font-size:12px; height:23px;
    font-family: Tahoma, sans-serif; white-space:nowrap; display:inline-flex; align-items:center; gap:3px;
}
.win-btn:hover  { background: #e8e4d8; }
.win-btn-green { background:#1a7a1a; color:white; border-color:#44aa44 #0a500a #0a500a #44aa44; }
.win-btn-green:hover { background:#1e8c1e; }
.win-btn-red   { background:#8b0000; color:white; border-color:#cc4444 #550000 #550000 #cc4444; }
.win-btn-red:hover { background:#a00000; }

.win-table { width:100%; border-collapse:collapse; font-size:11px; }
.win-table thead th { border:1px solid #808080; padding:3px 5px; text-align:left; font-weight:bold; background:#d4d0c8; white-space:nowrap; position:sticky; top:0; z-index:1; }
.win-table tbody tr { background:#fff; }
.win-table tbody tr:nth-child(even) { background:#f5f3ee; }
.win-table tbody tr:hover { background:#c5d5e8 !important; cursor:pointer; }
.win-table tbody tr.row-selected { background:#0a246a !important; color:white; }
.win-table td { border:1px solid #d0ccc4; padding:3px 5px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:0; }

.win-section-label { background:#d4d0c8; font-weight:bold; padding:3px 8px; border-bottom:1px solid #808080; display:flex; align-items:center; justify-content:space-between; }
.win-statusbar { background:#d4d0c8; border-top:1px solid #808080; padding:3px 8px; display:flex; gap:14px; }
.win-statusbar span { border-right:1px solid #808080; padding-right:12px; }

label.lbl { font-weight:bold; white-space:nowrap; width:100px; flex-shrink:0; }
.field-row { display:flex; align-items:center; gap:6px; margin-bottom:3px; }
.legend-text { color:#333; font-size:11px; white-space:nowrap; }
.required-star { color:darkred; }

.mfg-list-row { padding:2px 6px; cursor:pointer; white-space:nowrap; }
.mfg-list-row:hover { background:#c5d5e8; }
.mfg-list-row.row-selected { background:#0a246a; color:#fff; }

#toast { position:fixed; bottom:16px; right:16px; z-index:9999; padding:8px 16px; font-weight:bold; font-size:12px; border:1px solid; display:none; }
</style>
</head>
<body class="flex flex-col h-screen">

<div class="win-titlebar">
    <span>&#x1F4A7; AISellProduct — Manufacture</span>
    <span id="live-clock" style="font-weight:normal;font-size:11px;"></span>
</div>

<div class="win-menubar">
    <span class="win-menu-item" onclick="window.location='pos.php'">&#x2190; Back to Sale</span>
    <span class="win-menu-item nav-active">Manufacture</span>
    <?php if (!empty($_SESSION['emp_is_admin'])): ?>
    <span class="win-menu-item" style="color:#5b3a8a;font-weight:bold;" onclick="window.location='admin_users.php'" title="Admin only">&#x1F464; Manage Users</span>
    <span class="win-menu-item" style="color:#5b3a8a;font-weight:bold;" onclick="window.location='admin_dashboard.php'" title="Admin only">&#x1F4CA; Dashboard</span>
    <span class="win-menu-item" style="color:#5b3a8a;font-weight:bold;" onclick="window.location='reports/admin_reports.php'" title="Admin only">&#x1F4C8; Profit Reports</span>
    <span class="win-menu-item" style="color:#5b3a8a;font-weight:bold;" onclick="window.location='item_details.php'" title="Admin only">&#x1F4E6; Item Details</span>
    <?php endif; ?>
    <span style="flex:1"></span>
    <span class="win-menu-item" style="color:#555;">Database: <b><?php echo htmlspecialchars($_SESSION['active_db_label'] ?? 'Water Distribution'); ?></b></span>
    <span class="win-menu-item" onclick="window.location='login.php'" title="Pick a different database">&#x1F504; Switch Database</span>
    <span class="win-menu-item" style="color:#555;">User: <b><?php echo htmlspecialchars($_SESSION['emp_user_name'] ?? '—'); ?></b></span>
    <span class="win-menu-item" onclick="window.location='logout.php'" title="Sign out" style="color:darkred;">&#x1F6AA; Logout</span>
</div>

<div style="display:flex;flex-direction:column;flex:1;padding:5px;gap:4px;min-height:0;">

    <div style="display:flex;gap:4px;align-items:stretch;">

        <div class="win-panel" style="padding:6px;flex:1.4;">
            <div class="win-section-label" style="margin:-6px -6px 6px -6px;">
                <span>Item Details</span>
                <span id="form-mode" style="font-weight:normal;font-size:11px;color:#555;">New Record</span>
            </div>

            <div class="field-row">
                <label class="lbl">Stock Number</label>
                <input id="item-stock-number" type="text" class="field-white" placeholder="Type # + Find, or click New" style="flex:1;">
                <button class="win-btn" style="height:20px;padding:0 8px;" onclick="findStockItem()">Find</button>
            </div>

            <div class="field-row">
                <label class="lbl">Brand Name <span class="required-star">*</span></label>
                <input id="item-brand-name" type="text" placeholder="e.g. Margalla Pure Life" style="flex:1;">
            </div>

            <div class="field-row">
                <label class="lbl">Item Name</label>
                <input id="item-item-name" type="text" placeholder="e.g. 19L Jar" style="flex:1;">
            </div>

            <div class="field-row">
                <label class="lbl">Item Type</label>
                <input id="item-item-type" type="text" placeholder="e.g. Jar / Bottle / Can" style="flex:1;">
            </div>

            <div class="field-row">
                <label class="lbl">Stock Type</label>
                <input id="item-stock-type" type="text" value="01" readonly tabindex="-1" style="width:40px;flex:none;cursor:pointer;text-align:center;" onclick="toggleStockType()">
                <span class="legend-text">01: Water, 02: Non-Water (click to toggle)</span>
            </div>

            <div class="field-row">
                <label class="lbl">Volume / ML</label>
                <input id="item-volume-ml" type="text" placeholder="e.g. 500ML" style="width:80px;flex:none;">
                <label class="lbl" style="width:auto;margin-left:10px;">Units Per Item <span class="required-star">*</span></label>
                <input id="item-units-peritem" type="number" min="1" value="1" style="width:55px;flex:none;">
            </div>

            <div class="field-row">
                <label class="lbl">Barcode</label>
                <input id="item-barcode" type="text" placeholder="Optional barcode" style="flex:1;">
            </div>

            <div class="field-row">
                <label class="lbl">Size Description</label>
                <input id="item-size-desc" type="text" placeholder="e.g. 19L" style="flex:1;">
            </div>

            <div class="field-row">
                <label class="lbl">Available</label>
                <input id="item-available-status" type="text" value="Active" readonly tabindex="-1" style="width:65px;flex:none;cursor:pointer;text-align:center;" onclick="toggleAvailableStatus()">
                <span class="legend-text">click to toggle</span>
                <label class="lbl" style="width:auto;margin-left:10px;">Unit Type</label>
                <input id="item-unit-type" type="text" placeholder="e.g. Bottle" style="flex:1;">
            </div>
            <div class="field-row" style="margin-top:-4px;">
                <span class="legend-text" style="margin-left:106px;">B: Bottle, C: Carton, J: Jar</span>
            </div>

            <div style="border-top:1px solid #808080;margin-top:2px;padding-top:4px;">
                <div class="field-row" style="margin-bottom:0;flex-wrap:wrap;">
                    <label class="lbl" style="width:auto;">Search In:</label>
                    <label style="display:flex;align-items:center;gap:2px;font-weight:normal;"><input type="radio" name="search-scope" value="brand" style="width:auto;height:auto;"> Brand</label>
                    <label style="display:flex;align-items:center;gap:2px;font-weight:normal;"><input type="radio" name="search-scope" value="item" style="width:auto;height:auto;"> Item</label>
                    <label style="display:flex;align-items:center;gap:2px;font-weight:normal;"><input type="radio" name="search-scope" value="both" checked style="width:auto;height:auto;"> Both</label>
                    <input id="item-filter-text" type="text" placeholder="Type to filter list below…" style="flex:1;min-width:100px;" oninput="filterStockItems()">
                </div>
            </div>
        </div>

        <div class="win-panel" style="padding:6px;flex:1;display:flex;flex-direction:column;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                <label class="lbl" style="color:darkred;width:auto;">Manufacturer <span class="required-star">*</span></label>
                <button class="win-btn" style="height:20px;padding:0 8px;" onclick="window.location='pos.php'">Exit</button>
            </div>
            <input id="mfg-search" type="text" class="field-white" placeholder="Enter Name for search" oninput="filterManufacturers(this.value)" style="margin-bottom:4px;">
            <div class="win-inset" id="mfg-listbox" style="height:118px;overflow-y:auto;margin-bottom:4px;"></div>
            <div class="field-row">
                <input id="mfg-selected-no" type="text" readonly value="" tabindex="-1" style="width:40px;flex:none;text-align:center;">
                <span id="mfg-selected-name" class="legend-text" style="font-weight:bold;">No manufacturer selected</span>
            </div>

            <div class="field-row">
                <label class="lbl">Location:</label>
                <input id="item-location" type="text" placeholder="e.g. Shelf A / Warehouse 1" style="flex:1;">
            </div>

            <div style="display:flex;gap:5px;flex-wrap:wrap;margin-top:2px;">
                <button class="win-btn" onclick="newStockItem()">New</button>
                <button class="win-btn win-btn-green" onclick="saveStockItem()">Save</button>
                <button class="win-btn" onclick="window.open('manufacture_list.php','_blank','width=700,height=650')">Manufacture List</button>
                <button class="win-btn win-btn-red" onclick="removeStockItem()">Remove</button>
            </div>
        </div>

    </div>

    <div class="win-panel" style="flex:1;display:flex;flex-direction:column;padding:8px;min-height:0;">
        <div class="win-section-label" style="margin:-8px -8px 8px -8px;">
            <span>Item Stock List</span>
            <button class="win-btn" style="height:18px;font-size:11px;padding:0 8px;" onclick="loadStockItems()">Refresh</button>
        </div>
        <div style="flex:1;overflow:auto;min-height:0;">
            <table class="win-table">
                <thead>
                    <tr>
                        <th>Stock#</th><th>Brand Name</th><th>Item Name</th><th>Item Type</th>
                        <th>Stock Type</th><th>Volume/ML</th><th>Barcode</th><th>Size Desc</th>
                        <th>Available</th><th>Manufacturer</th><th>Mfg#</th>
                    </tr>
                </thead>
                <tbody id="item-grid-body"><tr><td colspan="11" style="text-align:center;padding:8px;color:#888;">Loading…</td></tr></tbody>
            </table>
        </div>
    </div>

</div>

<div class="win-statusbar">
    <span id="status-msg">Ready</span>
    <span id="item-count"></span>
</div>

<div id="toast"></div>

<script>
let allManufacturers = [];
let mfgCurrentList = [];
let selectedManufactureNo = null;

let allItems = [];
let itemCurrentList = [];
let selectedStockNumber = null;

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

function setStatus(msg) { document.getElementById('status-msg').textContent = msg; }

/* ---------- Manufacturer picker ---------- */

function loadManufacturers() {
    return fetch('api/get_manufacturers.php')
        .then(r => r.json())
        .then(rows => {
            if (rows && rows.error) { toast('Error: ' + rows.error, 'err'); return; }
            allManufacturers = rows;
            mfgCurrentList = rows;
            renderMfgList();
        })
        .catch(() => {
            toast('Network error loading manufacturers', 'err');
            document.getElementById('mfg-listbox').innerHTML =
                '<div style="padding:6px;color:darkred;">Could not load — check DB connection</div>';
        });
}

window.refreshManufacturersFromChild = function() { loadManufacturers(); };

function renderMfgList() {
    const box = document.getElementById('mfg-listbox');
    box.innerHTML = '';
    if (!mfgCurrentList.length) {
        box.innerHTML = '<div style="padding:6px;color:#888;">No manufacturers found</div>';
        return;
    }
    mfgCurrentList.forEach(m => {
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
    mfgCurrentList = !q ? allManufacturers : allManufacturers.filter(m =>
        (m.M_Name || '').toLowerCase().includes(q) || (m.M_ShortName || '').toLowerCase().includes(q)
    );
    renderMfgList();
}

function selectManufacturer(m) {
    selectedManufactureNo = m.Manufacture_no;
    document.getElementById('mfg-selected-no').value = m.Manufacture_no;
    document.getElementById('mfg-selected-name').textContent = m.M_Name;
    renderMfgList();
}

function setManufacturerSelection(no, name) {
    selectedManufactureNo = no || null;
    document.getElementById('mfg-selected-no').value = no || '';
    document.getElementById('mfg-selected-name').textContent = no ? (name || '') : 'No manufacturer selected';
    renderMfgList();
}

/* ---------- Item Stock Type toggle ---------- */

function toggleStockType() {
    const el = document.getElementById('item-stock-type');
    el.value = el.value === '01' ? '02' : '01';
}

function toggleAvailableStatus() {
    const el = document.getElementById('item-available-status');
    el.value = el.value === 'Active' ? 'Inactive' : 'Active';
}

/* ---------- Item grid ---------- */

function loadStockItems() {
    setStatus('Loading items…');
    fetch('api/get_item_stock_list.php')
        .then(r => r.json())
        .then(rows => {
            if (rows && rows.error) { toast('Error: ' + rows.error, 'err'); return; }
            allItems = rows;
            itemCurrentList = rows;
            renderItemGrid();
            document.getElementById('item-count').textContent = rows.length + ' item(s)';
            setStatus('Ready');
        })
        .catch(() => {
            toast('Network error loading items', 'err');
            document.getElementById('item-grid-body').innerHTML =
                '<tr><td colspan="11" style="text-align:center;color:darkred;padding:8px;">Could not load items — check DB connection</td></tr>';
        });
}

function renderItemGrid() {
    const tbody = document.getElementById('item-grid-body');
    tbody.innerHTML = '';
    if (!itemCurrentList.length) {
        tbody.innerHTML = '<tr><td colspan="11" style="text-align:center;padding:8px;color:#888;">No items found</td></tr>';
        return;
    }
    itemCurrentList.forEach(it => {
        const tr = document.createElement('tr');
        tr.setAttribute('tabindex', '0');
        tr.className = it.STOCK_NUMBER === selectedStockNumber ? 'row-selected' : '';
        tr.innerHTML = `<td>${it.STOCK_NUMBER||''}</td><td>${it.BRAND_NAME||''}</td><td>${it.ITEM_NAME||''}</td>
            <td>${it.ITEM_TYPE||''}</td><td>${it.STOCK_TYPE||''}</td><td>${it.VOLUME_L||''}</td>
            <td>${it.BARCODE||''}</td><td>${it.SIZE_DESC||''}</td><td>${it.AVAILABLE_STATUS||''}</td>
            <td>${it.M_Name||''}</td><td>${it.MANUFACTURE_NO||''}</td>`;
        tr.onclick = () => selectStockItem(it);
        tbody.appendChild(tr);
    });
}

function filterStockItems() {
    const q = document.getElementById('item-filter-text').value.trim().toLowerCase();
    const scope = document.querySelector('input[name="search-scope"]:checked').value;
    itemCurrentList = !q ? allItems : allItems.filter(it => {
        const brand = (it.BRAND_NAME || '').toLowerCase();
        const item  = (it.ITEM_NAME  || '').toLowerCase();
        if (scope === 'brand') return brand.includes(q);
        if (scope === 'item')  return item.includes(q);
        return brand.includes(q) || item.includes(q);
    });
    renderItemGrid();
}

function selectStockItem(it) {
    selectedStockNumber = it.STOCK_NUMBER;
    const stockNoField = document.getElementById('item-stock-number');
    stockNoField.disabled = false;
    stockNoField.value    = it.STOCK_NUMBER || '';
    document.getElementById('item-brand-name').value     = it.BRAND_NAME || '';
    document.getElementById('item-item-name').value      = it.ITEM_NAME || '';
    document.getElementById('item-item-type').value      = it.ITEM_TYPE || '';
    document.getElementById('item-stock-type').value     = it.STOCK_TYPE || '01';
    document.getElementById('item-volume-ml').value      = it.VOLUME_L || '';
    document.getElementById('item-units-peritem').value  = (it.UNITS_PERITEM ?? 1);
    document.getElementById('item-barcode').value        = it.BARCODE || '';
    document.getElementById('item-size-desc').value       = it.SIZE_DESC || '';
    document.getElementById('item-available-status').value = it.AVAILABLE_STATUS || 'Active';
    document.getElementById('item-unit-type').value      = it.UNIT_TYPE || '';
    document.getElementById('item-location').value       = it.LOCATION || '';
    setManufacturerSelection(it.MANUFACTURE_NO, it.M_Name);
    document.getElementById('form-mode').textContent = 'Editing Record';
    renderItemGrid();
    setStatus('Selected: ' + it.STOCK_NUMBER);
}

function newStockItem() {
    selectedStockNumber = null;
    const stockNoField = document.getElementById('item-stock-number');
    stockNoField.readOnly = false;
    stockNoField.disabled = true;
    stockNoField.value = '';
    stockNoField.placeholder = '(auto-assigned)';
    document.getElementById('item-brand-name').value = '';
    document.getElementById('item-item-name').value = '';
    document.getElementById('item-item-type').value = '';
    document.getElementById('item-stock-type').value = '01';
    document.getElementById('item-volume-ml').value = '';
    document.getElementById('item-units-peritem').value = 1;
    document.getElementById('item-barcode').value = '';
    document.getElementById('item-size-desc').value = '';
    document.getElementById('item-available-status').value = 'Active';
    document.getElementById('item-unit-type').value = '';
    document.getElementById('item-location').value = '';
    setManufacturerSelection(null, null);
    document.getElementById('form-mode').textContent = 'New Record';
    renderItemGrid();
    document.getElementById('item-stock-number').focus();
    setStatus('Ready for new item');
}

function findStockItem() {
    const stockNoField = document.getElementById('item-stock-number');
    const stockNo = stockNoField.value.trim();
    if (!stockNo) { toast('Enter a Stock Number to find', 'warn'); document.getElementById('item-brand-name').focus(); return; }
    fetch('api/find_stock_item.php?stock_number=' + encodeURIComponent(stockNo))
        .then(r => r.json())
        .then(res => {
            if (res.found) {
                selectStockItem(res.item);
                toast('Item found', 'ok');
            } else {
                // Stock Number is now auto-assigned by the database — typing
                // an unused number no longer reserves it for a new item.
                toast('No item with that Stock Number. Click New to create a new item — Stock Number is auto-assigned.', 'warn');
                stockNoField.value = '';
            }
            document.getElementById('item-brand-name').focus();
        })
        .catch(() => { toast('Network error finding item', 'err'); document.getElementById('item-brand-name').focus(); });
}

function saveStockItem() {
    const brand   = document.getElementById('item-brand-name').value.trim();
    const units   = parseInt(document.getElementById('item-units-peritem').value) || 0;

    if (!brand)   { toast('Brand Name is required', 'warn'); document.getElementById('item-brand-name').focus(); return; }
    if (!selectedManufactureNo) { toast('Select a Manufacturer from the list', 'warn'); return; }
    if (units < 1) { toast('Units Per Item must be greater than 0', 'warn'); document.getElementById('item-units-peritem').focus(); return; }

    // selectedStockNumber (not the text field) drives insert-vs-update — it's
    // only set by loading an existing record via Find or the grid, so a fresh
    // "New" always inserts regardless of anything left typed in the field.
    const payload = {
        stock_number:     selectedStockNumber,
        brand_name:       brand,
        item_name:        document.getElementById('item-item-name').value.trim(),
        item_type:        document.getElementById('item-item-type').value.trim(),
        stock_type:       document.getElementById('item-stock-type').value.trim(),
        volume_ml:        document.getElementById('item-volume-ml').value.trim(),
        units_peritem:    units,
        barcode:          document.getElementById('item-barcode').value.trim(),
        size_desc:        document.getElementById('item-size-desc').value.trim(),
        available_status: document.getElementById('item-available-status').value.trim() || 'Active',
        unit_type:        document.getElementById('item-unit-type').value.trim(),
        location:         document.getElementById('item-location').value.trim(),
        manufacture_no:   selectedManufactureNo
    };

    fetch('api/save_stock_item.php', { method: 'POST', body: JSON.stringify(payload) })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                toast(res.mode === 'inserted' ? 'Item added — Stock #' + res.stock_number : 'Item updated', 'ok');
                selectedStockNumber = res.stock_number;
                const stockNoField = document.getElementById('item-stock-number');
                stockNoField.disabled  = false;
                stockNoField.value     = res.stock_number;
                document.getElementById('form-mode').textContent = 'Editing Record';
                loadStockItems();
            } else {
                toast('Error: ' + (res.error || 'Save failed'), 'err');
            }
        })
        .catch(() => toast('Network error saving item', 'err'));
}

function removeStockItem() {
    if (!selectedStockNumber) { toast('Select an item from the list first', 'warn'); return; }
    const stockNo = selectedStockNumber;
    if (!confirm('Remove item "' + stockNo + '"? This cannot be undone.')) return;

    fetch('api/delete_stock_item.php', { method: 'POST', body: JSON.stringify({ stock_number: stockNo }) })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                toast('Item removed', 'ok');
                newStockItem();
                loadStockItems();
            } else {
                toast('Error: ' + (res.error || 'Remove failed'), 'err');
            }
        })
        .catch(() => toast('Network error removing item', 'err'));
}

// Enter moves top-to-bottom through the main form, jumps into the
// Manufacturer list once you've typed a search, and confirming a manufacturer
// (or a row in either table) advances to the next logical field.
const itemFieldOrder = ['item-brand-name','item-item-name','item-item-type','item-volume-ml',
    'item-units-peritem','item-barcode','item-size-desc','item-unit-type'];

document.addEventListener('keydown', e => {
    if (e.key !== 'Enter') return;
    const active = document.activeElement;
    const id = active.id;

    if (id === 'item-stock-number') {
        e.preventDefault();
        findStockItem();
        return;
    }

    if (id === 'item-unit-type') {
        e.preventDefault();
        document.getElementById('mfg-search').focus();
        return;
    }

    if (id === 'mfg-search') {
        e.preventDefault();
        if (mfgCurrentList.length) selectManufacturer(mfgCurrentList[0]);
        document.getElementById('item-location').focus();
        return;
    }

    if (active.tagName === 'DIV' && active.closest('#mfg-listbox')) {
        e.preventDefault();
        const no = parseInt(active.textContent.split(':')[0]);
        const m  = mfgCurrentList.find(x => x.Manufacture_no === no);
        if (m) selectManufacturer(m);
        document.getElementById('item-location').focus();
        return;
    }

    if (id === 'item-location') {
        e.preventDefault();
        saveStockItem();
        return;
    }

    if (id === 'item-filter-text') {
        e.preventDefault();
        // selectStockItem() re-renders the grid (to update the highlighted
        // row), which would destroy a focused <tr> and drop focus to <body>
        // if we tried to focus-then-click it — so select directly and jump
        // straight to the next field instead of routing through the row.
        if (itemCurrentList.length) selectStockItem(itemCurrentList[0]);
        document.getElementById('item-brand-name').focus();
        return;
    }

    if (active.tagName === 'TR' && active.closest('#item-grid-body')) {
        e.preventDefault();
        selectStockItem(itemCurrentList.find(it => it.STOCK_NUMBER === active.children[0].textContent) || itemCurrentList[0]);
        document.getElementById('item-brand-name').focus();
        return;
    }

    const idx = itemFieldOrder.indexOf(id);
    if (idx !== -1 && idx < itemFieldOrder.length - 1) {
        e.preventDefault();
        document.getElementById(itemFieldOrder[idx + 1]).focus();
    }
});

window.addEventListener('focus', () => loadManufacturers());

loadManufacturers();
loadStockItems();
</script>
</body>
</html>
