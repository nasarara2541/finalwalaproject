<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'includes/auth_guard.php';
date_default_timezone_set('Asia/Karachi');
include 'includes/header.php';
include 'includes/navbar.php';
?>

<div style="padding:3px; background:#d4d0c8; height:calc(100vh - 60px); overflow:hidden;">
<div style="background:#d4d0c8; border:2px solid; border-color:#ffffff #808080 #808080 #ffffff; padding:10px; height:100%; display:flex; flex-direction:column; align-items:center; overflow:auto;">

    <!-- Brand strip -->
    <div style="padding:1px 4px; align-self:flex-start; flex-shrink:0;">
        <span style="color:#aa0000; font-weight:bold; font-size:13px; font-family:Tahoma,Arial,sans-serif;"><?php echo htmlspecialchars($_SESSION['company_name'] ?? ''); ?></span>
        <span style="color:#555555; font-size:11px; margin-left:14px;">Purchase Report</span>
    </div>

    <div style="width:760px; max-width:100%; display:flex; flex-direction:column; gap:16px; margin-top:24px; font-size:15px;">

    <!-- Date range -->
    <div class="win-panel" style="display:flex; gap:10px; align-items:end; flex-wrap:wrap; justify-content:center;">
        <div>
            <label style="font-size:13px;">From</label>
            <input id="prFrom" type="date" class="bg-yellow-100 nav-el" style="width:160px; font-size:15px; padding:5px;" value="<?php echo date('Y-m-d'); ?>">
        </div>
        <div>
            <label style="font-size:13px;">To</label>
            <input id="prTo" type="date" class="bg-yellow-100 nav-el" style="width:160px; font-size:15px; padding:5px;" value="<?php echo date('Y-m-d'); ?>">
        </div>
        <div style="display:flex; align-items:center; gap:6px; padding-bottom:6px;">
            <input id="prDataToDate" type="checkbox" checked style="width:16px; height:16px;">
            <label style="margin:0; font-weight:bold; font-size:14px;">Data To Date</label>
        </div>
    </div>

    <!-- Filter fields -->
    <div class="win-panel" style="display:flex; flex-direction:column; gap:6px;">
        <div style="display:flex; align-items:center; gap:10px; position:relative;">
            <label style="width:150px; margin:0; text-align:right; flex-shrink:0; font-size:14px;">Supplier Name</label>
            <input id="prSupplierName" oninput="searchSupplierDropdown()" autocomplete="off" class="bg-yellow-100 nav-el" style="width:360px; font-size:15px; padding:5px;">
            <div id="prSupplierDropdown" style="position:absolute; left:160px; top:32px; width:360px; max-height:160px; overflow-y:auto; background:#fff; border:1px solid #808080; z-index:20; display:none;"></div>
        </div>
        <div style="display:flex; align-items:center; gap:10px;">
            <label style="width:150px; margin:0; text-align:right; flex-shrink:0; font-size:14px;">Transaction # / RID</label>
            <input id="prTransRid" class="bg-yellow-100 nav-el" style="width:360px; font-size:15px; padding:5px;">
        </div>
        <div style="display:flex; align-items:center; gap:10px;">
            <label style="width:150px; margin:0; text-align:right; flex-shrink:0; font-size:14px;">Invoice #</label>
            <input id="prInvoiceNo" class="bg-yellow-100 nav-el" style="width:360px; font-size:15px; padding:5px;">
        </div>
        <div style="display:flex; align-items:center; gap:10px;">
            <label style="width:150px; margin:0; text-align:right; flex-shrink:0; font-size:14px;">Bar Code</label>
            <input id="prBarCode" class="bg-yellow-100 nav-el" style="width:360px; font-size:15px; padding:5px;">
        </div>
        <div style="display:flex; align-items:center; gap:10px; position:relative;">
            <label style="width:150px; margin:0; text-align:right; flex-shrink:0; font-size:14px;">Item Name</label>
            <input id="prItemName" oninput="searchItemDropdown()" autocomplete="off" class="bg-yellow-100 nav-el" style="width:360px; font-size:15px; padding:5px;">
            <div id="prItemDropdown" style="position:absolute; left:160px; top:32px; width:360px; max-height:160px; overflow-y:auto; background:#fff; border:1px solid #808080; z-index:20; display:none;"></div>
        </div>
        <div style="display:flex; align-items:center; gap:10px;">
            <label style="width:150px; margin:0; text-align:right; flex-shrink:0; font-size:14px;">Company</label>
            <input id="prCompany" class="bg-yellow-100 nav-el" style="width:360px; font-size:15px; padding:5px;">
        </div>
        <div style="display:flex; align-items:center; gap:10px;">
            <label style="width:150px; margin:0; text-align:right; flex-shrink:0; font-size:14px;">Group</label>
            <input id="prGroup" class="bg-yellow-100 nav-el" style="width:360px; font-size:15px; padding:5px;">
        </div>
    </div>

    <!-- Buttons grid -->
    <div style="display:flex; gap:10px; font-size:14px;">
        <div style="flex:1; display:flex; flex-direction:column; gap:8px;">
            <button onclick="prNotImplemented()" style="padding:12px; font-size:14px;">Day/s Wise Purchase</button>
            <button onclick="prNotImplemented()" style="padding:12px; font-size:14px;">Group Wise Purchase</button>
            <button onclick="prNotImplemented()" style="padding:12px; font-size:14px;">Un-Posted Invoice(s)</button>
        </div>
        <div style="flex:1; display:flex; flex-direction:column; gap:8px;">
            <button onclick="prNotImplemented()" style="padding:12px; font-size:14px;">Purchase Summary</button>
            <button onclick="prNotImplemented()" style="padding:12px; font-size:14px;">Cancelled Invoice</button>
            <a href="stock.php" style="text-align:center; padding:12px; font-size:14px; background:#d4d0c8; border:2px solid; border-color:#ffffff #808080 #808080 #ffffff; text-decoration:none; color:#000;">eXit</a>
        </div>
    </div>

    </div>

