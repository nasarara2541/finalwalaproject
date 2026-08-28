<?php
require_once __DIR__ . '/includes/access.php';
requireAccess('inventory');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AISellProduct - Stock Receiving</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
* { font-family: Tahoma, 'Segoe UI', sans-serif; font-size: 12px; box-sizing: border-box; }
html, body { height: 100%; margin: 0; }
body { background: #d4d0c8; overflow: hidden; }

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
input[readonly], input.readonly-field, input:disabled {
    background: #d4d0c8 !important; color: #333;
}
.win-btn:disabled { opacity:0.5; cursor:default; }
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
<body class="flex flex-col h-screen">

<?php $SCREEN_NAME = 'Stock Receiving Form'; require __DIR__ . '/includes/navbar.php'; ?>

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
<div style="display:flex;flex-direction:column;flex:1;min-height:0;padding:5px;gap:4px;">

    <!-- ROW 1: Invoice header -->
    <div class="win-panel" style="padding:5px 8px;">
        <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
            <div class="field-row">
                <label class="lbl" style="background:#0a246a;color:white;padding:2px 8px;">Invoice#</label>
                <input id="inv-no" type="text" readonly class="readonly-field field-blue" style="width:70px;font-weight:bold;color:#0a246a;" tabindex="-1">
                <button class="win-btn" onclick="openPopup('invoice-list-popup');loadInvoiceList();" title="View all invoices" style="padding:0 6px;font-size:13px;">&#x229E;</button>
            </div>
            <div class="field-row">
                <label class="lbl">Inv Date</label>
                <input id="inv-date" type="date" style="width:110px;" title="Date on the supplier's invoice/challan">
            </div>
            <div class="field-row">
                <label class="lbl">Supplier Code</label>
                <input id="supplier-code" type="text" style="width:75px;" placeholder="Code">
                <button id="btn-pick-supplier" class="win-btn" onclick="openPopup('supplier-popup');loadSuppliers();" title="Pick Supplier">&#x229E;</button>
            </div>
            <div class="field-row">
                <label class="lbl">Type</label>
                <select id="purchase-type" style="width:70px;">
                    <option value="Cash">Cash</option>
                    <option value="Credit">Credit</option>
                </select>
            </div>
            <div class="field-row">
                <label class="lbl" title="The supplier's own invoice number, separate from our internal Invoice#">Supplier Invoice #</label>
                <input id="supplier-invoice-no" type="text" style="width:90px;" placeholder="Optional">
            </div>
            <div class="field-row">
                <label style="display:flex;align-items:center;gap:3px;font-weight:bold;cursor:pointer;">
                    <input id="loose-purchase" type="checkbox" style="width:auto;height:auto;">
                    Loose Purchase
                </label>
            </div>
            <div class="field-row">
                <label class="lbl">Total Amount</label>
                <input id="total-amount" type="text" readonly class="readonly-field" style="width:80px;font-weight:bold;font-size:13px;text-align:right;" tabindex="-1">
            </div>
            <div class="field-row">
                <label class="lbl" style="color:darkred;">Aggr. Amt</label>
                <input id="aggr-amt" type="text" readonly class="readonly-field" style="width:65px;text-align:right;font-weight:bold;" tabindex="-1">
            </div>
            <div style="margin-left:auto;display:flex;gap:4px;">
                <button id="btn-save-stock" class="win-btn win-btn-green" onclick="saveStock()" style="height:26px;font-size:12px;">&#x2714; Save-Stock</button>
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
            <div class="field-row">
                <label class="lbl" style="color:#8b6508;" title="Total printed on the supplier's paper invoice — compared against the computed Aggr. Amt to catch data-entry mistakes">Supplier Inv. Total</label>
                <input id="supplier-inv-total" type="number" min="0" step="0.01" style="width:80px;text-align:right;" placeholder="Optional">
            </div>
            <div class="field-row">
                <label class="lbl" title="Transport / loading charge (Adda charge)">Adda Chg</label>
                <input id="adda-charges" type="number" min="0" step="0.01" value="0" style="width:65px;text-align:right;">
            </div>
            <div class="field-row">
                <label class="lbl">Other Chg</label>
                <input id="other-charges" type="number" min="0" step="0.01" value="0" style="width:65px;text-align:right;">
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
                    <button id="btn-pick-item" class="win-btn" onclick="openPopup('stock-popup');loadStockItems();" title="Pick item">&#x229E;</button>
                </div>
                <div id="item-search-dropdown" style="position:absolute;z-index:900;background:#fff;border:1px solid #808080;max-height:160px;overflow-y:auto;display:none;min-width:240px;box-shadow:2px 2px 6px rgba(0,0,0,0.3);"></div>
            </div>
            <div style="display:flex;flex-direction:column;gap:1px;">
                <label class="lbl">Stock No.</label>
                <input id="item-stock-no" type="text" readonly class="readonly-field field-blue" style="width:72px;" tabindex="-1">
            </div>
            <div style="display:flex;flex-direction:column;gap:1px;">
                <label class="lbl" title="Number of boxes/cartons on this shipment">QTY Received (Boxes)</label>
                <input id="item-qty-received" type="number" min="0" value="0" style="width:55px;text-align:right;" oninput="recalcItemAmount()">
            </div>
            <div style="display:flex;flex-direction:column;gap:1px;">
                <label class="lbl" title="Free/bonus boxes given by the supplier on top of the paid quantity">Bonus (Boxes)</label>
                <input id="item-bonus-qty" type="number" min="0" value="0" style="width:52px;text-align:right;">
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
                <label class="lbl" title="Total pieces on hand for this product, added up across every batch (cartons available &times; pieces per carton)">Qty Available (pcs)</label>
                <input id="item-qty-avail" type="text" readonly class="readonly-field" style="width:75px;text-align:right;" tabindex="-1">
            </div>
            <div style="display:flex;flex-direction:column;gap:1px;">
                <label class="lbl" title="Bottles/pieces inside one box — fixed per item, loaded from the item master">Pcs Per Box</label>
                <input id="item-units-per" type="number" min="1" value="1" readonly class="readonly-field" tabindex="-1" style="width:52px;text-align:right;">
            </div>
            <div style="display:flex;flex-direction:column;gap:1px;">
                <label class="lbl" style="color:darkred;" title="Sale price for the WHOLE box — divided by Pcs Per Box to get the per-bottle price charged in Sale">Sales Price/Box</label>
                <input id="item-sale-price" type="number" min="0" step="0.01" value="0" style="width:65px;text-align:right;" oninput="recalcItemAmount()">
            </div>
            <div style="display:flex;flex-direction:column;gap:1px;">
                <label class="lbl" style="color:darkblue;" title="Purchase cost for the WHOLE box — loaded from the item master, not editable here">Purch. Price/Box</label>
                <input id="item-purch-price" type="number" min="0" step="0.01" value="0" readonly class="readonly-field" tabindex="-1" style="width:65px;text-align:right;">
            </div>
            <div style="display:flex;flex-direction:column;gap:1px;">
                <label class="lbl" title="Discount percentage on this line">Disc %</label>
                <input id="item-disc-pct" type="number" min="0" max="100" step="0.01" value="0" style="width:52px;text-align:right;" oninput="recalcItemAmount()">
            </div>
            <div style="display:flex;flex-direction:column;gap:1px;">
                <label class="lbl" title="GST percentage on this line">GST %</label>
                <input id="item-gst-pct" type="number" min="0" max="100" step="0.01" value="0" style="width:52px;text-align:right;" oninput="recalcItemAmount()">
            </div>
            <div style="display:flex;flex-direction:column;gap:1px;">
                <label class="lbl">Amount</label>
                <input id="item-amount" type="text" readonly class="readonly-field" style="width:70px;text-align:right;font-weight:bold;background:#ffff99!important;" tabindex="-1">
            </div>
            <input type="hidden" id="item-price-perunit">
            <input type="hidden" id="item-pprice-perunit">
            <div style="display:flex;flex-direction:column;gap:1px;">
                <label>&nbsp;</label>
                <button id="btn-save-line" class="win-btn win-btn-green" onclick="addDetailLine()" style="height:22px;">Save Line</button>
            </div>
            <div style="display:flex;flex-direction:column;gap:1px;">
                <label>&nbsp;</label>
                <button class="win-btn" onclick="clearItemForm()" style="height:22px;color:darkred;">Clear</button>
            </div>
        </div>
    </div>

    <!-- ROW 3: Received Items (this invoice only) + Expiry Info / Available Stock sidebar -->
    <div style="display:grid;grid-template-columns:1fr 300px;gap:4px;flex:1;min-height:0;">

        <!-- LEFT: This invoice's own lines only — decluttered, no more system-wide merge -->
        <div class="win-panel" style="display:flex;flex-direction:column;min-height:0;overflow:hidden;">
            <div class="win-section-label">
                <span>Received Items</span>
                <span id="detail-count" style="font-weight:normal;color:#555;"></span>
            </div>
            <div class="win-scroll" style="flex:1;">
                <table class="win-table" style="table-layout:fixed;font-size:11px;">
                    <colgroup>
                        <col style="width:65px;">
                        <col style="width:60px;">
                        <col style="width:75px;">
                        <col style="width:60px;">
                        <col style="width:55px;">
                        <col style="width:50px;">
                        <col style="width:50px;">
                        <col style="width:65px;">
                        <col style="width:65px;">
                        <col style="width:120px;">
                        <col style="width:65px;">
                        <col style="width:65px;">
                        <col style="width:30px;">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>Stock#</th>
                            <th style="text-align:right;">Qty Rec</th>
                            <th>Exp Date</th>
                            <th>Batch No</th>
                            <th style="text-align:right;">Qty Avail</th>
                            <th style="text-align:right;">Bon.</th>
                            <th style="text-align:right;">GST%</th>
                            <th style="text-align:right;">Sale/Item</th>
                            <th style="text-align:right;">Purch/Item</th>
                            <th>Brand Name</th>
                            <th style="text-align:right;">Amount</th>
                            <th style="text-align:right;" title="Current total on-hand for this item across all batches, as of when this line was added/loaded">Qty In Hand</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="detail-body">
                        <tr><td colspan="13" style="text-align:center;padding:10px;color:#888;">No items received yet — create a new invoice and add items above</td></tr>
                    </tbody>
                </table>
            </div>
            <div style="background:#d4d0c8;border-top:1px solid #808080;padding:4px 8px;display:flex;justify-content:flex-end;align-items:center;gap:8px;">
                <label style="font-weight:bold;font-size:13px;">Total:</label>
                <input id="detail-total" type="text" readonly class="readonly-field" style="width:85px;text-align:right;font-weight:bold;font-size:14px;color:#003087;" tabindex="-1">
                <div style="width:4px;"></div>
                <span id="update-status-badge" style="font-size:11px;color:#555;"></span>
            </div>
        </div>

        <!-- RIGHT: Expiry Info (per selected item) + searchable Available Stock -->
        <div style="display:flex;flex-direction:column;gap:4px;min-height:0;">

            <div class="win-panel" style="display:flex;flex-direction:column;flex:1;min-height:0;overflow:hidden;">
                <div class="win-section-label" style="font-size:11px;">
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

            <div class="win-panel" style="display:flex;flex-direction:column;flex:1;min-height:0;overflow:hidden;">
                <div class="win-section-label" style="font-size:11px;">
                    <span>Available Stock</span>
                    <span id="avail-stock-count" style="font-weight:normal;color:#555;"></span>
                </div>
                <div style="padding:4px;">
                    <input id="avail-stock-search" type="text" placeholder="Search stock…" style="width:100%;height:20px;" oninput="filterAvailableStock(this.value)">
                </div>
                <div class="win-scroll notebook-panel" style="flex:1;">
                    <table class="win-table" style="font-size:11px;background:transparent;">
                        <thead>
                            <tr>
                                <th>Stock#</th>
                                <th>Brand Name</th>
                                <th>Type</th>
                                <th style="text-align:right;">In Hand</th>
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
let globalLedger = [];
let formLocked = false;

const LOCKABLE_INPUT_IDS = ['inv-date','supplier-code','purchase-type','supplier-invoice-no','loose-purchase','discount-amt','receipt-status','received-date','received-by','adda-charges','other-charges',
    'item-brand-search','item-qty-received','item-bonus-qty','item-expiry','item-batch','item-units-per','item-sale-price','item-purch-price','item-disc-pct','item-gst-pct'];
const LOCKABLE_BUTTON_IDS = ['btn-pick-supplier','btn-pick-item','btn-save-line','btn-save-stock'];

function setFormLocked(isLocked) {
    formLocked = isLocked;
    LOCKABLE_INPUT_IDS.forEach(id => { const el = document.getElementById(id); if (el) el.disabled = isLocked; });
    LOCKABLE_BUTTON_IDS.forEach(id => { const el = document.getElementById(id); if (el) el.disabled = isLocked; });
    renderDetailTable();
}
function lockForm()   { setFormLocked(true); }
function unlockForm() { setFormLocked(false); }

function newInvoice() {
    clearAll();
    unlockForm();
    setStatus('New Invoice — entry unlocked');
}

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

function openPopup(id) { document.getElementById(id).classList.add('open'); }
function closePopup(id) { document.getElementById(id).classList.remove('open'); }

// Enter moves through the form top-to-bottom: header fields, then into the
// item-entry row, then Enter on the last field commits the line and jumps
// back to the item search box for the next product.
const stockRecFieldOrder = ['inv-date','supplier-code','supplier-invoice-no','discount-amt','received-date','received-by','supplier-inv-total','adda-charges','other-charges',
    'item-brand-search','item-qty-received','item-bonus-qty','item-expiry','item-batch','item-units-per','item-sale-price','item-purch-price','item-disc-pct','item-gst-pct'];

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        ['supplier-popup','stock-popup','invoice-list-popup'].forEach(id => closePopup(id));
    }
    if (e.key === 'F2')  { e.preventDefault(); document.getElementById('item-brand-search').focus(); }
    if (e.key === 'F8')  { e.preventDefault(); saveStock(); }
    if (e.key === 'F9')  { e.preventDefault(); clearAll(); }

    if (e.key === 'Enter') {
        const id = document.activeElement.id;

        if (id === 'item-brand-search') {
            e.preventDefault();
            clearTimeout(liveSearchTimer);
            const q = document.activeElement.value;
            if (!q.trim()) return;
            fetch('api/get_all_stock_items.php?q=' + encodeURIComponent(q)).then(r=>r.json()).then(rows=>{
                if (rows.length) selectStockItem(rows[0]);
            });
            return;
        }

        if (id === 'item-gst-pct') {
            e.preventDefault();
            if (addDetailLine()) document.getElementById('item-brand-search').focus();
            return;
        }

        const idx = stockRecFieldOrder.indexOf(id);
        if (idx !== -1 && idx < stockRecFieldOrder.length - 1) {
            e.preventDefault();
            document.getElementById(stockRecFieldOrder[idx + 1]).focus();
        }
    }
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
loadGlobalLedger();

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
    }).catch(() => {
        document.getElementById('supplier-popup-body').innerHTML =
            '<tr><td colspan="4" style="text-align:center;color:darkred;padding:8px;">Could not load suppliers — check DB connection</td></tr>';
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
        String(s.SUPPLIER_CODE ?? '').toLowerCase().includes(q.toLowerCase()) ||
        (s.SUPPLIER_NAME||'').toLowerCase().includes(q.toLowerCase())
    );
    renderSupplierTable(filtered);
}

