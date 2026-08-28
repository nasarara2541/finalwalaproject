<div style="display:flex;flex-direction:column;gap:4px;padding:5px;min-height:0;">

    <!-- Header fields -->
    <div class="win-panel" style="padding:8px;">
        <div style="display:flex;flex-wrap:wrap;gap:8px 20px;margin-bottom:6px;">
            <div class="field-cell" style="min-width:140px;">
                <label class="lbl">Trans #</label>
                <input type="text" id="sr-transaction-no" readonly disabled style="width:80px;">
            </div>
            <div class="field-cell" style="min-width:170px;">
                <label class="lbl">Invoice #</label>
                <input type="text" id="sr-invoice-no" class="field-blue" style="width:80px;">
                <button id="sr-btn-find" type="button" class="win-btn"><i class="fa-solid fa-magnifying-glass" style="font-size:10px;"></i> Find</button>
            </div>
            <div class="field-cell"><label class="lbl">Inv Date</label><input type="date" id="sr-invoice-date"></div>
            <div class="field-cell" style="position:relative;"><label class="lbl">Supplier</label><input type="text" id="sr-supplier-display" style="width:150px;"></div>
            <div class="field-cell"><label class="lbl">Total Amount</label><input type="number" id="sr-total-amount" style="width:90px;text-align:right;"></div>
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:8px 20px;">
            <div class="field-cell"><label class="lbl">Received Date</label><input type="date" id="sr-received-date"></div>
            <div class="field-cell"><label class="lbl">Status</label>
                <select id="sr-status"><option value="Y" selected>Yes</option><option value="NO">No</option></select>
            </div>
            <div class="field-cell"><label class="lbl">Received By</label><input type="text" id="sr-received-by" value="admin" style="width:90px;"></div>
            <div class="field-cell"><label class="lbl">Aggr. Amt</label><input type="text" id="sr-aggregate-amt" readonly disabled style="width:90px;text-align:right;"></div>
            <div class="field-cell" style="gap:6px;">
                <input type="checkbox" id="sr-loose-purchase" style="width:auto;height:auto;">
                <label for="sr-loose-purchase" style="font-weight:bold;color:#003087;">Loose Purchase</label>
            </div>
        </div>
    </div>

    <!-- Item entry row -->
    <div class="win-panel" style="padding:8px;">
        <div style="display:flex;flex-wrap:wrap;gap:6px 12px;align-items:end;">
            <div class="field-cell" style="flex:2;min-width:180px;"><label class="lbl" style="width:70px;">Item Name</label><input type="text" id="sr-search-term" autocomplete="off" class="field-blue" placeholder="Type to search..." style="width:100%;"></div>
            <div class="field-cell" style="min-width:90px;"><label class="lbl" style="width:60px;">Stock No.</label><input type="text" id="sr-stock-no" readonly disabled style="width:60px;"></div>
            <div class="field-cell" style="min-width:90px;"><label class="lbl" style="width:65px;">Item Recv.</label><input type="number" id="sr-qty-received" min="1" value="1" style="width:60px;"></div>
            <div class="field-cell" style="min-width:90px;"><label class="lbl" style="width:65px;">Bonus Item</label><input type="number" id="sr-bonus-qty" min="0" style="width:60px;"></div>
            <div class="field-cell" style="min-width:130px;"><label class="lbl" style="width:60px;">Expiry</label><input type="date" id="sr-expiry-date" style="width:130px;"></div>
            <div class="field-cell" style="min-width:100px;"><label class="lbl" style="width:60px;">Batch No.</label><input type="text" id="sr-batch-no" style="width:90px;"></div>
            <div class="field-cell" style="min-width:90px;"><label class="lbl" style="width:65px;">Qty Avail.</label><input type="text" id="sr-qty-available" readonly disabled style="width:60px;"></div>
            <div class="field-cell" style="min-width:90px;"><label class="lbl" style="width:70px;">Units/Item</label><input type="number" id="sr-units-per-item" readonly disabled style="width:60px;"></div>
            <div class="field-cell" style="min-width:90px;"><label class="lbl" style="width:65px;">Price/Item</label><input type="number" id="sr-sale-price" min="0" step="0.01" style="width:70px;text-align:right;"></div>
            <div class="field-cell" style="min-width:90px;"><label class="lbl" style="width:70px;">PPrice/Item</label><input type="number" id="sr-purchase-price" style="width:70px;text-align:right;"></div>
            <div class="field-cell" style="min-width:90px;"><label class="lbl" style="width:50px;">Group</label><input type="text" id="sr-group" placeholder="Group" style="width:80px;"></div>
            <div class="field-cell" style="min-width:80px;"><label class="lbl" style="width:55px;">Margin</label><div id="sr-margin-display" style="font-weight:bold;">-</div></div>
            <div class="field-cell" style="min-width:80px;"><label class="lbl" style="width:45px;">GST %</label><input type="number" id="sr-gst" step="0.01" placeholder="0.00" style="width:60px;text-align:right;"></div>
            <button id="sr-btn-add-item" type="button" disabled class="win-btn win-btn-blue"><i class="fa-solid fa-plus" style="font-size:10px;"></i> Save</button>
        </div>
    </div>

    <!-- Received Items + Available Stock -->
    <div style="display:flex;gap:4px;flex:1;min-height:0;">
        <div class="win-panel" style="flex:3;min-height:0;display:flex;flex-direction:column;">
            <div class="win-section-label"><span>Received Items</span><span id="sr-items-count" style="font-weight:normal;color:#555;">0 item(s)</span></div>
            <div style="flex:1;overflow:auto;min-height:0;">
                <table class="win-table">
                    <thead><tr>
                        <th>Stock No.</th><th>Qty Recv.</th><th>Bonus</th><th>Exp Date</th><th>Batch No.</th>
                        <th>Qty Avail</th><th>Units/Item</th><th>SalePrice</th><th>PurchPrice</th><th>Item</th><th>Amount</th><th>Del</th>
                    </tr></thead>
                    <tbody id="sr-items-body"><tr><td colspan="12" style="text-align:center;padding:20px;color:#888;">No items received yet</td></tr></tbody>
                </table>
            </div>
        </div>
        <div class="win-panel" style="flex:1;min-height:0;display:flex;flex-direction:column;">
            <div class="win-section-label"><span>Available Stock</span><span id="sr-stock-count" style="font-weight:normal;color:#555;">0 item(s)</span></div>
            <div style="flex:1;overflow:auto;min-height:0;">
                <table class="win-table">
                    <thead><tr><th>Stock#</th><th>Item Name</th><th>Type</th><th>In Hand</th></tr></thead>
                    <tbody id="sr-stock-body"><tr><td colspan="4" style="text-align:center;padding:20px;color:#888;">Loading…</td></tr></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Summary + actions -->
    <div class="win-panel" style="padding:8px;display:flex;flex-wrap:wrap;align-items:center;gap:14px;">
        <div class="field-cell" style="min-width:70px;"><label class="lbl" style="width:60px;">Packs</label><input id="sr-total-packs" type="text" readonly disabled style="width:50px;text-align:center;" value="0"></div>
        <div class="field-cell" style="min-width:70px;"><label class="lbl" style="width:60px;">Items</label><input id="sr-total-items" type="text" readonly disabled style="width:50px;text-align:center;" value="0"></div>
        <div class="field-cell" style="min-width:70px;"><label class="lbl" style="width:60px;">Units</label><input id="sr-total-units" type="text" readonly disabled style="width:50px;text-align:center;" value="0"></div>
        <div class="field-cell" style="min-width:70px;"><label class="lbl" style="width:60px;">Bonus</label><input id="sr-total-bonus" type="text" readonly disabled style="width:50px;text-align:center;" value="0"></div>
        <div class="field-cell" style="min-width:80px;"><label class="lbl" style="width:60px;">Flat Disc</label><input id="sr-flat-disc" type="number" min="0" step="0.01" style="width:60px;text-align:right;" value="0"></div>
        <div class="field-cell" style="min-width:80px;"><label class="lbl" style="width:60px;">Flat GST</label><input id="sr-flat-gst" type="number" min="0" step="0.01" style="width:60px;text-align:right;" value="0"></div>
        <div class="field-cell" style="min-width:80px;"><label class="lbl" style="width:60px;">Item Disc</label><input id="sr-item-disc" type="number" min="0" step="0.01" style="width:60px;text-align:right;" value="0"></div>
        <div class="field-cell" style="min-width:80px;"><label class="lbl" style="width:60px;">Item GST</label><input id="sr-item-gst" type="number" min="0" step="0.01" style="width:60px;text-align:right;" value="0"></div>
        <div class="field-cell" style="min-width:70px;"><label class="lbl" style="width:65px;">Total QTY</label><input id="sr-total-qty" type="text" readonly disabled style="width:50px;text-align:center;" value="0"></div>
        <div class="field-cell" style="min-width:100px;"><label class="lbl" style="width:80px;">Net Payable</label><input id="sr-net-payable" type="text" readonly disabled style="width:80px;text-align:right;font-weight:bold;color:#003087;" value="0.00"></div>
        <span style="flex:1"></span>
        <button id="sr-btn-post" type="button" class="win-btn win-btn-blue"><i class="fa-solid fa-check" style="font-size:11px;"></i> POST</button>
        <button id="sr-btn-modify" type="button" class="win-btn"><i class="fa-solid fa-pen" style="font-size:11px;"></i> MODIFY</button>
        <button id="sr-btn-cancellation" type="button" class="win-btn win-btn-red"><i class="fa-solid fa-times" style="font-size:11px;"></i> CANCELLATION</button>
    </div>

</div>