</div>
</div>

<script>
function prNotImplemented() {
    alert('This report is not wired up yet — coming soon.');
}

let prSupplierTimer = null;
function searchSupplierDropdown() {
    let q = document.getElementById('prSupplierName').value.trim();
    let box = document.getElementById('prSupplierDropdown');
    clearTimeout(prSupplierTimer);
    if (!q) { box.style.display = 'none'; return; }
    prSupplierTimer = setTimeout(() => {
        fetch('api/search_supplier.php?q=' + encodeURIComponent(q)).then(r => r.json()).then(list => {
            if (!Array.isArray(list) || list.length === 0) { box.style.display = 'none'; return; }
            box.innerHTML = list.map(s =>
                `<div onclick="pickSupplierPR('${s.SUPPLIER_NAME.replace(/'/g, "\\'")}')" style="padding:5px 8px; font-size:14px; cursor:pointer; border-bottom:1px solid #eee;" onmouseover="this.style.background='#dce8f4'" onmouseout="this.style.background='#fff'">${s.SUPPLIER_NAME}</div>`
            ).join('');
            box.style.display = 'block';
        });
    }, 200);
}
function pickSupplierPR(name) {
    document.getElementById('prSupplierName').value = name;
    document.getElementById('prSupplierDropdown').style.display = 'none';
}

let prItemTimer = null;
function searchItemDropdown() {
    let q = document.getElementById('prItemName').value.trim();
    let box = document.getElementById('prItemDropdown');
    clearTimeout(prItemTimer);
    if (!q) { box.style.display = 'none'; return; }
    prItemTimer = setTimeout(() => {
        fetch('api/search_item.php?q=' + encodeURIComponent(q)).then(r => r.json()).then(list => {
            if (!Array.isArray(list) || list.length === 0) { box.style.display = 'none'; return; }
            box.innerHTML = list.map(it =>
                `<div onclick="pickItemPR('${String(it.ITEM_NAME).replace(/'/g, "\\'")}')" style="padding:5px 8px; font-size:14px; cursor:pointer; border-bottom:1px solid #eee;" onmouseover="this.style.background='#dce8f4'" onmouseout="this.style.background='#fff'">${it.ITEM_NAME}</div>`
            ).join('');
            box.style.display = 'block';
        });
    }, 200);
}
function pickItemPR(name) {
    document.getElementById('prItemName').value = name;
    document.getElementById('prItemDropdown').style.display = 'none';
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('#prSupplierName') && !e.target.closest('#prSupplierDropdown')) {
        document.getElementById('prSupplierDropdown').style.display = 'none';
    }
    if (!e.target.closest('#prItemName') && !e.target.closest('#prItemDropdown')) {
        document.getElementById('prItemDropdown').style.display = 'none';
    }
});
</script>