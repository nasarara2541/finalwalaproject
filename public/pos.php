<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AISellH2O — Point of Sale</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
* { font-family: Tahoma, 'Segoe UI', sans-serif; font-size: 12px; box-sizing: border-box; }
body { background: #d4d0c8; }

.win-inset  { border: 1px solid; border-color: #808080 #ffffff #ffffff #808080; background:#fff; }
.win-raised { border: 1px solid; border-color: #ffffff #808080 #808080 #ffffff; }
.win-panel  { border: 1px solid; border-color: #808080 #ffffff #ffffff #808080; background:#ece9d8; }

.win-titlebar { background: linear-gradient(to right, #0a246a, #3a6ea5); color: white; font-weight: bold; font-size: 12px; padding: 4px 8px; display:flex; align-items:center; justify-content:space-between; }
.win-menubar { background: #d4d0c8; border-bottom: 1px solid #808080; display:flex; gap:0; padding: 2px 2px; }
.win-menu-item { padding: 3px 10px; cursor:pointer; font-size:12px; }
.win-menu-item:hover, .win-menu-item.active { background:#0a246a; color:white; }

input[type=text], input[type=number], select {
    border: 1px solid; border-color: #808080 #ffffff #ffffff #808080;
    background: #ffff99; padding: 2px 4px; height: 22px; font-size:12px;
    font-family: Tahoma, sans-serif;
}
input[readonly], input.readonly-field { background: #d4d0c8 !important; color: #333; }
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
.win-table td { border: 1px solid #d0ccc4; padding: 4px 6px; white-space:nowrap; }

.win-section-label { background: #d4d0c8; font-weight:bold; font-size:12px; padding: 3px 8px; border-bottom:1px solid #808080; display:flex; align-items:center; justify-content:space-between; }
.win-scroll { overflow:auto; }
.field-yellow { background: #ffff99 !important; }
.field-blue   { background: #cce0ff !important; }
.amt-field { text-align:right; font-weight:bold; }

.total-label { font-weight:bold; text-align:right; padding-right:8px; white-space:nowrap; }

#search-dropdown { position:absolute; z-index:999; background:#fff; border:1px solid #808080; max-height:180px; overflow-y:auto; width:100%; top:100%; left:0; box-shadow: 2px 2px 4px rgba(0,0,0,0.3); }
#search-dropdown div { padding:4px 6px; border-bottom:1px solid #eee; cursor:pointer; font-size:12px; }
#search-dropdown div:hover { background:#0a246a; color:white; }

.nav-active { background:#0a246a !important; color:white !important; }

.win-statusbar { background:#d4d0c8; border-top:1px solid #808080; padding:3px 8px; font-size:12px; display:flex; gap:14px; }
.win-statusbar span { border-right:1px solid #808080; padding-right:12px; }

/* Invoice / Challan preview */
.invoice-preview {
    background:#fff; border:1px solid #808080; padding:10px; font-family:'Courier New',monospace;
    font-size:11px; line-height:1.5; overflow-y:auto;
}
.invoice-preview .inv-title { text-align:center; font-weight:bold; font-size:13px; }
.invoice-preview .inv-sub   { text-align:center; font-size:10px; color:#333; }
.invoice-preview table { width:100%; border-collapse:collapse; font-size:10px; margin-top:4px; }
.invoice-preview table td { padding:2px 3px; border:none; vertical-align:top; word-wrap:break-word; white-space:normal; }
.invoice-preview hr { border:none; border-top:1px dashed #888; margin:4px 0; }

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
</style>
</head>
<body class="min-h-screen flex flex-col">

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


<div class="win-titlebar">
    <span>&#x1F4A7; AISellH2O — Water Distribution Point of Sale</span>
    <span id="live-clock" style="font-weight:normal;font-size:11px;"></span>
</div>

<div class="win-menubar">
    <span class="win-menu-item nav-active" id="nav-pos"          onclick="switchView('pos')">Sale</span>
    <span class="win-menu-item"            id="nav-transactions"  onclick="switchView('transactions')">Transactions</span>
    <span class="win-menu-item"            id="nav-inventory"     onclick="switchView('inventory')">Inventory</span>
    <span class="win-menu-item"            onclick="window.location='stock_receiving.php'">Stock Receiving</span>
    <span class="win-menu-item"            id="nav-booking"       onclick="switchView('booking')">Booking</span>
    <span class="win-menu-item"            id="nav-reports"       onclick="switchView('reports')">Reports</span>
    <span style="flex:1"></span>
    <span class="win-menu-item" style="color:#555;">User: <b>admin</b></span>
</div>

<!-- ===================== POS VIEW ===================== -->
<div id="view-pos" style="display:flex; flex-direction:column; flex:1; padding:5px; gap:4px;">

    <!-- Top bar: Bill info + buttons -->
    <div class="win-panel" style="display:flex; align-items:center; gap:8px; padding:4px 8px; flex-wrap:wrap;">
        <div style="display:flex;align-items:center;gap:3px;">
            <span style="font-weight:bold;background:#0a246a;color:white;padding:3px 10px;border:1px solid #000;">Bill#</span>
            <input id="bill-no" type="text" readonly value="—" style="width:65px;font-weight:bold;color:#0a246a;" class="readonly-field">
        </div>
        <div style="display:flex;align-items:center;gap:3px;">
            <label style="font-weight:bold;">Customer Name</label>
            <input id="cust-name" type="text" placeholder="Walk-in customer" style="width:170px;" tabindex="1">
        </div>
        <div style="display:flex;align-items:center;gap:3px;">
            <label style="font-weight:bold;">Mobile</label>
            <input id="cust-tel" type="text" placeholder="03xx-xxxxxxx" style="width:115px;" tabindex="2">
        </div>
        <div style="display:flex;align-items:center;gap:3px;">
            <label style="font-weight:bold;">Date</label>
            <input id="trans-date" type="text" readonly style="width:145px;" class="readonly-field">
        </div>
        <div style="display:flex;align-items:center;gap:3px;">
            <label style="font-weight:bold;">Type</label>
            <select id="trans-type" style="width:75px;" tabindex="3">
                <option value="Cash">Cash</option>
                <option value="Credit">Credit</option>
                <option value="Card">Card</option>
            </select>
        </div>
        <div style="display:flex;align-items:center;gap:3px;">
            <label style="font-weight:bold;">Branch</label>
            <input id="branch-code" type="text" value="HQ" style="width:42px;" tabindex="4">
        </div>
        <div style="margin-left:auto;display:flex;gap:4px;">
            <button class="win-btn win-btn-blue" onclick="resetForm()">&#x2B06; New</button>
            <button class="win-btn win-btn-green" onclick="saveSale()">&#x2714; Save</button>
            <button class="win-btn" onclick="doPrint()">&#x1F5A8; Print</button>
            <button class="win-btn" onclick="openInvoicePopup()" style="background:#5b3a8a;color:white;border-color:#9966cc #3d1f6b #3d1f6b #9966cc;">&#x1F4CB; View Challan</button>
            <button class="win-btn" onclick="resetForm()" style="color:darkred;">&#x2716; Cancel</button>
        </div>
    </div>

    <!-- Search box row -->
    <div class="win-panel" style="padding:4px 8px;">
        <div style="display:flex;align-items:flex-end;gap:6px;flex-wrap:wrap;">
            <div style="position:relative;display:flex;flex-direction:column;gap:1px;min-width:230px;">
                <label style="font-weight:bold;">Item / Brand / Stock No. (Search)</label>
                <input id="item-search" type="text" placeholder="Type to search…" style="width:230px;" oninput="searchItems(this.value)" autocomplete="off" tabindex="5">
                <div id="search-dropdown" class="hidden"></div>
            </div>
            <div style="display:flex;flex-direction:column;gap:1px;">
                <label style="font-weight:bold;">Stock No.</label>
                <input id="sel-stock" type="text" readonly style="width:85px;" class="readonly-field field-blue">
            </div>
            <div style="display:flex;flex-direction:column;gap:1px;">
                <label style="font-weight:bold;">Volume/Size</label>
                <input id="sel-vol" type="text" readonly style="width:75px;" class="readonly-field">
            </div>
            <div style="display:flex;flex-direction:column;gap:1px;">
                <label style="font-weight:bold;">Item Type</label>
                <input id="sel-type" type="text" readonly style="width:65px;" class="readonly-field">
            </div>
            <div style="display:flex;flex-direction:column;gap:1px;">
                <label style="font-weight:bold;">Unit Price</label>
                <input id="sel-price" type="number" min="0" step="0.01" style="width:75px;" oninput="recalcLine()" class="amt-field" tabindex="6">
            </div>
            <div style="display:flex;flex-direction:column;gap:1px;">
                <label style="font-weight:bold;">Qty</label>
                <input id="sel-qty" type="number" min="1" value="1" style="width:55px;" oninput="recalcLine()" tabindex="7">
            </div>
            <div style="display:flex;flex-direction:column;gap:1px;">
                <label style="font-weight:bold;">Amount</label>
                <input id="sel-amount" type="text" readonly style="width:85px;font-weight:bold;color:#0a246a;" class="readonly-field field-yellow amt-field">
            </div>
            <div style="display:flex;flex-direction:column;gap:1px;">
                <label style="font-weight:bold;">In-Hand</label>
                <input id="sel-inhand" type="text" readonly style="width:55px;" class="readonly-field">
            </div>
            <div style="display:flex;flex-direction:column;gap:1px;">
                <label>&nbsp;</label>
                <button class="win-btn win-btn-blue" onclick="addItemToCart()" style="height:22px;">+ Add to Bill</button>
            </div>
        </div>
    </div>

    <!-- MAIN 3-COLUMN AREA: Cart | Product List | Invoice Preview -->
    <div style="display:flex;gap:4px;flex:1;min-height:0;">

        <!-- LEFT: Bill Items (cart) - kept large -->
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
        </div>

        <!-- MIDDLE: Live Product List (replaces dropdown, always visible & scrollable) -->
        <div class="win-panel" style="width:300px;display:flex;flex-direction:column;min-height:0;">
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
                        </tr>
                    </thead>
                    <tbody id="product-list-body">
                        <tr><td colspan="3" style="text-align:center;color:#888;padding:10px;">Loading products…</td></tr>
                    </tbody>
                </table>
            </div>
        </div>


    </div>

    <!-- BOTTOM: Calculator / Totals panel - full width -->
    <div class="win-panel" style="padding:6px 10px;">
        <div class="win-section-label" style="margin-bottom:5px;">Calculator</div>
        <div style="display:flex;align-items:center;gap:18px;flex-wrap:wrap;">

            <div style="display:flex;align-items:center;gap:5px;">
                <label class="total-label" style="width:90px;">Total:</label>
                <input id="gross-total" type="text" readonly value="0.00" class="readonly-field amt-field" style="width:110px;font-weight:bold;font-size:15px;color:#333;">
            </div>

            <div style="display:flex;align-items:center;gap:5px;">
                <label class="total-label" style="width:95px;">Discount %age:</label>
                <input id="disc-pct" type="number" min="0" max="100" value="0" style="width:50px;text-align:center;" oninput="recalcTotals()" tabindex="8">
            </div>

            <div style="display:flex;align-items:center;gap:5px;">
                <label class="total-label" style="width:100px;">Discount AMT:</label>
                <input id="disc-amt" type="text" readonly value="0.00" class="readonly-field amt-field" style="width:75px;color:darkred;">
            </div>

            <div style="display:flex;align-items:center;gap:5px;">
                <label class="total-label" style="width:90px;">Net Total:</label>
                <input id="net-total" type="text" readonly value="0.00" class="readonly-field field-yellow amt-field" style="width:110px;font-weight:bold;color:#0a246a;font-size:17px;">
            </div>

            <div style="display:flex;align-items:center;gap:5px;">
                <label class="total-label" style="width:55px;">Cash:</label>
                <input id="cash-paid" type="number" min="0" value="0" class="field-yellow amt-field" style="width:110px;font-weight:bold;font-size:16px;" oninput="recalcBalance()" tabindex="9">
            </div>

            <div style="display:flex;align-items:center;gap:5px;">
                <label class="total-label" style="width:75px;">Balance:</label>
                <input id="balance-amt" type="text" readonly value="0.00" class="readonly-field amt-field" style="width:110px;font-weight:bold;color:darkgreen;font-size:17px;">
            </div>

            <div style="display:flex;align-items:center;gap:10px;margin-left:auto;">
                <div style="text-align:center;">
                    <div style="color:#555;font-size:11px;">Items</div>
                    <div id="stat-items" style="font-weight:bold;font-size:13px;">0</div>
                </div>
                <div style="text-align:center;">
                    <div style="color:#555;font-size:11px;">Qty</div>
                    <div id="stat-qty" style="font-weight:bold;font-size:13px;">0</div>
                </div>
                <div style="text-align:center;">
                    <div style="color:#555;font-size:11px;">User</div>
                    <input id="user-id" type="text" value="admin" style="width:75px;text-align:center;" tabindex="10">
                </div>
            </div>
        </div>
    </div>

    <!-- Status bar -->
    <div class="win-statusbar">
        <span id="status-msg">Ready &nbsp;|&nbsp; F2=Search &nbsp; F5=Cash &nbsp; F8=Save &nbsp; F9=New &nbsp; F10=Invoice &nbsp; Enter=Next Field</span>
        <span>AISellH2O v1.0</span>
        <span>Margalla 3M Industries — Islamabad</span>
    </div>
</div>

<!-- ===================== TRANSACTIONS VIEW ===================== -->
<div id="view-transactions" style="display:none;flex-direction:column;flex:1;padding:5px;gap:4px;">

    <!-- Search bar -->
    <div class="win-panel" style="padding:5px 8px;display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
        <span style="font-weight:bold;">Search Transactions:</span>
        <select id="txn-search-field" style="width:145px;" onchange="filterTransactions()">
            <option value="all">All Fields</option>
            <option value="Trans_no">Transaction ID</option>
            <option value="Cust_name">Customer Name</option>
            <option value="Cust_telno">Mobile No.</option>
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

    <div class="win-panel" style="flex:1;display:flex;flex-direction:column;">
        <div class="win-section-label">
            <span>All Transactions <span style="font-weight:normal;color:#555;">-- click a row to view detail</span></span>
        </div>
        <div class="win-scroll" style="flex:1;max-height:340px;">
            <table class="win-table">
                <thead><tr>
                    <th>Trans#</th><th>Customer</th><th>Mobile</th><th>Date</th><th>Type</th>
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

<!-- ===================== INVENTORY VIEW ===================== -->
<div id="view-inventory" style="display:none;flex-direction:column;flex:1;padding:5px;gap:4px;">
    <div class="win-panel" style="flex:1;display:flex;flex-direction:column;">
        <div class="win-section-label">
            <span>Item Stock / Inventory</span>
            <button class="win-btn" style="height:18px;font-size:11px;padding:0 8px;" onclick="loadInventory()">Refresh</button>
        </div>
        <div class="win-scroll" style="flex:1;">
            <table class="win-table">
                <thead><tr><th>Stock No.</th><th>Brand</th><th>Item Name</th><th>Type</th><th>Volume</th><th>Size</th><th style="text-align:right;">Price</th><th style="text-align:right;">Qty In Hand</th><th>Status</th><th>Barcode</th></tr></thead>
                <tbody id="inv-body"><tr><td colspan="10" style="text-align:center;padding:8px;color:#888;">Loading…</td></tr></tbody>
            </table>
        </div>
    </div>
</div>

<!-- ===================== SUPPLIERS VIEW ===================== -->
<div id="view-suppliers" style="display:none;flex-direction:column;flex:1;padding:5px;gap:4px;">
    <div class="win-panel" style="flex:1;display:flex;flex-direction:column;">
        <div class="win-section-label">
            <span>Suppliers</span>
            <button class="win-btn" style="height:18px;font-size:11px;padding:0 8px;" onclick="loadSuppliers()">Refresh</button>
        </div>
        <div class="win-scroll" style="flex:1;">
            <table class="win-table">
                <thead><tr><th>Code</th><th>Supplier Name</th><th>Contact Person</th><th>City</th><th>Telephone</th><th>Mobile</th><th>Email</th><th>Region</th></tr></thead>
                <tbody id="sup-body"><tr><td colspan="8" style="text-align:center;padding:8px;color:#888;">Loading…</td></tr></tbody>
            </table>
        </div>
    </div>
</div>

<!-- ===================== BOOKING VIEW ===================== -->
<div id="view-booking" style="display:none;flex-direction:column;flex:1;padding:5px;gap:4px;">
    <div class="win-panel" style="flex:1;display:flex;flex-direction:column;">
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
<div id="view-reports" style="display:none;flex-direction:column;flex:1;padding:5px;gap:4px;">
    <div class="win-panel" style="flex:1;display:flex;flex-direction:column;">
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

function clockTick() {
    const now = new Date();
    document.getElementById('live-clock').textContent = now.toLocaleString('en-GB');
    document.getElementById('trans-date').value = now.toLocaleString('en-GB');
}
clockTick();
setInterval(clockTick, 1000);

const views = ['pos','transactions','inventory','suppliers','booking','reports'];

function switchView(v) {
    views.forEach(name => {
        const el  = document.getElementById('view-' + name);
        const btn = document.getElementById('nav-' + name);
        el.style.display = name === v ? 'flex' : 'none';
        if (btn) btn.classList.toggle('nav-active', name === v);
    });
    if (v === 'transactions') loadTransactionsFull();
    if (v === 'inventory')    loadInventory();
    if (v === 'suppliers')    loadSuppliers();
    if (v === 'booking')      loadBookings();
    if (v === 'reports')      loadReports();
}


function buildInvoiceHTML() {
    const billNo   = document.getElementById('bill-no').value;
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
            <div style="font-weight:bold;font-size:14px;">MARGALLA 3M INDUSTRIES</div>
            <div style="font-size:11px;color:#555;">AISellH2O &mdash; Islamabad</div>
            <div style="font-size:11px;color:#555;">Tel: (051) 230 6867</div>
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
        <div style="text-align:center;font-size:10px;color:#666;font-family:Arial,sans-serif;">
            Please check all items before leaving.<br>Thank You &mdash; AISellH2O Software
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

function makeDraggable() {
    const box  = document.getElementById('invoice-popup-box');
    const bar  = document.getElementById('invoice-popup-titlebar');
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

        if (e.key === 'ArrowRight' || e.key === 'Enter') {
            if (id === 'item-search') {
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
                const firstRow = document.querySelector('#product-list-body tr[tabindex]');
                if (firstRow) { firstRow.focus(); firstRow.click(); }
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
        });
}

function renderProductList(data) {
    const tbody = document.getElementById('product-list-body');
    const countEl = document.getElementById('product-count');
    tbody.innerHTML = '';
    countEl.textContent = data.length + ' item(s)';
    if (!data.length) {
        tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;color:#888;padding:10px;">No products found</td></tr>';
        return;
    }
    data.forEach(item => {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td style="font-weight:bold;color:#0a246a;">${item.STOCK_NUMBER}</td><td>${item.BRAND_NAME||'—'}</td><td>${item.SIZE_DESC||'—'}</td>`;
        tr.onclick = () => {
            document.querySelectorAll('#product-list-body tr').forEach(r => r.classList.remove('row-selected'));
    document.querySelectorAll('#product-list-body tr').forEach(r => r.setAttribute('tabindex','0'));
            tr.classList.add('row-selected');
            selectItem(item);
        };
        tbody.appendChild(tr);
    });
}

let searchTimer = null;
function searchItems(q) {
    clearTimeout(searchTimer);
    const dd = document.getElementById('search-dropdown');
    dd.classList.add('hidden');
    if (!q.trim()) { renderProductList(allProducts); return; }
    searchTimer = setTimeout(() => {
        fetch('api/search_items.php?q=' + encodeURIComponent(q))
            .then(r => r.json())
            .then(data => renderProductList(data));
    }, 250);
}

function selectItem(item) {
    selectedItem = item;
    document.getElementById('item-search').value  = (item.BRAND_NAME||'') + ' — ' + (item.ITEM_NAME||'');
    document.getElementById('sel-stock').value    = item.STOCK_NUMBER;
    document.getElementById('sel-vol').value      = (item.VOLUME_ML||'') + (item.SIZE_DESC ? ' / '+item.SIZE_DESC : '');
    document.getElementById('sel-type').value     = item.ITEM_TYPE||'';
    document.getElementById('sel-price').value    = parseFloat(item.PRICE||0).toFixed(2);
    document.getElementById('sel-inhand').value   = item.QTY_INHAND;
    document.getElementById('sel-qty').value      = 1;
    recalcLine();
    setStatus('Item selected: ' + (item.BRAND_NAME||'') + ' ' + (item.ITEM_NAME||''));
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
    if (existing) { existing.quantity += qty; existing.amount = existing.quantity * existing.price; }
    else cart.push({ stock_number:selectedItem.STOCK_NUMBER, brand:selectedItem.BRAND_NAME, item:selectedItem.ITEM_NAME, type:selectedItem.ITEM_TYPE, volume:selectedItem.VOLUME_ML, quantity:qty, price:price, amount:qty*price });
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
    document.querySelectorAll('#product-list-body tr').forEach(r => r.classList.remove('row-selected'));
    setStatus('Item added to bill');
}

function removeFromCart(idx) { cart.splice(idx,1); renderCart(); recalcTotals(); }

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
    document.getElementById('stat-items').textContent = cart.length;
    document.getElementById('stat-qty').textContent   = cart.reduce((s,c)=>s+c.quantity,0);
    recalcBalance();
}

function recalcBalance() {
    const net  = parseFloat(document.getElementById('net-total').value) || 0;
    const cash = parseFloat(document.getElementById('cash-paid').value) || 0;
    const bal  = cash - net;
    document.getElementById('balance-amt').value = bal.toFixed(2);
    document.getElementById('balance-amt').style.color = bal < 0 ? 'darkred' : 'darkgreen';
    updateInvoicePreview();
}

function updateInvoicePreview() {
    const billNo  = document.getElementById('bill-no').value;
    const cust    = document.getElementById('cust-name').value || 'Walk-in Customer';
    const date    = document.getElementById('trans-date').value;
    const gross   = parseFloat(document.getElementById('gross-total').value) || 0;
    const discPct = parseFloat(document.getElementById('disc-pct').value) || 0;
    const discAmt = parseFloat(document.getElementById('disc-amt').value) || 0;
    const net     = parseFloat(document.getElementById('net-total').value) || 0;
    const cash    = parseFloat(document.getElementById('cash-paid').value) || 0;
    const bal     = parseFloat(document.getElementById('balance-amt').value) || 0;
    const totalQty = cart.reduce((s,c)=>s+c.quantity,0);

    let rows = '';
    cart.forEach(c => {
        const name = (c.brand||'') + ' ' + (c.item||'');
        rows += `<tr>
                    <td style="width:50%;">${name}</td>
                    <td style="width:15%;text-align:center;">${c.quantity}</td>
                    <td style="width:35%;text-align:right;">${c.amount.toFixed(2)}</td>
                 </tr>`;
    });
    if (!cart.length) rows = '<tr><td colspan="3" style="text-align:center;color:#999;">No items yet</td></tr>';

    document.getElementById('invoice-preview').innerHTML = `
        <div class="inv-title">MARGALLA 3M INDUSTRIES</div>
        <div class="inv-sub">AISellH2O — Islamabad</div>
        <div class="inv-sub">Tel: (051) 230 6867</div>
        <hr>
        <div>Bill#: <b>${billNo}</b>&nbsp;&nbsp;Date: ${date}</div>
        <div>Name: ${cust}</div>
        <hr>
        <table style="table-layout:fixed;">
            <tr style="font-weight:bold;">
                <td style="width:50%;">Item</td>
                <td style="width:15%;text-align:center;">Qty</td>
                <td style="width:35%;text-align:right;">Amt</td>
            </tr>
            ${rows}
        </table>
        <hr>
        <div>Total Qty: ${totalQty} &nbsp;&nbsp; Total: ${gross.toFixed(2)}</div>
        <div>Disc: ${discAmt.toFixed(2)} (${discPct}%)</div>
        <div style="font-weight:bold;">Net Total: ${net.toFixed(2)}</div>
        <div>Paid: ${cash.toFixed(2)}</div>
        <div style="font-weight:bold;">Balance: ${bal.toFixed(2)}</div>
        <hr>
        <div class="inv-sub">Please check all items before leaving.</div>
        <div class="inv-sub">Thank You — Software by AISellH2O</div>
    `;
}

function saveSale() {
    if (!cart.length) { toast('Add items to the bill first','warn'); return; }
    const gross   = cart.reduce((s,c)=>s+c.amount,0);
    const discPct = parseFloat(document.getElementById('disc-pct').value) || 0;
    const net     = gross - (gross * discPct / 100);
    const paid    = parseFloat(document.getElementById('cash-paid').value) || 0;
    const payload = {
        cust_name:      document.getElementById('cust-name').value,
        cust_telno:     document.getElementById('cust-tel').value,
        trans_type:     document.getElementById('trans-type').value,
        disc_percentage:discPct, gross_amount:gross, trans_amount:net,
        paid_amount:paid, balance_amount:paid-net,
        user_id:document.getElementById('user-id').value,
        tax_status:'N',
        items: cart.map(c=>({ stock_number:c.stock_number, quantity:c.quantity, price:c.price, amount:c.amount }))
    };
    setStatus('Saving…');
    fetch('api/save_transaction.php', { method:'POST', body:JSON.stringify(payload) })
        .then(r=>r.json())
        .then(res => {
            if (res.success) {
                const no = res.trans_no;
                document.getElementById('bill-no').value = no;
                toast('Sale saved — Bill #' + no,'ok');
                setStatus('Sale saved — Bill #' + no);
                updateInvoicePreview();
                loadInventory();
                doPrint();
            } else { toast('Error: '+(res.error||'Unknown'),'err'); setStatus('Save failed'); }
        });
}

function buildReceiptText(header, detail) {
    const line  = '--------------------------------';
    const dline = '================================';
    let t = '';
    t += '      MARGALLA 3M INDUSTRIES\n';
    t += '        AISellH2O — Islamabad\n';
    t += dline + '\n';
    t += 'Bill #: ' + (header.Trans_no||'—') + '\n';
    t += 'Date  : ' + (header.Trans_date||new Date().toLocaleString('en-GB')) + '\n';
    t += 'Cust  : ' + (header.Cust_name||'Walk-in Customer') + '\n';
    t += 'Mobile: ' + (header.Cust_telno||'—') + '\n';
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
    t += '   Thank you for your business!\n';
    t += '    Margalla 3M Industries\n';
    return t;
}

function doPrint() {
    if (!cart.length && document.getElementById('bill-no').value === '—') {
        toast('Add items or save the sale first','warn'); return;
    }
    const billNo = document.getElementById('bill-no').value;
    if (billNo !== '—') {
        fetch('api/get_transaction_detail.php?id=' + billNo)
            .then(r=>r.json())
            .then(res => { if (res.header) triggerPrint(buildReceiptText(res.header, res.detail)); });
    } else {
        const fakeHeader = {
            Trans_no:'(unsaved)', Trans_date:new Date().toLocaleString('en-GB'),
            Cust_name:document.getElementById('cust-name').value||'Walk-in',
            Cust_telno:document.getElementById('cust-tel').value,
            Trans_type:document.getElementById('trans-type').value,
            Gross_amount:cart.reduce((s,c)=>s+c.amount,0),
            Disc_percentage:parseFloat(document.getElementById('disc-pct').value)||0,
            Trans_amount:parseFloat(document.getElementById('net-total').value)||0,
            Paid_amount:parseFloat(document.getElementById('cash-paid').value)||0,
            Balance_amount:parseFloat(document.getElementById('balance-amt').value)||0,
        };
        const fakeDetail = cart.map(c=>({ BRAND_NAME:c.brand, ITEM_NAME:c.item, quantity:c.quantity, Price_PerItem:c.price, amount:c.amount }));
        triggerPrint(buildReceiptText(fakeHeader, fakeDetail));
    }
}

function triggerPrint(text) {
    document.getElementById('print-area').innerHTML = '<pre style="font-family:Courier New,monospace;font-size:11px;">'+text+'</pre>';
    window.print();
}

function printDetailReceipt() {
    if (currentDetailHeader) triggerPrint(buildReceiptText(currentDetailHeader, currentDetailRows));
}

function resetForm() {
    cart=[]; selectedItem=null; renderCart(); recalcTotals();
    document.getElementById('cust-name').value='';
    document.getElementById('cust-tel').value='';
    document.getElementById('disc-pct').value='0';
    document.getElementById('cash-paid').value='0';
    document.getElementById('bill-no').value='—';
    document.querySelectorAll('#product-list-body tr').forEach(r => r.classList.remove('row-selected'));
    setStatus('Ready');
}

function setStatus(msg) { document.getElementById('status-msg').textContent = msg; }

let allTransactions = [];

function loadTransactionsFull() {
    fetch('api/get_transactions.php').then(r=>r.json()).then(rows=>{
        allTransactions = rows;
        renderTxnRows('txn-full-body', rows, rows.length, true);
        document.getElementById('txn-result-count').textContent = rows.length + ' record(s)';
    });
}

function filterTransactions() {
    const field  = document.getElementById('txn-search-field').value;
    const query  = document.getElementById('txn-search-input').value.trim().toLowerCase();

    if (!query) {
        renderTxnRows('txn-full-body', allTransactions, allTransactions.length, true);
        document.getElementById('txn-result-count').textContent = allTransactions.length + ' record(s)';
        return;
    }

    const filtered = allTransactions.filter(row => {
        if (field === 'all') {
            return (
                String(row.Trans_no   ||'').toLowerCase().includes(query) ||
                String(row.Cust_name  ||'').toLowerCase().includes(query) ||
                String(row.Cust_telno ||'').toLowerCase().includes(query) ||
                String(row.Trans_date ||'').toLowerCase().includes(query) ||
                String(row.Trans_type ||'').toLowerCase().includes(query) ||
                String(row.User_id    ||'').toLowerCase().includes(query)
            );
        }
        return String(row[field]||'').toLowerCase().includes(query);
    });

    renderTxnRows('txn-full-body', filtered, filtered.length, true);
    document.getElementById('txn-result-count').textContent = filtered.length + ' of ' + allTransactions.length + ' record(s)';
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
            tr.innerHTML = `<td style="font-weight:bold;color:#0a246a;">${d.stock_number}</td><td style="font-weight:bold;">${d.BRAND_NAME||'—'}</td><td>${d.ITEM_NAME||'—'}</td><td>${d.VOLUME_ML||'—'}</td><td style="text-align:right;font-weight:bold;">${d.quantity}</td><td style="text-align:right;">${parseFloat(d.Price_PerItem||0).toFixed(2)}</td><td style="text-align:right;font-weight:bold;color:darkgreen;">${parseFloat(d.amount||0).toFixed(2)}</td>`;
            tbody.appendChild(tr);
        });
        document.getElementById('txn-receipt').textContent = buildReceiptText(res.header, res.detail);
        document.getElementById('txn-detail-panel').style.display = 'block';
    });
}

function loadInventory() {
    fetch('api/get_inventory.php').then(r=>r.json()).then(rows => {
        const tbody = document.getElementById('inv-body');
        tbody.innerHTML = '';
        if (!rows.length) { tbody.innerHTML='<tr><td colspan="10" style="text-align:center;padding:8px;color:#888;">No stock found</td></tr>'; return; }
        rows.forEach(row => {
            const tr = document.createElement('tr');
            const qty = parseInt(row.QTY_INHAND||0);
            const qtyColor = qty<=10?'darkred':qty<=50?'darkorange':'darkgreen';
            tr.innerHTML = `<td style="font-weight:bold;color:#0a246a;">${row.STOCK_NUMBER}</td><td style="font-weight:bold;">${row.BRAND_NAME||'—'}</td><td>${row.ITEM_NAME||'—'}</td><td>${row.ITEM_TYPE||'—'}</td><td>${row.VOLUME_ML||'—'}</td><td>${row.SIZE_DESC||'—'}</td><td style="text-align:right;font-weight:bold;">${parseFloat(row.PRICE||0).toFixed(2)}</td><td style="text-align:right;font-weight:bold;color:${qtyColor};">${qty}</td><td>${row.AVAILABLE_STATUS||'—'}</td><td style="font-family:monospace;">${row.BARCODE||'—'}</td>`;
            tbody.appendChild(tr);
        });
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

document.addEventListener('click', e => {
    const dd = document.getElementById('search-dropdown');
    if (dd && !dd.contains(e.target) && e.target.id !== 'item-search') {
        dd.classList.add('hidden');
    }
});

loadProductList();
setupKeyboardNav();
makeDraggable();
</script>
</body>
</html>