function loadStockItems() {
    if (allStockItems.length) { renderStockPopup(allStockItems); return; }
    fetch('api/get_all_stock_items.php?q=').then(r=>r.json()).then(rows=>{
        allStockItems = rows;
        renderStockPopup(rows);
    }).catch(() => {
        document.getElementById('stock-popup-body').innerHTML =
            '<tr><td colspan="7" style="text-align:center;color:darkred;padding:8px;">Could not load items — check DB connection</td></tr>';
    });
}

function renderStockPopup(rows) {
    const tbody = document.getElementById('stock-popup-body');
    tbody.innerHTML = '';
    if (!rows.length) { tbody.innerHTML='<tr><td colspan="7" style="text-align:center;padding:8px;color:#888;">No items found</td></tr>'; return; }
    rows.forEach(s => {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td style="font-weight:bold;color:#0a246a;">${s.STOCK_NUMBER}</td><td>${s.BRAND_NAME||'—'}</td><td>${s.ITEM_NAME||'—'}</td><td>${s.ITEM_TYPE||'—'}</td><td>${s.VOLUME_L||'—'}</td><td style="text-align:right;">${parseFloat(s.PRICE||0).toFixed(2)}</td><td style="text-align:right;">${s.QTY_INHAND}</td>`;
        tr.onclick = () => { selectStockItem(s); closePopup('stock-popup'); };
        tbody.appendChild(tr);
    });
}

function filterStockItems(q) {
    const filtered = allStockItems.filter(s =>
        String(s.STOCK_NUMBER ?? '').toLowerCase().includes(q.toLowerCase()) ||
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
                        <button onclick="window.open('manufacture.php','_blank','width=1000,height=650')"
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

// Total pieces of this product actually on hand: add up ITEMS_AVAILABLE across
// every batch in the ledger, not just this one line's own batch.
// ITEMS_AVAILABLE is already stored in pieces (save_stock_receipt.php converts
// boxes x pcs/box once at receiving time, and save_transaction.php deducts sold
// pieces from it directly) — do NOT multiply by UNITS_PERITEM again here.
function computeTotalAvailablePieces(stockNumber) {
    return globalLedger
        .filter(r => r.STOCK_NUMBER === stockNumber && (parseInt(r.ITEMS_AVAILABLE)||0) > 0)
        .reduce((sum, r) => sum + (parseInt(r.ITEMS_AVAILABLE)||0), 0);
}

function selectStockItem(s) {
    selectedStock = s;
    document.getElementById('item-brand-search').value = (s.BRAND_NAME||'') + ' ' + (s.ITEM_NAME||'');
    document.getElementById('item-stock-no').value     = s.STOCK_NUMBER;
    document.getElementById('item-qty-avail').value    = computeTotalAvailablePieces(s.STOCK_NUMBER);
    renderExpiryInfo(s.STOCK_NUMBER);
    const defaultUnits = s.UNITS_PERITEM || 1;
    document.getElementById('item-units-per').value    = defaultUnits;
    // Item_Stock.PRICE/PURCHASE_PRICE are PER-BOTTLE; these fields are per-BOX,
    // so reconstruct a box price from each (price/bottle x pcs/box) — dividing
    // back by units_per on save reproduces the same per-bottle price instead
    // of crushing it. Purch. Price/Box is read-only here (locked to the item
    // master), so this reconstructed value is final, not just a starting point.
    document.getElementById('item-sale-price').value      = (parseFloat(s.PRICE||0) * defaultUnits).toFixed(2);
    document.getElementById('item-purch-price').value     = (parseFloat(s.PURCHASE_PRICE||0) * defaultUnits).toFixed(2);
    document.getElementById('item-qty-received').value = 1;
    document.getElementById('selected-item-label').textContent = (s.BRAND_NAME||'') + ' ' + (s.ITEM_NAME||'') + ' — ' + (s.VOLUME_L||'') + ' [' + s.STOCK_NUMBER + ']';
    document.getElementById('selected-item-label').style.color = 'darkred';
    recalcItemAmount();
    document.getElementById('item-qty-received').focus();
    document.getElementById('item-search-dropdown').style.display='none';
    setStatus('Item selected: ' + (s.BRAND_NAME||'') + ' ' + (s.ITEM_NAME||''));
}

function recalcItemAmount() {
    const qty     = parseFloat(document.getElementById('item-qty-received').value) || 0;
    const pPrice  = parseFloat(document.getElementById('item-purch-price').value)  || 0;
    const sPrice  = parseFloat(document.getElementById('item-sale-price').value)   || 0;
    const units   = parseInt(document.getElementById('item-units-per').value)      || 1;
    document.getElementById('item-amount').value = (qty * pPrice).toFixed(2);
    document.getElementById('item-price-perunit').value  = (units > 0 ? sPrice/units : 0).toFixed(2);
    document.getElementById('item-pprice-perunit').value = (units > 0 ? pPrice/units : 0).toFixed(2);
}

function addDetailLine() {
    if (!selectedStock) { toast('Select an item first','warn'); return false; }

    const qty     = parseInt(document.getElementById('item-qty-received').value)   || 0;
    const expiry  = document.getElementById('item-expiry').value;
    const batch   = document.getElementById('item-batch').value;
    const units   = parseInt(document.getElementById('item-units-per').value)      || 1;
    const sPrice  = parseFloat(document.getElementById('item-sale-price').value)   || 0;
    const pPrice  = parseFloat(document.getElementById('item-purch-price').value)  || 0;
    const amount  = qty * pPrice;
    if (qty <= 0)   { toast('Quantity received must be > 0','warn'); return false; }
    if (!expiry)    { toast('Expiry date is required','warn'); return false; }
    if (!batch)     { toast('Batch number is required','warn'); return false; }
    if (sPrice <= 0){ toast('Sales price must be > 0','warn'); return false; }

    const existing = detailLines.findIndex(d => d.stock_number === selectedStock.STOCK_NUMBER);
    if (existing !== -1) { toast('This item is already on this invoice. Remove it first to update.','warn'); return false; }

    const bonusQty = parseInt(document.getElementById('item-bonus-qty').value) || 0;
    const discPct  = parseFloat(document.getElementById('item-disc-pct').value) || 0;
    const gstPct   = parseFloat(document.getElementById('item-gst-pct').value)  || 0;
    const discAmount = amount * discPct / 100;
    const gstAmount   = (amount - discAmount) * gstPct / 100;

    const pricePerUnit  = parseFloat(document.getElementById('item-price-perunit').value)  || 0;
    const ppricePerUnit = parseFloat(document.getElementById('item-pprice-perunit').value) || 0;
    detailLines.push({
        stock_number:   selectedStock.STOCK_NUMBER,
        brand_name:     selectedStock.BRAND_NAME,
        item_name:      selectedStock.ITEM_NAME,
        qty_inhand:     selectedStock.QTY_INHAND != null ? selectedStock.QTY_INHAND : null,
        batch_no:       batch,
        expiry_date:    expiry,
        qty_received:   qty,
        qty_available:  qty,
        bonus_qty:      bonusQty,
        units_peritem:  units,
        sale_price:     sPrice,
        purch_price:    pPrice,
        price_perunit:  pricePerUnit,
        pprice_perunit: ppricePerUnit,
        disc_pct:       discPct,
        disc_amount:    discAmount,
        gst_pct:        gstPct,
        gst_amount:     gstAmount,
        amount:         amount,
    });

    const addedBrandName = selectedStock.BRAND_NAME;

    detailLines.sort((a,b) => new Date(a.expiry_date) - new Date(b.expiry_date));
    renderDetailTable();
    recalcTotal();
    clearItemForm();
    setStatus('Line added: ' + addedBrandName + ' Batch ' + batch);
    toast('Item line added','ok');
    return true;
}

function removeDetailLine(idx) {
    detailLines.splice(idx,1);
    renderDetailTable();
    recalcTotal();
}

// Shows only THIS invoice's own lines (detailLines) — decluttered, no more
// merging in the system-wide ledger. Expiry/batch history for any item is
// available separately via the Expiry Info panel (renderExpiryInfo()).
function renderDetailTable() {
    const tbody = document.getElementById('detail-body');
    tbody.innerHTML = '';
    document.getElementById('detail-count').textContent = detailLines.length + ' item(s)';
    if (!detailLines.length) {
        tbody.innerHTML='<tr><td colspan="13" style="text-align:center;padding:10px;color:#888;">No items received yet — create a new invoice and add items above</td></tr>';
        return;
    }
    detailLines.forEach((d, idx) => {
        const tr = document.createElement('tr');
        tr.className = expiryClass(d.expiry_date);
        const tdClass = expiryTdClass(d.expiry_date);
        const delCell = !formLocked
            ? `<button class="win-btn win-btn-red" onclick="removeDetailLine(${idx})" style="height:16px;font-size:10px;padding:0 4px;">X</button>`
            : '';
        // qty_received is ALWAYS boxes now, whether pending or reloaded from
        // a saved line (loadInvoiceDetail() reconstructs boxes on load, see
        // its comment) — so always multiply by pcs/box for display. Only
        // qty_available differs by state: pending lines start as a raw copy
        // of the box count (needs the same conversion), but a saved line's
        // qty_available is ITEMS_AVAILABLE, real pieces tracked by actual
        // sales history, not derivable from a box count — never convert it.
        const isSaved      = !!d._saved;
        const dispQtyRec   = d.qty_received * (d.units_peritem||1);
        const dispQtyAvail = isSaved ? d.qty_available : d.qty_available * (d.units_peritem||1);
        tr.innerHTML = `
            <td style="font-weight:bold;color:#0a246a;overflow:hidden;text-overflow:ellipsis;">${d.stock_number}</td>
            <td style="text-align:right;font-weight:bold;">${dispQtyRec}</td>
            <td class="${tdClass}" style="font-weight:bold;">${formatDate(d.expiry_date)}</td>
            <td style="overflow:hidden;text-overflow:ellipsis;">${d.batch_no||'—'}</td>
            <td style="text-align:right;">${dispQtyAvail}</td>
            <td style="text-align:right;">${d.bonus_qty||0}</td>
            <td style="text-align:right;">${(d.gst_pct||0).toFixed(2)}</td>
            <td style="text-align:right;color:darkred;">${(d.price_perunit||0).toFixed(2)}</td>
            <td style="text-align:right;color:darkblue;">${(d.pprice_perunit||0).toFixed(2)}</td>
            <td style="overflow:hidden;text-overflow:ellipsis;">${d.brand_name||'—'} ${d.item_name||''}</td>
            <td style="text-align:right;font-weight:bold;">${(d.amount||0).toFixed(2)}</td>
            <td style="text-align:right;color:#555;">${d.qty_inhand!=null ? d.qty_inhand : '—'}</td>
            <td style="text-align:center;">${delCell}</td>`;
        tr.onclick = (e) => {
            if (e.target.tagName === 'BUTTON') return;
            document.querySelectorAll('#detail-body tr').forEach(r => r.classList.remove('row-selected'));
            tr.classList.add('row-selected');
            renderExpiryInfo(d.stock_number);
        };
        tbody.appendChild(tr);
    });
}

// Loaded once for computeTotalAvailablePieces() and the Expiry Info panel —
// no longer merged into the main Received Items table.
function loadGlobalLedger() {
    fetch('api/get_stock_expiry_panel.php').then(r=>r.json()).then(rows=>{
        globalLedger = rows;
    }).catch(() => { globalLedger = []; });
}

// Shows every batch (system-wide) of whichever item was last clicked/selected,
// nearest expiry first — this is what used to be baked into the main table.
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
        const tdClass = expiryTdClass(r.expiry);
        tr.innerHTML = `<td>${r.batchInv}</td><td class="${tdClass}">${formatDate(r.expiry)}</td><td style="text-align:right;">${r.qtyAvail}</td>`;
        tbody.appendChild(tr);
    });
}

function loadStockDirectory() {
    fetch('api/get_all_stock_items.php?q=').then(r=>r.json()).then(rows=>{
        allStockItems = rows;
        renderAvailableStock(rows);
    }).catch(() => {
        document.getElementById('stock-dir-body').innerHTML =
            '<tr><td colspan="4" style="text-align:center;color:darkred;padding:8px;font-size:10px;">Could not load — check DB connection</td></tr>';
    });
}

function renderAvailableStock(rows) {
    const tbody = document.getElementById('stock-dir-body');
    tbody.innerHTML = '';
    document.getElementById('avail-stock-count').textContent = rows.length + ' item(s)';
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
            <td style="font-size:11px;text-align:right;">${row.QTY_INHAND!=null?row.QTY_INHAND:'—'}</td>`;
        tr.onclick = () => {
            document.querySelectorAll('#stock-dir-body tr').forEach(r=>r.classList.remove('row-selected'));
            tr.classList.add('row-selected');
            selectStockItem(row);
        };
        tbody.appendChild(tr);
    });
}

