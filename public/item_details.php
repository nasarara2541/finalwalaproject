<?php
require_once __DIR__ . '/includes/access.php';
requireAccess('admin_area');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AISellProduct - Item Packaging Details</title>
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

input[type=text], input[type=number], textarea {
    border: 1px solid; border-color: #808080 #ffffff #ffffff #808080;
    background: #ffff99; padding: 2px 4px; height: 22px; font-size:12px;
    font-family: Tahoma, sans-serif; width:100%;
}
textarea { height:54px; padding:4px; resize:none; }
input.field-white { background:#fff !important; }
input[readonly] { background: #d4d0c8 !important; color:#333; }
input:focus, textarea:focus { outline: 2px solid #0a246a; }

.win-btn {
    background: #d4d0c8; border: 1px solid; border-color: #ffffff #808080 #808080 #ffffff;
    padding: 2px 10px; cursor:pointer; font-size:12px; height:23px;
    font-family: Tahoma, sans-serif; white-space:nowrap; display:inline-flex; align-items:center; gap:3px;
}
.win-btn:hover  { background: #e8e4d8; }
.win-btn-green { background:#1a7a1a; color:white; border-color:#44aa44 #0a500a #0a500a #44aa44; }
.win-btn-green:hover { background:#1e8c1e; }

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

label.lbl { font-weight:bold; white-space:nowrap; width:130px; flex-shrink:0; }
.field-row { display:flex; align-items:center; gap:6px; margin-bottom:6px; }
.legend-text { color:#333; font-size:11px; }

#toast { position:fixed; bottom:16px; right:16px; z-index:9999; padding:8px 16px; font-weight:bold; font-size:12px; border:1px solid; display:none; }
</style>
</head>
<body class="flex flex-col h-screen">

<?php $SCREEN_NAME = 'Item Packaging Details'; $SCREEN_ICON = 'box'; require __DIR__ . '/includes/navbar.php'; ?>

<div style="display:flex;flex-direction:column;flex:1;padding:5px;gap:4px;min-height:0;">

    <div style="display:flex;gap:4px;flex:1;min-height:0;">

        <div class="win-panel" style="flex:1;display:flex;flex-direction:column;padding:6px;min-height:0;">
            <div class="win-section-label" style="margin:-6px -6px 6px -6px;">
                <span>Select Item</span>
            </div>
            <input id="item-filter-text" type="text" class="field-white" placeholder="Search by brand or item name…" style="margin-bottom:4px;" oninput="filterItems()">
            <div style="flex:1;overflow:auto;min-height:0;">
                <table class="win-table">
                    <thead>
                        <tr><th>Stock#</th><th>Brand</th><th>Item Name</th><th>Size</th></tr>
                    </thead>
                    <tbody id="item-list-body"><tr><td colspan="4" style="text-align:center;padding:8px;color:#888;">Type a brand or item name above to search.</td></tr></tbody>
                </table>
            </div>
        </div>

        <div class="win-panel" style="flex:1;padding:8px;">
            <div class="win-section-label" style="margin:-8px -8px 8px -8px;">
                <span>Additional Details</span>
                <span id="form-mode" style="font-weight:normal;font-size:11px;color:#555;">No item selected</span>
            </div>

            <div class="field-row">
                <label class="lbl">Item</label>
                <input id="detail-item-label" type="text" readonly tabindex="-1" placeholder="Select an item from the list" style="flex:1;">
            </div>

            <div style="border-top:1px solid #808080;margin:6px 0;"></div>

            <div class="field-row">
                <label class="lbl">Units Per Box</label>
                <input id="detail-units-perbox" type="number" min="0" placeholder="e.g. 12" style="width:100px;flex:none;">
                <span class="legend-text">How many sellable units come in one box/carton</span>
            </div>

            <div class="field-row">
                <label class="lbl">Sub-Units Per Unit</label>
                <input id="detail-subunits" type="number" min="0" placeholder="e.g. 10 tablets" style="width:100px;flex:none;">
                <span class="legend-text">For items sold as strips/sheets - tablets per strip etc.</span>
            </div>

            <div class="field-row">
                <label class="lbl">Unit Type</label>
                <input id="detail-unit-type" type="text" placeholder="e.g. Box, Strip, Bottle" style="flex:1;">
            </div>

            <div class="field-row" style="align-items:flex-start;">
                <label class="lbl" style="margin-top:3px;">Packaging Notes</label>
                <textarea id="detail-notes" placeholder="Any other packaging detail…" style="flex:1;"></textarea>
            </div>

            <div style="border-top:1px solid #808080;margin:10px 0 8px;padding-top:6px;">
                <div style="display:flex;gap:5px;align-items:center;">
                    <button class="win-btn win-btn-green" onclick="saveDetails()">Save</button>
                    <span class="legend-text" style="font-style:italic;color:#888;">This screen is a layout preview - saving isn't wired up yet.</span>
                </div>
            </div>
        </div>

    </div>

</div>

<div class="win-statusbar">
    <span id="status-msg">Ready</span>
    <span id="item-count"></span>
</div>

<div id="toast"></div>

<script>
let allItems = [];
let itemCurrentList = [];
let itemsLoaded = false;
let selectedItem = null;

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
// instead of leaving the screen silently stuck on "Loading…" forever - the
// original rejection still propagates so each caller's existing .then()
// chain behaves exactly as it did before.
const _nativeFetch = window.fetch;
window.fetch = function(...args) {
    return _nativeFetch.apply(this, args).catch(err => {
        toast('Network/Server error - check DB_SERVER in .env and that the database is reachable', 'err');
        throw err;
    });
};

function setStatus(msg) { document.getElementById('status-msg').textContent = msg; }

function loadItems() {
    setStatus('Loading items…');
    fetch('api/get_item_stock_list.php')
        .then(r => r.json())
        .then(rows => {
            if (rows && rows.error) { toast('Error: ' + rows.error, 'err'); return; }
            allItems = rows;
            itemsLoaded = true;
            filterItems(); // apply whatever's already typed, in case the user kept typing while this was in flight
            setStatus('Ready');
        })
        .catch(() => {
            toast('Network error loading items', 'err');
            document.getElementById('item-list-body').innerHTML =
                '<tr><td colspan="4" style="text-align:center;color:darkred;padding:8px;">Could not load items - check DB connection</td></tr>';
        });
}

function renderItemList() {
    const tbody = document.getElementById('item-list-body');
    tbody.innerHTML = '';
    if (!itemCurrentList.length) {
        tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;padding:8px;color:#888;">No items found</td></tr>';
        return;
    }
    itemCurrentList.forEach(it => {
        const tr = document.createElement('tr');
        tr.setAttribute('tabindex', '0');
        tr.className = selectedItem && it.STOCK_NUMBER === selectedItem.STOCK_NUMBER ? 'row-selected' : '';
        tr.innerHTML = `<td>${it.STOCK_NUMBER||''}</td><td>${it.BRAND_NAME||''}</td><td>${it.ITEM_NAME||''}</td><td>${it.SIZE_DESC||''}</td>`;
        tr.onclick = () => selectItem(it);
        tbody.appendChild(tr);
    });
}

function filterItems() {
    const q = document.getElementById('item-filter-text').value.trim().toLowerCase();
    if (!itemsLoaded) {
        if (!q) return; // nothing typed yet -- leave the "type to search" prompt showing, don't fetch anything
        loadItems(); // first keystroke: fetch the item list once, then filter it client-side from here on
        return;
    }
    itemCurrentList = !q ? allItems : allItems.filter(it =>
        (it.BRAND_NAME || '').toLowerCase().includes(q) || (it.ITEM_NAME || '').toLowerCase().includes(q)
    );
    document.getElementById('item-count').textContent = itemCurrentList.length + ' item(s)';
    renderItemList();
}

function selectItem(it) {
    selectedItem = it;
    document.getElementById('detail-item-label').value = it.STOCK_NUMBER + ' - ' + (it.BRAND_NAME||'') + ' ' + (it.ITEM_NAME||'');
    // Pre-filled from the item's existing record so the layout doesn't look
    // empty - Units Per Box / Unit Type already exist on Item_Stock. The
    // other fields below are new and have nowhere to load from yet.
    document.getElementById('detail-units-perbox').value = it.UNITS_PERITEM ?? '';
    document.getElementById('detail-unit-type').value    = it.UNIT_TYPE || '';
    document.getElementById('detail-subunits').value = '';
    document.getElementById('detail-notes').value    = '';
    document.getElementById('form-mode').textContent = it.STOCK_NUMBER;
    renderItemList();
    setStatus('Selected: ' + it.STOCK_NUMBER);
}

function saveDetails() {
    if (!selectedItem) { toast('Select an item from the list first', 'warn'); return; }
    toast('Saving isn\'t implemented yet - layout only for now', 'warn');
}

document.addEventListener('keydown', e => {
    if (e.key !== 'Enter') return;
    const active = document.activeElement;
    if (active.id === 'item-filter-text') {
        e.preventDefault();
        if (itemCurrentList.length) selectItem(itemCurrentList[0]);
        return;
    }
    if (active.tagName === 'TR' && active.closest('#item-list-body')) {
        e.preventDefault();
        const it = itemCurrentList.find(x => String(x.STOCK_NUMBER) === active.children[0].textContent);
        if (it) selectItem(it);
        return;
    }
});

// Not calling loadItems() here -- it's fetched lazily on the first keystroke
// in the search box instead (see filterItems()), so this screen doesn't pull
// the whole item table just for being opened.
</script>
</body>
</html>
