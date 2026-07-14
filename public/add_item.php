<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Register New Item — AISellH2O</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
* { font-family: Tahoma, 'Segoe UI', sans-serif; font-size: 12px; box-sizing: border-box; }
body { background: #d4d0c8; margin: 0; padding: 0; }

.win-titlebar { background: linear-gradient(to right, #0a246a, #3a6ea5); color:white; font-weight:bold; padding:5px 10px; display:flex; align-items:center; justify-content:space-between; }

input[type=text], input[type=number], input[type=date] {
    border: 1px solid; border-color: #808080 #ffffff #ffffff #808080;
    background: #ffff99; padding: 2px 5px; height: 22px; font-size:12px;
    font-family: Tahoma, sans-serif; width: 100%;
}
input[readonly] { background: #d4d0c8 !important; }
input:focus { outline: 2px solid #0a246a; }

.win-btn { background:#d4d0c8; border:1px solid; border-color:#ffffff #808080 #808080 #ffffff; padding:3px 14px; cursor:pointer; font-size:12px; height:24px; font-family:Tahoma,sans-serif; white-space:nowrap; display:inline-flex; align-items:center; gap:3px; }
.win-btn:hover { background:#e8e4d8; }
.win-btn-green { background:#1a7a1a; color:white; border-color:#44aa44 #0a500a #0a500a #44aa44; }
.win-btn-green:hover { background:#1e8c1e; }

.win-panel { border:1px solid; border-color:#808080 #ffffff #ffffff #808080; background:#ece9d8; }
.win-section-label { background:#d4d0c8; font-weight:bold; padding:3px 8px; border-bottom:1px solid #808080; margin-bottom:6px; }

label.lbl { font-weight:bold; display:block; margin-bottom:2px; white-space:nowrap; }
.field-group { display:flex; flex-direction:column; gap:1px; }
.required-star { color:darkred; }

#toast { position:fixed; bottom:12px; right:12px; z-index:9999; padding:7px 14px; font-weight:bold; font-size:12px; border:1px solid; display:none; }
</style>
</head>
<body class="flex flex-col" style="min-height:100vh;">

<div class="win-titlebar">
    <span>&#x2795; Register New Stock Item — AISellH2O</span>
    <button onclick="window.close()" style="background:transparent;border:none;color:white;cursor:pointer;font-size:14px;font-weight:bold;">&#x2716;</button>
</div>

<div style="padding:8px;flex:1;overflow-y:auto;">

    <div class="win-panel" style="padding:8px;margin-bottom:6px;">
        <div class="win-section-label">Item Identity</div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;">
            <div class="field-group">
                <label class="lbl">Stock Number <span class="required-star">*</span></label>
                <input id="stock-number" type="text" placeholder="e.g. WTR-0006">
            </div>
            <div class="field-group">
                <label class="lbl">Brand Name <span class="required-star">*</span></label>
                <input id="brand-name" type="text" placeholder="e.g. Margalla Pure Life">
            </div>
            <div class="field-group">
                <label class="lbl">Item Name <span class="required-star">*</span></label>
                <input id="item-name" type="text" placeholder="e.g. 19L Jar">
            </div>
            <div class="field-group">
                <label class="lbl">Item Type</label>
                <input id="item-type" type="text" placeholder="e.g. Jar / Bottle / Can">
            </div>
            <div class="field-group">
                <label class="lbl">Stock Type</label>
                <input id="stock-type" type="text" placeholder="e.g. Water / Beverage">
            </div>
            <div class="field-group">
                <label class="lbl">Volume / ML</label>
                <input id="volume-ml" type="text" placeholder="e.g. 19000ml">
            </div>
        </div>
    </div>

    <div class="win-panel" style="padding:8px;margin-bottom:6px;">
        <div class="win-section-label">Size &amp; Packaging</div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;">
            <div class="field-group">
                <label class="lbl">Size Description</label>
                <input id="size-desc" type="text" placeholder="e.g. 19L">
            </div>
            <div class="field-group">
                <label class="lbl">Units Per Item</label>
                <input id="units-peritem" type="number" min="1" value="1" placeholder="1">
            </div>
            <div class="field-group">
                <label class="lbl">Unit Type</label>
                <input id="unit-type" type="text" placeholder="e.g. Pack / Carton / Piece">
            </div>
            <div class="field-group">
                <label class="lbl">OTC Qty</label>
                <input id="otc-qty" type="number" min="0" value="0">
            </div>
            <div class="field-group">
                <label class="lbl">Barcode</label>
                <input id="barcode" type="text" placeholder="Optional barcode">
            </div>
            <div class="field-group">
                <label class="lbl">Location</label>
                <input id="location" type="text" placeholder="e.g. Shelf A / Warehouse 1">
            </div>
        </div>
    </div>

    <div class="win-panel" style="padding:8px;margin-bottom:6px;">
        <div class="win-section-label">Pricing &amp; Stock</div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;">
            <div class="field-group">
                <label class="lbl">Sale Price (Rs.) <span class="required-star">*</span></label>
                <input id="price" type="number" min="0" step="0.01" value="0">
            </div>
            <div class="field-group">
                <label class="lbl">Qty In Hand</label>
                <input id="qty-inhand" type="number" min="0" value="0">
            </div>
            <div class="field-group">
                <label class="lbl">Discount %</label>
                <input id="percentage-disc" type="number" min="0" max="100" step="0.01" value="0">
            </div>
            <div class="field-group">
                <label class="lbl">Available Status</label>
                <input id="available-status" type="text" value="Active" placeholder="Active / Inactive">
            </div>
            <div class="field-group">
                <label class="lbl">Suppliers List</label>
                <input id="suppliers-list" type="text" placeholder="Comma separated supplier codes">
            </div>
        </div>
    </div>

    <div style="display:flex;justify-content:flex-end;gap:6px;padding:4px 0;">
        <button class="win-btn win-btn-green" onclick="saveItem()" style="height:26px;font-size:12px;padding:0 18px;">&#x2714; Save Item</button>
        <button class="win-btn" onclick="clearForm()" style="height:26px;">Clear</button>
        <button class="win-btn" onclick="window.close()" style="height:26px;color:darkred;">&#x2716; Close</button>
    </div>

    <div style="color:#8b0000;font-size:11px;padding:3px 0;">
        <span class="required-star">*</span> Required fields. After saving, go back to Stock Receiving and search for the item again.
    </div>
</div>

<div id="toast"></div>

<script>
function toast(msg, type) {
    const el = document.getElementById('toast');
    el.textContent = msg;
    el.style.display = 'block';
    el.style.background = type==='ok'?'#1a7a1a':type==='warn'?'#b8860b':'#990000';
    el.style.color = 'white';
    el.style.borderColor = type==='ok'?'#0a500a':type==='warn'?'#8b6508':'#660000';
    setTimeout(()=>{ el.style.display='none'; }, 3500);
}

function saveItem() {
    const stockNo = document.getElementById('stock-number').value.trim();
    const brand   = document.getElementById('brand-name').value.trim();
    const item    = document.getElementById('item-name').value.trim();
    const price   = parseFloat(document.getElementById('price').value) || 0;

    if (!stockNo) { toast('Stock Number is required','warn'); document.getElementById('stock-number').focus(); return; }
    if (!brand)   { toast('Brand Name is required','warn');   document.getElementById('brand-name').focus();   return; }
    if (!item)    { toast('Item Name is required','warn');     document.getElementById('item-name').focus();    return; }
    if (price <= 0){ toast('Sale Price must be greater than 0','warn'); document.getElementById('price').focus(); return; }

    const payload = {
        stock_number:     stockNo,
        brand_name:       brand,
        item_name:        item,
        item_type:        document.getElementById('item-type').value.trim(),
        stock_type:       document.getElementById('stock-type').value.trim(),
        volume_ml:        document.getElementById('volume-ml').value.trim(),
        size_desc:        document.getElementById('size-desc').value.trim(),
        units_peritem:    parseInt(document.getElementById('units-peritem').value) || 1,
        unit_type:        document.getElementById('unit-type').value.trim(),
        otc_qty:          parseInt(document.getElementById('otc-qty').value) || 0,
        barcode:          document.getElementById('barcode').value.trim(),
        location:         document.getElementById('location').value.trim(),
        price:            price,
        qty_inhand:       parseInt(document.getElementById('qty-inhand').value) || 0,
        percentage_disc:  parseFloat(document.getElementById('percentage-disc').value) || 0,
        available_status: document.getElementById('available-status').value.trim() || 'Active',
        suppliers_list:   document.getElementById('suppliers-list').value.trim(),
    };

    fetch('api/add_item.php', { method:'POST', body:JSON.stringify(payload) })
        .then(r=>r.json())
        .then(res => {
            if (res.success) {
                toast('Item saved successfully! You can now search for it in Stock Receiving.','ok');
                setTimeout(()=>{ clearForm(); }, 2000);
            } else {
                toast('Error: ' + (res.error||'Unknown error'),'err');
            }
        })
        .catch(()=>toast('Network error — check connection','err'));
}

function clearForm() {
    ['stock-number','brand-name','item-name','item-type','stock-type','volume-ml',
     'size-desc','unit-type','barcode','location','suppliers-list'].forEach(id=>{
        document.getElementById(id).value = '';
    });
    document.getElementById('units-peritem').value    = 1;
    document.getElementById('otc-qty').value          = 0;
    document.getElementById('price').value            = 0;
    document.getElementById('qty-inhand').value       = 0;
    document.getElementById('percentage-disc').value  = 0;
    document.getElementById('available-status').value = 'Active';
    document.getElementById('stock-number').focus();
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') window.close();
    if (e.key === 'F8') { e.preventDefault(); saveItem(); }
});

document.getElementById('stock-number').focus();
</script>
</body>
</html>