function filterAvailableStock(q) {
    q = q.trim().toLowerCase();
    const filtered = !q ? allStockItems : allStockItems.filter(s =>
        String(s.STOCK_NUMBER ?? '').toLowerCase().includes(q) ||
        (s.BRAND_NAME||'').toLowerCase().includes(q) ||
        (s.ITEM_NAME||'').toLowerCase().includes(q)
    );
    renderAvailableStock(filtered);
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
    document.getElementById('item-bonus-qty').value    = 0;
    document.getElementById('item-expiry').value       = '';
    document.getElementById('item-batch').value        = '';
    document.getElementById('item-qty-avail').value    = '';
    document.getElementById('item-units-per').value    = 1;
    document.getElementById('item-sale-price').value      = 0;
    document.getElementById('item-purch-price').value     = 0;
    document.getElementById('item-price-perunit').value   = 0;
    document.getElementById('item-pprice-perunit').value  = 0;
    document.getElementById('item-disc-pct').value     = 0;
    document.getElementById('item-gst-pct').value      = 0;
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
    document.getElementById('supplier-inv-total').value = '';
    document.getElementById('purchase-type').value      = 'Cash';
    document.getElementById('supplier-invoice-no').value = '';
    document.getElementById('loose-purchase').checked    = false;
    document.getElementById('adda-charges').value        = 0;
    document.getElementById('other-charges').value        = 0;
    document.getElementById('expiry-info-body').innerHTML = '<tr><td colspan="3" style="text-align:center;padding:10px;color:#888;font-size:10px;">Select an item to view expiry details.</td></tr>';
    document.getElementById('expiry-info-label').textContent = '';
    clearItemForm();
    renderDetailTable();
    document.getElementById('detail-total').value = '';
    unlockForm();
    setStatus('Form cleared — ready for new stock receipt');
}

