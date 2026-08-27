(function () {
    const transNoEl = document.getElementById('sr-transaction-no');
    const invoiceNoEl = document.getElementById('sr-invoice-no');
    const invoiceDateEl = document.getElementById('sr-invoice-date');
    const supplierDisplayEl = document.getElementById('sr-supplier-display');
    const totalAmountEl = document.getElementById('sr-total-amount');
    const receivedDateEl = document.getElementById('sr-received-date');
    const statusEl = document.getElementById('sr-status');
    const receivedByEl = document.getElementById('sr-received-by');
    const aggregateAmtEl = document.getElementById('sr-aggregate-amt');
    const looseCheckbox = document.getElementById('sr-loose-purchase');
    const btnFind = document.getElementById('sr-btn-find');

    const searchInput = document.getElementById('sr-search-term');
    const stockNoEl = document.getElementById('sr-stock-no');
    const qtyRecvEl = document.getElementById('sr-qty-received');
    const bonusQtyEl = document.getElementById('sr-bonus-qty');
    const expiryEl = document.getElementById('sr-expiry-date');
    const batchEl = document.getElementById('sr-batch-no');
    const qtyAvailEl = document.getElementById('sr-qty-available');
    const unitsEl = document.getElementById('sr-units-per-item');
    const salePriceEl = document.getElementById('sr-sale-price');
    const purchasePriceEl = document.getElementById('sr-purchase-price');
    const groupEl = document.getElementById('sr-group');
    const marginDisplay = document.getElementById('sr-margin-display');
    const gstEl = document.getElementById('sr-gst');
    const btnAddItem = document.getElementById('sr-btn-add-item');

    const itemsBody = document.getElementById('sr-items-body');
    const itemsCount = document.getElementById('sr-items-count');
    const stockBody = document.getElementById('sr-stock-body');
    const stockCount = document.getElementById('sr-stock-count');

    const totalPacksEl = document.getElementById('sr-total-packs');
    const totalItemsEl = document.getElementById('sr-total-items');
    const totalUnitsEl = document.getElementById('sr-total-units');
    const totalBonusEl = document.getElementById('sr-total-bonus');
    const flatDiscEl = document.getElementById('sr-flat-disc');
    const flatGstEl = document.getElementById('sr-flat-gst');
    const itemGstEl = document.getElementById('sr-item-gst');
    const totalQtyEl = document.getElementById('sr-total-qty');
    const netPayableEl = document.getElementById('sr-net-payable');

    const btnPost = document.getElementById('sr-btn-post');
    const btnModify = document.getElementById('sr-btn-modify');
    const btnCancellation = document.getElementById('sr-btn-cancellation');

    let receivedCart = [];
    let selectedItem = null;
    let allProducts = [];
    let allSuppliers = [];
    let suppliersLoaded = false;
    let isEditMode = false; 
    async function fetchNextTransNo() {
        try {
            const res = await fetch('api/purchase_order.php?action=next_trans_no');
            const data = await res.json();
            if (data.success && transNoEl) transNoEl.value = data.next_trans_no;
        } catch (e) { console.error(e); }
    }
    invoiceNoEl.addEventListener('input', () => {
        if (isEditMode) {
            isEditMode = false;
            fetchNextTransNo();
        }
    });

    async function fetchSuppliers() {
        if (suppliersLoaded) return;
        try {
            const res = await fetch('api/purchase_order.php?action=suppliers');
            const data = await res.json();
            if (data.success && data.data.length) {
                allSuppliers = data.data;
                suppliersLoaded = true;
            }
        } catch (e) { console.error('Supplier fetch error:', e); }
    }

    let supplierHighlightedIdx = -1;

    function showFilteredSupplierDropdown(filtered) {
        let existing = document.getElementById('sr-supplier-dropdown');
        if (existing) existing.remove();

        const dropdown = document.createElement('div');
        dropdown.id = 'sr-supplier-dropdown';
        dropdown.className = 'absolute z-50 bg-white shadow max-h-32 overflow-y-auto';
        dropdown.style.cssText = 'width:180px; top:100%; left:0; border: 1px solid #e2e8f0; outline: none;';
        dropdown.tabIndex = 0;

        if (!filtered.length) {
            dropdown.innerHTML = `<div class="px-2 py-1.5 text-[11px] text-gray-400">No matches found</div>`;
        } else {
            dropdown.innerHTML = filtered.map((s, idx) => `
                <div class="px-2 py-1.5 text-[11px] cursor-pointer border-b border-gray-100 last:border-b-0 sr-sup-row"
                     data-code="${s.SUPPLIER_CODE}" data-idx="${idx}">
                    ${s.SUPPLIER_CODE} - ${s.SUPPLIER_NAME}
                </div>
            `).join('');
        }

        supplierDisplayEl.parentElement.style.position = 'relative';
        supplierDisplayEl.parentElement.appendChild(dropdown);
        supplierHighlightedIdx = -1;

        dropdown.querySelectorAll('.sr-sup-row').forEach(row => {
            row.addEventListener('click', () => selectSupplier(row));
        });

        setTimeout(() => {
            document.addEventListener('click', function handler(e) {
                if (!dropdown.contains(e.target) && e.target !== supplierDisplayEl) {
                    dropdown.remove();
                    document.removeEventListener('click', handler);
                }
            });
        }, 0);
    }

    function highlightSupplierRow(rows, idx) {
        rows.forEach((row, i) => row.classList.toggle('bg-gray-50', i === idx));
        if (rows[idx]) rows[idx].scrollIntoView({ block: 'nearest' });
    }

    function selectSupplier(row) {
        const code = row.getAttribute('data-code');
        supplierDisplayEl.value = code;
        supplierDisplayEl.dataset.supplierCode = code;
        const dropdown = document.getElementById('sr-supplier-dropdown');
        if (dropdown) dropdown.remove();
    }

    supplierDisplayEl.addEventListener('input', async function () {
        const term = this.value.trim().toLowerCase();
        if (!suppliersLoaded) await fetchSuppliers();
        if (!allSuppliers.length) return;

        if (term === '') {
            const existing = document.getElementById('sr-supplier-dropdown');
            if (existing) existing.remove();
            return;
        }
        const filtered = allSuppliers.filter(s =>
            String(s.SUPPLIER_CODE ?? '').toLowerCase().includes(term) ||
            String(s.SUPPLIER_NAME ?? '').toLowerCase().includes(term)
        );
        showFilteredSupplierDropdown(filtered);
    });

    supplierDisplayEl.addEventListener('keydown', function (e) {
        const dropdown = document.getElementById('sr-supplier-dropdown');
        if (!dropdown) return;
        const rows = dropdown.querySelectorAll('.sr-sup-row');
        if (!rows.length) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            supplierHighlightedIdx = Math.min(supplierHighlightedIdx + 1, rows.length - 1);
            highlightSupplierRow(rows, supplierHighlightedIdx);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            supplierHighlightedIdx = Math.max(supplierHighlightedIdx - 1, 0);
            highlightSupplierRow(rows, supplierHighlightedIdx);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (supplierHighlightedIdx >= 0 && rows[supplierHighlightedIdx]) {
                selectSupplier(rows[supplierHighlightedIdx]);
            }
        } else if (e.key === 'Escape') {
            dropdown.remove();
        }
    });

    async function fetchAvailableStock() {
        try {
            const res = await fetch('api/available_products.php');
            const data = await res.json();
            if (data.success) allProducts = data.data;
        } catch (e) { console.error('Failed to fetch stock:', e); }
    }

    function filterAvailableProducts(term) {
        if (!term) { renderStockTable([]); return; }
        if (allProducts.length === 0) {
            fetchAvailableStock().then(() => applyFilter(term));
        } else {
            applyFilter(term);
        }
    }
    function applyFilter(term) {
        const lower = term.toLowerCase();
        const filtered = allProducts.filter(i => (i.ITEM_NAME ?? '').toLowerCase().includes(lower));
        renderStockTable(filtered);
    }

    let highlightedRowIdx = -1;

    function renderStockTable(items) {
        if (stockCount) stockCount.textContent = `${items.length} item(s)`;
        if (!items.length) {
            stockBody.innerHTML = `<tr><td colspan="4" class="px-3 py-6 text-center text-gray-400">Type to search for stock</td></tr>`;
            return;
        }
        stockBody.innerHTML = items.map((item, idx) => `
            <tr class="sr-stock-row" data-idx="${idx}" data-stock="${item.STOCK_NUMBER}">
                <td class="pl-4 pr-2 py-1.5 text-gray-700">${item.STOCK_NUMBER}</td>
                <td class="px-2 py-1.5 text-gray-700">${item.ITEM_NAME ?? ''}</td>
                <td class="px-2 py-1.5 text-gray-700">${item.ITEM_TYPE ?? ''}</td>
                <td class="px-2 py-1.5 text-center text-gray-700">${item.QTY_INHAND ?? 0}</td>
            </tr>
        `).join('');

        stockBody.querySelectorAll('.sr-stock-row').forEach(row => {
            row.addEventListener('click', () => {
                const idx = parseInt(row.getAttribute('data-idx'));
                setHighlight(idx);
                loadItemByStockNo(row.getAttribute('data-stock'));
            });
        });
    }

    function setHighlight(idx) {
        highlightedRowIdx = idx;
        stockBody.querySelectorAll('.sr-stock-row').forEach((row, i) => {
            if (i === idx) {
                row.classList.add('bg-blue-50');
                row.scrollIntoView({ block: 'nearest' });
            } else {
                row.classList.remove('bg-blue-50');
            }
        });
    }

    searchInput.addEventListener('keydown', (e) => {
        const rows = stockBody.querySelectorAll('.sr-stock-row');
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            setHighlight(Math.min(highlightedRowIdx + 1, rows.length - 1));
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            setHighlight(Math.max(highlightedRowIdx - 1, 0));
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (highlightedRowIdx >= 0 && rows[highlightedRowIdx]) {
                loadItemByStockNo(rows[highlightedRowIdx].getAttribute('data-stock'));
            } else if (rows.length > 0) {
                setHighlight(0);
            }
        }
    });

    searchInput.addEventListener('input', () => {
        const term = searchInput.value.trim();
        term ? filterAvailableProducts(term) : renderStockTable([]);
        highlightedRowIdx = -1;
    });

    async function loadItemByStockNo(stockNo) {
        try {
            const res = await fetch(`api/get_item.php?stock_no=${encodeURIComponent(stockNo)}`);
            const data = await res.json();
            if (!data.success) return;
            selectedItem = data.data;
            stockNoEl.value = selectedItem.STOCK_NUMBER;
            qtyAvailEl.value = selectedItem.QTY_INHAND ?? 0;
            salePriceEl.value = selectedItem.PRICE ?? 0;
            purchasePriceEl.value = selectedItem.PURCHASE_PRICE ?? 0;
            qtyRecvEl.value = 1;
            bonusQtyEl.value = 0;
            unitsEl.value = looseCheckbox.checked ? 1 : (selectedItem.UNITS_PERITEM ?? 1);
            searchInput.value = selectedItem.ITEM_NAME ?? '';
            btnAddItem.disabled = false;
            recalcMargin();
        } catch (e) { console.error(e); }
    }

    function clearEntryFields() {
        selectedItem = null;
        searchInput.value = '';
        stockNoEl.value = '';
        qtyRecvEl.value = 1;
        bonusQtyEl.value = 0;
        expiryEl.value = '';
        batchEl.value = '';
        qtyAvailEl.value = '';
        unitsEl.value = looseCheckbox.checked ? 1 : '';
        salePriceEl.value = '';
        purchasePriceEl.value = '';
        groupEl.value = '';
        gstEl.value = '';
        marginDisplay.textContent = '—';
        btnAddItem.disabled = true;
        renderStockTable([]);
    }

    looseCheckbox.addEventListener('change', () => {
        if (looseCheckbox.checked) {
            unitsEl.value = 1;
            unitsEl.disabled = true;
        } else {
            unitsEl.disabled = false;
        }
    });

    function recalcMargin() {
        const sale = parseFloat(salePriceEl.value) || 0;
        const purchase = parseFloat(purchasePriceEl.value) || 0;
        const margin = sale - purchase;
        marginDisplay.textContent = margin.toFixed(2);
        marginDisplay.className = 'text-sm font-bold px-2 py-1.5 ' + (margin < 0 ? 'text-red-600' : 'text-emerald-700');
    }
    salePriceEl.addEventListener('input', recalcMargin);
    purchasePriceEl.addEventListener('input', recalcMargin);

    function recalcTotals() {
        const totalPacks = receivedCart.reduce((s, r) => s + r.qtyReceived, 0);
        const totalBonus = receivedCart.reduce((s, r) => s + (r.bonusQty || 0), 0);
        const totalUnits = receivedCart.reduce((s, r) => s + ((r.qtyReceived + (r.bonusQty || 0)) * (r.unitsPerItem || 1)), 0);
        const distinctItems = new Set(receivedCart.map(r => r.stockNo)).size;
        const totalAmount = receivedCart.reduce((s, r) => s + r.amount, 0);
        const itemGstSum = receivedCart.reduce((s, r) => s + (r.taxAmount || 0), 0);

        totalPacksEl.value = totalPacks;
        totalItemsEl.value = distinctItems;
        totalUnitsEl.value = totalUnits;
        totalBonusEl.value = totalBonus;
        totalQtyEl.value = totalPacks + totalBonus;
        itemGstEl.value = itemGstSum.toFixed(2);
        aggregateAmtEl.value = totalAmount.toFixed(2);

        const flatDisc = parseFloat(flatDiscEl.value) || 0;
        const flatGst = parseFloat(flatGstEl.value) || 0;
        netPayableEl.value = (totalAmount - flatDisc + flatGst).toFixed(2);
    }
    [flatDiscEl, flatGstEl].forEach(el => el.addEventListener('input', recalcTotals));

    function renderCart() {
        if (!receivedCart.length) {
            itemsBody.innerHTML = `<tr><td colspan="12" class="px-3 py-8 text-center text-gray-400">No items received yet</td></tr>`;
        } else {
            itemsBody.innerHTML = receivedCart.map((row, idx) => `
                <tr>
                    <td class="px-2 py-1.5 text-center border-r border-gray-200">${row.stockNo}</td>
                    <td class="px-2 py-1.5 text-center border-r border-gray-200">${row.qtyReceived}</td>
                    <td class="px-2 py-1.5 text-center border-r border-gray-200">${row.bonusQty}</td>
                    <td class="px-2 py-1.5 text-center border-r border-gray-200">${row.expiry}</td>
                    <td class="px-2 py-1.5 text-center border-r border-gray-200">${row.batch}</td>
                    <td class="px-2 py-1.5 text-center border-r border-gray-200">${row.qtyAvail}</td>
                    <td class="px-2 py-1.5 text-center border-r border-gray-200">${row.unitsPerItem}</td>
                    <td class="px-2 py-1.5 text-center border-r border-gray-200">${row.salePrice.toFixed(2)}</td>
                    <td class="px-2 py-1.5 text-center border-r border-gray-200">${row.purchasePrice.toFixed(2)}</td>
                    <td class="px-2 py-1.5 text-center border-r border-gray-200">${row.name}</td>
                    <td class="px-2 py-1.5 text-right border-r border-gray-200">${row.amount.toFixed(2)}</td>
                    <td class="px-2 py-1.5 text-center">
                        <button type="button" class="text-red-400 hover:text-red-600 sr-del-row" data-idx="${idx}">
                            <i class="fa-solid fa-trash-can text-sm"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
            itemsBody.querySelectorAll('.sr-del-row').forEach(btn => {
                btn.addEventListener('click', () => {
                    receivedCart.splice(parseInt(btn.getAttribute('data-idx')), 1);
                    renderCart();
                });
            });
        }
        itemsCount.textContent = `${receivedCart.length} item(s)`;
        recalcTotals();
    }

    btnAddItem.addEventListener('click', () => {
        if (!selectedItem) return;
        if (!salePriceEl.value) { showToast('Please enter Sale Price.', 'warning'); return; }

        const qty = parseInt(qtyRecvEl.value) || 1;
        const bonus = parseInt(bonusQtyEl.value) || 0;
        const purch = parseFloat(purchasePriceEl.value) || 0;
        const sale = parseFloat(salePriceEl.value) || 0;
        const units = parseInt(unitsEl.value) || 1;
        const gst = parseFloat(gstEl.value) || 0;
        const taxAmount = (sale * gst) / 100;

        if (sale < purch) { showToast('Sale price is less than purchase price.', 'warning'); return; }

        const existing = receivedCart.find(r => r.stockNo === selectedItem.STOCK_NUMBER);
        if (existing) {
            existing.qtyReceived += qty;
            existing.bonusQty = (existing.bonusQty || 0) + bonus;
            existing.expiry = expiryEl.value || existing.expiry;
            existing.batch = batchEl.value || existing.batch;
            existing.purchasePrice = purch;
            existing.salePrice = sale;
            existing.group = groupEl.value || existing.group;
            existing.gst = gst;
            existing.taxAmount = taxAmount;
            existing.amount = existing.qtyReceived * existing.purchasePrice;
        } else {
            receivedCart.push({
                stockNo: selectedItem.STOCK_NUMBER,
                name: selectedItem.ITEM_NAME || '',
                qtyAvail: selectedItem.QTY_INHAND ?? 0,
                qtyReceived: qty,
                bonusQty: bonus,
                expiry: expiryEl.value || '',
                batch: batchEl.value || '',
                unitsPerItem: units,
                purchasePrice: purch,
                salePrice: sale,
                group: groupEl.value || '',
                gst: gst,
                taxAmount: taxAmount,
                amount: qty * purch,
            });
        }

        renderCart();
        clearEntryFields();
        searchInput.focus();
    });


    function buildPayload() {
        return {
            invoice_no: parseInt(invoiceNoEl.value) || 0,
            invoice_date: invoiceDateEl.value || new Date().toISOString().slice(0, 10),
            supplier_code: supplierDisplayEl.dataset.supplierCode || supplierDisplayEl.value,
            total_amount: parseFloat(totalAmountEl.value) || 0,
            received_date: receivedDateEl.value || new Date().toISOString().slice(0, 10),
            status: statusEl.value || 'Y',
            received_by: receivedByEl.value || 'admin',
            flat_discount: parseFloat(flatDiscEl.value) || 0,
            flat_gst: parseFloat(flatGstEl.value) || 0,
            is_loose_purchase: looseCheckbox.checked,
            total_packs: parseInt(totalPacksEl.value) || 0,
            total_items: parseInt(totalItemsEl.value) || 0,
            total_units: parseInt(totalUnitsEl.value) || 0,
            total_bonus: parseInt(totalBonusEl.value) || 0,
            items: receivedCart.map(r => ({
                stock_number: r.stockNo,
                qty_received: r.qtyReceived,
                bonus_qty: r.bonusQty,
                expiry_date: r.expiry || null,
                batch_no: r.batch || '',
                units_per_item: r.unitsPerItem,
                purchase_price: r.purchasePrice,
                sale_price: r.salePrice,
                group: r.group,
                gst: r.gst,
            })),
        };
    }

    btnPost.addEventListener('click', async () => {
        if (!receivedCart.length) { showToast('Please add at least one item.', 'warning'); return; }
        if (!invoiceNoEl.value) { showToast('Please enter Invoice number.', 'warning'); return; }
        if (!supplierDisplayEl.value) { showToast('Please select a supplier.', 'warning'); return; }
        if ((parseFloat(totalAmountEl.value) || 0) !== (parseFloat(aggregateAmtEl.value) || 0)) {
            showToast('Total and aggregate amounts must be equal.', 'warning');
            return;
        }

        const payload = buildPayload();
        btnPost.disabled = true;
        try {
            const res = await fetch('api/purchase_order.php?action=save', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });
            const data = await res.json();
            if (data.success) {
                showToast(`Purchase Order #${data.trans_no} saved.`, 'success');
                resetForm();
                allProducts = [];
                await fetchAvailableStock();
            } else {
                showToast('Save failed: ' + (data.error || 'Unknown error'), 'error');
            }
        } catch (e) {
            console.error(e);
            showToast('Network error.', 'error');
        } finally {
            btnPost.disabled = false;
        }
    });

    btnModify.addEventListener('click', async () => {
        if (!invoiceNoEl.value) { showToast('Please enter an invoice number.', 'warning'); return; }
        if (!receivedCart.length) { showToast('Please add at least one item.', 'warning'); return; }
        if (!supplierDisplayEl.value) { showToast('Please select a supplier.', 'warning'); return; }
        if ((parseFloat(totalAmountEl.value) || 0) !== (parseFloat(aggregateAmtEl.value) || 0)) {
            showToast('Total and aggregate amounts must be equal.', 'warning');
            return;
        }

        const result = await Swal.fire({
            title: 'Modify Purchase Order?',
            text: `This will update all items and stock for Invoice #${invoiceNoEl.value}.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, modify',
            cancelButtonText: 'Cancel',
        });
        if (!result.isConfirmed) return;

        btnModify.disabled = true;
        btnModify.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-[10px]"></i> Saving...';

        const payload = buildPayload();
        try {
            const res = await fetch('api/purchase_order.php?action=modify', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });
            const data = await res.json();
            if (data.success) {
                showToast(`Invoice #${data.invoice_no} updated successfully.`, 'success');
                resetForm();
                allProducts = [];
                await fetchAvailableStock();
            } else {
                showToast('Modify failed: ' + (data.error || 'Unknown error'), 'error');
            }
        } catch (e) {
            console.error(e);
            showToast('Network error.', 'error');
        } finally {
            btnModify.disabled = false;
            btnModify.innerHTML = '<i class="fa-solid fa-pen text-xs"></i> MODIFY';
        }
    });

    btnCancellation.addEventListener('click', async () => {
        const result = await Swal.fire({
            title: 'Cancel this Purchase Order?',
            text: 'All entered data will be cleared.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, cancel',
        });
        if (result.isConfirmed) resetForm();
    });

    btnFind.addEventListener('click', async () => {
        const invoiceNo = invoiceNoEl.value.trim();
        if (!invoiceNo) { showToast('Please enter an invoice number.', 'warning'); return; }

        try {
            const res = await fetch(`api/purchase_order.php?action=get_invoice&invoice_no=${encodeURIComponent(invoiceNo)}`);
            const data = await res.json();
            if (!data.success) {
                showToast(data.error || 'Invoice not found.', 'error');
                return;
            }

            const h = data.header;
            transNoEl.value = h.Trans_no || '';
            isEditMode = true; 

            invoiceDateEl.value = h.INVOICE_DATE ? h.INVOICE_DATE.slice(0, 10) : '';
            supplierDisplayEl.value = h.SUPPLIER_CODE || '';
            supplierDisplayEl.dataset.supplierCode = h.SUPPLIER_CODE || '';
            totalAmountEl.value = h.TOTAL_AMOUNT || 0;
            receivedDateEl.value = h.RECEIVED_DATE ? h.RECEIVED_DATE.slice(0, 10) : '';
            statusEl.value = h.STATUS || 'Y';
            receivedByEl.value = h.RECEIVED_BY || '';
            flatDiscEl.value = h.FLAT_DISCOUNT || 0;
            flatGstEl.value = h.FLAT_GST || 0;
            looseCheckbox.checked = h.IS_LOOSE_PURCHASE === '1';

            receivedCart = data.items.map(item => ({
                stockNo: item.STOCK_NUMBER,
                name: item.ITEM_NAME || '',
                qtyAvail: item.ITEMS_AVAILABLE ?? 0,
                qtyReceived: item.ITEMS_RECEIVED,
                bonusQty: item.BONUS_QTY || 0,
                expiry: item.EXPIRY_DATE || '',
                batch: item.BATCH_NO || '',
                unitsPerItem: item.UNITS_PERITEM || 1,
                purchasePrice: parseFloat(item.PPRICE_PERITEM || 0),
                salePrice: parseFloat(item.PRICE_PERITEM || 0),
                group: item.GROUP_NAME || '',
                gst: parseFloat(item.Tax_Percentage || 0),
                taxAmount: parseFloat(item.Tax_amount || 0),
                amount: item.ITEMS_RECEIVED * parseFloat(item.PPRICE_PERITEM || 0),
            }));
            renderCart();

            const agg = parseFloat(aggregateAmtEl.value) || 0;
            const tot = parseFloat(totalAmountEl.value) || 0;
            if (agg !== tot && (agg > 0 || tot > 0)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Amount Mismatch',
                    text: 'Aggregate amount and Total amount are not equal. Please adjust.',
                    timer: 3000,
                    showConfirmButton: false,
                });
            }
        } catch (e) {
            console.error(e);
            showToast('Network error.', 'error');
        }
    });

    function resetForm() {
        receivedCart = [];
        renderCart();
        clearEntryFields();
        invoiceNoEl.value = '';
        invoiceDateEl.value = '';
        supplierDisplayEl.value = '';
        supplierDisplayEl.dataset.supplierCode = '';
        totalAmountEl.value = '';
        receivedDateEl.value = '';
        statusEl.value = 'Y';
        receivedByEl.value = 'admin';
        flatDiscEl.value = 0;
        flatGstEl.value = 0;
        looseCheckbox.checked = false;
        unitsEl.disabled = false;
        aggregateAmtEl.value = '';
        isEditMode = false;
        fetchNextTransNo();
    }
    fetchNextTransNo();
    fetchSuppliers();
    renderStockTable([]);
    renderCart();
})();