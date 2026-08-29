<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'includes/auth_guard.php';
date_default_timezone_set('Asia/Karachi');
include 'includes/header.php';
include 'includes/navbar.php';
?>

<div style="padding:3px; background:#d4d0c8; height:calc(100vh - 60px); overflow:hidden;">
<div style="background:#d4d0c8; border:2px solid; border-color:#ffffff #808080 #808080 #ffffff; padding:10px; height:100%; display:flex; flex-direction:column; align-items:center; overflow-y:auto; overflow-x:hidden;">

    <!-- Vertically auto-centers when everything fits (bigger screens) without
         the scroll-clipping bug justify-content:center + overflow:auto has --
         on shorter screens this wrapper just sits at the top and the panel
         above scrolls normally. -->
    <div style="margin:auto 0; width:100%; display:flex; flex-direction:column; align-items:center;">

    <!-- Brand strip -->
    <div style="padding:1px 4px; align-self:flex-start; flex-shrink:0; width:min(760px, 92vw);">
        <span style="color:#aa0000; font-weight:bold; font-size:13px; font-family:Tahoma,Arial,sans-serif;"><?php echo htmlspecialchars($_SESSION['company_name'] ?? ''); ?></span>
        <span style="color:#555555; font-size:11px; margin-left:14px;">Purchase Report</span>
    </div>

        <div style="width:min(760px, 92vw); display:flex; flex-direction:column; gap:min(3.5vh,30px); font-size:15px;">

    <!-- Date range -->
    <div class="win-panel" style="display:flex; gap:10px; align-items:end; flex-wrap:wrap; justify-content:center;">
        <div>
            <label style="font-size:13px;">From</label>
            <input id="prFrom" type="date" class="bg-yellow-100 nav-el" style="width:160px; height:34px; font-size:15px; padding:4px 8px; line-height:1.2;" value="<?php echo date('Y-m-d'); ?>">
        </div>
        <div>
            <label style="font-size:13px;">To</label>
            <input id="prTo" type="date" class="bg-yellow-100 nav-el" style="width:160px; height:34px; font-size:15px; padding:4px 8px; line-height:1.2;" value="<?php echo date('Y-m-d'); ?>">
        </div>
        <div style="display:flex; align-items:center; gap:6px; padding-bottom:6px;">
            <input id="prDataToDate" type="checkbox" checked style="width:16px; height:16px;">
            <label style="margin:0; font-weight:bold; font-size:14px;">Data To Date</label>
        </div>
    </div>

        <!-- Filter fields -->
    <div class="win-panel" style="display:flex; flex-direction:column; gap:min(1.8vh,14px); padding:min(1.6vh,12px) 14px;">
                <div style="display:flex; align-items:center; gap:10px; position:relative;">
            <label style="width:min(150px, 30vw); margin:0; text-align:right; flex-shrink:0; font-size:14px;">Supplier Name</label>
                        <input id="prSupplierName" oninput="prSearch('prSupplierName')" autocomplete="off" class="bg-yellow-100 nav-el" style="width:360px; height:34px; font-size:15px; padding:4px 8px; line-height:1.2;">
            <div id="prSupplierNameDropdown" style="position:absolute; left:160px; top:42px; width:360px; max-height:160px; overflow-y:auto; background:#fff; border:1px solid #808080; z-index:20; display:none;"></div>
        </div>
        <div style="display:flex; align-items:center; gap:10px; position:relative;">
                        <label style="width:min(150px, 30vw); margin:0; text-align:right; flex-shrink:0; font-size:14px;">Transaction # / RID</label>
                        <input id="prTransRid" oninput="prSearch('prTransRid')" autocomplete="off" class="bg-yellow-100 nav-el" style="width:360px; height:34px; font-size:15px; padding:4px 8px; line-height:1.2;">
            <div id="prTransRidDropdown" style="position:absolute; left:min(160px, 32vw); top:42px; width:min(360px, 45vw); max-height:160px; overflow-y:auto; background:#fff; border:1px solid #808080; z-index:20; display:none;"></div>
        </div>
        <div style="display:flex; align-items:center; gap:10px; position:relative;">
                        <label style="width:min(150px, 30vw); margin:0; text-align:right; flex-shrink:0; font-size:14px;">Invoice #</label>
                        <input id="prInvoiceNo" oninput="prSearch('prInvoiceNo')" autocomplete="off" class="bg-yellow-100 nav-el" style="width:360px; height:34px; font-size:15px; padding:4px 8px; line-height:1.2;">
            <div id="prInvoiceNoDropdown" style="position:absolute; left:min(160px, 32vw); top:42px; width:min(360px, 45vw); max-height:160px; overflow-y:auto; background:#fff; border:1px solid #808080; z-index:20; display:none;"></div>
        </div>
        <div style="display:flex; align-items:center; gap:10px;">
            <label style="width:min(150px, 30vw); margin:0; text-align:right; flex-shrink:0; font-size:14px;">Bar Code</label>
                        <input id="prBarCode" class="bg-yellow-100 nav-el" style="width:360px; height:34px; font-size:15px; padding:4px 8px; line-height:1.2;">
        </div>
                <div style="display:flex; align-items:center; gap:10px; position:relative;">
            <label style="width:min(150px, 30vw); margin:0; text-align:right; flex-shrink:0; font-size:14px;">Item Name</label>
                        <input id="prItemName" oninput="prSearch('prItemName')" autocomplete="off" class="bg-yellow-100 nav-el" style="width:360px; height:34px; font-size:15px; padding:4px 8px; line-height:1.2;">
            <div id="prItemNameDropdown" style="position:absolute; left:160px; top:42px; width:360px; max-height:160px; overflow-y:auto; background:#fff; border:1px solid #808080; z-index:20; display:none;"></div>
        </div>
        <div style="display:flex; align-items:center; gap:10px; position:relative;">
                        <label style="width:min(150px, 30vw); margin:0; text-align:right; flex-shrink:0; font-size:14px;">Company</label>
                        <input id="prCompany" oninput="prSearch('prCompany')" autocomplete="off" class="bg-yellow-100 nav-el" style="width:360px; height:34px; font-size:15px; padding:4px 8px; line-height:1.2;">
            <div id="prCompanyDropdown" style="position:absolute; left:min(160px, 32vw); top:42px; width:min(360px, 45vw); max-height:160px; overflow-y:auto; background:#fff; border:1px solid #808080; z-index:20; display:none;"></div>
        </div>
        <div style="display:flex; align-items:center; gap:10px; position:relative;">
                        <label style="width:min(150px, 30vw); margin:0; text-align:right; flex-shrink:0; font-size:14px;">Group</label>
                        <input id="prGroup" oninput="prSearch('prGroup')" autocomplete="off" class="bg-yellow-100 nav-el" style="width:360px; height:34px; font-size:15px; padding:4px 8px; line-height:1.2;">
            <div id="prGroupDropdown" style="position:absolute; left:min(160px, 32vw); top:42px; width:min(360px, 45vw); max-height:160px; overflow-y:auto; background:#fff; border:1px solid #808080; z-index:20; display:none;"></div>
        </div>
    </div>

         <!-- Buttons grid -->
    <div style="display:flex; gap:10px; font-size:14px;">
        <div style="flex:1; display:flex; flex-direction:column; gap:min(1.4vh,10px);">
            <button onclick="prNotImplemented()" style="height:min(6vh,46px); display:flex; align-items:center; justify-content:center; padding:0 12px; font-size:14px;">Day/s Wise Purchase</button>
            <button onclick="prNotImplemented()" style="height:min(6vh,46px); display:flex; align-items:center; justify-content:center; padding:0 12px; font-size:14px;">Group Wise Purchase</button>
            <button onclick="prNotImplemented()" style="height:min(6vh,46px); display:flex; align-items:center; justify-content:center; padding:0 12px; font-size:14px;">Un-Posted Invoice(s)</button>
        </div>
        <div style="flex:1; display:flex; flex-direction:column; gap:min(1.4vh,10px);">
            <button onclick="prNotImplemented()" style="height:min(6vh,46px); display:flex; align-items:center; justify-content:center; padding:0 12px; font-size:14px;">Purchase Summary</button>
            <button onclick="prNotImplemented()" style="height:min(6vh,46px); display:flex; align-items:center; justify-content:center; padding:0 12px; font-size:14px;">Cancelled Invoice</button>
                        <button onclick="prResetFields()" style="height:min(6vh,46px); display:flex; align-items:center; justify-content:center; font-size:14px;">Reset</button>
        </div>
    </div>

    </div>

    </div>

