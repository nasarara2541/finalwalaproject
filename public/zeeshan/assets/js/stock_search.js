document.addEventListener('DOMContentLoaded', () => {
    
    // UI Elements
    const filterBrand = document.getElementById('filter-brand');
    const filterItemType = document.getElementById('filter-item-type');
    const filterStockType = document.getElementById('filter-stock-type');
    const btnReset = document.getElementById('btn-reset');
    
    const resultsTbody = document.getElementById('results-tbody');
    const resultCount = document.getElementById('result-count');
    const loadingState = document.getElementById('loading-state');
    const emptyState = document.getElementById('empty-state');

    // Pagination Elements
    const paginationControls = document.getElementById('pagination-controls');
    const pageStart = document.getElementById('page-start');
    const pageEnd = document.getElementById('page-end');
    const pageTotal = document.getElementById('page-total');
    const btnPrev = document.getElementById('btn-prev');
    const btnNext = document.getElementById('btn-next');
    const pageNumbers = document.getElementById('page-numbers');

    let allItems = [];
    let filteredItems = [];
    
    // Pagination State
    let currentPage = 1;
    const rowsPerPage = 50;

    // Fetch all items on load
    function loadItems() {
        loadingState.classList.remove('hidden');
        
        fetch('api/items.php')
            .then(res => res.json())
            .then(data => {
                if (data.error) throw new Error(data.error);
                allItems = data;
                populateFilterOptions();
                applyFilters(); // This will apply filters and render page 1
            })
            .catch(err => {
                console.error("Error loading items:", err);
            })
            .finally(() => {
                loadingState.classList.add('hidden');
            });
    }

    // Extract unique values and populate dropdowns
    function populateFilterOptions() {
        const brands = new Set();
        const itemTypes = new Set();
        const stockTypes = new Set();

        allItems.forEach(item => {
            if (item.brandName) brands.add(item.brandName);
            if (item.itemType) itemTypes.add(item.itemType);
            if (item.stockType) stockTypes.add(item.stockType);
        });

        const fillSelect = (selectEl, setValues) => {
            const defaultOpt = selectEl.options[0];
            selectEl.innerHTML = '';
            selectEl.appendChild(defaultOpt);
            
            Array.from(setValues).sort().forEach(val => {
                const opt = document.createElement('option');
                opt.value = val;
                opt.textContent = val;
                selectEl.appendChild(opt);
            });
        };

        fillSelect(filterBrand, brands);
        fillSelect(filterItemType, itemTypes);
        fillSelect(filterStockType, stockTypes);
    }

    // Apply filters and reset pagination
    function applyFilters() {
        const selectedBrand = filterBrand.value;
        const selectedItemType = filterItemType.value;
        const selectedStockType = filterStockType.value;

        filteredItems = allItems.filter(item => {
            let match = true;
            if (selectedBrand && item.brandName !== selectedBrand) match = false;
            if (selectedItemType && item.itemType !== selectedItemType) match = false;
            if (selectedStockType && item.stockType !== selectedStockType) match = false;
            return match;
        });

        resultCount.textContent = filteredItems.length;
        currentPage = 1;
        
        if (filteredItems.length === 0) {
            resultsTbody.innerHTML = '';
            emptyState.classList.remove('hidden');
            paginationControls.classList.add('hidden');
        } else {
            emptyState.classList.add('hidden');
            paginationControls.classList.remove('hidden');
            renderTablePage();
        }
    }

    // Render the current page of the table
    function renderTablePage() {
        resultsTbody.innerHTML = '';

        const totalItems = filteredItems.length;
        const totalPages = Math.ceil(totalItems / rowsPerPage);
        
        // Ensure currentPage is valid
        if (currentPage < 1) currentPage = 1;
        if (currentPage > totalPages) currentPage = totalPages;

        const startIndex = (currentPage - 1) * rowsPerPage;
        const endIndex = Math.min(startIndex + rowsPerPage, totalItems);

        const pageItems = filteredItems.slice(startIndex, endIndex);

        pageItems.forEach(item => {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-50 transition-colors border-b border-slate-200 last:border-b-0';
            
            let statusHtml = '-';
            if (item.availStatus) {
                const isAvail = item.availStatus.toLowerCase() === 'available' || item.availStatus === 'A';
                const colorClass = isAvail ? 'text-green-700 bg-green-100' : 'text-red-700 bg-red-100';
                const text = isAvail ? 'Available' : item.availStatus;
                statusHtml = `<span class="px-2 py-1 rounded text-[11px] font-bold ${colorClass}">${text}</span>`;
            }

            tr.innerHTML = `
                <td class="p-3 border-r border-slate-200 text-slate-500 font-mono">${item.stockNo || '-'}</td>
                <td class="p-3 border-r border-slate-200 font-medium text-slate-800">${item.brandName || '-'}</td>
                <td class="p-3 border-r border-slate-200 font-semibold text-teal-700">${item.itemName || '-'}</td>
                <td class="p-3 border-r border-slate-200">${item.itemType || '-'}</td>
                <td class="p-3 border-r border-slate-200">${item.stockType || '-'}</td>
                <td class="p-3 border-r border-slate-200 text-center font-medium">${item.volume || '-'}</td>
                <td class="p-3 text-center">${statusHtml}</td>
            `;
            resultsTbody.appendChild(tr);
        });

        updatePaginationUI(startIndex, endIndex, totalItems, totalPages);
    }

    function updatePaginationUI(startIndex, endIndex, totalItems, totalPages) {
        pageStart.textContent = totalItems === 0 ? 0 : startIndex + 1;
        pageEnd.textContent = endIndex;
        pageTotal.textContent = totalItems;

        btnPrev.disabled = currentPage === 1;
        btnNext.disabled = currentPage === totalPages || totalPages === 0;

        // Render page numbers (simple version, max 5 pages shown)
        pageNumbers.innerHTML = '';
        
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, startPage + 4);
        
        if (endPage - startPage < 4) {
            startPage = Math.max(1, endPage - 4);
        }

        for (let i = startPage; i <= endPage; i++) {
            const btn = document.createElement('button');
            btn.textContent = i;
            if (i === currentPage) {
                btn.className = 'px-3 py-1 bg-teal-600 text-white rounded font-bold shadow-sm';
            } else {
                btn.className = 'px-3 py-1 bg-white text-slate-700 hover:bg-slate-100 border border-slate-300 rounded transition-colors';
                btn.addEventListener('click', () => {
                    currentPage = i;
                    renderTablePage();
                });
            }
            pageNumbers.appendChild(btn);
        }
    }

    // Event Listeners for Filters
    filterBrand.addEventListener('change', applyFilters);
    filterItemType.addEventListener('change', applyFilters);
    filterStockType.addEventListener('change', applyFilters);

    // Event Listeners for Pagination
    btnPrev.addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            renderTablePage();
        }
    });

    btnNext.addEventListener('click', () => {
        const totalPages = Math.ceil(filteredItems.length / rowsPerPage);
        if (currentPage < totalPages) {
            currentPage++;
            renderTablePage();
        }
    });

    // Reset button
    btnReset.addEventListener('click', () => {
        filterBrand.value = '';
        filterItemType.value = '';
        filterStockType.value = '';
        applyFilters();
    });

    // Initialize
    loadItems();

});
