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
        <span style="color:#555555; font-size:11px; margin-left:14px;">Short Items List</span>
    </div>

    <!-- Date range -->
    <div class="win-panel" style="flex-shrink:0; display:flex; gap:10px; align-items:end;">
        <div>
            <label>From</label>
            <input id="siFrom" type="date" class="bg-yellow-100 nav-el" style="width:150px;">
        </div>
        <div>
            <label>To</label>
            <input id="siTo" type="date" class="bg-yellow-100 nav-el" style="width:150px;">
        </div>
        <div>
            <label>Qty Less Than</label>
            <input id="siQtyLessThan" type="number" min="1" step="1" value="1" class="nav-el" style="width:80px; background-color:#ffff66;"
                   oninput="if(this.value !== '' && parseInt(this.value,10) < 1) this.value = 1;">
        </div>
        <button onclick="loadShortItems()" class="nav-el bg-blue-600" style="padding:4px 16px; font-weight:bold;">Search</button>
        <div style="padding-bottom:4px;">
            <label style="font-size:11px;"><input id="siShowUnavailable" type="checkbox" checked onchange="loadShortItems()"> Show All Unavailable</label>
        </div>
        <div style="font-size:11px; color:#333; padding-bottom:4px; margin-left:auto;">
            Short Items: <span id="shortCount" style="font-weight:bold; color:#aa0000;">0</span>
        </div>
    </div>

    <!-- GRID -->
    <div class="win-white-panel" style="flex:1; display:flex; flex-direction:column; min-height:0;">
        <div style="background:#d4d0c8; padding:2px 5px; border-bottom:1px solid #808080; flex-shrink:0;">
            <span style="font-size:11px; font-weight:bold;">Out of Stock / Unavailable Items</span>
        </div>
        <div style="flex:1; overflow:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Stock#</th>
                        <th>Brand Name</th>
                        <th>Item Name</th>
                        <th>Item Type</th>
                        <th style="text-align:right;">Qty In Hand</th>
                        <th>Status</th>
                        <th>Manufacturer</th>
                        <th>Location</th>
                        <th>Last Received</th>
                    </tr>
                </thead>
                <tbody id="shortBody"></tbody>
            </table>
        </div>
    </div>

</div>
</div>

<script>
function loadShortItems() {
    let from = document.getElementById('siFrom').value;
    let to   = document.getElementById('siTo').value;

    // Qty Less Than: must be a positive integer (no 0 or negative values)
    let qtyInput = document.getElementById('siQtyLessThan');
    let qtyLessThan = parseInt(qtyInput.value, 10);
    if (isNaN(qtyLessThan) || qtyLessThan < 1) {
        qtyLessThan = 1;
        qtyInput.value = 1;
    }

    let showUnavailable = document.getElementById('siShowUnavailable').checked ? '1' : '0';

    let url = 'api/get_short_items.php';
    let params = [];
    if (from) params.push('from=' + encodeURIComponent(from));
    if (to)   params.push('to=' + encodeURIComponent(to));
    params.push('qty_less_than=' + encodeURIComponent(qtyLessThan));
    params.push('show_unavailable=' + encodeURIComponent(showUnavailable));
    if (params.length) url += '?' + params.join('&');

    fetch(url).then(r => r.json()).then(data => {
        if (!data.success) { alert('Error: ' + (data.message || 'Failed to load')); return; }
        renderShortItems(data.items);
    }).catch(err => alert('Network error: ' + err));
}

let siFullList = [];
let siRenderedCount = 0;
const SI_PAGE_SIZE = 100;

function renderShortItems(list) {
    siFullList = list;
    siRenderedCount = 0;
    document.getElementById('shortBody').innerHTML = '';
    document.getElementById('shortCount').textContent = list.length;
    renderNextBatch();
}

function renderNextBatch() {
    const body = document.getElementById('shortBody');
    const nextChunk = siFullList.slice(siRenderedCount, siRenderedCount + SI_PAGE_SIZE);
    const frag = document.createDocumentFragment();
    nextChunk.forEach(it => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${it.STOCK_NUMBER}</td>
            <td>${it.BRAND_NAME}</td>
            <td>${it.ITEM_NAME}</td>
            <td>${it.ITEM_TYPE}</td>
            <td style="text-align:right;">${it.QTY_INHAND}</td>
            <td>${it.AVAILABLE_STATUS === 'N' ? 'Unavailable' : 'Out of Stock'}</td>
            <td>${it.MANUFACTURER_NAME}</td>
            <td>${it.LOCATION}</td>
            <td>${it.LAST_RECEIVED || ''}</td>`;
        frag.appendChild(tr);
    });
    body.appendChild(frag);
    siRenderedCount += nextChunk.length;
    updateLoadMoreButton();
}

function updateLoadMoreButton() {
    let btn = document.getElementById('siLoadMoreBtn');
    const remaining = siFullList.length - siRenderedCount;
    if (remaining > 0) {
        if (!btn) {
            btn = document.createElement('button');
            btn.id = 'siLoadMoreBtn';
            btn.className = 'nav-el bg-blue-600';
            btn.style.cssText = 'margin:8px auto; display:block; padding:4px 16px; font-weight:bold;';
            btn.onclick = renderNextBatch;
            document.getElementById('shortBody').closest('div[style*="flex:1; overflow:auto;"]').after(btn);
        }
        btn.textContent = `Load More (${remaining} remaining)`;
        btn.style.display = 'block';
    } else if (btn) {
        btn.style.display = 'none';
    }
}


</script>