function saveStock() {
    if (!document.getElementById('supplier-code').value) { toast('Enter a Supplier Code','warn'); return; }
    if (!detailLines.length) { toast('Add at least one item line','warn'); return; }
    if (!document.getElementById('inv-date').value) { toast('Enter the Invoice Date (date on supplier challan)','warn'); return; }
    if (!document.getElementById('received-date').value) { toast('Enter the Received Date (date goods physically arrived)','warn'); return; }

    const total     = parseFloat(document.getElementById('total-amount').value) || 0;
    const discount  = parseFloat(document.getElementById('discount-amt').value) || 0;
    const aggrAmt   = total - discount;
    const invDate   = document.getElementById('inv-date').value;
    const recDate   = document.getElementById('received-date').value;
    const recBy     = document.getElementById('received-by').value;
    const supCode   = document.getElementById('supplier-code').value;
    const status    = document.getElementById('receipt-status').value;

    // If the clerk entered the total printed on the supplier's paper invoice,
    // flag any mismatch against what the system computed from the line items
    // — a real difference usually means a price/qty typo somewhere above.
    const supplierTotalRaw = document.getElementById('supplier-inv-total').value;
    if (supplierTotalRaw !== '') {
        const supplierTotal = parseFloat(supplierTotalRaw) || 0;
        if (Math.abs(supplierTotal - aggrAmt) > 0.01) {
            const proceed = confirm(
                'Supplier Invoice Total (Rs. ' + supplierTotal.toFixed(2) + ') does not match ' +
                'the computed Aggr. Amt (Rs. ' + aggrAmt.toFixed(2) + ').\n\n' +
                'Save anyway?'
            );
            if (!proceed) { toast('Save cancelled — review the line items','warn'); return; }
        }
    }

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
        payment_type:        document.getElementById('purchase-type').value,
        supplier_invoice_no: document.getElementById('supplier-invoice-no').value,
        loose_purchase:      document.getElementById('loose-purchase').checked,
        adda_charges:        parseFloat(document.getElementById('adda-charges').value) || 0,
        other_charges:       parseFloat(document.getElementById('other-charges').value) || 0,
        lines:         detailLines.map(l => ({...l,
            price_perunit:  l.price_perunit  || 0,
            pprice_perunit: l.pprice_perunit || 0,
            bonus_qty:      l.bonus_qty    || 0,
            disc_pct:       l.disc_pct     || 0,
            disc_amount:    l.disc_amount  || 0,
            gst_pct:        l.gst_pct      || 0,
            gst_amount:     l.gst_amount   || 0,
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
                setStatus('Saved — Invoice #' + res.invoice_no + ' (locked; click Modify to edit further)');
                lockForm();
                loadGlobalLedger();
                            } else {
                toast('Error: ' + (res.error||'Unknown'),'err');
                setStatus('Save failed');
            }
        });
}

