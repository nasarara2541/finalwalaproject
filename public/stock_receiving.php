<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AISellH2O - Stock Receiving</title>
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
.nav-active { background:#0a246a !important; color:white !important; }

input[type=text], input[type=number], input[type=date], select {
    border: 1px solid; border-color: #808080 #ffffff #ffffff #808080;
    background: #ffff99; padding: 2px 4px; height: 22px; font-size:12px;
    font-family: Tahoma, sans-serif;
}
input[readonly], input.readonly-field {
    background: #d4d0c8 !important; color: #333;
}
input.field-blue { background: #cce0ff !important; }
input:focus, select:focus { outline: 2px solid #0a246a; }

.win-btn {
    background: #d4d0c8; border: 1px solid; border-color: #ffffff #808080 #808080 #ffffff;
    padding: 2px 10px; cursor:pointer; font-size:12px; height:23px;
    font-family: Tahoma, sans-serif; white-space:nowrap; display:inline-flex; align-items:center; gap:3px;
}
.win-btn:hover  { background: #e8e4d8; }
.win-btn:active { border-color: #808080 #ffffff #ffffff #808080; }
.win-btn-blue  { background:#003087; color:white; border-color:#5599cc #002266 #002266 #5599cc; }
.win-btn-blue:hover { background:#004499; }
.win-btn-green { background:#1a7a1a; color:white; border-color:#44aa44 #0a500a #0a500a #44aa44; }
.win-btn-green:hover { background:#1e8c1e; }
.win-btn-red   { background:#8b0000; color:white; border-color:#cc4444 #550000 #550000 #cc4444; }

.win-table { width:100%; border-collapse:collapse; font-size:11px; }
.win-table thead th { border:1px solid #808080; padding:3px 5px; text-align:left; font-weight:bold; background:#d4d0c8; white-space:nowrap; position:sticky; top:0; z-index:1; }
.win-table tbody tr { background:#fff; }
.win-table tbody tr:nth-child(even) { background:#f5f3ee; }
.win-table tbody tr:hover { background:#c5d5e8 !important; cursor:pointer; }
.win-table tbody tr.row-selected { background:#0a246a !important; color:white; }
.win-table td { border:1px solid #d0ccc4; padding:3px 5px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:0; }
.win-table td.expiry-warn  { color:darkred;   font-weight:bold; }
.win-table td.expiry-ok    { color:darkgreen; }

.win-section-label { background:#d4d0c8; font-weight:bold; padding:3px 8px; border-bottom:1px solid #808080; display:flex; align-items:center; justify-content:space-between; }
.win-scroll { overflow:auto; }
.win-groupbox { border:1px solid #808080; padding:4px 6px; }
.win-statusbar { background:#d4d0c8; border-top:1px solid #808080; padding:3px 8px; display:flex; gap:14px; }
.win-statusbar span { border-right:1px solid #808080; padding-right:12px; }

label.lbl { font-weight:bold; white-space:nowrap; }

.field-row { display:flex; align-items:center; gap:3px; }

/* Popups */
.popup-overlay { display:none; position:fixed; top:0;left:0;width:100%;height:100%; background:rgba(0,0,0,0.5); z-index:8000; justify-content:center; align-items:center; }
.popup-overlay.open { display:flex; }
.popup-box { background:#ece9d8; border:2px solid #0a246a; box-shadow:4px 4px 16px rgba(0,0,0,0.5); display:flex; flex-direction:column; min-width:320px; max-height:85vh; animation:popIn 0.12s ease; }
@keyframes popIn { from{transform:scale(0.88);opacity:0;} to{transform:scale(1);opacity:1;} }
.popup-titlebar { background:linear-gradient(to right,#0a246a,#3a6ea5); color:white; font-weight:bold; padding:4px 8px; display:flex; align-items:center; justify-content:space-between; cursor:move; user-select:none; }
.popup-titlebar .close-x { cursor:pointer; font-size:14px; }
.popup-titlebar .close-x:hover { color:#ffaaaa; }
.popup-body { padding:8px; overflow-y:auto; flex:1; }
.popup-footer { padding:5px 8px; border-top:1px solid #aaa; background:#d4d0c8; display:flex; gap:5px; }

#toast { position:fixed; bottom:16px; right:16px; z-index:9999; padding:8px 16px; font-weight:bold; font-size:12px; border:1px solid; display:none; }

/* Notebook style for right panel */
#stock-panel-body tr { background: transparent; }
#stock-panel-body tr:nth-child(even) { background: transparent; }
#stock-panel-body td { border:none; border-bottom:1px solid #b8d4f0; padding:3px 5px; }
#stock-panel-body tr:hover td { background:#e8f0fe !important; }
.notebook-panel {
    background: #fff;
    background-image: repeating-linear-gradient(
        to bottom,
        transparent,
        transparent 22px,
        #b8d4f0 22px,
        #b8d4f0 23px
    );
    background-attachment: local;
}
.notebook-panel thead th {
    background: #d4d0c8 !important;
    border-bottom: 2px solid #808080 !important;
    position: sticky;
    top: 0;
    z-index: 1;
}

.expiry-urgent { background:#ffe0e0 !important; }
.expiry-soon   { background:#fff3cd !important; }
</style>
</head>
<body class="flex flex-col min-h-screen">

<div class="win-titlebar">
    <span>&#x1F4A7; AISellH2O — Stock Receiving Form</span>
    <span id="live-clock" style="font-weight:normal;font-size:11px;"></span>
</div>

<div class="win-menubar">
    <span class="win-menu-item" onclick="window.location='pos.php'">&#x2190; Back to Sale</span>
    <span class="win-menu-item nav-active">Stock Receiving</span>
    <span style="flex:1"></span>
    <span class="win-menu-item" style="color:#555;">User: <b>admin</b></span>
</div>

<!-- ===== SUPPLIER SEARCH POPUP ===== -->
<div id="supplier-popup" class="popup-overlay">
    <div class="popup-box" style="width:520px;">
        <div class="popup-titlebar" id="supplier-popup-bar">
            <span>Select Supplier</span>
            <span class="close-x" onclick="closePopup('supplier-popup')">&#x2716;</span>
        </div>
        <div class="popup-body">
            <div style="margin-bottom:5px;display:flex;gap:4px;">
                <input id="supplier-search-input" type="text" placeholder="Search by code or name…" style="flex:1;" oninput="filterSuppliers(this.value)">
            </div>
            <div class="win-scroll" style="max-height:300px;">
                <table class="win-table" id="supplier-popup-table">
                    <thead><tr><th>Code</th><th>Supplier Name</th><th>City</th><th>Tel</th></tr></thead>
                    <tbody id="supplier-popup-body"><tr><td colspan="4" style="text-align:center;padding:8px;color:#888;">Loading…</td></tr></tbody>
                </table>
            </div>
        </div>
        <div class="popup-footer">
            <button class="win-btn" onclick="closePopup('supplier-popup')">Close</button>
        </div>
    </div>
</div>



<!-- ===== STOCK ITEM SEARCH POPUP ===== -->
<div id="stock-popup" class="popup-overlay">
    <div class="popup-box" style="width:500px;">
        <div class="popup-titlebar" id="stock-popup-bar">
            <span>Select Stock Item</span>
            <span class="close-x" onclick="closePopup('stock-popup')">&#x2716;</span>
        </div>
        <div class="popup-body">
            <div style="margin-bottom:5px;display:flex;gap:4px;">
                <input id="stock-search-input" type="text" placeholder="Search by stock no, brand, item…" style="flex:1;" oninput="filterStockItems(this.value)">
            </div>
            <div class="win-scroll" style="max-height:300px;">
                <table class="win-table">
                    <thead><tr><th>Stock#</th><th>Brand</th><th>Item</th><th>Type</th><th>Volume</th><th>Price</th><th>Qty</th></tr></thead>
                    <tbody id="stock-popup-body"><tr><td colspan="7" style="text-align:center;padding:8px;color:#888;">Loading…</td></tr></tbody>
                </table>
            </div>
        </div>
        <div class="popup-footer">
            <button class="win-btn" onclick="closePopup('stock-popup')">Close</button>
        </div>
    </div>
</div>

<!-- ===== INVOICE LIST POPUP ===== -->
<div id="invoice-list-popup" class="popup-overlay">
    <div class="popup-box" style="width:500px;">
        <div class="popup-titlebar" id="invoice-list-bar">
            <span>&#x1F4CB; All Invoices — click a row to load</span>
            <span class="close-x" onclick="closePopup('invoice-list-popup')">&#x2716;</span>
        </div>
        <div class="popup-body" style="padding:0;">
            <div class="win-scroll" style="max-height:340px;">
                <table class="win-table">
                    <thead><tr>
                        <th>Invoice#</th>
                        <th>Supplier</th>
                        <th>Inv Date</th>
                        <th>Rec. Date</th>
                        <th>Received By</th>
                        <th>Status</th>
                        <th style="text-align:right;">Total</th>
                    </tr></thead>
                    <tbody id="invoice-list-body">
                        <tr><td colspan="7" style="text-align:center;padding:10px;color:#888;">Loading…</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="popup-footer">
            <button class="win-btn win-btn-green" onclick="newInvoice();closePopup('invoice-list-popup');" style="height:22px;">+ New Invoice</button>
            <button class="win-btn" onclick="closePopup('invoice-list-popup')" style="height:22px;">Close</button>
        </div>
    </div>
</div>

<!-- ===== MAIN LAYOUT ===== -->
<div style="display:flex;flex-direction:column;flex:1;padding:5px;gap:4px;">

    <!-- ROW 1: Invoice header -->
    <div class="win-panel" style="padding:5px 8px;">
        <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
            <div class="field-row">
                <label class="lbl" style="background:#0a246a;color:white;padding:2px 8px;">Invoice#</label>
                <input id="inv-no" type="text" readonly class="readonly-field field-blue" style="width:70px;font-weight:bold;color:#0a246a;">
                <button class="win-btn" onclick="openPopup('invoice-list-popup');loadInvoiceList();" title="View all invoices" style="padding:0 6px;font-size:13px;">&#x229E;</button>
            </div>
            <div class="field-row">
                <label class="lbl">Inv Date</label>
                <input id="inv-date" type="date" style="width:110px;" title="Date on the supplier's invoice/challan">
            </div>
            <div class="field-row">
                <label class="lbl">Supplier Code</label>
                <input id="supplier-code" type="text" style="width:75px;" placeholder="Code">
                <button class="win-btn" onclick="openPopup('supplier-popup');loadSuppliers();" title="Pick Supplier">&#x229E;</button>
            </div>
            <div class="field-row">
                <label class="lbl">Total Amount</label>
                <input id="total-amount" type="text" readonly class="readonly-field" style="width:80px;font-weight:bold;font-size:13px;text-align:right;">
            </div>
            <div class="field-row">
                <label class="lbl" style="color:darkred;">Aggr. Amt</label>
                <input id="aggr-amt" type="text" readonly class="readonly-field" style="width:65px;text-align:right;font-weight:bold;">
            </div>
            <div style="margin-left:auto;display:flex;gap:4px;">
                <button class="win-btn win-btn-green" onclick="saveStock()" style="height:26px;font-size:12px;">&#x2714; Save-Stock</button>
                <button class="win-btn win-btn-blue" onclick="modifyReceipt()" style="height:26px;">&#x270E; Modify</button>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-top:4px;">
            <div class="field-row">
                <label class="lbl">Discount Amt</label>
                <input id="discount-amt" type="number" min="0" value="0" style="width:65px;text-align:right;" oninput="recalcTotal()">
            </div>
            <div class="field-row">
                <label class="lbl">Status</label>
                <input id="receipt-status" type="text" value="Y" style="width:55px;" placeholder="Y/N">
            </div>
            <div class="field-row">
                <label class="lbl">Received Date</label>
                <input id="received-date" type="date" style="width:108px;" title="Date goods physically arrived — may differ from today">
            </div>
            <div class="field-row">
                <label class="lbl">Received By</label>
                <input id="received-by" type="text" style="width:110px;" placeholder="Name">
            </div>
        </div>
    </div>

    <!-- ROW 2: Item entry -->
    <div class="win-panel" style="padding:5px 8px;">
        <div id="selected-item-label" style="color:darkred;font-weight:bold;margin-bottom:3px;font-size:12px;">No item selected</div>
        <div style="display:flex;align-items:flex-end;gap:4px;flex-wrap:wrap;">
            <div style="display:flex;flex-direction:column;gap:1px;">
                <label class="lbl">Brand / Item Name</label>
                <div style="display:flex;gap:2px;">
                    <input id="item-brand-search" type="text" placeholder="Type or click &#x229E; to pick…" style="width:175px;" oninput="liveItemSearch(this.value)" autocomplete="off">
                    <button class="win-btn" onclick="openPopup('stock-popup');loadStockItems();" title="Pick item">&#x229E;</button>
                </div>
                <div id="item-search-dropdown" style="position:absolute;z-index:900;background:#fff;border:1px solid #808080;max-height:160px;overflow-y:auto;display:none;min-width:240px;box-shadow:2px 2px 6px rgba(0,0,0,0.3);"></div>
            </div>
            <div style="display:flex;flex-direction:column;gap:1px;">
                <label class="lbl">Stock No.</label>
                <input id="item-stock-no" type="text" readonly class="readonly-field field-blue" style="width:72px;">
            </div>
            <div style="display:flex;flex-direction:column;gap:1px;">
                <label class="lbl">QTY Received</label>
                <input id="item-qty-received" type="number" min="0" value="0" style="width:55px;text-align:right;" oninput="recalcItemAmount()">
            </div>
            <div style="display:flex;flex-direction:column;gap:1px;">
                <label class="lbl">Expiry Date</label>
                <input id="item-expiry" type="date" style="width:110px;">
            </div>
            <div style="display:flex;flex-direction:column;gap:1px;">
                <label class="lbl">Batch No.</label>
                <input id="item-batch" type="text" style="width:68px;" placeholder="Batch">
            </div>
            <div style="display:flex;flex-direction:column;gap:1px;">
                <label class="lbl">Qty Available</label>
                <input id="item-qty-avail" type="text" readonly class="readonly-field" style="width:60px;text-align:right;">
            </div>
            <div style="display:flex;flex-direction:column;gap:1px;">
                <label class="lbl">Qty Per Item</label>
                <input id="item-units-per" type="number" min="1" value="1" style="width:52px;text-align:right;" oninput="recalcItemAmount()">
            </div>
            <div style="display:flex;flex-direction:column;gap:1px;">
                <label class="lbl" style="color:darkred;">Sales Price/Item</label>
                <input id="item-sale-price" type="number" min="0" step="0.01" value="0" style="width:65px;text-align:right;" oninput="recalcItemAmount()">
            </div>
            <div style="display:flex;flex-direction:column;gap:1px;">
                <label class="lbl" style="color:darkblue;">Purch. Price/Item</label>
                <input id="item-purch-price" type="number" min="0" step="0.01" value="0" style="width:65px;text-align:right;" oninput="recalcItemAmount()">
            </div>
            <div style="display:flex;flex-direction:column;gap:1px;">
                <label class="lbl">Amount</label>
                <input id="item-amount" type="text" readonly class="readonly-field" style="width:70px;text-align:right;font-weight:bold;background:#ffff99!important;">
            </div>
            <input type="hidden" id="item-price-perunit">
            <input type="hidden" id="item-pprice-perunit">
            <div style="display:flex;flex-direction:column;gap:1px;">
                <label>&nbsp;</label>
                <button class="win-btn win-btn-green" onclick="addDetailLine()" style="height:22px;">Save Line</button>
            </div>
            <div style="display:flex;flex-direction:column;gap:1px;">
                <label>&nbsp;</label>
                <button class="win-btn" onclick="clearItemForm()" style="height:22px;color:darkred;">Clear</button>
            </div>
        </div>
    </div>

    <!-- ROW 3: Two bottom tables side by side -->
    <div style="display:grid;grid-template-columns:1fr 280px;gap:4px;flex:1;min-height:0;">

        <!-- LEFT: Receipt detail lines — sorted by nearest expiry -->
        <div class="win-panel" style="display:flex;flex-direction:column;min-height:0;overflow:hidden;">
            <div class="win-section-label">
                <span>Stock Receipt Details <span style="font-weight:normal;color:#c00;">(&#x26A0; nearest expiry at top)</span></span>
                <span id="detail-count" style="font-weight:normal;color:#555;"></span>
            </div>
            <div class="win-scroll" style="flex:1;">
                <table class="win-table" style="table-layout:fixed;font-size:11px;">
                    <colgroup>
                        <col style="width:70px;">
                        <col style="width:50px;">
                        <col style="width:75px;">
                        <col style="width:65px;">
                        <col style="width:55px;">
                        <col style="width:50px;">
                        <col style="width:65px;">
                        <col style="width:70px;">
                        <col style="width:130px;">
                        <col style="width:70px;">
                        <col style="width:30px;">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>Stock#</th>
                            <th style="text-align:right;">Qty Rec</th>
                            <th>Exp Date</th>
                            <th>Batch No</th>
                            <th style="text-align:right;">Qty Avail</th>
                            <th style="text-align:right;">Qty/Item</th>
                            <th style="text-align:right;">Price/Item</th>
                            <th style="text-align:right;">Purch Price</th>
                            <th>Brand Name</th>
                            <th style="text-align:right;">Amount</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="detail-body">
                        <tr><td colspan="11" style="text-align:center;padding:10px;color:#888;">No lines added yet — create a new invoice and add items above</td></tr>
                    </tbody>
                </table>
            </div>
            <div style="background:#d4d0c8;border-top:1px solid #808080;padding:4px 8px;display:flex;justify-content:flex-end;align-items:center;gap:8px;">
                <label style="font-weight:bold;font-size:13px;">Total:</label>
                <input id="detail-total" type="text" readonly class="readonly-field" style="width:85px;text-align:right;font-weight:bold;font-size:14px;color:#003087;">
                <div style="width:4px;"></div>
                <span id="update-status-badge" style="font-size:11px;color:#555;"></span>
            </div>
        </div>

        <!-- RIGHT: Two stacked panels matching reference exactly -->
        <div style="display:flex;flex-direction:column;gap:4px;min-height:0;">

            <!-- TOP: SELECT s.S box — shows selected line detail -->
            <div class="win-panel" style="display:flex;flex-direction:column;flex-shrink:0;">
                <div style="background:#d4d0c8;border-bottom:1px solid #808080;padding:3px 6px;display:flex;align-items:center;gap:6px;">
                    <span style="font-weight:bold;font-size:11px;">SELECT s.S</span>
                    <input id="sel-invoice-no" type="text" readonly style="width:65px;font-weight:bold;color:#0a246a;font-size:11px;background:#ffff99;border:1px solid #808080;padding:1px 3px;height:18px;">
                </div>
                <div style="padding:4px 6px;border-bottom:1px solid #ccc;display:flex;align-items:center;gap:6px;background:#ece9d8;">
                    <label style="font-weight:bold;font-size:11px;">Price/Unit:</label>
                    <input id="sel-price-perunit" type="text" readonly style="width:45px;text-align:right;font-weight:bold;font-size:12px;color:#0a246a;background:#d4d0c8;border:1px solid #808080;padding:1px 3px;height:18px;">
                    <button class="win-btn" onclick="openCalcPopup()" style="margin-left:auto;height:20px;font-size:11px;padding:0 8px;">Calculator</button>
                </div>
                <div style="overflow:auto;">
                    <table class="win-table" style="font-size:11px;">
                        <thead>
                            <tr>
                                <th>Brand Name</th>
                                <th>ExpDate</th>
                                <th style="text-align:right;">QtyAvailable</th>
                            </tr>
                        </thead>
                        <tbody id="stock-panel-body">
                            <tr><td colspan="3" style="text-align:center;padding:6px;color:#888;font-size:10px;">Click a row to view here</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- BOTTOM: Stock# | Brand Name | Type | Location — notebook style -->
            <div class="win-panel" style="display:flex;flex-direction:column;flex:1;min-height:0;overflow:hidden;">
                <div class="win-section-label" style="font-size:11px;">
                    <span>Stock Directory</span>
                    <button class="win-btn" style="height:15px;font-size:10px;padding:0 5px;" onclick="loadStockDirectory()">Refresh</button>
                </div>
                <div class="win-scroll notebook-panel" style="flex:1;">
                    <table class="win-table" style="font-size:11px;background:transparent;">
                        <thead>
                            <tr>
                                <th>Stock#</th>
                                <th>Brand Name</th>
                                <th>Type</th>
                                <th>Location</th>
                            </tr>
                        </thead>
                        <tbody id="stock-dir-body">
                            <tr><td colspan="4" style="text-align:center;padding:8px;color:#888;font-size:10px;">Loading…</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="win-statusbar" style="align-items:center;">
        <span style="display:flex;align-items:center;gap:6px;">
            <label style="font-weight:bold;">Update Status</label>
            <input id="update-status-input" type="text" value="Y" style="width:40px;text-align:center;height:20px;background:#ffff99;">
            <button class="win-btn" onclick="updateReceiptStatus()" style="height:20px;font-size:11px;">Update</button>
        </span>
        <span id="status-msg">Ready — Create a new invoice or load an existing one</span>
        <span>F2=Search &nbsp; F8=Save &nbsp; F9=Clear &nbsp; Esc=Close</span>
    </div>
</div>

<div id="toast"></div>

<script>
let detailLines = [];
let selectedStock = null;
let currentInvoiceNo = null;
let allSuppliers = [];
let allStockItems = [];

function clockTick() {
    const now = new Date();
    document.getElementById('live-clock').textContent = now.toLocaleString('en-GB');
}
clockTick();
setInterval(clockTick, 1000);

function setStatus(msg) { document.getElementById('status-msg').textContent = msg; }

function toast(msg, type) {
    const el = document.getElementById('toast');
    el.textContent = msg;
    el.style.display = 'block';
    el.style.background = type==='ok'?'#1a7a1a':type==='warn'?'#b8860b':'#990000';
    el.style.color = 'white';
    el.style.borderColor = type==='ok'?'#0a500a':type==='warn'?'#8b6508':'#660000';
    setTimeout(()=>{ el.style.display='none'; }, 3000);
}

function openPopup(id) { document.getElementById(id).classList.add('open'); }
function closePopup(id) { document.getElementById(id).classList.remove('open'); }

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        ['supplier-popup','stock-popup','invoice-list-popup'].forEach(id => closePopup(id));
    }
    if (e.key === 'F2')  { e.preventDefault(); document.getElementById('item-brand-search').focus(); }
    if (e.key === 'F8')  { e.preventDefault(); saveStock(); }
    if (e.key === 'F9')  { e.preventDefault(); clearAll(); }
});

document.querySelectorAll('.popup-overlay').forEach(ov => {
    ov.addEventListener('click', e => { if (e.target === ov) ov.classList.remove('open'); });
});

function makeDraggable(barId, boxId) {
    const bar = document.getElementById(barId);
    const box = bar ? bar.closest('.popup-box') : null;
    if (!bar || !box) return;
    let drag=false, ox=0, oy=0;
    bar.addEventListener('mousedown', e => {
        drag=true; box.style.position='absolute';
        ox=e.clientX-box.offsetLeft; oy=e.clientY-box.offsetTop;
    });
    document.addEventListener('mousemove', e => { if (!drag) return; box.style.left=(e.clientX-ox)+'px'; box.style.top=(e.clientY-oy)+'px'; });
    document.addEventListener('mouseup', () => drag=false);
}
makeDraggable('supplier-popup-bar','supplier-popup');
makeDraggable('invoice-list-bar','invoice-list-popup');
makeDraggable('stock-popup-bar','stock-popup');
loadStockDirectory();

function expiryClass(dateStr) {
    if (!dateStr) return '';
    const exp  = new Date(dateStr);
    const now  = new Date();
    const days = Math.ceil((exp - now) / (1000*60*60*24));
    if (days <= 30)  return 'expiry-urgent';
    if (days <= 90)  return 'expiry-soon';
    return '';
}

function expiryTdClass(dateStr) {
    if (!dateStr) return '';
    const exp  = new Date(dateStr);
    const now  = new Date();
    const days = Math.ceil((exp - now) / (1000*60*60*24));
    if (days <= 30)  return 'expiry-warn';
    if (days <= 90)  return '';
    return 'expiry-ok';
}

function formatDate(d) {
    if (!d) return '—';
    return new Date(d).toLocaleDateString('en-GB');
}

function loadSuppliers() {
    if (allSuppliers.length) { renderSupplierTable(allSuppliers); return; }
    fetch('api/get_suppliers.php').then(r=>r.json()).then(rows=>{
        allSuppliers = rows;
        renderSupplierTable(rows);
    });
}

function renderSupplierTable(rows) {
    const tbody = document.getElementById('supplier-popup-body');
    tbody.innerHTML = '';
    if (!rows.length) { tbody.innerHTML='<tr><td colspan="4" style="text-align:center;padding:8px;color:#888;">No suppliers found</td></tr>'; return; }
    rows.forEach(s => {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td style="font-weight:bold;color:#0a246a;">${s.SUPPLIER_CODE}</td><td>${s.SUPPLIER_NAME}</td><td>${s.CITY||'—'}</td><td>${s.TELEPHONE_NO||'—'}</td>`;
        tr.onclick = () => {
            document.getElementById('supplier-code').value = s.SUPPLIER_CODE;
            closePopup('supplier-popup');
            setStatus('Supplier selected: ' + s.SUPPLIER_NAME);
        };
        tbody.appendChild(tr);
    });
}

function filterSuppliers(q) {
    const filtered = allSuppliers.filter(s =>
        (s.SUPPLIER_CODE||'').toLowerCase().includes(q.toLowerCase()) ||
        (s.SUPPLIER_NAME||'').toLowerCase().includes(q.toLowerCase())
    );
    renderSupplierTable(filtered);
}

function loadStockItems() {
    if (allStockItems.length) { renderStockPopup(allStockItems); return; }
    fetch('api/get_all_stock_items.php?q=').then(r=>r.json()).then(rows=>{
        allStockItems = rows;
        renderStockPopup(rows);
    });
}

function renderStockPopup(rows) {
    const tbody = document.getElementById('stock-popup-body');
    tbody.innerHTML = '';
    if (!rows.length) { tbody.innerHTML='<tr><td colspan="7" style="text-align:center;padding:8px;color:#888;">No items found</td></tr>'; return; }
    rows.forEach(s => {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td style="font-weight:bold;color:#0a246a;">${s.STOCK_NUMBER}</td><td>${s.BRAND_NAME||'—'}</td><td>${s.ITEM_NAME||'—'}</td><td>${s.ITEM_TYPE||'—'}</td><td>${s.VOLUME_ML||'—'}</td><td style="text-align:right;">${parseFloat(s.PRICE||0).toFixed(2)}</td><td style="text-align:right;">${s.QTY_INHAND}</td>`;
        tr.onclick = () => { selectStockItem(s); closePopup('stock-popup'); };
        tbody.appendChild(tr);
    });
}

function filterStockItems(q) {
    const filtered = allStockItems.filter(s =>
        (s.STOCK_NUMBER||'').toLowerCase().includes(q.toLowerCase()) ||
        (s.BRAND_NAME||'').toLowerCase().includes(q.toLowerCase()) ||
        (s.ITEM_NAME||'').toLowerCase().includes(q.toLowerCase())
    );
    renderStockPopup(filtered);
}

let liveSearchTimer = null;
function liveItemSearch(q) {
    const dd = document.getElementById('item-search-dropdown');
    clearTimeout(liveSearchTimer);
    if (!q.trim()) { dd.style.display='none'; return; }
    liveSearchTimer = setTimeout(() => {
        fetch('api/get_all_stock_items.php?q=' + encodeURIComponent(q)).then(r=>r.json()).then(rows=>{
            dd.innerHTML = '';
            if (!rows.length) {
                    dd.innerHTML=`<div style="padding:6px 8px;border-bottom:1px solid #eee;">
                        <span style="color:#8b0000;font-weight:bold;">Item not found in directory.</span><br>
                        <span style="font-size:11px;color:#555;">This product is not registered yet.</span><br>
                        <button onclick="window.open('add_item.php','_blank','width=700,height=500')"
                            style="margin-top:4px;background:#003087;color:white;border:1px solid #002266;padding:3px 10px;cursor:pointer;font-size:11px;">
                            + Register New Item
                        </button>
                    </div>`;
                    dd.style.display='block'; return;
                }
            rows.forEach(s => {
                const div = document.createElement('div');
                div.style.cssText = 'padding:3px 6px;border-bottom:1px solid #eee;cursor:pointer;';
                div.innerHTML = `<b>${s.BRAND_NAME||''}</b> ${s.ITEM_NAME||''} <span style="color:#0a246a;">[${s.STOCK_NUMBER}]</span> Rs.${parseFloat(s.PRICE||0).toFixed(2)} Qty:${s.QTY_INHAND}`;
                div.onmouseover = () => div.style.background='#0a246a', div.style.color='white';
                div.onmouseout  = () => div.style.background='', div.style.color='';
                div.onclick = () => { selectStockItem(s); dd.style.display='none'; };
                dd.appendChild(div);
            });
            dd.style.display = 'block';
        });
    }, 250);
}

function selectStockItem(s) {
    selectedStock = s;
    document.getElementById('item-brand-search').value = (s.BRAND_NAME||'') + ' ' + (s.ITEM_NAME||'');
    document.getElementById('item-stock-no').value     = s.STOCK_NUMBER;
    document.getElementById('item-qty-avail').value    = s.QTY_INHAND;
    document.getElementById('item-units-per').value    = s.UNITS_PERITEM || 1;
    document.getElementById('item-sale-price').value      = parseFloat(s.PRICE||0).toFixed(2);
    document.getElementById('item-purch-price').value     = '';
    const units = parseInt(s.UNITS_PERITEM||1);
    const salePerUnit = units > 0 ? (parseFloat(s.PRICE||0) / units) : 0;
    document.getElementById('item-price-perunit').value   = salePerUnit.toFixed(2);
    document.getElementById('item-pprice-perunit').value  = '';
    document.getElementById('item-qty-received').value = 1;
    document.getElementById('selected-item-label').textContent = (s.BRAND_NAME||'') + ' ' + (s.ITEM_NAME||'') + ' — ' + (s.VOLUME_ML||'') + ' [' + s.STOCK_NUMBER + ']';
    document.getElementById('selected-item-label').style.color = 'darkred';
    recalcItemAmount();
    document.getElementById('item-qty-received').focus();
    document.getElementById('item-search-dropdown').style.display='none';
    setStatus('Item selected: ' + (s.BRAND_NAME||'') + ' ' + (s.ITEM_NAME||''));
}

function recalcItemAmount() {
    const qty   = parseFloat(document.getElementById('item-qty-received').value) || 0;
    const price = parseFloat(document.getElementById('item-purch-price').value) || 0;
    document.getElementById('item-amount').value = (qty * price).toFixed(2);
}

function addDetailLine() {
    if (!selectedStock) { toast('Select an item first','warn'); return; }

    const qty     = parseInt(document.getElementById('item-qty-received').value)   || 0;
    const expiry  = document.getElementById('item-expiry').value;
    const batch   = document.getElementById('item-batch').value;
    const units   = parseInt(document.getElementById('item-units-per').value)      || 1;
    const sPrice  = parseFloat(document.getElementById('item-sale-price').value)   || 0;
    const pPrice  = parseFloat(document.getElementById('item-purch-price').value)  || 0;
    const amount  = qty * pPrice;
    if (qty <= 0)   { toast('Quantity received must be > 0','warn'); return; }
    if (!expiry)    { toast('Expiry date is required','warn'); return; }
    if (!batch)     { toast('Batch number is required','warn'); return; }
    if (sPrice <= 0){ toast('Sales price must be > 0','warn'); return; }

    const existing = detailLines.findIndex(d => d.stock_number === selectedStock.STOCK_NUMBER && d.batch_no === batch);
    if (existing !== -1) { toast('This item + batch already added. Remove it first to update.','warn'); return; }

    const pricePerUnit  = parseFloat(document.getElementById('item-price-perunit').value)  || 0;
    const ppricePerUnit = parseFloat(document.getElementById('item-pprice-perunit').value) || 0;
    detailLines.push({
        stock_number:   selectedStock.STOCK_NUMBER,
        brand_name:     selectedStock.BRAND_NAME,
        item_name:      selectedStock.ITEM_NAME,
        batch_no:       batch,
        expiry_date:    expiry,
        qty_received:   qty,
        qty_available:  parseInt(document.getElementById('item-qty-avail').value) || 0,
        units_peritem:  units,
        sale_price:     sPrice,
        purch_price:    pPrice,
        price_perunit:  pricePerUnit,
        pprice_perunit: ppricePerUnit,
        amount:         amount,
    });

    detailLines.sort((a,b) => new Date(a.expiry_date) - new Date(b.expiry_date));
    renderDetailTable();
    recalcTotal();
    clearItemForm();
    setStatus('Line added: ' + selectedStock.BRAND_NAME + ' Batch ' + batch);
    toast('Item line added','ok');
}

function removeDetailLine(idx) {
    detailLines.splice(idx,1);
    renderDetailTable();
    recalcTotal();
}

function renderDetailTable() {
    const tbody = document.getElementById('detail-body');
    tbody.innerHTML = '';
    document.getElementById('detail-count').textContent = detailLines.length + ' line(s)';
    if (!detailLines.length) {
        tbody.innerHTML='<tr><td colspan="11" style="text-align:center;padding:10px;color:#888;">No lines added yet</td></tr>';
        return;
    }
    detailLines.forEach((d,i) => {
        const tr = document.createElement('tr');
        tr.className = expiryClass(d.expiry_date);
        const tdClass = expiryTdClass(d.expiry_date);
        tr.innerHTML = `
            <td style="font-weight:bold;color:#0a246a;overflow:hidden;text-overflow:ellipsis;">${d.stock_number}</td>
            <td style="text-align:right;font-weight:bold;">${d.qty_received}</td>
            <td class="${tdClass}" style="font-weight:bold;">${formatDate(d.expiry_date)}</td>
            <td style="overflow:hidden;text-overflow:ellipsis;">${d.batch_no||'—'}</td>
            <td style="text-align:right;">${d.qty_available}</td>
            <td style="text-align:right;">${d.units_peritem}</td>
            <td style="text-align:right;color:darkred;">${d.sale_price.toFixed(2)}</td>
            <td style="text-align:right;color:darkblue;">${d.purch_price.toFixed(2)}</td>
            <td style="overflow:hidden;text-overflow:ellipsis;">${d.brand_name||'—'} ${d.item_name||''}</td>
            <td style="text-align:right;font-weight:bold;">${d.amount.toFixed(2)}</td>
            <td style="text-align:center;"><button class="win-btn win-btn-red" onclick="removeDetailLine(${i})" style="height:16px;font-size:10px;padding:0 4px;">X</button></td>`;
        tr.onclick = (e) => {
            if (e.target.tagName === 'BUTTON') return;
            document.querySelectorAll('#detail-body tr').forEach(r => r.classList.remove('row-selected'));
            tr.classList.add('row-selected');
            updateRightPanel(d);
        };
        tbody.appendChild(tr);
    });
}

function loadStockDirectory() {
    fetch('api/get_all_stock_items.php?q=').then(r=>r.json()).then(rows=>{
        const tbody = document.getElementById('stock-dir-body');
        tbody.innerHTML = '';
        if (!rows.length) {
            tbody.innerHTML='<tr><td colspan="4" style="text-align:center;padding:8px;color:#888;font-size:10px;">No stock items found</td></tr>';
            return;
        }
        rows.forEach(row => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td style="font-weight:bold;color:#0a246a;font-size:11px;">${row.STOCK_NUMBER}</td>
                <td style="font-size:11px;">${row.BRAND_NAME||'—'}</td>
                <td style="font-size:11px;">${row.ITEM_TYPE||'—'}</td>
                <td style="font-size:11px;">${row.LOCATION||'—'}</td>`;
            tr.onclick = () => {
                document.querySelectorAll('#stock-dir-body tr').forEach(r=>r.classList.remove('row-selected'));
                tr.classList.add('row-selected');
                selectStockItem(row);
            };
            tbody.appendChild(tr);
        });
    });
}

function updateRightPanel(d) {
    document.getElementById('sel-invoice-no').value    = currentInvoiceNo || '—';
    const pricePerUnit = d.price_perunit
        ? parseFloat(d.price_perunit).toFixed(2)
        : (d.units_peritem > 0 ? (d.sale_price / d.units_peritem).toFixed(2) : '0.00');
    document.getElementById('sel-price-perunit').value = pricePerUnit;
    const days = d.expiry_date ? Math.ceil((new Date(d.expiry_date) - new Date()) / 86400000) : 999;
    const color = days <= 30 ? 'darkred' : days <= 90 ? '#b8860b' : 'darkgreen';
    const tbody = document.getElementById('stock-panel-body');
    tbody.innerHTML = `<tr>
        <td style="font-weight:bold;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${d.brand_name||'—'} ${d.item_name||''}</td>
        <td style="font-weight:bold;color:${color};">${formatDate(d.expiry_date)}</td>
        <td style="text-align:right;font-weight:bold;">${d.qty_available}</td>
    </tr>`;
}

function openCalcPopup() {
    const existing = document.getElementById('calc-popup-div');
    if (existing) { existing.remove(); return; }
    const div = document.createElement('div');
    div.id = 'calc-popup-div';
    div.style.cssText = 'position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);z-index:9000;background:#d4d0c8;border:2px solid #0a246a;box-shadow:4px 4px 12px rgba(0,0,0,0.5);width:220px;';
    div.innerHTML = `
        <div style="background:linear-gradient(to right,#0a246a,#3a6ea5);color:white;font-weight:bold;padding:4px 8px;display:flex;justify-content:space-between;align-items:center;cursor:move;user-select:none;" id="calc-bar">
            <span>Calculator</span>
            <span onclick="document.getElementById('calc-popup-div').remove()" style="cursor:pointer;font-size:14px;">&#x2716;</span>
        </div>
        <div style="padding:8px;">
            <input id="calc-display" type="text" readonly style="width:100%;text-align:right;font-size:16px;font-weight:bold;margin-bottom:6px;background:#ffff99;border:1px solid #808080;padding:3px 6px;height:28px;">
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:3px;">
                ${['7','8','9','÷','4','5','6','×','1','2','3','−','0','.','=','+'].map(k =>
                    `<button onclick="calcKey('${k}')" style="background:#d4d0c8;border:1px solid;border-color:#fff #808080 #808080 #fff;padding:5px;font-size:13px;font-weight:bold;cursor:pointer;">${k}</button>`
                ).join('')}
                <button onclick="calcKey('C')" style="grid-column:span 2;background:#8b0000;color:white;border:1px solid;border-color:#cc4444 #550000 #550000 #cc4444;padding:5px;font-size:12px;font-weight:bold;cursor:pointer;">Clear</button>
                <button onclick="calcKey('←')" style="grid-column:span 2;background:#003087;color:white;border:1px solid;border-color:#5599cc #002266 #002266 #5599cc;padding:5px;font-size:12px;cursor:pointer;">&#x2190; Del</button>
            </div>
        </div>`;
    document.body.appendChild(div);
    let calcVal = '', calcOp = '', calcPrev = '';
    window.calcKey = function(k) {
        const disp = document.getElementById('calc-display');
        if (k === 'C') { calcVal=''; calcOp=''; calcPrev=''; disp.value=''; return; }
        if (k === '←') { calcVal=calcVal.slice(0,-1); disp.value=calcVal; return; }
        if (k === '=' ) {
            if (!calcPrev || !calcOp || !calcVal) return;
            const a=parseFloat(calcPrev), b=parseFloat(calcVal);
            let r = k==='÷'?a/b : k==='×'?a*b : k==='−'?a-b : a+b;
            if (calcOp==='÷') r=a/b;
            else if (calcOp==='×') r=a*b;
            else if (calcOp==='−') r=a-b;
            else r=a+b;
            disp.value = parseFloat(r.toFixed(6)).toString();
            calcVal=disp.value; calcOp=''; calcPrev=''; return;
        }
        if (['÷','×','−','+'].includes(k)) {
            calcPrev=calcVal||disp.value; calcOp=k; calcVal='';
            disp.value = calcPrev + ' ' + k; return;
        }
        calcVal += k; disp.value=calcVal;
    };
    const bar = document.getElementById('calc-bar');
    let drag=false,ox=0,oy=0;
    bar.addEventListener('mousedown',e=>{drag=true;ox=e.clientX-div.offsetLeft;oy=e.clientY-div.offsetTop;div.style.transform='none';});
    document.addEventListener('mousemove',e=>{if(drag){div.style.left=(e.clientX-ox)+'px';div.style.top=(e.clientY-oy)+'px';}});
    document.addEventListener('mouseup',()=>drag=false);
}

function updateReceiptStatus() {
    const status = document.getElementById('update-status-input').value;
    if (!currentInvoiceNo) { toast('Load an invoice first','warn'); return; }
    fetch('api/update_receipt_status.php', { method:'POST', body:JSON.stringify({ invoice_no: currentInvoiceNo, status: status }) })
        .then(r=>r.json())
        .then(res => {
            if (res.success) { toast('Status updated to: ' + status, 'ok'); document.getElementById('receipt-status').value = status; }
            else toast('Error: ' + (res.error||'Unknown'), 'err');
        });
}

function recalcTotal() {
    const total = detailLines.reduce((s,d)=>s+d.amount, 0);
    const disc  = parseFloat(document.getElementById('discount-amt').value) || 0;
    const aggr  = total - disc;
    document.getElementById('total-amount').value = total.toFixed(2);
    document.getElementById('aggr-amt').value     = aggr.toFixed(2);
    document.getElementById('detail-total').value = total.toFixed(2);
}

function clearItemForm() {
    selectedStock = null;
    document.getElementById('item-brand-search').value = '';
    document.getElementById('item-stock-no').value     = '';
    document.getElementById('item-qty-received').value = 0;
    document.getElementById('item-expiry').value       = '';
    document.getElementById('item-batch').value        = '';
    document.getElementById('item-qty-avail').value    = '';
    document.getElementById('item-units-per').value    = 1;
    document.getElementById('item-sale-price').value      = 0;
    document.getElementById('item-purch-price').value     = 0;
    document.getElementById('item-price-perunit').value   = 0;
    document.getElementById('item-pprice-perunit').value  = 0;
    document.getElementById('item-amount').value       = '';
    document.getElementById('selected-item-label').textContent = 'No item selected';
}

function clearAll() {
    detailLines = []; currentInvoiceNo = null; selectedStock = null;
    document.getElementById('inv-no').value          = '';
    document.getElementById('inv-date').value         = '';
    document.getElementById('supplier-code').value    = '';
    document.getElementById('total-amount').value     = '';
    document.getElementById('aggr-amt').value         = '';
    document.getElementById('discount-amt').value     = 0;
    document.getElementById('receipt-status').value   = 'Y';
    document.getElementById('received-date').value    = '';
    document.getElementById('received-by').value      = '';
    clearItemForm();
    renderDetailTable();
    document.getElementById('detail-total').value = '';
    setStatus('Form cleared — ready for new stock receipt');
}

function saveStock() {
    if (!document.getElementById('supplier-code').value) { toast('Enter a Supplier Code','warn'); return; }
    if (!detailLines.length) { toast('Add at least one item line','warn'); return; }
    if (!document.getElementById('inv-date').value) { toast('Enter the Invoice Date (date on supplier challan)','warn'); return; }
    if (!document.getElementById('received-date').value) { toast('Enter the Received Date (date goods physically arrived)','warn'); return; }

    const total     = parseFloat(document.getElementById('total-amount').value) || 0;
    const discount  = parseFloat(document.getElementById('discount-amt').value) || 0;
    const invDate   = document.getElementById('inv-date').value;
    const recDate   = document.getElementById('received-date').value;
    const recBy     = document.getElementById('received-by').value;
    const supCode   = document.getElementById('supplier-code').value;
    const status    = document.getElementById('receipt-status').value;

    const payload = {
        invoice_no:    currentInvoiceNo,
        supplier_code: supCode,
        invoice_date:  invDate,
        received_date: recDate,
        received_by:   recBy,
        total_amount:  total,
        discount:      discount,
        status:        status,
        user_id:       'admin',
        lines:         detailLines.map(l => ({...l,
            price_perunit:  l.price_perunit  || 0,
            pprice_perunit: l.pprice_perunit || 0,
        })),
    };

    setStatus('Saving stock receipt…');
    fetch('api/save_stock_receipt.php', { method:'POST', body:JSON.stringify(payload) })
        .then(r=>r.json())
        .then(res => {
            if (res.success) {
                currentInvoiceNo = res.invoice_no;
                document.getElementById('inv-no').value = res.invoice_no;
                document.getElementById('update-status-badge').textContent = 'Saved ✓';
                toast('Stock receipt saved — Invoice #' + res.invoice_no,'ok');
                setStatus('Saved — Invoice #' + res.invoice_no);
                            } else {
                toast('Error: ' + (res.error||'Unknown'),'err');
                setStatus('Save failed');
            }
        });
}

function modifyReceipt() {
    const no = document.getElementById('inv-no').value;
    if (!no) { toast('Load an invoice first','warn'); return; }
    loadInvoiceDetail(no);
}

function loadInvoiceList() {
    fetch('api/get_stock_receipts.php').then(r=>r.json()).then(rows=>{
        const tbody = document.getElementById('invoice-list-body');
        tbody.innerHTML = '';
        if (!rows.length) {
            tbody.innerHTML='<tr><td colspan="7" style="text-align:center;padding:10px;color:#888;">No invoices yet — click New Invoice to create one</td></tr>';
            return;
        }
        rows.forEach(row => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td style="font-weight:bold;color:#0a246a;">${row.Invoice_no}</td>
                <td>${row.SUPPLIER_CODE||'—'}</td>
                <td>${row.INVOICE_DATE||'—'}</td>
                <td>${row.RECEIVED_DATE||'—'}</td>
                <td>${row.RECEIVED_BY||'—'}</td>
                <td><span style="font-weight:bold;color:${row.STATUS==='Y'?'darkgreen':'#b8860b'};">${row.STATUS||'—'}</span></td>
                <td style="text-align:right;font-weight:bold;">${parseFloat(row.TOTAL_AMOUNT||0).toFixed(2)}</td>`;
            tr.onclick = () => {
                document.querySelectorAll('#invoice-list-body tr').forEach(r => r.classList.remove('row-selected'));
                tr.classList.add('row-selected');
                loadInvoiceDetail(row.Invoice_no);
                closePopup('invoice-list-popup');
            };
            tbody.appendChild(tr);
        });
    });
}

function loadInvoiceDetail(invoiceNo) {
    fetch('api/get_stock_receipt_detail.php?id=' + invoiceNo).then(r=>r.json()).then(res=>{
        if (!res.header) { toast('Invoice not found','err'); return; }
        const h = res.header;
        currentInvoiceNo = h.Invoice_no;
        document.getElementById('inv-no').value        = h.Invoice_no;
        document.getElementById('sel-invoice-no').value = h.Invoice_no;
        document.getElementById('inv-date').value      = h.INVOICE_DATE ? h.INVOICE_DATE.substring(0,10) : '';
        document.getElementById('supplier-code').value = h.SUPPLIER_CODE||'';
        document.getElementById('discount-amt').value  = h.DISCOUNT||0;
        document.getElementById('receipt-status').value= h.STATUS||'Y';
        document.getElementById('received-date').value = h.RECEIVED_DATE ? h.RECEIVED_DATE.substring(0,10) : '';
        document.getElementById('received-by').value   = h.RECEIVED_BY||'';
        document.getElementById('total-amount').value  = parseFloat(h.TOTAL_AMOUNT||0).toFixed(2);
        document.getElementById('aggr-amt').value      = (parseFloat(h.TOTAL_AMOUNT||0) - parseFloat(h.DISCOUNT||0)).toFixed(2);

        detailLines = res.detail.map(d=>({
            stock_number:  d.STOCK_NUMBER,
            brand_name:    d.BRAND_NAME||'',
            item_name:     d.ITEM_NAME||'',
            batch_no:      d.BATCH_NO||'',
            expiry_date:   d.EXPIRY_DATE ? d.EXPIRY_DATE.substring(0,10) : '',
            qty_received:  d.ITEMS_RECEIVED||0,
            qty_available: d.ITEMS_AVAILABLE||0,
            units_peritem: d.UNITS_PERITEM||1,
            sale_price:    parseFloat(d.PRICE_PERITEM||0),
            purch_price:   parseFloat(d.PPRICE_PERITEM||0),
            amount:        parseFloat(d.ITEMS_RECEIVED||0) * parseFloat(d.PPRICE_PERITEM||0),
        }));
        detailLines.sort((a,b) => new Date(a.expiry_date) - new Date(b.expiry_date));
        renderDetailTable();
        recalcTotal();
        if (detailLines.length) updateRightPanel(detailLines[0]);
        setStatus('Loaded Invoice #' + invoiceNo);
        toast('Invoice #' + invoiceNo + ' loaded','ok');
    });
}

function loadStockPanel() {
    fetch('api/get_all_stock_items.php?q=').then(r=>r.json()).then(rows=>{
        const tbody = document.getElementById('stock-panel-body');
        tbody.innerHTML = '';
        if (!rows.length) { tbody.innerHTML='<tr><td colspan="3" style="text-align:center;padding:8px;color:#888;">No stock receipt data yet</td></tr>'; return; }
        rows.forEach(row => {
            const tr = document.createElement('tr');

            tr.innerHTML = `<td style="font-weight:bold;color:#0a246a;">${row.STOCK_NUMBER||'—'}</td><td>${row.BRAND_NAME||'—'}</td><td>${row.ITEM_TYPE||'—'}</td><td>${row.LOCATION||'—'}</td>`;
            tr.onclick = () => {
                fetch('api/get_all_stock_items.php?q=' + encodeURIComponent(row.STOCK_NUMBER)).then(r=>r.json()).then(d=>{ if(d.length) selectStockItem(d[0]); });
            };
            tbody.appendChild(tr);
        });
    });
}

document.addEventListener('click', e => {
    const dd = document.getElementById('item-search-dropdown');
    if (!dd.contains(e.target) && e.target.id !== 'item-brand-search') dd.style.display='none';
});


</script>
</body>
</html>