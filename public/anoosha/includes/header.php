<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AISellProduct - <?php echo htmlspecialchars($_SESSION['company_name'] ?? 'Margalla 3M Industries'); ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
/* ===== House XP style, matching this app's own native screens exactly ===== */
* { font-family: Tahoma, 'Segoe UI', sans-serif; font-size: 12px; box-sizing: border-box; }
html, body { height: 100%; margin: 0; }
body { background: #d4d0c8 !important; overflow: hidden; color:#000; }

[class*="rounded"] { border-radius: 0 !important; }
[class*="shadow"]  { box-shadow: none !important; }

.win-inset      { border: 1px solid; border-color: #808080 #ffffff #ffffff #808080; background:#fff; }
.win-panel,
.win-white-panel{ border: 1px solid; border-color: #808080 #ffffff #ffffff #808080; background:#ece9d8; }
.win-white-panel{ background:#fff; }
.win-titlebar   { background: linear-gradient(to right, #0a246a, #3a6ea5); color: white; font-weight: bold; font-size: 12px; padding: 4px 8px; display:flex; align-items:center; justify-content:space-between; }
.win-menubar    { background: #d4d0c8; border-bottom: 1px solid #808080; display:flex; gap:0; padding: 2px 2px; align-items:center; }
.win-menu-item  { padding: 3px 10px; cursor:pointer; font-size:12px; text-decoration:none; color:#000; display:inline-block; white-space:nowrap; }
.win-menu-item:hover, .win-menu-item.nav-active { background:#0a246a; color:white; }

input:not([type="submit"]):not([type="button"]):not([type="checkbox"]):not([type="radio"]),
select {
    border: 1px solid !important; border-color: #808080 #ffffff #ffffff #808080 !important;
    border-radius: 0 !important; background: #ffff99;
    font-family: Tahoma, sans-serif !important; font-size:12px !important; padding: 2px 4px; height:22px;
}
input[readonly], input[disabled] { background: #d4d0c8 !important; color:#555; }
.bg-yellow-100 { background-color: #ffff99 !important; }
.bg-gray-100, .bg-gray-200, .bg-gray-300 { background-color: #d4d0c8 !important; }
.bg-white      { background-color: #ffffff !important; }

button, .win-btn {
    border: 1px solid !important; border-color: #ffffff #808080 #808080 #ffffff !important;
    border-radius: 0 !important; background: #d4d0c8 !important; color:#000 !important;
    font-family: Tahoma, sans-serif !important; font-size:12px !important;
    padding: 2px 10px; height:23px; cursor:pointer; white-space:nowrap;
}
button:hover { background: #e8e4d8 !important; }
button:active { border-color: #808080 #ffffff #ffffff #808080 !important; }
/* Her original tailwind color-coded buttons -> this app's real save/delete/primary convention */
button.bg-green-600, button.bg-green-500  { background:#1a7a1a !important; color:#fff !important; border-color:#5ccc5c #0a500a #0a500a #5ccc5c !important; }
button.bg-blue-700, button.bg-blue-600    { background:#003087 !important; color:#fff !important; border-color:#5599cc #002266 #002266 #5599cc !important; }
button.bg-red-500, button.bg-red-600      { background:#8b0000 !important; color:#fff !important; border-color:#cc5555 #500000 #500000 #cc5555 !important; }
button.bg-amber-500                       { background:#b8860b !important; color:#fff !important; }
button.bg-gray-500, button.bg-gray-600    { background:#a8a8a8 !important; }

table { border-collapse:collapse; width:100%; font-size:11px; }
thead th {
    background:#d4d0c8 !important; border:1px solid #808080 !important; font-weight:bold;
    padding:3px 5px !important; text-align:left; white-space:nowrap;
}
tbody td { border:1px solid #d0ccc4 !important; padding:3px 5px !important; }
tbody tr { background:#fff; }
tbody tr:nth-child(even) { background:#f5f3ee; }
tbody tr:hover td { background-color:#c5d5e8 !important; cursor:pointer; }
.nav-highlighted td { background-color:#0a246a !important; color:#fff !important; }
#inventoryBody tr td:first-child { color:#003087; font-weight:bold; cursor:pointer; }
#inventoryBody tr:hover td:first-child { color:#8b0000; }
#inventoryBody tr.nav-highlighted td:first-child { color:#88aaff !important; }

label { font-size:11px !important; color:#000 !important; display:block; margin-bottom:1px; font-weight:normal; }

.nav-el:focus { outline: 2px dotted #0a246a !important; outline-offset:0 !important; }

::-webkit-scrollbar { width:16px; height:16px; }
::-webkit-scrollbar-track { background:#d4d0c8; border:1px solid #808080; }
::-webkit-scrollbar-thumb { background:#d4d0c8; border:1px solid; border-color:#ffffff #808080 #808080 #ffffff; }
</style>
</head>
<body>
<div class="win-titlebar">
    <span>&#x1F4CB; AISellProduct &mdash; <?php echo htmlspecialchars($_SESSION['company_name'] ?? 'Margalla 3M Industries'); ?></span>
    <span id="anoosha-live-clock" style="font-weight:normal;font-size:11px;"></span>
</div>
<script>
(function(){
    function tick(){ var el=document.getElementById('anoosha-live-clock'); if(el) el.textContent = new Date().toLocaleString('en-GB'); }
    tick(); setInterval(tick, 1000);
})();
</script>
