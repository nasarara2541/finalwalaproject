<?php
require_once __DIR__ . '/includes/access.php';
requireAccess('admin_area');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AISellProduct - AI Insights</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
* { font-family: Tahoma, 'Segoe UI', sans-serif; font-size: 12px; box-sizing: border-box; }
html, body { height: 100%; margin: 0; }
body { background: #d4d0c8; overflow: hidden; }

.win-panel  { border: 1px solid; border-color: #808080 #ffffff #ffffff #808080; background:#ece9d8; }
.win-inset  { border: 1px solid; border-color: #808080 #ffffff #ffffff #808080; background:#fff; }
.win-titlebar { background: linear-gradient(to right, #0a246a, #3a6ea5); color: white; font-weight: bold; font-size: 12px; padding: 4px 8px; display:flex; align-items:center; justify-content:space-between; }
.win-menubar { background: #d4d0c8; border-bottom: 1px solid #808080; display:flex; gap:0; padding: 2px 2px; }
.win-menu-item { padding: 3px 10px; cursor:pointer; font-size:12px; }
.win-menu-item:hover, .win-menu-item.active { background:#0a246a; color:white; }

.win-btn {
    background: #d4d0c8; border: 1px solid; border-color: #ffffff #808080 #808080 #ffffff;
    padding: 2px 10px; cursor:pointer; font-size:12px; height:23px;
    font-family: Tahoma, sans-serif; white-space:nowrap; display:inline-flex; align-items:center; gap:4px;
}
.win-btn:hover { background: #e8e4d8; }
.win-btn:active { border-color: #808080 #ffffff #ffffff #808080; }
.win-btn-blue { background:#003087; color:white; border-color:#5599cc #002266 #002266 #5599cc; }
.win-btn-blue:hover { background:#0040ad; }

.win-table { width:100%; border-collapse:collapse; font-size:11px; }
.win-table thead th { border:1px solid #808080; padding:5px 7px; text-align:left; font-weight:bold; background:#d4d0c8; white-space:nowrap; position:sticky; top:0; z-index:1; }
.win-table tbody tr { background:#fff; }
.win-table tbody tr:nth-child(even) { background:#f7f5f0; }
.win-table tbody tr:hover { background:#c5d5e8 !important; }
.win-table td { border:1px solid #d0ccc4; padding:5px 7px; white-space:nowrap; vertical-align:middle; }
.win-table tr.row-reorder td { background:#fff0f0; }
.win-table tr.row-reorder:nth-child(even) td { background:#fde6e6; }

.win-section-label { background:#d4d0c8; font-weight:bold; padding:4px 8px; border-bottom:1px solid #808080; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:6px; }
.win-statusbar { background:#d4d0c8; border-top:1px solid #808080; padding:3px 8px; display:flex; gap:14px; }
.win-statusbar span { border-right:1px solid #808080; padding-right:12px; }
.empty-note { text-align:center; color:#888; padding:16px; font-size:11px; }
#toast { position:fixed; bottom:16px; right:16px; z-index:9999; padding:8px 16px; font-weight:bold; font-size:12px; border:1px solid; display:none; }

/* ---- AI Insights-specific ---- */
.kpi-row { display:flex; gap:8px; padding:8px; flex-wrap:wrap; }
.kpi-card {
    flex:1; min-width:170px; background:#fff; border:1px solid; border-color:#808080 #ffffff #ffffff #808080;
    padding:8px 10px; display:flex; flex-direction:column; gap:3px; position:relative; overflow:hidden;
}
.kpi-card::before { content:''; position:absolute; left:0; top:0; bottom:0; width:4px; }
.kpi-card.kpi-red::before   { background:#8b0000; }
.kpi-card.kpi-blue::before  { background:#003087; }
.kpi-card.kpi-green::before { background:#1a7a1a; }
.kpi-card.kpi-amber::before { background:#b8860b; }
.kpi-label { font-size:10px; color:#666; text-transform:uppercase; letter-spacing:.03em; font-weight:bold; }
.kpi-value { font-size:20px; font-weight:bold; color:#0a246a; line-height:1.15; }
.kpi-sub { font-size:11px; color:#555; }

.how-strip {
    background:#fffbe0; border-top:1px solid #808080; border-bottom:1px solid #808080;
    padding:5px 10px; font-size:11px; color:#5b4a00; display:flex; align-items:center; gap:6px;
}

.status-badge {
    display:inline-flex; align-items:center; gap:4px; padding:2px 8px; font-size:10px; font-weight:bold;
    border:1px solid; text-transform:uppercase; letter-spacing:.02em;
}
.status-reorder     { background:#8b0000; color:#fff; border-color:#500000; }
.status-sufficient  { background:#1a7a1a; color:#fff; border-color:#0a500a; }
.status-overstocked { background:#b8860b; color:#fff; border-color:#8b6508; }
.status-quiet       { background:#808080; color:#fff; border-color:#555; }

.trend-bars { display:flex; align-items:flex-end; gap:2px; height:26px; }
.trend-bars .bar { width:7px; background:#0a246a; min-height:2px; }
.trend-bars .bar.bar-zero { background:#d0ccc4; min-height:2px; }

.trend-pct { font-weight:bold; }
.trend-up   { color:#1a7a1a; }
.trend-down { color:#8b0000; }
.trend-flat { color:#666; }
</style>
</head>
<body class="flex flex-col h-screen">

<?php $SCREEN_NAME = 'AI Insights'; $SCREEN_ICON = 'brain'; require __DIR__ . '/includes/navbar.php'; ?>

<div style="flex:1;min-height:0;display:flex;flex-direction:column;padding:6px;gap:6px;">

    <div class="win-panel" style="display:flex;flex-direction:column;flex:1;min-height:0;">
        <div class="win-section-label">
            <span><i class="fa-solid fa-brain"></i> AI Demand Forecast &amp; Reorder Suggestions</span>
            <span style="display:flex;align-items:center;gap:8px;">
                <span id="months-label" style="font-weight:normal;color:#555;"></span>
                <button class="win-btn win-btn-blue" onclick="loadInsights()"><i class="fa-solid fa-rotate"></i> Refresh</button>
            </span>
        </div>

        <div class="kpi-row">
            <div class="kpi-card kpi-red">
                <span class="kpi-label">Needs Reorder Soon</span>
                <span class="kpi-value" id="kpi-reorder-count">—</span>
                <span class="kpi-sub">products below next month's forecast</span>
            </div>
            <div class="kpi-card kpi-blue">
                <span class="kpi-label">Forecasted Demand</span>
                <span class="kpi-value" id="kpi-forecast">—</span>
                <span class="kpi-sub">total packs, next month, all products</span>
            </div>
            <div class="kpi-card kpi-green">
                <span class="kpi-label">Fastest Growing</span>
                <span class="kpi-value" id="kpi-growing" style="font-size:14px;">—</span>
                <span class="kpi-sub" id="kpi-growing-sub">&nbsp;</span>
            </div>
            <div class="kpi-card kpi-amber">
                <span class="kpi-label">Slowest / Declining</span>
                <span class="kpi-value" id="kpi-declining" style="font-size:14px;">—</span>
                <span class="kpi-sub" id="kpi-declining-sub">&nbsp;</span>
            </div>
        </div>

        <div class="how-strip">
            <i class="fa-solid fa-circle-info"></i>
            How this works: for each product, next month's forecast is a weighted average of real sales over the last 3 months
            (most recent month weighted heaviest) &mdash; compared against what's actually on the shelf right now. No outside AI
            service and nothing invented; every number here is computed straight from your own sales and stock records.
        </div>

        <div style="flex:1;overflow:auto;min-height:0;" tabindex="0">
            <table class="win-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>3-Month Trend</th>
                        <th style="text-align:right;">Trend %</th>
                        <th style="text-align:right;">Forecast (Next Mo.)</th>
                        <th style="text-align:right;">Current Stock</th>
                        <th>Status</th>
                        <th style="text-align:right;">Suggested Reorder</th>
                    </tr>
                </thead>
                <tbody id="insights-body"><tr><td colspan="7" class="empty-note">Loading…</td></tr></tbody>
            </table>
        </div>
    </div>

</div>

<div class="win-statusbar">
    <span id="status-msg">Ready</span>
    <span>Forecast model: 20% oldest month + 30% middle month + 50% most recent month of real packs sold.</span>
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
    setTimeout(()=>{ el.style.display='none'; }, 3000);
}
function setStatus(msg) { document.getElementById('status-msg').textContent = msg; }

const _nativeFetch = window.fetch;
window.fetch = function(...args) {
    return _nativeFetch.apply(this, args).catch(err => {
        toast('Network/Server error — check DB_SERVER in .env and that the database is reachable', 'err');
        throw err;
    });
};

const fmt = n => Number(n||0).toLocaleString('en-US');
const STATUS_LABEL = { reorder: 'Reorder Soon', sufficient: 'Sufficient', overstocked: 'Overstocked', quiet: 'No Recent Demand' };
const MONTH_NAMES = ['','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

function trendBars(series) {
    const max = Math.max(...series, 1);
    return '<div class="trend-bars">' + series.map(v => {
        const h = Math.max(2, Math.round((v / max) * 24));
        return `<div class="bar${v === 0 ? ' bar-zero' : ''}" style="height:${h}px;" title="${fmt(v)} packs"></div>`;
    }).join('') + '</div>';
}

function trendPctHtml(pct) {
    if (pct === null) return '<span class="trend-pct trend-flat">New</span>';
    if (pct > 0) return `<span class="trend-pct trend-up"><i class="fa-solid fa-arrow-up"></i> ${pct}%</span>`;
    if (pct < 0) return `<span class="trend-pct trend-down"><i class="fa-solid fa-arrow-down"></i> ${Math.abs(pct)}%</span>`;
    return '<span class="trend-pct trend-flat">Flat</span>';
}

function loadInsights() {
    setStatus('Calculating…');
    fetch('api/get_ai_insights.php').then(r => r.json()).then(res => {
        if (res.error) { toast('Error: ' + res.error, 'err'); setStatus('Ready'); return; }

        document.getElementById('months-label').textContent = res.months.length
            ? 'Based on ' + res.months.filter(m => m).map(m => MONTH_NAMES[m.Mo] + ' ' + m.Yr).join(', ')
            : '';

        const s = res.summary;
        document.getElementById('kpi-reorder-count').textContent = s ? fmt(s.reorder_count) : '0';
        document.getElementById('kpi-forecast').textContent = s ? fmt(s.total_forecast) + ' pk' : '0 pk';
        document.getElementById('kpi-growing').textContent = s && s.growing_label ? s.growing_label : '—';
        document.getElementById('kpi-growing-sub').innerHTML = s && s.growing_label ? trendPctHtml(s.growing_pct) + ' vs 3 months ago' : 'Nothing trending up right now';
        document.getElementById('kpi-declining').textContent = s && s.declining_label ? s.declining_label : '—';
        document.getElementById('kpi-declining-sub').innerHTML = s && s.declining_label ? trendPctHtml(s.declining_pct) + ' vs 3 months ago' : 'Nothing trending down right now';

        const tbody = document.getElementById('insights-body');
        tbody.innerHTML = '';
        if (!res.products.length) {
            tbody.innerHTML = '<tr><td colspan="7" class="empty-note">No recent sales to forecast from yet.</td></tr>';
            setStatus('Ready');
            return;
        }
        res.products.forEach(p => {
            const tr = document.createElement('tr');
            if (p.status === 'reorder') tr.className = 'row-reorder';
            tr.innerHTML = `
                <td style="font-weight:bold;color:#0a246a;white-space:normal;min-width:160px;">${p.label}</td>
                <td>${trendBars(p.series)}</td>
                <td style="text-align:right;">${trendPctHtml(p.trend_pct)}</td>
                <td style="text-align:right;font-weight:bold;">${fmt(p.forecast)}</td>
                <td style="text-align:right;">${fmt(p.on_hand)}</td>
                <td><span class="status-badge status-${p.status}">${STATUS_LABEL[p.status]}</span></td>
                <td style="text-align:right;font-weight:bold;color:${p.suggested_reorder > 0 ? '#8b0000' : '#888'};">${p.suggested_reorder > 0 ? fmt(p.suggested_reorder) : '—'}</td>`;
            tbody.appendChild(tr);
        });
        setStatus('Ready');
    }).catch(() => {
        document.getElementById('insights-body').innerHTML =
            '<tr><td colspan="7" style="text-align:center;color:darkred;padding:10px;">Could not load — check DB connection</td></tr>';
        setStatus('Ready');
    });
}

loadInsights();
</script>
</body>
</html>
