<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'includes/auth_guard.php';
date_default_timezone_set('Asia/Karachi');
include 'includes/header.php';
include 'includes/navbar.php';
?>

<div style="padding:3px; background:#d4d0c8; height:calc(100vh - 60px); overflow:hidden;">
<div style="background:#d4d0c8; border:2px solid; border-color:#ffffff #808080 #808080 #ffffff; padding:8px; height:100%; display:flex; flex-direction:column; gap:8px; overflow:hidden;">

    <!-- Brand strip -->
    <div style="padding:1px 4px; flex-shrink:0;">
        <span style="color:#aa0000; font-weight:bold; font-size:13px; font-family:Tahoma,Arial,sans-serif;"><?php echo htmlspecialchars($_SESSION['company_name'] ?? ''); ?></span>
        <span style="color:#555555; font-size:11px; margin-left:14px;">Search Items</span>
    </div>

    <!-- Search bar -->
    <div class="win-panel" style="flex-shrink:0; display:flex; gap:10px; align-items:end;">
        <div style="flex:1; max-width:400px;">
            <label>Item Name</label>
            <input id="siqSearch" oninput="searchItemsFull()" autocomplete="off" class="bg-yellow-100 nav-el" style="width:100%;" placeholder="Type to search...">
        </div>
        <div style="font-size:11px; color:#333; padding-bottom:6px; margin-left:auto;">
            Results: <span id="siqCount" style="font-weight:bold;">0</span>
        </div>
    </div>

    <!-- GRID -->
    <div class="win-white-panel" style="flex:1; display:flex; flex-direction:column; min-height:0;">
        <div style="flex:1; overflow:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Stock#</th>
                        <th>Brand Name</th>
                        <th>Item Name</th>
                        <th>Item Type</th>
                        <th>Stock Type</th>
                        <th>Volume(ML)</th>
                        <th>Units/Item</th>
                        <th>Barcode</th>
                        <th>Size Desc</th>
                        <th>Avail</th>
                        <th>Unit Type</th>
                        <th style="text-align:right;">Price</th>
                        <th style="text-align:right;">Price 2</th>
                        <th style="text-align:right;">Price 3</th>
                        <th style="text-align:right;">WS Price</th>
                        <th style="text-align:right;">Retail Price</th>
                        <th style="text-align:right;">Avg Price</th>
                        <th style="text-align:right;">Purch. Price</th>
                        <th style="text-align:right;">Qty</th>
                        <th style="text-align:right;">Disc %</th>
                        <th>Sale Disc</th>
                        <th>Narcotic</th>
                        <th>Disc Status</th>
                        <th>Safety Level</th>
                        <th>Manufacturer</th>
                        <th>Supplier</th>
                        <th>Location</th>
                        <th>Suppliers List</th>
                    </tr>
                </thead>
                <tbody id="siqBody"></tbody>
            </table>
        </div>
    </div>

</div>
</div>

<script>
let siqTimer = null;
function searchItemsFull() {
    let q = document.getElementById('siqSearch').value.trim();
    clearTimeout(siqTimer);
    if (!q) {
        document.getElementById('siqBody').innerHTML = '';
        document.getElementById('siqCount').textContent = '0';
        return;
    }
    siqTimer = setTimeout(() => {
        fetch('api/search_items_full.php?q=' + encodeURIComponent(q)).then(r => r.json()).then(list => {
            if (!Array.isArray(list)) { alert('Error loading results'); return; }
            renderSiqResults(list);
        }).catch(err => alert('Network error: ' + err));
    }, 200);
}

function renderSiqResults(list) {
    const body = document.getElementById('siqBody');
    body.innerHTML = '';
    list.forEach(it => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${it.STOCK_NUMBER}</td>
            <td>${it.BRAND_NAME}</td>
            <td>${it.ITEM_NAME}</td>
            <td>${it.ITEM_TYPE}</td>
            <td>${it.STOCK_TYPE || ''}</td>
            <td>${it.VOLUME_L || ''}</td>
            <td>${it.UNITS_PERITEM || ''}</td>
            <td>${it.BARCODE}</td>
            <td>${it.SIZE_DESC}</td>
            <td>${it.AVAILABLE_STATUS}</td>
            <td>${it.UNIT_TYPE}</td>
            <td style="text-align:right;">${parseFloat(it.PRICE || 0).toFixed(2)}</td>
            <td style="text-align:right;">${parseFloat(it.PRICE_2 || 0).toFixed(2)}</td>
            <td style="text-align:right;">${parseFloat(it.PRICE_3 || 0).toFixed(2)}</td>
            <td style="text-align:right;">${parseFloat(it.WS_Price || 0).toFixed(2)}</td>
            <td style="text-align:right;">${parseFloat(it.RETAIL_PRICE || 0).toFixed(2)}</td>
            <td style="text-align:right;">${parseFloat(it.AvgPrice || 0).toFixed(2)}</td>
            <td style="text-align:right;">${parseFloat(it.PURCHASE_PRICE || 0).toFixed(2)}</td>
            <td style="text-align:right;">${it.QTY_INHAND}</td>
            <td style="text-align:right;">${it.PERCENTAGE_DISC || 0}</td>
            <td>${it.SALE_DISCOUNT}</td>
            <td>${it.NARCOTICS_STATUS}</td>
            <td>${it.DISC_STATUS}</td>
            <td>${it.SAFETY_LEVEL || ''}</td>
            <td>${it.MANUFACTURER_NAME}</td>
            <td>${it.SUPPLIER_NAME || ''}</td>
            <td>${it.LOCATION}</td>
            <td>${it.SUPPLIERS_LIST}</td>`;
        body.appendChild(tr);
    });
    document.getElementById('siqCount').textContent = list.length;
}

</script>