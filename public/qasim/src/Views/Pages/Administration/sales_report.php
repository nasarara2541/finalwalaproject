<div style="display:flex;flex-direction:column;gap:6px;padding:8px;min-height:0;overflow:hidden;flex:1;">

    <div class="win-panel" style="padding:14px;max-width:600px;margin:0 auto;width:100%;">
        <form id="report-form" target="_blank" method="GET" action="index.php">
            <input type="hidden" name="page" value="report_graph">
            <div style="display:flex;gap:16px;margin-bottom:14px;">
                <div style="flex:1;">
                    <label class="lbl" style="display:block;margin-bottom:3px;">From Date</label>
                    <input type="date" name="start_date" id="start-date" style="width:100%;" required>
                </div>
                <div style="flex:1;">
                    <label class="lbl" style="display:block;margin-bottom:3px;">To Date</label>
                    <input type="date" name="end_date" id="end-date" style="width:100%;" required>
                </div>
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:8px;">
                <button type="submit" name="type" value="summary" class="win-btn win-btn-blue" style="flex:1;justify-content:center;"><i class="fa-solid fa-chart-bar"></i> Sales Profit Summary</button>
                <button type="submit" name="type" value="per_transaction" class="win-btn win-btn-blue" style="flex:1;justify-content:center;"><i class="fa-solid fa-chart-line"></i> Profit per Transaction</button>
                <button type="submit" name="type" value="daywise" class="win-btn win-btn-blue" style="flex:1;justify-content:center;"><i class="fa-solid fa-calendar-day"></i> Daywise Sale Return</button>
            </div>
        </form>
    </div>

    <!-- Qasim's original submitted this form to a page called report_graph.php
         (via index.php's router) that renders/charts the fetched data -- that
         page, and its JS, were never included with the 3 given files. This
         results area + assets/js/sales_report.js were built from scratch to
         actually display what api/sales_report_get.php returns, reusing his
         real form/date fields and his real API untouched. -->
    <div id="report-results" class="hidden win-panel" style="padding:10px;flex:1;min-height:0;display:flex;flex-direction:column;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;flex-shrink:0;">
            <span id="report-title" style="font-weight:bold;"></span>
            <button id="btn-print-report" type="button" class="win-btn win-btn-blue"><i class="fa-solid fa-print"></i> Print / Save as PDF</button>
        </div>
        <div id="report-totals" style="display:flex;flex-wrap:wrap;gap:14px;margin-bottom:8px;color:#555;flex-shrink:0;"></div>
        <div id="report-scrollbox" class="win-white-panel" style="flex:1;min-height:0;overflow:auto;background:#fff;border:1px solid;border-color:#808080 #fff #fff #808080;">
            <table class="win-table">
                <thead id="report-thead"></thead>
                <tbody id="report-tbody"></tbody>
            </table>
            <div id="report-load-more" style="display:none;text-align:center;padding:8px;font-size:12px;font-weight:bold;color:#0a246a;background:#f0f0f0;cursor:pointer;">Load more...</div>
        </div>
    </div>

    <script>
        (function() {
            const today = new Date();
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            const pad = n => String(n).padStart(2, '0');
            document.getElementById('start-date').value =
                `${firstDay.getFullYear()}-${pad(firstDay.getMonth()+1)}-${pad(firstDay.getDate())}`;
            document.getElementById('end-date').value =
                `${today.getFullYear()}-${pad(today.getMonth()+1)}-${pad(today.getDate())}`;
        })();
    </script>
</div>
