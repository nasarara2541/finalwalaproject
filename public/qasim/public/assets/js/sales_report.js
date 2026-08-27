(function () {
    const form = document.getElementById('report-form');
    const startEl = document.getElementById('start-date');
    const endEl = document.getElementById('end-date');
    const resultsBox = document.getElementById('report-results');
    const titleEl = document.getElementById('report-title');
    const totalsEl = document.getElementById('report-totals');
    const theadEl = document.getElementById('report-thead');
    const tbodyEl = document.getElementById('report-tbody');
    const btnPrint = document.getElementById('btn-print-report');

    // Column definitions per mode -- mirrors sales_report_pdf.php's own
    // headers/keyMap exactly, so the on-screen table and the printed report
    // show identical columns.
    const MODES = {
        summary: {
            title: 'Sales Profit Summary',
            cols: [
                ['Month', 'Month', false],
                ['Total Qty', 'TotalQty', true],
                ['Total Sale', 'TotalSale', true],
                ['Total Cost', 'TotalCost', true],
                ['Total Profit', 'TotalProfit', true],
            ],
        },
        per_transaction: {
            title: 'Sales Profit per Transaction',
            cols: [
                ['Trans No', 'Trans_no', true],
                ['Date', 'Trans_date', false],
                ['Disc %', 'Disc_percentage', true],
                ['Gross', 'GrossAmount', true],
                ['Disc Amt', 'DiscAmount', true],
                ['Net', 'NetAmount', true],
                ['Profit', 'Profit', true],
            ],
        },
        daywise: {
            title: 'Daywise Sale & Return',
            cols: [
                ['Day', 'Day', false],
                ['Discount', 'Discount', true],
                ['Sale Amount', 'SaleAmount', true],
                ['Sale Return', 'SaleReturn', true],
                ['Sale - Sale Return', 'NetSale', true],
                ['Cost', 'Cost', true],
                ['Profit', 'Profit', true],
                ['Profit %', 'ProfitPct', true],
            ],
        },
    };

    let lastMode = null, lastStart = null, lastEnd = null;

    function fmt(val, isNumeric, key) {
        if (val === null || val === undefined || val === '') return '&mdash;';
        if (!isNumeric) return String(val).replace(/</g, '&lt;');
        const n = parseFloat(val);
        if (key === 'TotalQty' || key === 'Trans_no') return Number(n).toLocaleString();
        return Number(n).toFixed(2);
    }

    async function runReport(mode, startDate, endDate) {
        lastMode = mode; lastStart = startDate; lastEnd = endDate;
        const def = MODES[mode];
        titleEl.textContent = def.title + ' (' + startDate + ' to ' + endDate + ')';
        theadEl.innerHTML = '<tr>' + def.cols.map(c => '<th class="px-3 py-2 text-left font-semibold border-b border-slate-300">' + c[0] + '</th>').join('') + '</tr>';
        tbodyEl.innerHTML = '<tr><td colspan="' + def.cols.length + '" class="px-3 py-6 text-center text-slate-400">Loading...</td></tr>';
        totalsEl.innerHTML = '';
        resultsBox.classList.remove('hidden');

        try {
            const res = await fetch('api/sales_report_get.php?mode=' + encodeURIComponent(mode) +
                '&start_date=' + encodeURIComponent(startDate) + '&end_date=' + encodeURIComponent(endDate));
            const data = await res.json();
            if (!data.success) {
                tbodyEl.innerHTML = '<tr><td colspan="' + def.cols.length + '" class="px-3 py-6 text-center text-red-500">' + (data.error || 'Failed to load report') + '</td></tr>';
                return;
            }
            const rows = data.data || [];
            if (!rows.length) {
                tbodyEl.innerHTML = '<tr><td colspan="' + def.cols.length + '" class="px-3 py-6 text-center text-slate-400">No data found</td></tr>';
            } else {
                tbodyEl.innerHTML = rows.map(r => '<tr>' + def.cols.map(c =>
                    '<td class="px-3 py-1.5' + (c[2] ? ' text-right' : '') + '">' + fmt(r[c[1]], c[2], c[1]) + '</td>'
                ).join('') + '</tr>').join('');
            }
            if (data.totals) {
                totalsEl.innerHTML = Object.entries(data.totals).map(([k, v]) =>
                    '<span><b>' + k.replace(/([A-Z])/g, ' $1').replace(/^./, s => s.toUpperCase()) + ':</b> ' + Number(v).toFixed(2) + '</span>'
                ).join('');
            }
        } catch (e) {
            console.error(e);
            tbodyEl.innerHTML = '<tr><td colspan="' + def.cols.length + '" class="px-3 py-6 text-center text-red-500">Network error</td></tr>';
        }
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const submitter = e.submitter;
        const mode = submitter ? submitter.value : 'summary';
        runReport(mode, startEl.value, endEl.value);
    });

    // Print/PDF -- Qasim's own version used a Composer PDF library
    // (mpdf/vendor) for this; per his own note that either his package or
    // "existing logic" was fine, this reuses the browser-print pattern
    // already used elsewhere in this app (pos.php's invoice printing)
    // instead of adding a new dependency this project has never used.
    btnPrint.addEventListener('click', function () {
        if (!lastMode) return;
        const def = MODES[lastMode];
        const win = window.open('', '_blank');
        const rowsHtml = tbodyEl.innerHTML;
        win.document.write(`
            <html><head><title>${def.title}</title>
            <style>
                body { font-family: 'Courier New', monospace; font-size: 11px; padding: 12px; }
                h2 { text-align:center; margin-bottom:4px; }
                .sub { text-align:center; color:#444; margin-bottom:16px; }
                table { width:100%; border-collapse:collapse; }
                th, td { border-bottom:1px solid #000; padding:4px 6px; text-align:left; }
                th { border-top:1px solid #000; border-bottom:2px solid #000; }
                td.num, th.num { text-align:right; }
            </style></head><body>
            <h2>${def.title}</h2>
            <div class="sub">Date Range: ${lastStart} to ${lastEnd}</div>
            <table><thead>${theadEl.innerHTML}</thead><tbody>${rowsHtml}</tbody></table>
            </body></html>
        `);
        win.document.close();
        win.focus();
        setTimeout(() => win.print(), 300);
    });

    // Default From/To dates are already set by the inline <script> at the
    // bottom of Qasim's own fragment (src/Views/Pages/Administration/
    // sales_report.php) -- not duplicated here.
})();