function modifyReceipt() {
    const no = document.getElementById('inv-no').value;
    if (!no) { toast('Load an invoice first','warn'); return; }
    unlockForm();
    setStatus('Editing Invoice #' + no + ' — make changes and click Save-Stock');
    toast('Invoice unlocked for editing','ok');
}

function loadInvoiceList() {
    fetch('api/get_stock_receipts.php').then(r=>r.json()).then(rows=>{
        const tbody = document.getElementById('invoice-list-body');
        tbody.innerHTML = '';
        if (!rows.length) {
            tbody.innerHTML='<tr><td colspan="7" style="text-align:center;padding:10px;color:#888;">No invoices yet — click New Invoice to create one</td></tr>';
            return;
        }
        renderInvoiceListRows(rows, tbody);
    }).catch(() => {
        document.getElementById('invoice-list-body').innerHTML =
            '<tr><td colspan="7" style="text-align:center;color:darkred;padding:10px;">Could not load invoices — check DB connection</td></tr>';
    });
}

function renderInvoiceListRows(rows, tbody) {
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
}

function loadInvoiceDetail(invoiceNo) {
    fetch('api/get_stock_receipt_detail.php?id=' + invoiceNo).then(r=>r.json()).then(res=>{
        if (!res.header) { toast('Invoice not found','err'); return; }
        const h = res.header;
        currentInvoiceNo = h.Invoice_no;
        document.getElementById('inv-no').value        = h.Invoice_no;
        document.getElementById('inv-date').value      = h.INVOICE_DATE ? h.INVOICE_DATE.substring(0,10) : '';
        document.getElementById('supplier-code').value = h.SUPPLIER_CODE||'';
        document.getElementById('discount-amt').value  = h.DISCOUNT||0;
        document.getElementById('receipt-status').value= h.STATUS||'Y';
        document.getElementById('received-date').value = h.RECEIVED_DATE ? h.RECEIVED_DATE.substring(0,10) : '';
        document.getElementById('received-by').value   = h.RECEIVED_BY||'';
        document.getElementById('total-amount').value  = parseFloat(h.TOTAL_AMOUNT||0).toFixed(2);
        document.getElementById('aggr-amt').value      = (parseFloat(h.TOTAL_AMOUNT||0) - parseFloat(h.DISCOUNT||0)).toFixed(2);
        document.getElementById('purchase-type').value      = h.PAYMENT_TYPE || 'Cash';
        document.getElementById('supplier-invoice-no').value = h.SUPPLIER_INVOICE_NO || '';
        document.getElementById('loose-purchase').checked    = !!h.LOOSE_PURCHASE;
        document.getElementById('adda-charges').value        = h.ADDA_CHARGES || 0;
        document.getElementById('other-charges').value       = h.OTHER_CHARGES || 0;

        detailLines = res.detail.map(d => {
            const units      = parseFloat(d.UNITS_PERITEM) || 1;
            const bonusBoxes = parseFloat(d.BONUS_QTY) || 0;
            const pPricePerBox = parseFloat(d.PPRICE_PERITEM||0) * units;
            // ITEMS_RECEIVED is stored in PIECES and includes bonus units
            // (save_stock_receipt.php: (qty_received + bonus_qty) * units).
            // save_stock_receipt.php always expects qty_received back as
            // BOXES, paid-only -- so re-derive that here by reversing both
            // conversions. Without this, re-saving an untouched loaded line
            // (e.g. editing just one other line on the invoice, then
            // clicking Save-Stock) would silently re-multiply this line's
            // stock count on every save: bonus would get added a second
            // time, and any item with UNITS_PERITEM > 1 would have its
            // received pieces multiplied by units all over again -- the
            // second bug already existed before bonus tracking was added,
            // this fixes both at once.
            const paidBoxes = Math.round((parseFloat(d.ITEMS_RECEIVED||0) / units) - bonusBoxes);
            return {
                stock_number:  d.STOCK_NUMBER,
                brand_name:    d.BRAND_NAME||'',
                item_name:     d.ITEM_NAME||'',
                qty_inhand:    d.QTY_INHAND != null ? parseFloat(d.QTY_INHAND) : null,
                batch_no:      d.BATCH_NO||'',
                expiry_date:   d.EXPIRY_DATE ? d.EXPIRY_DATE.substring(0,10) : '',
                qty_received:  paidBoxes,
                qty_available: d.ITEMS_AVAILABLE||0,
                bonus_qty:     bonusBoxes,
                units_peritem: units,
                // PRICE_PERITEM/PPRICE_PERITEM are per-bottle; reconstruct the
                // per-box price (what the save payload expects) by multiplying
                // back by units, same pattern as selectStockItem().
                sale_price:    parseFloat(d.PRICE_PERITEM||0) * units,
                purch_price:   pPricePerBox,
                price_perunit:  parseFloat(d.PRICE_PERITEM||0),
                pprice_perunit: parseFloat(d.PPRICE_PERITEM||0),
                // DECIMAL columns come back from sqlsrv as strings, not numbers
                // (same reason PRICE_PERITEM etc. above are already wrapped in
                // parseFloat) -- without this, renderDetailTable()'s .toFixed()
                // calls on these throw and silently blank the whole table.
                disc_pct:      parseFloat(d.LINE_DISC_PERCENT||0),
                disc_amount:   parseFloat(d.LINE_DISC_AMOUNT||0),
                gst_pct:       parseFloat(d.Tax_Percentage||0),
                gst_amount:    parseFloat(d.Tax_amount||0),
                // Paid boxes x per-box price -- matches how Amount was
                // computed at save time (qty_received x purch_price, bonus
                // excluded since bonus units are free). Using ITEMS_RECEIVED
                // directly here would wrongly bill bonus units as if paid for.
                amount:        paidBoxes * pPricePerBox,
                _saved:        true,
            };
        });
        detailLines.sort((a,b) => new Date(a.expiry_date) - new Date(b.expiry_date));
        renderDetailTable();
        recalcTotal();
        lockForm();
        setStatus('Viewing Invoice #' + invoiceNo + ' — click Modify to edit');
        toast('Invoice #' + invoiceNo + ' loaded','ok');
    });
}

document.addEventListener('click', e => {
    const dd = document.getElementById('item-search-dropdown');
    if (!dd.contains(e.target) && e.target.id !== 'item-brand-search') dd.style.display='none';
});


</script>
</body>
</html>