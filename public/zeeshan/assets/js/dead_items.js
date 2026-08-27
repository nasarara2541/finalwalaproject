document.addEventListener('DOMContentLoaded', () => {
    
    // UI Elements
    const btnRun = document.getElementById('btn-run');
    const btnExport = document.getElementById('btn-export');
    const tbody = document.getElementById('dead-items-tbody');
    const recordCount = document.getElementById('record-count');
    const toastMsg = document.getElementById('toast-msg');
    
    let currentData = null; // Store for export
    let currentPage = 1;
    const rowsPerPage = 50;
    
    const paginationInfo = document.getElementById('pagination-info');
    const paginationControls = document.getElementById('pagination-controls');

    // Toast function
    function showToast(message, isError = false) {
        toastMsg.textContent = message;
        toastMsg.className = `fixed bottom-4 right-4 px-4 py-2 rounded shadow-lg transition-all duration-300 z-50 font-medium ${isError ? 'bg-red-600 text-white' : 'bg-slate-800 text-white'}`;
        
        toastMsg.style.opacity = '1';
        toastMsg.style.transform = 'translateY(0)';
        
        setTimeout(() => {
            toastMsg.style.opacity = '0';
            toastMsg.style.transform = 'translateY(10px)';
        }, 3000);
    }

    // Run Query
    btnRun.addEventListener('click', loadReport);

    async function loadReport() {
        btnRun.disabled = true;
        btnRun.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Generating...';
        
        tbody.innerHTML = '<tr><td colspan="6" class="text-center p-8 text-slate-400">Loading data...</td></tr>';
        recordCount.textContent = 'Loading...';
        paginationInfo.textContent = 'Showing 0 to 0 of 0 items';
        paginationControls.innerHTML = '';

        try {
            const res = await fetch(`api/get_dead_items.php`);
            if (!res.ok) throw new Error('Network response was not ok');
            
            const result = await res.json();
            
            if (!result.success) {
                throw new Error(result.error);
            }

            currentData = result.data;
            currentPage = 1;
            renderTable();

        } catch(e) {
            console.error(e);
            showToast('Error loading report', true);
            tbody.innerHTML = '<tr><td colspan="6" class="text-center p-8 text-red-500">Failed to load data.</td></tr>';
            recordCount.textContent = 'Error';
        } finally {
            btnRun.disabled = false;
            btnRun.innerHTML = '<i class="fa-solid fa-arrows-rotate"></i> Generate';
        }
    }

    window.changePage = function(page) {
        currentPage = page;
        renderTable();
    };

    function renderTable() {
        tbody.innerHTML = '';
        
        if (!currentData || currentData.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="p-8 text-center text-slate-400">No dead items found.</td></tr>';
            recordCount.textContent = '0 items found';
            paginationInfo.textContent = 'Showing 0 to 0 of 0 items';
            paginationControls.innerHTML = '';
            return;
        }

        recordCount.textContent = `${currentData.length} items found`;

        const totalRows = currentData.length;
        const totalPages = Math.ceil(totalRows / rowsPerPage);
        
        if (currentPage < 1) currentPage = 1;
        if (currentPage > totalPages) currentPage = totalPages;

        const startIndex = (currentPage - 1) * rowsPerPage;
        const endIndex = Math.min(startIndex + rowsPerPage, totalRows);
        const paginatedData = currentData.slice(startIndex, endIndex);

        paginatedData.forEach(row => {
            const tr = document.createElement('tr');
            tr.className = 'border-b border-slate-100 hover:bg-slate-50 transition-colors';
            
            const qtyClass = row.QTY_INHAND > 0 ? 'text-red-600 font-bold' : 'text-slate-600';
            const dateStr = row.LastSoldDate ? row.LastSoldDate : 'Never Sold';

            tr.innerHTML = `
                <td class="p-2 border-r border-slate-200 font-mono text-slate-500">${row.STOCK_NUMBER}</td>
                <td class="p-2 border-r border-slate-200 text-slate-800 font-medium">${row.ITEM_NAME}</td>
                <td class="p-2 border-r border-slate-200 text-slate-600">${row.BRAND_NAME}</td>
                <td class="p-2 border-r border-slate-200 text-right font-mono text-slate-700">${parseFloat(row.Retail_Price).toFixed(2)}</td>
                <td class="p-2 border-r border-slate-200 text-center font-mono ${qtyClass}">${row.QTY_INHAND}</td>
                <td class="p-2 text-slate-500">${dateStr}</td>
            `;
            tbody.appendChild(tr);
        });

        // Render Pagination UI
        paginationInfo.textContent = `Showing ${startIndex + 1} to ${endIndex} of ${totalRows} items`;
        
        let paginationHTML = '';
        
        // Prev button
        if (currentPage > 1) {
            paginationHTML += `<button onclick="changePage(${currentPage - 1})" class="px-2 py-1 text-sm bg-white border border-slate-300 text-slate-600 rounded hover:bg-slate-100 transition-colors"><i class="fa-solid fa-chevron-left"></i></button>`;
        } else {
            paginationHTML += `<button disabled class="px-2 py-1 text-sm bg-slate-100 border border-slate-300 text-slate-400 rounded cursor-not-allowed"><i class="fa-solid fa-chevron-left"></i></button>`;
        }
        
        // Page numbers
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, startPage + 4);
        
        if (endPage - startPage < 4) {
            startPage = Math.max(1, endPage - 4);
        }

        if (startPage > 1) {
            paginationHTML += `<button onclick="changePage(1)" class="px-3 py-1 text-sm bg-white border border-slate-300 text-slate-600 rounded hover:bg-slate-100 transition-colors">1</button>`;
            if (startPage > 2) paginationHTML += `<span class="px-1 text-slate-400">...</span>`;
        }
        
        for (let i = startPage; i <= endPage; i++) {
            if (i === currentPage) {
                paginationHTML += `<button class="px-3 py-1 text-sm bg-teal-600 border border-teal-600 text-white font-bold rounded shadow-sm">${i}</button>`;
            } else {
                paginationHTML += `<button onclick="changePage(${i})" class="px-3 py-1 text-sm bg-white border border-slate-300 text-slate-600 rounded hover:bg-slate-100 transition-colors">${i}</button>`;
            }
        }
        
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) paginationHTML += `<span class="px-1 text-slate-400">...</span>`;
            paginationHTML += `<button onclick="changePage(${totalPages})" class="px-3 py-1 text-sm bg-white border border-slate-300 text-slate-600 rounded hover:bg-slate-100 transition-colors">${totalPages}</button>`;
        }
        
        // Next button
        if (currentPage < totalPages) {
            paginationHTML += `<button onclick="changePage(${currentPage + 1})" class="px-2 py-1 text-sm bg-white border border-slate-300 text-slate-600 rounded hover:bg-slate-100 transition-colors"><i class="fa-solid fa-chevron-right"></i></button>`;
        } else {
            paginationHTML += `<button disabled class="px-2 py-1 text-sm bg-slate-100 border border-slate-300 text-slate-400 rounded cursor-not-allowed"><i class="fa-solid fa-chevron-right"></i></button>`;
        }

        paginationControls.innerHTML = paginationHTML;
    }

    // Export PDF
    btnExport.addEventListener('click', () => {
        if (!currentData || currentData.length === 0) {
            showToast('No data to export', true);
            return;
        }
        
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({ orientation: 'landscape' });
        
        doc.setFontSize(18);
        doc.text("Dead Items Report", 14, 22);
        doc.setFontSize(11);
        doc.text(`Generated: ${moment().format('YYYY-MM-DD HH:mm')}`, 14, 30);
        
        const cols = ['STOCK_NUMBER', 'ITEM_NAME', 'BRAND_NAME', 'Retail_Price', 'QTY_INHAND', 'LastSoldDate'];
        const colNames = ['Stock No.', 'Item Name', 'Brand Name', 'Price', 'Qty In Hand', 'Last Sold Date'];
        
        const rows = currentData.map(row => {
            return cols.map(col => {
                if (col === 'Retail_Price') return parseFloat(row[col] || 0).toFixed(2);
                if (col === 'LastSoldDate') return row[col] ? row[col] : 'Never Sold';
                return row[col] === null ? '-' : row[col];
            });
        });
        
        doc.autoTable({
            startY: 36,
            head: [colNames],
            body: rows,
            theme: 'striped',
            headStyles: { fillColor: [15, 118, 110], halign: 'center' }, // teal-700
            styles: { fontSize: 9, cellPadding: 3, valign: 'middle' },
            columnStyles: {
                0: { cellWidth: 25, halign: 'center' },   // Stock No.
                1: { cellWidth: 'auto' },                 // Item Name
                2: { cellWidth: 50 },                     // Brand Name
                3: { cellWidth: 20, halign: 'right' },    // Price
                4: { cellWidth: 22, halign: 'center' },   // Qty In Hand
                5: { cellWidth: 30, halign: 'center' }    // Last Sold Date
            }
        });
        
        doc.save(`dead_items_report_${moment().format('YYYYMMDD')}.pdf`);
    });

});
