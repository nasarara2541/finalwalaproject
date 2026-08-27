<?php
require_once __DIR__ . '/includes/access.php';
// pos.php holds both the Sale workflow (Sale/Transactions/Reports tabs) and
// the Booking tab -- two different buckets in Zeeshan's role matrix living
// in one file. The Inventory role is excluded from both, so it's the only
// role actually blocked from this screen entirely; Booking-role users pass
// this top-level gate but only ever see the Booking tab (below).
if (!canAccess('sale') && !canAccess('booking')) {
    // Nothing in this screen is reachable for this role (Inventory) -- send
    // them to whichever real landing screen their role actually has.
    $fallback = canAccess('inventory') ? 'stock_receiving.php' : 'login.php';
    header('Location: ' . appBasePrefix() . $fallback);
    exit;
}
$canSale    = canAccess('sale');
$canBooking = canAccess('booking');
$canSuppliers = canAccess('suppliers');
$defaultView = $canSale ? 'pos' : 'booking';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AISellProduct — Point of Sale</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
* { font-family: Tahoma, 'Segoe UI', sans-serif; font-size: 12px; box-sizing: border-box; }
html, body { height: 100%; margin: 0; }
body { background: #d4d0c8; overflow: hidden; }

.win-inset  { border: 1px solid; border-color: #808080 #ffffff #ffffff #808080; background:#fff; }
.win-raised { border: 1px solid; border-color: #ffffff #808080 #808080 #ffffff; }
.win-panel  { border: 1px solid; border-color: #808080 #ffffff #ffffff #808080; background:#ece9d8; }

.win-titlebar { background: linear-gradient(to right, #0a246a, #3a6ea5); color: white; font-weight: bold; font-size: 12px; padding: 4px 8px; display:flex; align-items:center; justify-content:space-between; }
.win-menubar { background: #d4d0c8; border-bottom: 1px solid #808080; display:flex; flex-wrap:wrap; gap:3px; padding: 3px 3px; }
.win-menu-item {
    padding: 3px 10px; cursor:pointer; font-size:12px;
    border: 1px solid; border-color: #ffffff #808080 #808080 #ffffff; background:#d4d0c8;
}
.win-menu-item:hover, .win-menu-item.active { background:#0a246a; color:white; }
.win-menu-item:active { border-color: #808080 #ffffff #ffffff #808080; }
/* Dropdown's own inner rows read as a plain list, not stacked buttons --
   the boxed look above is for the top-level menubar only. */
.win-dropdown-item { border:none !important; display:block; text-align:left; white-space:nowrap; }

input[type=text], input[type=number], select {
    border: 1px solid; border-color: #808080 #ffffff #ffffff #808080;
    background: #ffff99; padding: 2px 4px; height: 22px; font-size:12px;
    font-family: Tahoma, sans-serif;
}
input[readonly], input.readonly-field { background: #d4d0c8 !important; color: #333; }
input.bill-preview { font-style: italic; }
select { background: #ffff99; }
input:focus, select:focus { outline: 1px solid #0a246a; }

.win-btn {
    background: #d4d0c8; border: 1px solid; border-color: #ffffff #808080 #808080 #ffffff;
    padding: 3px 12px; cursor:pointer; font-size:12px; height:24px;
    font-family: Tahoma, sans-serif; white-space:nowrap; display:inline-flex; align-items:center; gap:4px;
}
.win-btn:hover  { background: #e8e4d8; }
.win-btn:active { border-color: #808080 #ffffff #ffffff #808080; }
.win-btn-blue   { background: #003087; color:white; border-color: #5599cc #002266 #002266 #5599cc; }
.win-btn-blue:hover { background: #004499; }
.win-btn-green  { background: #1a7a1a; color:white; border-color: #44aa44 #0a500a #0a500a #44aa44; }
.win-btn-green:hover { background: #1e8c1e; }

.win-table { width:100%; border-collapse:collapse; font-size:12px; }
.win-table thead tr { background: #d4d0c8; }
.win-table thead th { border: 1px solid #808080; padding: 4px 6px; text-align:left; font-weight:bold; white-space:nowrap; background:#d4d0c8; position:sticky; top:0; }
.win-table tbody tr { background:#fff; }
.win-table tbody tr:nth-child(even) { background: #f5f3ee; }
.win-table tbody tr:hover { background: #c5d5e8 !important; cursor:pointer; }
.win-table tbody tr.row-selected { background: #0a246a !important; color:white; }
.win-table tbody tr.row-selected td { color:white !important; }
.win-table td { border: 1px solid #d0ccc4; padding: 4px 6px; white-space:nowrap; }

.win-section-label { background: #d4d0c8; font-weight:bold; font-size:12px; padding: 3px 8px; border-bottom:1px solid #808080; display:flex; align-items:center; justify-content:space-between; }
.win-scroll { overflow:auto; min-height:0; }

/* Expiry Info panel — same convention as stock_receiving.php's version */
.expiry-urgent { background:#ffe0e0 !important; }
.expiry-soon   { background:#fff3cd !important; }
.expiry-warn   { color:darkred;   font-weight:bold; }
.expiry-ok     { color:darkgreen; }
.field-yellow { background: #ffff99 !important; }
.field-blue   { background: #cce0ff !important; }
.amt-field { text-align:right; font-weight:bold; }

.total-label { font-weight:bold; text-align:right; padding-right:8px; white-space:nowrap; font-size:14px; }

.fkey-badge { display:inline-flex; align-items:center; justify-content:center; width:18px; height:18px; border-radius:50%; background:#0a246a; color:white; font-weight:bold; font-size:11px; }

/* Calculator bar result fields (Total/Disc Amt/Net Total/Balance) — per
   teacher instruction, made larger and switched to a black-background/
   red-text combination so they stand out clearly from the rest of the
   screen. Deliberately scoped to this class alone, not input[readonly]
   generally, so every other readonly field in the app keeps its normal
   grey styling. !important + declared after input[readonly]/.field-yellow
   so it wins over both regardless of which one a given field also has. */
input.calc-highlight { background:#000 !important; color:#ff3333 !important; font-size:20px !important; height:34px !important; padding:2px 8px !important; }

#search-dropdown { position:absolute; z-index:999; background:#fff; border:1px solid #808080; max-height:180px; overflow-y:auto; width:100%; top:100%; left:0; box-shadow: 2px 2px 4px rgba(0,0,0,0.3); }
#search-dropdown div { padding:4px 6px; border-bottom:1px solid #eee; cursor:pointer; font-size:12px; }
#search-dropdown div:hover { background:#0a246a; color:white; }

.nav-active { background:#0a246a !important; color:white !important; }

.win-statusbar { background:#d4d0c8; border-top:1px solid #808080; padding:3px 8px; font-size:12px; display:flex; gap:14px; }
.win-statusbar span { border-right:1px solid #808080; padding-right:12px; }

@media print {
    html, body { margin:0; padding:0; }
    body > * { display:none !important; }
    #print-area { display:block !important; }
}
#print-area { display:none; width:72mm; margin:0 auto; padding:8px; font-family:'Courier New',monospace; font-size:11px; line-height:1.5; }

#toast { position:fixed; bottom:16px; right:16px; z-index:9999; padding:8px 16px; font-weight:bold; font-size:12px; border:1px solid; display:none; }

/* Invoice popup overlay */
#invoice-popup-overlay {
    display:none; position:fixed; top:0; left:0; width:100%; height:100%;
    background:rgba(0,0,0,0.55); z-index:8000;
    justify-content:center; align-items:center;
}
#invoice-popup-overlay.open { display:flex; }
#invoice-popup-box {
    background:#fff; width:370px; max-height:88vh; display:flex; flex-direction:column;
    border:2px solid #0a246a; box-shadow:4px 4px 18px rgba(0,0,0,0.5);
    animation: popIn 0.15s ease;
}
@keyframes popIn { from { transform:scale(0.88); opacity:0; } to { transform:scale(1); opacity:1; } }
#invoice-popup-titlebar {
    background:linear-gradient(to right,#0a246a,#3a6ea5); color:white;
    font-weight:bold; font-size:12px; padding:4px 8px;
    display:flex; align-items:center; justify-content:space-between; cursor:move;
    user-select:none;
}
#invoice-popup-titlebar span.close-x {
    cursor:pointer; font-size:14px; padding:0 4px; font-weight:bold;
}
#invoice-popup-titlebar span.close-x:hover { color:#ffaaaa; }
#invoice-popup-body {
    overflow-y:auto; flex:1; padding:16px;
    font-family:'Courier New',monospace; font-size:11px; line-height:1.6;
}
#invoice-popup-footer {
    padding:6px 8px; border-top:1px solid #ccc; display:flex; gap:6px;
    background:#ece9d8;
}

/* Held bills popup — same visual pattern as the invoice popup */
#held-popup-overlay {
    display:none; position:fixed; top:0; left:0; width:100%; height:100%;
    background:rgba(0,0,0,0.55); z-index:8000;
    justify-content:center; align-items:center;
}
#held-popup-overlay.open { display:flex; }
#held-popup-box {
    background:#fff; width:520px; max-height:80vh; display:flex; flex-direction:column;
    border:2px solid #0a246a; box-shadow:4px 4px 18px rgba(0,0,0,0.5);
    animation: popIn 0.15s ease;
}
#held-popup-titlebar {
    background:linear-gradient(to right,#0a246a,#3a6ea5); color:white;
    font-weight:bold; font-size:12px; padding:4px 8px;
    display:flex; align-items:center; justify-content:space-between; cursor:move;
    user-select:none;
}
#held-popup-titlebar span.close-x { cursor:pointer; font-size:14px; padding:0 4px; font-weight:bold; }
#held-popup-titlebar span.close-x:hover { color:#ffaaaa; }
#held-popup-body { overflow-y:auto; flex:1; padding:8px; }
#held-popup-footer { padding:6px 8px; border-top:1px solid #ccc; display:flex; gap:6px; background:#ece9d8; }

/* Customer Invoices popup — same visual pattern as the held bills popup */
#custinv-popup-overlay {
    display:none; position:fixed; top:0; left:0; width:100%; height:100%;
    background:rgba(0,0,0,0.55); z-index:8000;
    justify-content:center; align-items:center;
}
#custinv-popup-overlay.open { display:flex; }
#custinv-popup-box {
    background:#fff; width:560px; max-height:80vh; display:flex; flex-direction:column;
    border:2px solid #0a246a; box-shadow:4px 4px 18px rgba(0,0,0,0.5);
    animation: popIn 0.15s ease;
}
#custinv-popup-titlebar {
    background:linear-gradient(to right,#0a246a,#3a6ea5); color:white;
    font-weight:bold; font-size:12px; padding:4px 8px;
    display:flex; align-items:center; justify-content:space-between; cursor:move;
    user-select:none;
}
#custinv-popup-titlebar span.close-x { cursor:pointer; font-size:14px; padding:0 4px; font-weight:bold; }
#custinv-popup-titlebar span.close-x:hover { color:#ffaaaa; }
#custinv-popup-body { overflow-y:auto; flex:1; padding:8px; }
#custinv-popup-footer { padding:6px 8px; border-top:1px solid #ccc; display:flex; gap:6px; background:#ece9d8; }
</style>
</head>
<body class="h-screen flex flex-col">

<div id="print-area"></div>

<!-- ===== INVOICE POPUP OVERLAY ===== -->
<div id="invoice-popup-overlay">
    <div id="invoice-popup-box">
        <div id="invoice-popup-titlebar">
            <span>&#x1F4CB; Invoice / Challan</span>
            <span class="close-x" onclick="closeInvoicePopup()">&#x2716;</span>
        </div>
        <div id="invoice-popup-body"></div>
        <div id="invoice-popup-footer">
            <button class="win-btn win-btn-blue" onclick="printInvoicePopup()" style="height:22px;">&#x1F5A8; Print</button>
            <button class="win-btn" onclick="closeInvoicePopup()" style="height:22px;color:darkred;">Close</button>
        </div>
    </div>
</div>

<!-- ===== HELD BILLS POPUP OVERLAY ===== -->
<div id="held-popup-overlay">
    <div id="held-popup-box">
        <div id="held-popup-titlebar">
            <span>&#x23F8; Held Bills — click a row to resume</span>
            <span class="close-x" onclick="closeHeldPopup()">&#x2716;</span>
        </div>
        <div id="held-popup-body">
            <table class="win-table">
                <thead><tr><th>Held At</th><th>Customer</th><th style="text-align:right;">Items</th><th style="text-align:right;">Amount</th><th></th></tr></thead>
                <tbody id="held-bills-body"><tr><td colspan="5" style="text-align:center;padding:10px;color:#888;">No held bills</td></tr></tbody>
            </table>
        </div>
        <div id="held-popup-footer">
            <button class="win-btn" onclick="closeHeldPopup()" style="height:22px;color:darkred;">Close</button>
        </div>
    </div>
</div>

<!-- ===== CUSTOMER INVOICES POPUP OVERLAY ===== -->
<!-- Regular-customer reorder flow: pick a customer above, open this to see
     their past invoices (newest first), click one to see its items, click an
     item to load it into the entry fields (never straight into the cart) so
     the cashier can review/adjust qty or price before clicking "+ Add to
     Bill" themselves. -->
<div id="custinv-popup-overlay">
    <div id="custinv-popup-box">
        <div id="custinv-popup-titlebar">
            <span id="custinv-popup-title">&#x1F4DC; Customer Invoices</span>
            <span class="close-x" onclick="closeCustomerInvoicesPopup()">&#x2716;</span>
        </div>
        <div id="custinv-popup-body">
            <div id="custinv-list-view">
                <table class="win-table">
                    <thead><tr><th>Bill#</th><th>Date</th><th>Type</th><th style="text-align:right;">Net Total</th></tr></thead>
                    <tbody id="custinv-list-body"><tr><td colspan="4" style="text-align:center;padding:10px;color:#888;">No invoices found</td></tr></tbody>
                </table>
            </div>
            <div id="custinv-detail-view" style="display:none;">
                <div style="margin-bottom:6px;">
                    <button class="win-btn" style="height:20px;font-size:11px;" onclick="showCustomerInvoiceList()">&#x2190; Back to invoices</button>
                </div>
                <table class="win-table">
                    <thead><tr><th>Stock No.</th><th>Brand</th><th>Item</th><th>Volume</th><th style="text-align:right;">Qty</th><th style="text-align:right;">Price</th><th style="text-align:right;">Amount</th></tr></thead>
                    <tbody id="custinv-detail-body"></tbody>
                </table>
                <div style="font-size:10px;color:#888;margin-top:6px;">Click an item to load it into the entry fields above — review the quantity/price, then click "+ Add to Bill" yourself.</div>
            </div>
        </div>
        <div id="custinv-popup-footer">
            <button class="win-btn" onclick="closeCustomerInvoicesPopup()" style="height:22px;color:darkred;">Close</button>
        </div>
    </div>
</div>


<div class="win-titlebar">
    <span>&#x1F4A7; AISellProduct — Water Distribution Point of Sale</span>
    <span id="live-clock" style="font-weight:normal;font-size:11px;"></span>
</div>

<div class="win-menubar">
    <?php if ($canSale): ?>
    <span class="win-menu-item<?php echo $defaultView === 'pos' ? ' nav-active' : ''; ?>" id="nav-pos"          onclick="switchView('pos')">Sale</span>
    <span class="win-menu-item"            id="nav-transactions"  onclick="switchView('transactions')">Transactions</span>
    <?php endif; ?>
    <?php if (canAccess('inventory')): ?>
    <span class="win-menu-item"            onclick="window.location='stock_receiving.php'">Stock Receiving</span>
    <span class="win-menu-item"            onclick="window.location='stock_search.php'">Stock Search</span>
    <span class="win-menu-item"            onclick="window.location='manufacture.php'">Manufacture</span>
    <span class="win-menu-item"            onclick="window.location='anoosha/purchase_report.php'">Purchase Report</span>
    <span class="win-menu-item"            onclick="window.location='anoosha/short_items.php'">Short Items</span>
    <span class="win-menu-item"            onclick="window.location='anoosha/search_items.php'">Search Items</span>
    <span class="win-menu-item"            onclick="window.location='zeeshan/stock_search.php'">Group Wise Stock Search</span>
    <span class="win-menu-item"            onclick="window.location='zeeshan/dead_items.php'">Dead Items</span>
    <span class="win-menu-item"            onclick="window.location='qasim/public/purchase_order.php'">Purchase Order</span>
    <span class="win-menu-item"            onclick="window.location='rafia/stock_in_hand.php'">Stock In Hand</span>
    <span class="win-menu-item"            onclick="window.location='rafia/purchase_return_summary.php'">Purchase &amp; Returns</span>
    <span class="win-menu-item"            onclick="window.location='rafia/narcotics.php'">Narcotics Register</span>
    <?php endif; ?>
    <?php if ($canSale): ?>
    <span class="win-menu-item"            onclick="window.location='sale_reports.php'">Sale Reports</span>
    <span class="win-menu-item"            onclick="window.location='sale_items.php'">Sale Items</span>
    <?php endif; ?>
    <?php if ($canBooking): ?>
    <span class="win-menu-item<?php echo $defaultView === 'booking' ? ' nav-active' : ''; ?>" id="nav-booking"       onclick="switchView('booking')">Booking</span>
    <?php endif; ?>
    <?php if ($canSale): ?>
    <span class="win-menu-item"            id="nav-reports"       onclick="switchView('reports')">Reports</span>
    <?php endif; ?>
    <?php if (canAccess('admin_area')): ?>
    <span class="win-menu-item" style="position:relative;color:#5b3a8a;font-weight:bold;" onclick="toggleAdminMenu(event)" title="Admin/Management only">
        Admin Options &#x25BE;
        <div id="admin-dropdown" style="display:none;position:absolute;top:100%;left:0;z-index:50;min-width:170px;background:#d4d0c8;border:1px solid;border-color:#ffffff #808080 #808080 #ffffff;box-shadow:2px 2px 4px rgba(0,0,0,0.3);padding:2px;">
            <?php if (!empty($_SESSION['emp_is_admin'])): ?>
            <span class="win-menu-item win-dropdown-item" onclick="window.location='zeeshan/manage_users.php'" title="Admin only -- creating/deleting staff">Manage Users</span>
            <?php endif; ?>
            <span class="win-menu-item win-dropdown-item" onclick="window.location='admin_dashboard.php'">Dashboard</span>
            <span class="win-menu-item win-dropdown-item" onclick="window.location='reports/admin_reports.php'">Profit Reports</span>
            <span class="win-menu-item win-dropdown-item" onclick="window.location='item_details.php'">Item Details</span>
            <span class="win-menu-item win-dropdown-item" onclick="window.location='qasim/public/sales_report.php'">Sales Report (Qasim)</span>
        </div>
    </span>
    <?php endif; ?>
    <span style="flex:1"></span>
    <span class="win-menu-item" style="color:#555;">Database: <b><?php echo htmlspecialchars($_SESSION['active_db_label'] ?? 'Water Distribution'); ?></b></span>
    <span class="win-menu-item" onclick="window.location='login.php'" title="Pick a different database">Switch Database</span>
    <span class="win-menu-item" style="color:#555;">User: <b><?php echo htmlspecialchars($_SESSION['emp_user_name'] ?? '—'); ?></b></span>
    <span class="win-menu-item" onclick="window.location='logout.php'" title="Sign out" style="color:darkred;">Logout</span>
</div>

<!-- ===================== POS VIEW ===================== -->
<div id="view-pos" style="display:<?php echo $defaultView === 'pos' ? 'flex' : 'none'; ?>; flex-direction:column; flex:1; min-height:0; padding:5px; gap:4px;">

    <!-- Top bar: Bill info + buttons -->
    <div class="win-panel" style="display:flex; align-items:center; gap:8px; padding:4px 8px; flex-wrap:wrap;">
        <div style="display:flex;align-items:center;gap:3px;">
            <span style="font-weight:bold;background:#0a246a;color:white;padding:3px 10px;border:1px solid #000;">Bill#</span>
            <input id="bill-no" type="text" readonly value="—" title="Preview of the next bill number until this sale is actually saved" style="width:90px;font-weight:bold;color:#0a246a;" class="readonly-field" tabindex="-1">
        </div>
        <div style="display:flex;align-items:center;gap:3px;">
            <label style="font-weight:bold;" title="Human-editable reference number — separate from the internal Bill#, can be changed any time including after saving">Bill Ref</label>
            <input id="invoice-reference" type="text" placeholder="Optional ref#" style="width:90px;" onblur="onInvoiceReferenceBlur()">
        </div>
        <div style="display:flex;align-items:center;gap:3px;">
            <label style="font-weight:bold;">Customer</label>
            <input id="cust-select" type="text" list="cust-datalist" placeholder="Walk-in / Type to search…" style="width:150px;" oninput="onCustomerInput()" autocomplete="off">
            <datalist id="cust-datalist"></datalist>
            <button class="win-btn" style="height:22px;font-size:11px;" onclick="openCustomerInvoicesPopup()" title="View this customer's past invoices and quickly load an item from one">&#x1F4DC; Invoices</button>
        </div>
        <div style="display:flex;align-items:center;gap:3px;">
            <label style="font-weight:bold;">Customer Name</label>
            <input id="cust-name" type="text" placeholder="Walk-in customer" style="width:170px;">
        </div>
        <div style="display:flex;align-items:center;gap:3px;">
            <label style="font-weight:bold;">Contact</label>
            <input id="cust-tel" type="text" placeholder="03xx-xxxxxxx" style="width:115px;">
        </div>
        <div style="display:flex;align-items:center;gap:3px;">
            <label style="font-weight:bold;">Date</label>
            <input id="trans-date" type="text" readonly style="width:145px;" class="readonly-field" tabindex="-1">
        </div>
        <div style="display:flex;align-items:center;gap:3px;">
            <label style="font-weight:bold;">Type</label>
            <select id="trans-type" style="width:75px;">
                <option value="Cash">Cash</option>
                <option value="Credit">Credit</option>
                <option value="Card">Card</option>
            </select>
        </div>
        <div style="display:flex;align-items:center;gap:3px;">
            <label style="font-weight:bold;">Branch</label>
            <input id="branch-code" type="text" value="HQ" style="width:42px;">
        </div>
        <div style="margin-left:auto;display:flex;gap:4px;">
            <button class="win-btn win-btn-blue" onclick="resetForm()">&#x2B06; New</button>
            <button class="win-btn win-btn-green" onclick="saveSale()">&#x2714; Save</button>
            <button class="win-btn" onclick="postponeInvoice()" style="background:#b8860b;color:white;border-color:#d4a017 #8b6508 #8b6508 #d4a017;" title="Set this bill aside if the customer needs to grab more items">&#x23F8; Postpone</button>
            <button class="win-btn" onclick="openHeldPopup()" title="Resume a postponed bill">&#x1F4C2; Held (<span id="held-count">0</span>)</button>
            <button class="win-btn" onclick="openInvoicePopup()" style="background:#5b3a8a;color:white;border-color:#9966cc #3d1f6b #3d1f6b #9966cc;">&#x1F4CB; View Challan</button>
            <button class="win-btn" onclick="resetForm()" style="color:darkred;">&#x2716; Cancel</button>
        </div>
    </div>

    <!-- Search box row -->
    <div class="win-panel" style="padding:4px 8px;">
        <div style="display:flex;align-items:flex-end;gap:6px;flex-wrap:wrap;">
            <div style="position:relative;display:flex;flex-direction:column;gap:1px;min-width:230px;">
                <label style="font-weight:bold;">Item Name (Search)</label>
                <input id="item-search" type="text" placeholder="Type to search…" style="width:230px;" oninput="searchItems(this.value)" autocomplete="off">
                <div id="search-dropdown" class="hidden"></div>
            </div>
            <div style="display:flex;flex-direction:column;gap:1px;">
                <label style="font-weight:bold;">Stock No.</label>
                <input id="sel-stock" type="text" readonly style="width:85px;" class="readonly-field field-blue" tabindex="-1">
            </div>
            <div style="display:flex;flex-direction:column;gap:1px;">
                <label style="font-weight:bold;">Volume/Size</label>
                <input id="sel-vol" type="text" readonly style="width:75px;" class="readonly-field" tabindex="-1">
            </div>
            <div style="display:flex;flex-direction:column;gap:1px;">
                <label style="font-weight:bold;">Item Type</label>
                <input id="sel-type" type="text" readonly style="width:65px;" class="readonly-field" tabindex="-1">
            </div>
            <div style="display:flex;flex-direction:column;gap:1px;">
                <label style="font-weight:bold;">Unit Price</label>
                <input id="sel-price" type="number" min="0" step="0.01" style="width:75px;" oninput="recalcLine()" class="amt-field">
            </div>
            <div style="display:flex;flex-direction:column;gap:1px;">
                <label style="font-weight:bold;">Qty</label>
                <input id="sel-qty" type="number" min="1" value="1" style="width:55px;" oninput="recalcLine()">
            </div>
            <div style="display:flex;flex-direction:column;gap:1px;">
                <label style="font-weight:bold;">Amount</label>
                <input id="sel-amount" type="text" readonly style="width:85px;font-weight:bold;color:#0a246a;" class="readonly-field field-yellow amt-field" tabindex="-1">
            </div>
            <div style="display:flex;flex-direction:column;gap:1px;">
                <label style="font-weight:bold;">In-Hand</label>
                <input id="sel-inhand" type="text" readonly style="width:55px;" class="readonly-field" tabindex="-1">
            </div>
            <div style="display:flex;flex-direction:column;gap:1px;">
                <label>&nbsp;</label>
                <button class="win-btn win-btn-blue" onclick="addItemToCart()" style="height:22px;">+ Add to Bill</button>
            </div>
        </div>
    </div>

    <!-- MAIN 3-COLUMN AREA: Cart | Product List | Invoice Preview -->
    <div style="display:flex;gap:4px;flex:1;min-height:0;">

        <!-- LEFT: Bill Items (cart), with the info/lookup block and Calculator
             now pushed up into this same column as a fixed-height footer —
             Bill Items shrinks to make room instead of this footer being a
             separate full-width bar below everything. Sits to the left of
             the Expiry Info panel in the next column, matching the
             MedPharma reference layout. Fields with no real backing data in
             our schema (Max. Disc per user, transaction Status, "Sale Mode")
             stay static "—" labels rather than fabricated values — flagged
             to the user, not silently invented; ask before wiring them to
             something real. -->
        <div class="win-panel" style="flex:1.5;display:flex;flex-direction:column;min-height:0;">
            <div class="win-section-label">
                <span>Bill Items</span>
                <span id="cart-count" style="font-weight:normal;color:#555;">0 item(s)</span>
            </div>
            <div class="win-scroll" style="flex:1;">
                <table class="win-table" style="font-size:13px;" id="cart-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Stock No.</th>
                            <th>Brand Name</th>
                            <th>Item Name</th>
                            <th>Type</th>
                            <th>Volume</th>
                            <th style="text-align:right;">Qty</th>
                            <th style="text-align:right;">Unit Price</th>
                            <th style="text-align:right;">Amount</th>
                            <th>Del</th>
                        </tr>
                    </thead>
                    <tbody id="cart-body">
                        <tr><td colspan="10" style="text-align:center;color:#888;padding:14px;font-size:13px;">No items added yet</td></tr>
                    </tbody>
                </table>
            </div>

            <div style="border-top:1px solid #808080;padding:6px 10px;display:flex;gap:10px;flex-shrink:0;flex-wrap:wrap;">

                <!-- item lookup + info + action buttons — widened (min-width
                     and internal gaps both increased) so it fills more of
                     the footer row instead of leaving a big empty gap before
                     the Calculator block, which stays right-anchored via
                     margin-left:auto below so it keeps lining up under the
                     cart's Amount column. -->
                <div style="display:flex;flex-direction:column;gap:8px;flex-shrink:0;padding-right:16px;border-right:1px solid #808080;min-width:340px;">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span class="fkey-badge" id="cart-item-badge" title="Number of items added to this bill">0</span>
                        <button class="win-btn" style="height:22px;" onclick="loadProductList();toast('Product list refreshed','ok');">List</button>
                        <label style="font-weight:bold;">BarCode</label>
                        <input id="barcode-input" type="text" class="field-yellow" style="width:110px;" placeholder="Scan/type…" onkeydown="if(event.key==='Enter'){event.preventDefault();lookupBarcode();}">
                    </div>
                    <div style="display:flex;gap:20px;font-size:11px;">
                        <span>User: <b><?php echo htmlspecialchars($_SESSION['emp_user_id'] ?? 'admin'); ?></b></span>
                        <span title="No per-user discount limit tracked in the database yet">Max. Disc: <b style="color:#888;">—</b></span>
                        <!-- Not a visible field anymore (replaced by the "User:" label above), but
                             saveSale() and the Enter-key tab order still read this exact id -- kept
                             as a hidden input so removing the old visible box doesn't break either. -->
                        <input type="hidden" id="user-id" value="<?php echo htmlspecialchars($_SESSION['emp_user_id'] ?? 'admin'); ?>">
                    </div>
                    <div style="display:flex;gap:20px;font-size:11px;">
                        <span>Bill#: <b id="invno-display">—</b></span>
                        <span title="No transaction-level Status column in the schema yet">Status: <b style="color:#888;">—</b></span>
                    </div>
                    <div style="font-size:11px;">
                        <label style="display:inline-flex;align-items:center;gap:3px;color:#888;" title="Not wired to anything yet — no matching concept in the schema">
                            <input type="checkbox" disabled style="width:auto;height:auto;"> Sale Mode
                        </label>
                    </div>
                    <div style="font-size:11px;color:#0a246a;font-weight:bold;">
                        StockQTY: <span id="sel-qty-inhand">—</span> &nbsp; ExpDate: <span id="sel-nearest-expiry">—</span>
                    </div>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;">
                        <button class="win-btn" style="height:22px;font-size:11px;" onclick="document.getElementById('expiry-info-label').scrollIntoView({behavior:'smooth',block:'center'});">Expiry Dates</button>
                        <button class="win-btn" style="height:22px;font-size:11px;" onclick="openCalculatorPopup()">Calculator</button>
                        <button class="win-btn" style="height:22px;font-size:11px;color:darkred;" onclick="resetForm()">Cancel</button>
                    </div>
                </div>

                <!-- Calculator: Discount %age/Disc Amt stacked in their own
                     column on the left, Total/Net Total/Cash/Balance stacked
                     to their right (directly under the cart's Amount column)
                     — matches the MedPharma reference layout instead of the
                     old paired-column grid. margin-left:auto pushes the whole
                     block toward the right edge of the footer row, closer to
                     where the Amount column actually sits in the cart table
                     above it — do not remove that, it's load-bearing for the
                     alignment. gap:32px between the two stacks below (was
                     16px) so there's visible breathing room between Discount
                     and Total instead of them crowding together. -->
                <div style="display:flex;flex-direction:column;flex-shrink:0;margin-left:auto;">
                    <span class="win-section-label" style="padding:2px 6px;margin-bottom:4px;">Calculator</span>
                    <div style="display:flex;gap:32px;align-items:flex-start;">
                        <div style="display:flex;flex-direction:column;gap:6px;">
                            <div style="display:flex;align-items:center;gap:4px;">
                                <label class="total-label" style="width:70px;">Disc %age:</label>
                                <input id="disc-pct" type="number" min="0" max="100" value="0" style="width:52px;height:30px;font-size:16px;text-align:center;" oninput="recalcTotals()">
                            </div>
                            <div style="display:flex;align-items:center;gap:4px;">
                                <label class="total-label" style="width:70px;">Disc Amt:</label>
                                <input id="disc-amt" type="text" readonly value="0.00" class="readonly-field amt-field calc-highlight" style="width:85px;" tabindex="-1">
                            </div>
                        </div>
                        <div style="display:flex;flex-direction:column;gap:6px;">
                            <div style="display:flex;align-items:center;gap:4px;">
                                <label class="total-label" style="width:64px;">Total:</label>
                                <input id="gross-total" type="text" readonly value="0.00" class="readonly-field amt-field calc-highlight" style="width:95px;font-weight:bold;" tabindex="-1">
                            </div>
                            <div style="display:flex;align-items:center;gap:4px;">
                                <label class="total-label" style="width:64px;">Net Total:</label>
                                <input id="net-total" type="text" readonly value="0.00" class="readonly-field amt-field calc-highlight" style="width:115px;font-weight:bold;" tabindex="-1">
                            </div>
                            <div style="display:flex;align-items:center;gap:4px;">
                                <label class="total-label" style="width:64px;">Cash:</label>
                                <input id="cash-paid" type="number" min="0" value="0" class="field-yellow amt-field" style="width:90px;height:30px;font-size:16px;font-weight:bold;" oninput="recalcBalance()">
                            </div>
                            <div style="display:flex;align-items:center;gap:4px;">
                                <label class="total-label" style="width:64px;">Balance:</label>
                                <input id="balance-amt" type="text" readonly value="0.00" class="readonly-field amt-field calc-highlight" style="width:115px;font-weight:bold;" tabindex="-1">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MIDDLE: Live Product List, Expiry Info stacked directly below it
             (unchanged — same panel, same data source, still right below
             Available Products), and the Print row now appended at the very
             bottom, stacked horizontally, to the right of the info/calculator
             footer in the Bill Items column. Each panel scrolls internally
             on its own, so a small laptop screen just shows fewer rows
             instead of forcing the whole page to scroll. -->
        <div style="flex:1;min-width:340px;max-width:420px;display:flex;flex-direction:column;gap:4px;min-height:0;">

            <div class="win-panel" style="flex:1.4;display:flex;flex-direction:column;min-height:0;">
                <div class="win-section-label">
                    <span>Available Products</span>
                    <span id="product-count" style="font-weight:normal;color:#555;">—</span>
                </div>
                <div class="win-scroll" style="flex:1;">
                    <table class="win-table">
                        <thead>
                            <tr>
                                <th>Stock#</th>
                                <th>Brand Name</th>
                                <th>Size</th>
                                <th style="text-align:right;">Price</th>
                            </tr>
                        </thead>
                        <tbody id="product-list-body">
                            <tr><td colspan="4" style="text-align:center;color:#888;padding:10px;">Loading products…</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Expiry Info (same idea as the panel on stock_receiving.php) —
                 shows every batch of whichever item was last selected/clicked,
                 system-wide, nearest expiry first. Read-only reference, no
                 editing here. -->
            <div class="win-panel" style="flex:1;display:flex;flex-direction:column;min-height:0;">
                <div class="win-section-label">
                    <span>&#x23F3; Expiry Info</span>
                    <span id="expiry-info-label" style="font-weight:normal;color:#555;"></span>
                </div>
                <div class="win-scroll" style="flex:1;">
                    <table class="win-table" style="font-size:11px;">
                        <thead>
                            <tr>
                                <th>Batch/Inv#</th>
                                <th>Expiry Date</th>
                                <th style="text-align:right;">Qty Avail</th>
                            </tr>
                        </thead>
                        <tbody id="expiry-info-body">
                            <tr><td colspan="3" style="text-align:center;padding:10px;color:#888;font-size:10px;">Select an item to view expiry details.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Print row — sits below Expiry Info, stacked horizontally. -->
            <div class="win-panel" style="flex-shrink:0;padding:6px 8px;display:flex;align-items:center;justify-content:center;gap:10px;">
                <button class="win-btn win-btn-blue" style="height:26px;" onclick="doPrint()">&#x1F5A8; Print</button>
                <div style="font-size:10px;color:#888;" title="No printer-selection feature built yet — nothing here changes what Print actually does">
                    Printer: <label><input type="radio" name="printer-choice" checked disabled style="width:auto;height:auto;"> None</label>
                </div>
                <button class="win-btn" style="height:22px;font-size:11px;" onclick="clearSelection()">Remove</button>
            </div>
        </div>

    </div>

    <!-- Status bar -->
    <div class="win-statusbar">
        <span id="status-msg">Ready &nbsp;|&nbsp; F2=Search &nbsp; F5=Cash &nbsp; F8=Save &nbsp; F9=New &nbsp; F10=Invoice &nbsp; Enter=Next Field</span>
        <span>AISellProduct v1.0</span>
        <span>Margalla 3M Industries — Islamabad</span>
    </div>
</div>

<!-- ===================== TRANSACTIONS VIEW ===================== -->
<div id="view-transactions" style="display:none;flex-direction:column;flex:1;min-height:0;padding:5px;gap:4px;">

    <!-- Search bar -->
    <div class="win-panel" style="padding:5px 8px;display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
        <span style="font-weight:bold;">Search Transactions:</span>
        <select id="txn-search-field" style="width:145px;" onchange="filterTransactions()">
            <option value="all">All Fields</option>
            <option value="Trans_no">Transaction ID</option>
            <option value="Cust_name">Customer Name</option>
            <option value="Cust_telno">Contact No.</option>
            <option value="Trans_date">Date</option>
            <option value="Trans_type">Type (Cash/Credit/Card)</option>
            <option value="User_id">User</option>
        </select>
        <input id="txn-search-input" type="text" placeholder="Type to search…" style="width:220px;" oninput="filterTransactions()">
        <button class="win-btn win-btn-blue" onclick="filterTransactions()" style="height:22px;">Search</button>
        <button class="win-btn" onclick="clearTxnSearch()" style="height:22px;">Clear</button>
        <span id="txn-result-count" style="color:#555;margin-left:6px;"></span>
        <button class="win-btn" style="height:18px;font-size:11px;padding:0 8px;margin-left:auto;" onclick="loadTransactionsFull()">Refresh</button>
    </div>

    <div class="win-panel" style="flex:1;min-height:0;display:flex;flex-direction:column;">
        <div class="win-section-label">
            <span>All Transactions <span style="font-weight:normal;color:#555;">-- click a row to view detail</span></span>
        </div>
        <div class="win-scroll" style="flex:1;max-height:340px;">
            <table class="win-table">
                <thead><tr>
                    <th>Trans#</th><th>Customer</th><th>Contact</th><th>Date</th><th>Type</th>
                    <th style="text-align:right;">Gross</th><th style="text-align:right;">Disc%</th>
                    <th style="text-align:right;">Net</th><th style="text-align:right;">Paid</th>
                    <th style="text-align:right;">Balance</th><th>User</th>
                </tr></thead>
                <tbody id="txn-full-body">
                    <tr><td colspan="11" style="text-align:center;padding:8px;color:#888;">Loading…</td></tr>
                </tbody>
            </table>
        </div>
    </div>
    <div id="txn-detail-panel" style="display:none;" class="win-panel">
        <div class="win-section-label">
            <span>Detail — <span id="txn-detail-header"></span></span>
            <div style="display:flex;gap:5px;">
                <button class="win-btn win-btn-blue" onclick="printDetailReceipt()" style="height:18px;font-size:11px;padding:0 8px;">Print Receipt</button>
                <button class="win-btn" style="height:18px;font-size:11px;padding:0 8px;color:darkred;" onclick="document.getElementById('txn-detail-panel').style.display='none'">Close</button>
            </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 280px;gap:8px;padding:6px;">
            <div class="win-scroll" style="max-height:180px;">
                <table class="win-table">
                    <thead><tr><th>Stock No.</th><th>Brand</th><th>Item</th><th>Volume</th><th style="text-align:right;">Qty</th><th style="text-align:right;">Price</th><th style="text-align:right;">Amount</th></tr></thead>
                    <tbody id="txn-detail-body"></tbody>
                </table>
            </div>
            <div id="txn-receipt" class="win-inset" style="padding:8px;font-family:'Courier New',monospace;font-size:11px;overflow:auto;max-height:180px;white-space:pre;background:#fffef0;"></div>
        </div>
    </div>
</div>

<!-- ===================== SUPPLIERS VIEW ===================== -->
<div id="view-suppliers" style="display:none;flex-direction:column;flex:1;min-height:0;padding:5px;gap:4px;">
    <div class="win-panel" style="flex:1;min-height:0;display:flex;flex-direction:column;">
        <div class="win-section-label">
            <span>Suppliers</span>
            <button class="win-btn" style="height:18px;font-size:11px;padding:0 8px;" onclick="loadSuppliers()">Refresh</button>
        </div>
        <div class="win-scroll" style="flex:1;">
            <table class="win-table">
                <thead><tr><th>Code</th><th>Supplier Name</th><th>Contact Person</th><th>City</th><th>Telephone</th><th>Contact</th><th>Email</th><th>Region</th></tr></thead>
                <tbody id="sup-body"><tr><td colspan="8" style="text-align:center;padding:8px;color:#888;">Loading…</td></tr></tbody>
            </table>
        </div>
    </div>
</div>

<!-- ===================== BOOKING VIEW ===================== -->
<div id="view-booking" style="display:<?php echo $defaultView === 'booking' ? 'flex' : 'none'; ?>;flex-direction:column;flex:1;min-height:0;padding:5px;gap:4px;">
    <div class="win-panel" style="flex:1;min-height:0;display:flex;flex-direction:column;">
        <div class="win-section-label">
            <span>Item Bookings</span>
            <button class="win-btn" style="height:18px;font-size:11px;padding:0 8px;" onclick="loadBookings()">Refresh</button>
        </div>
        <div class="win-scroll" style="flex:1;">
            <table class="win-table">
                <thead><tr><th>ID</th><th>Item Name</th><th style="text-align:right;">Demand Qty</th><th>Booking Date</th><th>Demand Date</th><th>Supplier</th><th>Prod Type</th><th>Status</th><th>Comments</th></tr></thead>
                <tbody id="book-body"><tr><td colspan="9" style="text-align:center;padding:8px;color:#888;">Loading…</td></tr></tbody>
            </table>
        </div>
    </div>
</div>

<!-- ===================== REPORTS VIEW ===================== -->
<div id="view-reports" style="display:none;flex-direction:column;flex:1;min-height:0;padding:5px;gap:4px;">
    <div class="win-panel" style="flex:1;min-height:0;display:flex;flex-direction:column;">
        <div class="win-section-label">
            <span>Sales Summary by Month &amp; Size</span>
            <button class="win-btn" style="height:18px;font-size:11px;padding:0 8px;" onclick="loadReports()">Refresh</button>
        </div>
        <div class="win-scroll" style="flex:1;">
            <table class="win-table">
                <thead><tr><th>Month</th><th>Size</th><th style="text-align:right;">Transactions</th><th style="text-align:right;">Total Sales (Rs.)</th></tr></thead>
                <tbody id="report-body"><tr><td colspan="4" style="text-align:center;padding:8px;color:#888;">Loading…</td></tr></tbody>
            </table>
        </div>
    </div>
</div>

<div id="toast"></div>

<script>
let cart = [];
let selectedItem = null;
let currentDetailHeader = null;
let currentDetailRows = [];
let allProducts = [];
let globalLedger = [];
let clientInfo = { Client_name: 'Margalla 3M Industries' };
let selectedCustomerId = null;
let billSaved = false;

// Shows what the NEXT bill's number will actually be (read-only preview,
// styled distinctly with "(next)" so it's never mistaken for a saved Bill#)
// instead of a bare, uninformative dash. Called on load and whenever the
// form resets; overwritten with the real, confirmed number the moment a
// sale actually saves (see saveSale()/doPrint()).
function refreshBillPreview() {
    fetch('api/get_next_bill_no.php').then(r => r.json()).then(res => {
        if (billSaved) return; // a save landed while this was in flight -- don't clobber it
        const billNoEl = document.getElementById('bill-no');
        const nextNo = res.next_no || 1;
        billNoEl.value = nextNo + ' (next)';
        billNoEl.dataset.trans = nextNo;
        billNoEl.classList.add('bill-preview');
        document.getElementById('invno-display').textContent = nextNo + ' (next)';
    }).catch(() => {}); // preview is a courtesy, not required -- silent no-op on failure
}

function loadClientInfo() {
    fetch('api/get_client_info.php').then(r=>r.json()).then(row => {
        if (row && row.Client_name) clientInfo = row;
    });
}

let allCustomers = [];

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

// Fires on every keystroke — the datalist itself does the as-you-type
// filtering natively; this just detects when the typed text exactly matches
// a suggestion (i.e. the user picked one, or typed the full "ID - Name")
// and auto-fills Name/Contact from that customer. Free-typed text that
// doesn't match anything is left alone — that's a walk-in customer.
function onCustomerInput() {
    const val = document.getElementById('cust-select').value;
    const match = val.match(/^(\d+)\s*-\s*/);
    const cust = match ? allCustomers.find(c => c.Customer_id === parseInt(match[1])) : null;
    if (cust) {
        selectedCustomerId = cust.Customer_id;
        document.getElementById('cust-name').value = cust.Cust_name  || '';
        document.getElementById('cust-tel').value  = cust.Contact_no || '';
    } else {
        selectedCustomerId = null;
    }
}

// Regular-customer reorder flow: shows the selected customer's past
// invoices (newest first), click one to see its items, click an item to
// load it into the entry fields — never straight into the cart, and always
// at today's live price, never the old invoice's price (prices change).
function openCustomerInvoicesPopup() {
    if (!selectedCustomerId) { toast('Select a customer first', 'warn'); return; }
    document.getElementById('custinv-popup-overlay').classList.add('open');
    showCustomerInvoiceList();
    document.getElementById('custinv-list-body').innerHTML = '<tr><td colspan="4" style="text-align:center;padding:10px;color:#888;">Loading…</td></tr>';
    fetch('api/get_customer_invoices.php?customer_id=' + selectedCustomerId)
        .then(r => r.json())
        .then(rows => {
            const tbody = document.getElementById('custinv-list-body');
            tbody.innerHTML = '';
            if (!rows.length) {
                tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;padding:10px;color:#888;">No past invoices for this customer</td></tr>';
                return;
            }
            rows.forEach(row => {
                const tr = document.createElement('tr');
                tr.title = 'Click to view items on this invoice';
                // Some older bulk-loaded historical rows genuinely have NULL
                // Trans_type/Trans_amount (confirmed against the live DB) —
                // shown as an honest "—" rather than a misleading "null" or
                // a fabricated "0.00".
                const type = row.Trans_type || '—';
                const net  = (row.Trans_amount !== null && row.Trans_amount !== undefined) ? parseFloat(row.Trans_amount).toFixed(2) : '—';
                tr.innerHTML = `<td style="font-weight:bold;color:#0a246a;">${row.Trans_no}</td><td>${row.Trans_date}</td><td>${type}</td><td style="text-align:right;font-weight:bold;color:darkgreen;">${net}</td>`;
                tr.onclick = () => showCustomerInvoiceDetail(row.Trans_no);
                tbody.appendChild(tr);
            });
        })
        .catch(() => {
            document.getElementById('custinv-list-body').innerHTML = '<tr><td colspan="4" style="text-align:center;color:darkred;padding:10px;">Could not load invoices</td></tr>';
        });
}

function closeCustomerInvoicesPopup() { document.getElementById('custinv-popup-overlay').classList.remove('open'); }

function showCustomerInvoiceList() {
    document.getElementById('custinv-list-view').style.display = 'block';
    document.getElementById('custinv-detail-view').style.display = 'none';
    document.getElementById('custinv-popup-title').textContent = '\u{1F4DC} Customer Invoices';
}

function showCustomerInvoiceDetail(transNo) {
    fetch('api/get_transaction_detail.php?id=' + transNo).then(r => r.json()).then(res => {
        if (!res.header) { toast('Could not load that invoice', 'err'); return; }
        document.getElementById('custinv-list-view').style.display = 'none';
        document.getElementById('custinv-detail-view').style.display = 'block';
        document.getElementById('custinv-popup-title').textContent = 'Bill #' + res.header.Trans_no + ' — ' + res.header.Trans_date;
        const tbody = document.getElementById('custinv-detail-body');
        tbody.innerHTML = '';
        res.detail.forEach(d => {
            const tr = document.createElement('tr');
            tr.title = 'Click to load this item into the entry fields';
            tr.innerHTML = `<td style="font-weight:bold;color:#0a246a;">${d.stock_number}</td><td style="font-weight:bold;">${d.BRAND_NAME||'—'}</td><td>${d.ITEM_NAME||'—'}</td><td>${d.VOLUME_L||'—'}</td><td style="text-align:right;font-weight:bold;">${d.quantity}</td><td style="text-align:right;">${parseFloat(d.Price_PerItem||0).toFixed(2)}</td><td style="text-align:right;font-weight:bold;color:darkgreen;">${parseFloat(d.amount||0).toFixed(2)}</td>`;
            tr.onclick = () => loadHistoricalItemIntoEntry(d);
            tbody.appendChild(tr);
        });
    });
}

// Loads a past-invoice line item into the same entry fields "Available
// Products" selection uses (selectItem), pre-filled with TODAY'S live
// price/stock data — the historical Price_PerItem is shown for reference in
// the popup but never used here, since prices can have changed since that
// old sale. The historical quantity is carried over as a convenience
// default. This never touches the cart directly — the cashier still has to
// review and click "+ Add to Bill" themselves.
function loadHistoricalItemIntoEntry(d) {
    const current = allProducts.find(p => p.STOCK_NUMBER === d.stock_number);
    if (!current) {
        toast('Stock #' + d.stock_number + ' is no longer an active product', 'warn');
        return;
    }
    selectItem(current);
    document.getElementById('sel-qty').value = d.quantity || 1;
    recalcLine();
    closeCustomerInvoicesPopup();
    toast('Loaded from past invoice — review qty/price, then click "+ Add to Bill"', 'ok');
}

// Bill Ref (Invoice_reference) is editable any time, including after the
// sale is already saved — unlike Bill# (Trans_no), it's not tied to any
// foreign key, so it's safe to patch on an already-saved transaction.
function onInvoiceReferenceBlur() {
    const billNo = document.getElementById('bill-no').value;
    if (!billSaved) return;
    const ref = document.getElementById('invoice-reference').value;
    fetch('api/update_invoice_reference.php', { method:'POST', body:JSON.stringify({ trans_no: billNo, invoice_reference: ref }) })
        .then(r=>r.json())
        .then(res => { if (res.success) toast('Bill Ref updated', 'ok'); else toast('Error: '+(res.error||'Update failed'), 'err'); });
}

function clockTick() {
    const now = new Date();
    document.getElementById('live-clock').textContent = now.toLocaleString('en-GB');
    document.getElementById('trans-date').value = now.toLocaleString('en-GB');
}
clockTick();
setInterval(clockTick, 1000);

const views = ['pos','transactions','suppliers','booking','reports'];

function switchView(v) {
    views.forEach(name => {
        const el  = document.getElementById('view-' + name);
        const btn = document.getElementById('nav-' + name);
        el.style.display = name === v ? 'flex' : 'none';
        if (btn) btn.classList.toggle('nav-active', name === v);
    });
    if (v === 'transactions') loadTransactionsFull();
    if (v === 'suppliers')    loadSuppliers();
    if (v === 'booking')      loadBookings();
    if (v === 'reports')      loadReports();
}


function buildInvoiceHTML() {
    const invoiceRef = document.getElementById('invoice-reference').value;
    const billNo   = invoiceRef || document.getElementById('bill-no').value;
    const cust     = document.getElementById('cust-name').value || 'Walk-in Customer';
    const date     = document.getElementById('trans-date').value;
    const gross    = parseFloat(document.getElementById('gross-total').value) || 0;
    const discPct  = parseFloat(document.getElementById('disc-pct').value) || 0;
    const discAmt  = parseFloat(document.getElementById('disc-amt').value) || 0;
    const net      = parseFloat(document.getElementById('net-total').value) || 0;
    const cash     = parseFloat(document.getElementById('cash-paid').value) || 0;
    const bal      = parseFloat(document.getElementById('balance-amt').value) || 0;
    const totalQty = cart.reduce((s,c)=>s+c.quantity, 0);

    let rows = '';
    cart.forEach(c => {
        rows += `<tr>
            <td style="padding:2px 4px;border-bottom:1px solid #eee;">${c.brand||''} ${c.item||''}</td>
            <td style="padding:2px 4px;border-bottom:1px solid #eee;text-align:center;">${c.quantity}</td>
            <td style="padding:2px 4px;border-bottom:1px solid #eee;text-align:right;">${c.price.toFixed(2)}</td>
            <td style="padding:2px 4px;border-bottom:1px solid #eee;text-align:right;font-weight:bold;">${c.amount.toFixed(2)}</td>
        </tr>`;
    });
    if (!cart.length) rows = '<tr><td colspan="4" style="text-align:center;padding:8px;color:#999;">No items added</td></tr>';

    return `
        <div style="text-align:center;font-family:Arial,sans-serif;margin-bottom:6px;">
            <div style="font-weight:bold;font-size:14px;">${(clientInfo.Client_name||'Margalla 3M Industries').toUpperCase()}</div>
            <div style="font-size:11px;color:#555;">${clientInfo.Address ? clientInfo.Address + (clientInfo.City ? ', ' + clientInfo.City : '') : (clientInfo.City || 'Islamabad')}</div>
            <div style="font-size:11px;color:#555;">${clientInfo.Contact_no ? 'Tel: ' + clientInfo.Contact_no : ''}</div>
        </div>
        <hr style="border:none;border-top:1px dashed #888;margin:6px 0;">
        <div style="font-family:Arial,sans-serif;font-size:12px;margin:3px 0;"><b>Bill #:</b> ${billNo} &nbsp;&nbsp; <b>Date:</b> ${date}</div>
        <div style="font-family:Arial,sans-serif;font-size:12px;margin:3px 0;"><b>Customer:</b> ${cust}</div>
        <hr style="border:none;border-top:1px dashed #888;margin:6px 0;">
        <table style="width:100%;border-collapse:collapse;font-size:11px;">
            <thead>
                <tr style="background:#d4d0c8;">
                    <th style="padding:3px 4px;text-align:left;">Item</th>
                    <th style="padding:3px 4px;text-align:center;">Qty</th>
                    <th style="padding:3px 4px;text-align:right;">Price</th>
                    <th style="padding:3px 4px;text-align:right;">Amt</th>
                </tr>
            </thead>
            <tbody>${rows}</tbody>
        </table>
        <hr style="border:none;border-top:1px dashed #888;margin:6px 0;">
        <div style="font-family:Arial,sans-serif;font-size:11px;display:flex;justify-content:space-between;"><span>Total Qty:</span><span>${totalQty}</span></div>
        <div style="font-family:Arial,sans-serif;font-size:11px;display:flex;justify-content:space-between;"><span>Gross:</span><span>Rs. ${gross.toFixed(2)}</span></div>
        <div style="font-family:Arial,sans-serif;font-size:11px;display:flex;justify-content:space-between;color:darkred;"><span>Discount (${discPct}%):</span><span>- Rs. ${discAmt.toFixed(2)}</span></div>
        <hr style="border:none;border-top:1px dashed #888;margin:4px 0;">
        <div style="font-family:Arial,sans-serif;font-size:14px;font-weight:bold;display:flex;justify-content:space-between;color:#003087;"><span>Net Total:</span><span>Rs. ${net.toFixed(2)}</span></div>
        <div style="font-family:Arial,sans-serif;font-size:12px;display:flex;justify-content:space-between;"><span>Cash Paid:</span><span>Rs. ${cash.toFixed(2)}</span></div>
        <div style="font-family:Arial,sans-serif;font-size:14px;font-weight:bold;display:flex;justify-content:space-between;color:darkgreen;"><span>Balance:</span><span>Rs. ${bal.toFixed(2)}</span></div>
        <hr style="border:none;border-top:1px dashed #888;margin:6px 0;">
        <div style="text-align:center;font-size:10px;color:darkred;font-weight:bold;font-family:Arial,sans-serif;">
            The items bought can not be refunded.
        </div>
        <div style="text-align:center;font-size:10px;color:#666;font-family:Arial,sans-serif;margin-top:2px;">
            Please check all items before leaving.<br>Thank You &mdash; AISellProduct Software
        </div>
        <hr style="border:none;border-top:1px solid #333;margin:6px 0;">
        <div style="text-align:center;font-size:10px;color:#666;font-family:Arial,sans-serif;">
            Software by Fast NUCES Students
        </div>`;
}

function openInvoicePopup() {
    document.getElementById('invoice-popup-body').innerHTML = buildInvoiceHTML();
    document.getElementById('invoice-popup-overlay').classList.add('open');
}

function closeInvoicePopup() {
    document.getElementById('invoice-popup-overlay').classList.remove('open');
}

function printInvoicePopup() {
    const content = document.getElementById('invoice-popup-body').innerHTML;
    const w = window.open('','_blank','width=420,height=600');
    w.document.write(`<!DOCTYPE html><html><head><title>Invoice</title>
        <style>body{font-family:'Courier New',monospace;font-size:11px;padding:12px;margin:0;}
        @media print{body{padding:0;}}</style></head>
        <body>${content}<script>window.onload=function(){window.print();window.close();}<\/script></body></html>`);
    w.document.close();
}

function makeDraggable(boxId, barId) {
    const box  = document.getElementById(boxId || 'invoice-popup-box');
    const bar  = document.getElementById(barId || 'invoice-popup-titlebar');
    let dragging=false, ox=0, oy=0;
    bar.addEventListener('mousedown', e => {
        dragging=true;
        ox = e.clientX - box.offsetLeft;
        oy = e.clientY - box.offsetTop;
        box.style.position='absolute';
    });
    document.addEventListener('mousemove', e => {
        if (!dragging) return;
        box.style.left = (e.clientX - ox) + 'px';
        box.style.top  = (e.clientY - oy) + 'px';
    });
    document.addEventListener('mouseup', () => dragging = false);
}

document.getElementById('invoice-popup-overlay').addEventListener('click', function(e) {
    if (e.target === this) closeInvoicePopup();
});
document.getElementById('held-popup-overlay').addEventListener('click', function(e) {
    if (e.target === this) closeHeldPopup();
});
document.getElementById('custinv-popup-overlay').addEventListener('click', function(e) {
    if (e.target === this) closeCustomerInvoicesPopup();
});

// ---------- Postpone / Held Bills (browser-local only — nothing is written
// to the database until a held bill is recalled and actually saved) ----------
// Scoped per active database so switching between Water and Med Stock data
// never mixes held bills from one dataset into the other.
const HELD_BILLS_KEY = 'aisellh2o_held_bills_' + <?php echo json_encode($_SESSION['active_db'] ?? 'default'); ?>;
let heldBills = JSON.parse(localStorage.getItem(HELD_BILLS_KEY) || '[]');

function persistHeldBills() {
    localStorage.setItem(HELD_BILLS_KEY, JSON.stringify(heldBills));
    document.getElementById('held-count').textContent = heldBills.length;
}

function postponeInvoice() {
    if (!cart.length) { toast('Add items to the bill first','warn'); return; }
    heldBills.push({
        heldAt: new Date().toLocaleString('en-GB'),
        cart: cart,
        custName: document.getElementById('cust-name').value,
        custTel:  document.getElementById('cust-tel').value,
        selectedCustomerId: selectedCustomerId,
        invoiceReference: document.getElementById('invoice-reference').value,
        transType: document.getElementById('trans-type').value,
        branchCode: document.getElementById('branch-code').value,
        discPct:  document.getElementById('disc-pct').value,
        cashPaid: document.getElementById('cash-paid').value,
    });
    persistHeldBills();
    resetForm();
    toast('Bill postponed — resume it from Held ('+heldBills.length+')','ok');
}

function openHeldPopup() {
    renderHeldBillsList();
    document.getElementById('held-popup-overlay').classList.add('open');
}
function closeHeldPopup() { document.getElementById('held-popup-overlay').classList.remove('open'); }

function renderHeldBillsList() {
    const tbody = document.getElementById('held-bills-body');
    tbody.innerHTML = '';
    if (!heldBills.length) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:10px;color:#888;">No held bills</td></tr>';
        return;
    }
    heldBills.forEach((h, i) => {
        const amount = h.cart.reduce((s,c)=>s+c.amount, 0);
        const tr = document.createElement('tr');
        tr.style.cursor = 'pointer';
        tr.innerHTML = `
            <td>${h.heldAt}</td>
            <td>${h.custName || 'Walk-in'}</td>
            <td style="text-align:right;">${h.cart.length}</td>
            <td style="text-align:right;font-weight:bold;">${amount.toFixed(2)}</td>
            <td style="text-align:center;"><button class="win-btn win-btn-red" onclick="deleteHeldBill(${i},event)" style="height:18px;font-size:11px;padding:0 6px;">X</button></td>`;
        tr.onclick = (e) => { if (e.target.tagName === 'BUTTON') return; recallHeldBill(i); };
        tbody.appendChild(tr);
    });
}

function recallHeldBill(idx) {
    const h = heldBills[idx];
    if (!h) return;
    if (cart.length && !confirm('This will replace the current unsaved bill. Continue?')) return;
    cart = h.cart;
    document.getElementById('cust-name').value = h.custName || '';
    document.getElementById('cust-tel').value  = h.custTel  || '';
    selectedCustomerId = h.selectedCustomerId || null;
    document.getElementById('cust-select').value = selectedCustomerId ? (selectedCustomerId + ' - ' + (h.custName||'')) : '';
    document.getElementById('invoice-reference').value = h.invoiceReference || '';
    document.getElementById('trans-type').value  = h.transType  || 'Cash';
    document.getElementById('branch-code').value = h.branchCode || 'HQ';
    document.getElementById('disc-pct').value    = h.discPct    || 0;
    document.getElementById('cash-paid').value   = h.cashPaid   || 0;
    renderCart(); recalcTotals();
    heldBills.splice(idx, 1);
    persistHeldBills();
    closeHeldPopup();
    toast('Held bill resumed','ok');
    setStatus('Resumed a postponed bill — review and Save');
}

function deleteHeldBill(idx, e) {
    if (e) e.stopPropagation();
    if (!confirm('Discard this held bill? This cannot be undone.')) return;
    heldBills.splice(idx, 1);
    persistHeldBills();
    renderHeldBillsList();
}

function setupKeyboardNav() {
    const fieldOrder = ['cust-name','cust-tel','trans-type','branch-code','item-search','sel-price','sel-qty','disc-pct','cash-paid','user-id'];

    document.addEventListener('keydown', function(e) {
        const active = document.activeElement;
        if (!active) return;
        const id = active.id;

        if (e.key === 'Escape') {
            closeInvoicePopup();
            return;
        }

        const onProductRow = active.tagName === 'TR' && active.closest('#product-list-body');

        if (e.key === 'ArrowRight' || e.key === 'Enter') {
            if (id === 'item-search') {
                e.preventDefault();
                clearTimeout(searchTimer);
                performSearch(active.value).then(() => {
                    const firstRow = document.querySelector('#product-list-body tr[tabindex]');
                    if (firstRow) { firstRow.focus(); firstRow.click(); }
                });
                return;
            }
            if (onProductRow) {
                e.preventDefault();
                document.getElementById('sel-price').focus();
                return;
            }
            if (id === 'sel-price') {
                e.preventDefault();
                document.getElementById('sel-qty').focus();
                return;
            }
            if (id === 'sel-qty') {
                e.preventDefault();
                addItemToCart();
                document.getElementById('item-search').focus();
                return;
            }
            if (id === 'cash-paid') {
                e.preventDefault();
                saveSale();
                return;
            }
            const idx = fieldOrder.indexOf(id);
            if (idx !== -1 && idx < fieldOrder.length - 1) {
                e.preventDefault();
                document.getElementById(fieldOrder[idx + 1]).focus();
            }
        }

        if (e.key === 'ArrowLeft') {
            if (id === 'sel-qty') { e.preventDefault(); document.getElementById('sel-price').focus(); return; }
            if (id === 'sel-price') { e.preventDefault(); document.getElementById('item-search').focus(); return; }
            const idx = fieldOrder.indexOf(id);
            if (idx > 0) { e.preventDefault(); document.getElementById(fieldOrder[idx - 1]).focus(); }
        }

        if (e.key === 'ArrowDown') {
            if (id === 'item-search') {
                e.preventDefault();
                clearTimeout(searchTimer);
                performSearch(active.value).then(() => {
                    const firstRow = document.querySelector('#product-list-body tr[tabindex]');
                    if (firstRow) { firstRow.focus(); firstRow.click(); }
                });
                return;
            }
            const rows = Array.from(document.querySelectorAll('#product-list-body tr[tabindex]'));
            const idx  = rows.indexOf(active);
            if (idx !== -1 && rows[idx+1]) { e.preventDefault(); rows[idx+1].focus(); rows[idx+1].click(); }
        }

        if (e.key === 'ArrowUp') {
            const rows = Array.from(document.querySelectorAll('#product-list-body tr[tabindex]'));
            const idx  = rows.indexOf(active);
            if (idx > 0)  { e.preventDefault(); rows[idx-1].focus(); rows[idx-1].click(); }
            if (idx === 0){ e.preventDefault(); document.getElementById('item-search').focus(); }
        }

        if (e.key === 'F2')  { e.preventDefault(); document.getElementById('item-search').focus(); }
        if (e.key === 'F5')  { e.preventDefault(); document.getElementById('cash-paid').focus(); }
        if (e.key === 'F8')  { e.preventDefault(); saveSale(); }
        if (e.key === 'F9')  { e.preventDefault(); resetForm(); }
        if (e.key === 'F10') { e.preventDefault(); openInvoicePopup(); }
    });
}

function loadProductList() {
    fetch('api/search_items.php?q=')
        .then(r => r.json())
        .then(data => {
            allProducts = data;
            renderProductList(data);
        })
        .catch(() => {
            document.getElementById('product-list-body').innerHTML =
                '<tr><td colspan="4" style="text-align:center;color:darkred;padding:10px;">Could not load products — check DB connection</td></tr>';
        });
}

function renderProductList(data) {
    const tbody = document.getElementById('product-list-body');
    const countEl = document.getElementById('product-count');
    tbody.innerHTML = '';
    countEl.textContent = data.length + ' item(s)';
    if (!data.length) {
        tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;color:#888;padding:10px;">No products found</td></tr>';
        return;
    }
    data.forEach(item => {
        const tr = document.createElement('tr');
        tr.setAttribute('tabindex', '0');
        const outOfStock = (parseInt(item.QTY_INHAND) || 0) <= 0;
        const nameCell = (item.BRAND_NAME||'—') + (outOfStock ? ' <span style="color:#8b0000;font-weight:bold;">(Out of Stock)</span>' : '');
        if (outOfStock) tr.style.color = '#888';
        tr.innerHTML = `<td style="font-weight:bold;color:#0a246a;">${item.STOCK_NUMBER}</td><td>${nameCell}</td><td>${item.SIZE_DESC||item.ITEM_NAME||'—'}</td><td style="text-align:right;">${parseFloat(item.PRICE||0).toFixed(2)}</td>`;
        tr.onclick = () => {
            document.querySelectorAll('#product-list-body tr').forEach(r => r.classList.remove('row-selected'));
            tr.classList.add('row-selected');
            selectItem(item);
        };
        tbody.appendChild(tr);
    });
}

let searchTimer = null;

// Runs the search immediately (no debounce) and returns a promise that
// resolves once the product table has actually been re-rendered — used by
// the Enter-key handler so it never jumps into the table before the matching
// results have arrived.
function performSearch(q) {
    const dd = document.getElementById('search-dropdown');
    dd.classList.add('hidden');
    if (!q.trim()) { renderProductList(allProducts); return Promise.resolve(); }
    return fetch('api/search_items.php?q=' + encodeURIComponent(q))
        .then(r => r.json())
        .then(data => renderProductList(data));
}

function searchItems(q) {
    clearTimeout(searchTimer);
    const dd = document.getElementById('search-dropdown');
    dd.classList.add('hidden');
    if (!q.trim()) { renderProductList(allProducts); return; }
    searchTimer = setTimeout(() => performSearch(q), 250);
}

function selectItem(item) {
    selectedItem = item;
    document.getElementById('item-search').value  = (item.BRAND_NAME||'') + ' — ' + (item.ITEM_NAME||'');
    document.getElementById('sel-stock').value    = item.STOCK_NUMBER;
    document.getElementById('sel-vol').value      = (item.VOLUME_L||'') + (item.SIZE_DESC ? ' / '+item.SIZE_DESC : '');
    document.getElementById('sel-type').value     = item.ITEM_TYPE||'';
    document.getElementById('sel-price').value    = parseFloat(item.PRICE||0).toFixed(2);
    document.getElementById('sel-inhand').value   = item.QTY_INHAND;
    document.getElementById('sel-qty').value      = 1;
    recalcLine();
    renderExpiryInfo(item.STOCK_NUMBER);
    updateStockExpirySummary(item.STOCK_NUMBER, item.QTY_INHAND);
    // Item search now returns out-of-stock items too (so they're findable),
    // but they still can't be sold -- addItemToCart() enforces this as a
    // hard block; this is just the immediate, upfront warning on selection
    // so the cashier finds out before typing a quantity, not after.
    if ((parseInt(item.QTY_INHAND) || 0) <= 0) {
        toast('Out of stock — 0 in hand, cannot sell this item', 'err');
        setStatus('Selected: ' + (item.BRAND_NAME||'') + ' — OUT OF STOCK');
    } else {
        setStatus('Item selected: ' + (item.BRAND_NAME||'') + ' ' + (item.ITEM_NAME||''));
    }
}

// Compact "StockQTY / ExpDate" line — the same two pieces of data already
// shown elsewhere (In-Hand field, Expiry Info panel), just combined into one
// line the way the reference layout does it. Nearest expiry = earliest date
// among this item's batches with stock left, from the same globalLedger
// already loaded for the Expiry Info panel -- no separate fetch needed.
function updateStockExpirySummary(stockNumber, qtyInHand) {
    document.getElementById('sel-qty-inhand').textContent = (qtyInHand != null) ? qtyInHand : '—';
    const batches = globalLedger.filter(r => r.STOCK_NUMBER === stockNumber && (parseInt(r.ITEMS_AVAILABLE)||0) > 0);
    if (!batches.length) { document.getElementById('sel-nearest-expiry').textContent = '—'; return; }
    const nearest = batches.reduce((a,b) => new Date(a.EXPIRY_DATE) < new Date(b.EXPIRY_DATE) ? a : b);
    document.getElementById('sel-nearest-expiry').textContent = nearest.EXPIRY_DATE ? new Date(nearest.EXPIRY_DATE).toLocaleDateString('en-GB') : '—';
}

// Barcode field — reuses search_items.php, which now also matches BARCODE
// exactly (see that file's comment). Auto-selects the first/only match,
// same as pressing Enter in the main item-search box.
function lookupBarcode() {
    const val = document.getElementById('barcode-input').value.trim();
    if (!val) return;
    fetch('api/search_items.php?q=' + encodeURIComponent(val)).then(r=>r.json()).then(rows=>{
        if (!rows.length) { toast('No item found for barcode ' + val, 'warn'); return; }
        selectItem(rows[0]);
        document.getElementById('barcode-input').value = '';
        toast('Item found: ' + (rows[0].BRAND_NAME||'') + ' ' + (rows[0].ITEM_NAME||''), 'ok');
    });
}

// "Remove" — clears the item currently loaded in the entry fields (search
// result not yet added to the bill). Does not touch the cart -- cart rows
// already have their own per-row Del button for that.
function clearSelection() {
    selectedItem = null;
    document.getElementById('item-search').value = '';
    document.getElementById('sel-stock').value = '';
    document.getElementById('sel-vol').value = '';
    document.getElementById('sel-type').value = '';
    document.getElementById('sel-price').value = '';
    document.getElementById('sel-inhand').value = '';
    document.getElementById('sel-qty').value = 1;
    document.getElementById('sel-amount').value = '';
    document.getElementById('sel-qty-inhand').textContent = '—';
    document.getElementById('sel-nearest-expiry').textContent = '—';
    document.querySelectorAll('#product-list-body tr').forEach(r => r.classList.remove('row-selected'));
    setStatus('Selection cleared');
}

// Simple standalone calculator popup — pure UI utility, no data behind it,
// matches the reference's "Calculator" button.
function openCalculatorPopup() {
    let existing = document.getElementById('calc-popup-overlay');
    if (existing) { existing.remove(); }
    const overlay = document.createElement('div');
    overlay.id = 'calc-popup-overlay';
    overlay.className = 'popup-overlay open';
    overlay.innerHTML = `
        <div class="popup-box" style="width:220px;">
            <div class="popup-titlebar"><span>Calculator</span><span class="close-x" onclick="document.getElementById('calc-popup-overlay').remove()">&#x2716;</span></div>
            <div class="popup-body">
                <input id="calc-display" type="text" readonly class="readonly-field" style="width:100%;text-align:right;font-size:18px;height:32px;margin-bottom:6px;" value="0">
                <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:4px;">
                    ${['7','8','9','/','4','5','6','*','1','2','3','-','0','.','=','+'].map(k =>
                        `<button class="win-btn" style="height:32px;" onclick="calcPress('${k}')">${k}</button>`).join('')}
                    <button class="win-btn" style="height:32px;grid-column:span 4;color:darkred;" onclick="calcPress('C')">Clear</button>
                </div>
            </div>
        </div>`;
    document.body.appendChild(overlay);
    overlay.addEventListener('click', e => { if (e.target === overlay) overlay.remove(); });
}
let calcExpr = '';
function calcPress(k) {
    const disp = document.getElementById('calc-display');
    if (k === 'C') { calcExpr = ''; disp.value = '0'; return; }
    if (k === '=') {
        try { disp.value = Function('"use strict"; return (' + calcExpr + ')')(); calcExpr = String(disp.value); }
        catch (e) { disp.value = 'Error'; calcExpr = ''; }
        return;
    }
    calcExpr += k;
    disp.value = calcExpr;
}

// Loaded once at startup — same system-wide batch ledger stock_receiving.php
// uses for its Expiry Info panel, just reused here read-only.
function loadGlobalLedger() {
    fetch('api/get_stock_expiry_panel.php').then(r=>r.json()).then(rows=>{
        globalLedger = rows;
    }).catch(() => { globalLedger = []; });
}

function expiryTdClass(dateStr) {
    if (!dateStr) return '';
    const days = Math.ceil((new Date(dateStr) - new Date()) / (1000*60*60*24));
    if (days <= 30) return 'expiry-warn';
    if (days <= 90) return '';
    return 'expiry-ok';
}

function expiryRowClass(dateStr) {
    if (!dateStr) return '';
    const days = Math.ceil((new Date(dateStr) - new Date()) / (1000*60*60*24));
    if (days <= 30) return 'expiry-urgent';
    if (days <= 90) return 'expiry-soon';
    return '';
}

// Shows every batch (system-wide) of whichever item was last selected,
// nearest expiry first — mirrors stock_receiving.php's Expiry Info panel.
function renderExpiryInfo(stockNumber) {
    const tbody = document.getElementById('expiry-info-body');
    const label = document.getElementById('expiry-info-label');
    tbody.innerHTML = '';
    const rows = globalLedger
        .filter(r => r.STOCK_NUMBER === stockNumber && (parseInt(r.ITEMS_AVAILABLE)||0) > 0)
        .map(r => ({ batchInv: (r.BATCH_NO||'—') + ' / #' + r.Invoice_no, expiry: r.EXPIRY_DATE, qtyAvail: parseInt(r.ITEMS_AVAILABLE)||0 }))
        .sort((a,b) => new Date(a.expiry) - new Date(b.expiry));
    label.textContent = 'Stock #' + stockNumber;
    if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;padding:10px;color:#888;font-size:10px;">No batches on hand for this item.</td></tr>';
        return;
    }
    rows.forEach(r => {
        const tr = document.createElement('tr');
        tr.className = expiryRowClass(r.expiry);
        const tdClass = expiryTdClass(r.expiry);
        tr.innerHTML = `<td>${r.batchInv}</td><td class="${tdClass}">${r.expiry ? new Date(r.expiry).toLocaleDateString('en-GB') : '—'}</td><td style="text-align:right;">${r.qtyAvail}</td>`;
        tbody.appendChild(tr);
    });
}

function recalcLine() {
    const qty   = parseFloat(document.getElementById('sel-qty').value)   || 0;
    const price = parseFloat(document.getElementById('sel-price').value) || 0;
    document.getElementById('sel-amount').value = (qty * price).toFixed(2);
}

function addItemToCart() {
    if (!selectedItem) { toast('Select an item first','warn'); return; }
    const qty   = parseInt(document.getElementById('sel-qty').value)     || 0;
    const price = parseFloat(document.getElementById('sel-price').value) || 0;
    if (qty <= 0)   { toast('Quantity must be > 0','warn'); return; }
    if (price <= 0) { toast('Price must be > 0','warn');    return; }
    const existing = cart.find(c => c.stock_number === selectedItem.STOCK_NUMBER);
    const alreadyInCart = existing ? existing.quantity : 0;
    const inHand = parseInt(selectedItem.QTY_INHAND) || 0;
    if (alreadyInCart + qty > inHand) {
        toast('Only ' + inHand + ' in hand' + (alreadyInCart ? ' (' + alreadyInCart + ' already in this bill)' : ''), 'warn');
        return;
    }
    if (existing) { existing.quantity += qty; existing.amount = existing.quantity * existing.price; }
    else cart.push({ stock_number:selectedItem.STOCK_NUMBER, brand:selectedItem.BRAND_NAME, item:selectedItem.ITEM_NAME, type:selectedItem.ITEM_TYPE, volume:selectedItem.VOLUME_L, quantity:qty, price:price, amount:qty*price });
    renderCart(); recalcTotals();
    selectedItem = null;
    document.getElementById('item-search').value = '';
    document.getElementById('sel-stock').value = '';
    document.getElementById('sel-vol').value = '';
    document.getElementById('sel-type').value = '';
    document.getElementById('sel-price').value = '';
    document.getElementById('sel-inhand').value = '';
    document.getElementById('sel-qty').value = 1;
    document.getElementById('sel-amount').value = '';
    document.getElementById('sel-qty-inhand').textContent = '—';
    document.getElementById('sel-nearest-expiry').textContent = '—';
    document.querySelectorAll('#product-list-body tr').forEach(r => r.classList.remove('row-selected'));
    setStatus('Item added to bill');
}

function removeFromCart(idx) { cart.splice(idx,1); renderCart(); recalcTotals(); }

// Click a cart row to pull that line back into the entry fields above for
// editing (adjust qty/price, then Add to Bill again to re-confirm it) —
// removes it from the cart in the meantime so it isn't double-counted.
function editCartItem(idx) {
    const item = cart[idx];
    if (!item) return;
    const product = allProducts.find(p => p.STOCK_NUMBER === item.stock_number);
    selectedItem = {
        STOCK_NUMBER: item.stock_number,
        BRAND_NAME:   item.brand,
        ITEM_NAME:    item.item,
        ITEM_TYPE:    item.type,
        VOLUME_L:     item.volume,
        PRICE:        item.price,
        QTY_INHAND:   product ? product.QTY_INHAND : item.quantity
    };
    cart.splice(idx, 1);
    renderCart(); recalcTotals();
    document.getElementById('item-search').value = (item.brand||'') + ' — ' + (item.item||'');
    document.getElementById('sel-stock').value  = item.stock_number;
    document.getElementById('sel-vol').value    = item.volume || '';
    document.getElementById('sel-type').value   = item.type || '';
    document.getElementById('sel-price').value  = item.price.toFixed(2);
    document.getElementById('sel-inhand').value = selectedItem.QTY_INHAND;
    document.getElementById('sel-qty').value    = item.quantity;
    recalcLine();
    renderExpiryInfo(item.stock_number);
    updateStockExpirySummary(item.stock_number, selectedItem.QTY_INHAND);
    setStatus('Editing ' + (item.brand||'') + ' ' + (item.item||'') + ' — adjust and click Add to Bill');
}

function renderCart() {
    const tbody = document.getElementById('cart-body');
    tbody.innerHTML = '';
    document.getElementById('cart-count').textContent = cart.length + ' item(s)';
    if (!cart.length) {
        tbody.innerHTML = '<tr><td colspan="10" style="text-align:center;color:#888;padding:14px;font-size:13px;">No items added yet</td></tr>';
        return;
    }
    cart.forEach((item, i) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${i+1}</td>
            <td style="font-weight:bold;color:#0a246a;">${item.stock_number}</td>
            <td style="font-weight:bold;">${item.brand||'—'}</td>
            <td>${item.item||'—'}</td>
            <td>${item.type||'—'}</td>
            <td>${item.volume||'—'}</td>
            <td style="text-align:right;font-weight:bold;">${item.quantity}</td>
            <td style="text-align:right;">${item.price.toFixed(2)}</td>
            <td style="text-align:right;font-weight:bold;color:#0a246a;">${item.amount.toFixed(2)}</td>
            <td style="text-align:center;"><button class="win-btn" onclick="removeFromCart(${i})" style="height:18px;font-size:11px;padding:0 6px;color:darkred;">X</button></td>`;
        tr.style.cursor = 'pointer';
        tr.title = 'Click to revisit this item';
        tr.onclick = (e) => { if (e.target.tagName === 'BUTTON') return; editCartItem(i); };
        tbody.appendChild(tr);
    });
}

function recalcTotals() {
    const gross   = cart.reduce((s,c)=>s+c.amount,0);
    const discPct = parseFloat(document.getElementById('disc-pct').value) || 0;
    const discAmt = gross * discPct / 100;
    const net     = gross - discAmt;
    document.getElementById('gross-total').value = gross.toFixed(2);
    document.getElementById('disc-amt').value    = discAmt.toFixed(2);
    document.getElementById('net-total').value   = net.toFixed(2);
    document.getElementById('cart-item-badge').textContent = cart.length;
    recalcBalance();
}

function recalcBalance() {
    const net  = parseFloat(document.getElementById('net-total').value) || 0;
    const cash = parseFloat(document.getElementById('cash-paid').value) || 0;
    const bal  = cash - net;
    document.getElementById('balance-amt').value = bal.toFixed(2);
    document.getElementById('balance-amt').style.color = bal < 0 ? 'darkred' : 'darkgreen';
}

// Shared save logic — used by the Save button (save only) and by Print
// (save-first-if-needed, then print). Returns the fetch promise so callers
// can chain their own success handling (toast/print) without duplicating
// validation or the request itself.
// Resolves to null when a client-side check fails (already toasted here, so
// callers should treat null as "stop silently"), otherwise resolves to the
// server's JSON response.
function performSave() {
    if (!cart.length) { toast('Add items to the bill first','warn'); return Promise.resolve(null); }
    const gross     = cart.reduce((s,c)=>s+c.amount,0);
    const discPct   = parseFloat(document.getElementById('disc-pct').value) || 0;
    const net       = gross - (gross * discPct / 100);
    const paid      = parseFloat(document.getElementById('cash-paid').value) || 0;
    const transType = document.getElementById('trans-type').value;

    // Cash/Card sales must be paid in full — only Credit is allowed to carry
    // an outstanding (negative) balance, since that's the whole point of credit.
    if (transType !== 'Credit' && (paid - net) < 0) {
        toast('Cash/Card sales cannot be saved with a negative balance — collect full payment or switch Type to Credit', 'warn');
        document.getElementById('cash-paid').focus();
        return Promise.resolve(null);
    }

    const payload = {
        cust_name:      document.getElementById('cust-name').value,
        cust_telno:     document.getElementById('cust-tel').value,
        customer_id:    selectedCustomerId,
        invoice_reference: document.getElementById('invoice-reference').value,
        trans_type:     document.getElementById('trans-type').value,
        disc_percentage:discPct, gross_amount:gross, trans_amount:net,
        paid_amount:paid, balance_amount:paid-net,
        user_id:document.getElementById('user-id').value,
        tax_status:'N',
        items: cart.map(c=>({ stock_number:c.stock_number, quantity:c.quantity, price:c.price, amount:c.amount }))
    };
    setStatus('Saving…');
    return fetch('api/save_transaction.php', { method:'POST', body:JSON.stringify(payload) }).then(r=>r.json());
}

function saveSale() {
    performSave().then(res => {
        if (!res) return;
        if (res.success) {
            billSaved = true;
            const billNoEl = document.getElementById('bill-no');
            billNoEl.value = res.trans_no;
            billNoEl.classList.remove('bill-preview');
            document.getElementById('invno-display').textContent = res.trans_no;
            toast('Sale saved — Bill #' + res.trans_no,'ok');
            setStatus('Sale saved — Bill #' + res.trans_no);
        } else {
            toast('Error: '+(res.error||'Unknown'),'err'); setStatus('Save failed');
        }
    });
}

function buildReceiptText(header, detail) {
    const line  = '--------------------------------';
    const dline = '================================';
    let t = '';
    const companyName = (clientInfo.Client_name||'Margalla 3M Industries').toUpperCase();
    t += companyName.padStart(Math.floor((32+companyName.length)/2)).padEnd(32) + '\n';
    t += '        AISellProduct — Islamabad\n';
    t += dline + '\n';
    t += 'Bill #: ' + (header.Invoice_reference || header.Trans_no || '—') + '\n';
    t += 'Date  : ' + (header.Trans_date||new Date().toLocaleString('en-GB')) + '\n';
    t += 'Cust  : ' + (header.Cust_name||'Walk-in Customer') + '\n';
    t += 'Contact: ' + (header.Cust_telno||'—') + '\n';
    t += 'Type  : ' + (header.Trans_type||'Cash') + '\n';
    t += line + '\n';
    t += 'Item              Qty   Price    Amt\n';
    t += line + '\n';
    detail.forEach(d => {
        const name  = ((d.BRAND_NAME||d.brand||'')+ ' '+(d.ITEM_NAME||d.item||'')).substring(0,17).padEnd(17);
        const qty   = String(d.quantity||0).padStart(3);
        const price = ('Rs.'+parseFloat(d.Price_PerItem||d.price||0).toFixed(0)).padStart(7);
        const amt   = ('Rs.'+parseFloat(d.amount||0).toFixed(0)).padStart(7);
        t += name+' '+qty+' '+price+' '+amt+'\n';
    });
    t += line + '\n';
    const gross=parseFloat(header.Gross_amount||0), discPct=parseFloat(header.Disc_percentage||0), discAmt=gross*discPct/100, net=parseFloat(header.Trans_amount||0), paid=parseFloat(header.Paid_amount||0), bal=parseFloat(header.Balance_amount||0);
    t += 'Gross   : Rs. ' + gross.toFixed(2).padStart(12) + '\n';
    if (discPct>0) t += 'Disc('+discPct+'%): Rs. -' + discAmt.toFixed(2).padStart(10) + '\n';
    t += 'NET     : Rs. ' + net.toFixed(2).padStart(12) + '\n';
    t += 'Paid    : Rs. ' + paid.toFixed(2).padStart(12) + '\n';
    t += 'Balance : Rs. ' + bal.toFixed(2).padStart(12) + '\n';
    t += dline + '\n';
    t += ' The items bought can not be refunded.\n';
    t += '   Thank you for your business!\n';
    t += '    ' + (clientInfo.Client_name||'Margalla 3M Industries') + '\n';
    t += line + '\n';
    t += '   Software by Fast NUCES Students\n';
    return t;
}

function printSavedInvoice(billNo) {
    fetch('api/get_transaction_detail.php?id=' + billNo)
        .then(r=>r.json())
        .then(res => { if (res.header) triggerPrint(buildReceiptText(res.header, res.detail)); });
}

// Print always prints an actually-saved invoice — never a draft — so an
// "official" receipt is never handed out for a sale that was never recorded.
// If the current bill isn't saved yet, this saves it first (same as clicking
// Save), then prints once that succeeds.
function doPrint() {
    const billNo = document.getElementById('bill-no').value;
    if (billSaved) { printSavedInvoice(billNo); return; }

    performSave().then(res => {
        if (!res) return;
        if (res.success) {
            billSaved = true;
            const billNoEl = document.getElementById('bill-no');
            billNoEl.value = res.trans_no;
            billNoEl.classList.remove('bill-preview');
            document.getElementById('invno-display').textContent = res.trans_no;
            toast('Sale saved — Bill #' + res.trans_no,'ok');
            setStatus('Sale saved — Bill #' + res.trans_no);
            printSavedInvoice(res.trans_no);
        } else {
            toast('Error: '+(res.error||'Unknown'),'err'); setStatus('Save failed');
        }
    });
}

function triggerPrint(text) {
    document.getElementById('print-area').innerHTML = '<pre style="font-family:Courier New,monospace;font-size:11px;">'+text+'</pre>';
    window.print();
}

function printDetailReceipt() {
    if (currentDetailHeader) triggerPrint(buildReceiptText(currentDetailHeader, currentDetailRows));
}

function resetForm() {
    cart=[]; selectedItem=null; renderCart();
    document.getElementById('cust-name').value='';
    document.getElementById('cust-tel').value='';
    document.getElementById('cust-select').value='';
    selectedCustomerId = null;
    document.getElementById('invoice-reference').value='';
    document.getElementById('disc-pct').value='0';
    document.getElementById('cash-paid').value='0';
    billSaved = false;
    refreshBillPreview();
    document.getElementById('item-search').value='';
    document.querySelectorAll('#product-list-body tr').forEach(r => r.classList.remove('row-selected'));
    document.getElementById('expiry-info-body').innerHTML = '<tr><td colspan="3" style="text-align:center;padding:10px;color:#888;font-size:10px;">Select an item to view expiry details.</td></tr>';
    document.getElementById('expiry-info-label').textContent = '';
    // Recalc after the discount/cash fields above are already reset to 0 —
    // doing this before them left Balance showing the previous bill's stale
    // cash-minus-net figure instead of 0.00.
    recalcTotals();
    // Re-fetch stock so a New/Cancel bill always starts from live quantities —
    // the last sale's deductions wouldn't otherwise show up until some other
    // action happened to reload the product list.
    loadProductList();
    setStatus('Ready');
}

function setStatus(msg) { document.getElementById('status-msg').textContent = msg; }

let allTransactions = [];

function loadTransactionsFull() {
    fetch('api/get_transactions.php').then(r=>r.json()).then(rows=>{
        allTransactions = rows;
        renderTxnRows('txn-full-body', rows, rows.length, true);
        document.getElementById('txn-result-count').textContent = rows.length + ' record(s)';
    }).catch(() => {
        document.getElementById('txn-full-body').innerHTML =
            '<tr><td colspan="11" style="text-align:center;color:darkred;padding:10px;">Could not load transactions — check DB connection</td></tr>';
    });
}

let txnSearchTimer = null;
function filterTransactions() {
    const field = document.getElementById('txn-search-field').value;
    const query = document.getElementById('txn-search-input').value.trim();

    clearTimeout(txnSearchTimer);
    if (!query) {
        renderTxnRows('txn-full-body', allTransactions, allTransactions.length, true);
        document.getElementById('txn-result-count').textContent = allTransactions.length + ' record(s)';
        return;
    }

    txnSearchTimer = setTimeout(() => {
        fetch('api/get_transactions.php?field=' + encodeURIComponent(field) + '&q=' + encodeURIComponent(query))
            .then(r => r.json())
            .then(rows => {
                renderTxnRows('txn-full-body', rows, rows.length, true);
                document.getElementById('txn-result-count').textContent = rows.length + ' matching record(s) (full history searched)';
            });
    }, 250);
}

function clearTxnSearch() {
    document.getElementById('txn-search-input').value = '';
    document.getElementById('txn-search-field').value = 'all';
    renderTxnRows('txn-full-body', allTransactions, allTransactions.length, true);
    document.getElementById('txn-result-count').textContent = allTransactions.length + ' record(s)';
}

function renderTxnRows(tbodyId, rows, limit, showMobile) {
    const tbody = document.getElementById(tbodyId);
    tbody.innerHTML = '';
    if (!rows.length) { tbody.innerHTML='<tr><td colspan="11" style="text-align:center;padding:8px;color:#888;">No transactions yet</td></tr>'; return; }
    rows.slice(0,limit).forEach(row => {
        const tr = document.createElement('tr');
        const bal = parseFloat(row.Balance_amount||0);
        let cells = `<td style="font-weight:bold;color:#0a246a;">${row.Trans_no}</td><td>${row.Cust_name||'Walk-in'}</td>`;
        if (showMobile) cells += `<td>${row.Cust_telno||'—'}</td>`;
        cells += `<td>${row.Trans_date}</td><td>${row.Trans_type}</td>
            <td style="text-align:right;">${parseFloat(row.Gross_amount||0).toFixed(2)}</td>
            <td style="text-align:right;">${row.Disc_percentage||0}%</td>
            <td style="text-align:right;font-weight:bold;color:darkgreen;">${parseFloat(row.Trans_amount||0).toFixed(2)}</td>
            <td style="text-align:right;">${parseFloat(row.Paid_amount||0).toFixed(2)}</td>
            <td style="text-align:right;font-weight:bold;color:${bal<0?'darkred':'darkgreen'};">${bal.toFixed(2)}</td>
            <td>${row.User_id||'—'}</td>`;
        tr.innerHTML = cells;
        tr.onclick = () => loadTxnDetail(row.Trans_no);
        tbody.appendChild(tr);
    });
}

function loadTxnDetail(id) {
    fetch('api/get_transaction_detail.php?id='+id).then(r=>r.json()).then(res => {
        if (!res.header) return;
        currentDetailHeader = res.header;
        currentDetailRows   = res.detail;
        document.getElementById('txn-detail-header').textContent = 'Bill #'+res.header.Trans_no+' — '+(res.header.Cust_name||'Walk-in')+' — '+res.header.Trans_date;
        const tbody = document.getElementById('txn-detail-body');
        tbody.innerHTML = '';
        res.detail.forEach(d => {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td style="font-weight:bold;color:#0a246a;">${d.stock_number}</td><td style="font-weight:bold;">${d.BRAND_NAME||'—'}</td><td>${d.ITEM_NAME||'—'}</td><td>${d.VOLUME_L||'—'}</td><td style="text-align:right;font-weight:bold;">${d.quantity}</td><td style="text-align:right;">${parseFloat(d.Price_PerItem||0).toFixed(2)}</td><td style="text-align:right;font-weight:bold;color:darkgreen;">${parseFloat(d.amount||0).toFixed(2)}</td>`;
            tbody.appendChild(tr);
        });
        document.getElementById('txn-receipt').textContent = buildReceiptText(res.header, res.detail);
        document.getElementById('txn-detail-panel').style.display = 'block';
    });
}

function loadSuppliers() {
    fetch('api/get_suppliers.php').then(r=>r.json()).then(rows => {
        const tbody = document.getElementById('sup-body');
        tbody.innerHTML = '';
        if (!rows.length) { tbody.innerHTML='<tr><td colspan="8" style="text-align:center;padding:8px;color:#888;">No suppliers found</td></tr>'; return; }
        rows.forEach(row => {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td style="font-weight:bold;color:#0a246a;">${row.SUPPLIER_CODE}</td><td style="font-weight:bold;">${row.SUPPLIER_NAME}</td><td>${row.CONTACT_PERSON||'—'}</td><td>${row.CITY||'—'}</td><td>${row.TELEPHONE_NO||'—'}</td><td>${row.MOBILE_NO||'—'}</td><td>${row.EMAIL||'—'}</td><td>${row.REGION||'—'}</td>`;
            tbody.appendChild(tr);
        });
    }).catch(() => {
        document.getElementById('sup-body').innerHTML =
            '<tr><td colspan="8" style="text-align:center;color:darkred;padding:10px;">Could not load suppliers — check DB connection</td></tr>';
    });
}

function loadBookings() {
    fetch('api/get_bookings.php').then(r=>r.json()).then(rows => {
        const tbody = document.getElementById('book-body');
        tbody.innerHTML = '';
        if (!rows.length) { tbody.innerHTML='<tr><td colspan="9" style="text-align:center;padding:8px;color:#888;">No bookings found</td></tr>'; return; }
        rows.forEach(row => {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td style="font-weight:bold;color:#0a246a;">${row.ID}</td><td style="font-weight:bold;">${row.Item_name||'—'}</td><td style="text-align:right;font-weight:bold;">${row.Demand_qty||0}</td><td>${row.Booking_date||'—'}</td><td>${row.Demand_date||'—'}</td><td>${row.Supplier_code||'—'}</td><td>${row.Prod_Type||'—'}</td><td style="font-weight:bold;color:${row.Status==='Complete'?'darkgreen':row.Status==='Pending'?'darkorange':'#333'};">${row.Status||'—'}</td><td>${row.Comments||'—'}</td>`;
            tbody.appendChild(tr);
        });
    }).catch(() => {
        document.getElementById('book-body').innerHTML =
            '<tr><td colspan="9" style="text-align:center;color:darkred;padding:10px;">Could not load bookings — check DB connection</td></tr>';
    });
}

function loadReports() {
    fetch('api/get_reports.php').then(r=>r.json()).then(rows => {
        const tbody = document.getElementById('report-body');
        tbody.innerHTML = '';
        if (!rows.length) { tbody.innerHTML='<tr><td colspan="4" style="text-align:center;padding:8px;color:#888;">No data</td></tr>'; return; }
        rows.forEach(row => {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td style="font-weight:bold;">${row.Month}</td><td>${row.Size}</td><td style="text-align:right;">${row.TxnCount}</td><td style="text-align:right;font-weight:bold;color:darkgreen;">${parseFloat(row.TotalSales||0).toFixed(2)}</td>`;
            tbody.appendChild(tr);
        });
    }).catch(() => {
        document.getElementById('report-body').innerHTML =
            '<tr><td colspan="4" style="text-align:center;color:darkred;padding:10px;">Could not load reports — check DB connection</td></tr>';
    });
}

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

document.addEventListener('click', e => {
    const dd = document.getElementById('search-dropdown');
    if (dd && !dd.contains(e.target) && e.target.id !== 'item-search') {
        dd.classList.add('hidden');
    }
});

// Admin Options menu -- groups the admin-only screens (previously flat nav
// items cluttering the bar) behind one dropdown, same pattern as the item
// search dropdown above.
function toggleAdminMenu(e) {
    e.stopPropagation();
    const dd = document.getElementById('admin-dropdown');
    if (dd) dd.style.display = (dd.style.display === 'none' || !dd.style.display) ? 'block' : 'none';
}
document.addEventListener('click', e => {
    const dd = document.getElementById('admin-dropdown');
    if (dd && dd.style.display === 'block' && !dd.contains(e.target)) {
        dd.style.display = 'none';
    }
});

<?php if ($canSale): ?>
loadProductList();
loadClientInfo();
loadCustomers();
loadGlobalLedger();
refreshBillPreview();
<?php endif; ?>
// Ensures nav-active + the right tab's own loader (e.g. loadBookings() for a
// Booking-role user landing here) fire correctly regardless of which tab the
// PHP above rendered as the initial view.
switchView('<?php echo $defaultView; ?>');
setupKeyboardNav();
makeDraggable('invoice-popup-box', 'invoice-popup-titlebar');
makeDraggable('held-popup-box', 'held-popup-titlebar');
makeDraggable('custinv-popup-box', 'custinv-popup-titlebar');
persistHeldBills();
</script>
</body>
</html>