</div>
</div>

<script>
function prNotImplemented() {
    alert('This report is not wired up yet - coming soon.');
}

function prResetFields() {
    if (!confirm('Are you sure you want to reset the fields?')) return;
    document.getElementById('prFrom').value = '<?php echo date('Y-m-d'); ?>';
    document.getElementById('prTo').value = '<?php echo date('Y-m-d'); ?>';
    document.getElementById('prDataToDate').checked = true;
    ['prSupplierName','prTransRid','prInvoiceNo','prBarCode','prItemName','prCompany','prGroup'].forEach(id => {
        document.getElementById(id).value = '';
        const box = document.getElementById(id + 'Dropdown');
        if (box) box.style.display = 'none';
    });
}
// One entry per searchable field: which API endpoint to call, and which
// property in each result row to display / fill into the input box.
const PR_FIELDS = {
    prSupplierName: { url: 'api/search_supplier_paged.php', labelKey: 'SUPPLIER_NAME' },
    prItemName:     { url: 'api/search_item_paged.php',     labelKey: 'ITEM_NAME' },
    prTransRid:     { url: 'api/search_transaction.php',    labelKey: 'Trans_no' },
    prInvoiceNo:    { url: 'api/search_invoice.php',        labelKey: 'Invoice_no' },
    prCompany:      { url: 'api/search_company.php',        labelKey: 'COMPANY_NAME' },
    prGroup:        { url: 'api/search_group.php',          labelKey: 'GROUP_NAME' }
};

const prState = {};   // per-field: { query, offset, hasMore, loading }
const prTimers = {};

function prSearch(fieldId) {
    const cfg = PR_FIELDS[fieldId];
    const q = document.getElementById(fieldId).value.trim();
    const box = document.getElementById(fieldId + 'Dropdown');
    clearTimeout(prTimers[fieldId]);
    if (!q) { box.style.display = 'none'; return; }
    prTimers[fieldId] = setTimeout(() => {
        prState[fieldId] = { query: q, offset: 0, hasMore: false, loading: false };
        prFetchPage(fieldId, true);
    }, 200);
}

function prFetchPage(fieldId, replace) {
    const cfg = PR_FIELDS[fieldId];
    const st = prState[fieldId];
    if (!st || st.loading) return;
    st.loading = true;
    fetch(cfg.url + '?q=' + encodeURIComponent(st.query) + '&offset=' + st.offset)
        .then(r => r.json())
        .then(data => {
            st.loading = false;
            st.hasMore = !!data.hasMore;
            prRenderDropdown(fieldId, data.results || [], replace);
        })
        .catch(() => { st.loading = false; });
}

function prRenderDropdown(fieldId, rows, replace) {
    const cfg = PR_FIELDS[fieldId];
    const box = document.getElementById(fieldId + 'Dropdown');
    const st = prState[fieldId];

    if (replace) box.innerHTML = '';

    const existingLoadMore = box.querySelector('.pr-load-more');
    if (existingLoadMore) existingLoadMore.remove();

    if (replace && rows.length === 0) { box.style.display = 'none'; return; }

    rows.forEach(row => {
        const label = String(row[cfg.labelKey] ?? '');
        const div = document.createElement('div');
        div.textContent = label;
        div.style.cssText = 'padding:5px 8px; font-size:14px; cursor:pointer; border-bottom:1px solid #eee;';
        div.onmouseover = () => div.style.background = '#dce8f4';
        div.onmouseout  = () => div.style.background = '#fff';
        div.onclick = () => {
            document.getElementById(fieldId).value = label;
            box.style.display = 'none';
        };
        box.appendChild(div);
    });

    if (st.hasMore) {
        const more = document.createElement('div');
        more.className = 'pr-load-more';
        more.textContent = 'Load more...';
        more.style.cssText = 'padding:6px 8px; font-size:13px; cursor:pointer; text-align:center; font-weight:bold; color:#0a246a; background:#f0f0f0;';
        more.onmouseover = () => more.style.background = '#dce8f4';
        more.onmouseout  = () => more.style.background = '#f0f0f0';
        more.onclick = () => {
            st.offset += 100;
            prFetchPage(fieldId, false);
        };
        box.appendChild(more);
    }

    box.style.display = 'block';
}

document.addEventListener('click', function(e) {
    Object.keys(PR_FIELDS).forEach(fieldId => {
        if (!e.target.closest('#' + fieldId) && !e.target.closest('#' + fieldId + 'Dropdown')) {
            document.getElementById(fieldId + 'Dropdown').style.display = 'none';
        }
    });
});
</script>