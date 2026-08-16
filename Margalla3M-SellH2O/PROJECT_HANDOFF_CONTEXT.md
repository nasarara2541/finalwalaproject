# AISellProduct — Project Handoff / Context Dump (v4)

> **Read this whole file before doing anything.** This is a university internship project for a real business, actively graded by the student's professor (often relayed through a teammate named **Qasim**, who explains professor instructions to the user, who relays them here). Business-logic and data correctness matter more than anything else on this project — mistakes get traced back and fixed carefully, and the user has repeatedly stressed this. **Never guess at business rules or invent data.** Verify live against the real running app/database before declaring anything done. This file **replaces every earlier version of itself** (including the ones written at the end of "Internship2" and "Internship3" sessions) — if you find an older copy anywhere, ignore it in favor of this one.
>
> **Do not trust any specific schema/column list below as necessarily still 100% current.** The user has explicitly asked that a new session verify live against the real database rather than blindly trust a written snapshot — schema and data both changed multiple times during the session that produced this file. A one-off throwaway PHP script through `includes/db.php` (pattern below) is the fastest way to check. Treat everything here as "true as of when this was written," not gospel.

---

## 1. What This Project Is

A full-stack **Point of Sale and Stock Management System** for **Margalla 3M Industries**, a water distribution company in Islamabad, Pakistan. University internship project. The professor periodically hands down new requirements referencing a Windows XP-era pharmacy reference software called **MedPharma** (also called "Pasha" by the user/Qasim — same reference software, different nickname), via screenshots. When a reference screenshot is given for a new field/screen:

1. Map every visible field to an actual DB column first.
2. Exclude anything with no matching column — **do not invent columns or fabricate data to fill in a field just because it's visible in a reference screenshot.** Show it as an honest placeholder (grey "—", disabled control) instead, and flag it to the user as needing a real definition/new column before it can be wired up. This was applied explicitly and repeatedly this session (Max. Discount%, transaction Status, "Sale Mode", printer selection — all shown inert, not faked).
3. Adapt labels to the water-business domain.
4. If a professor's instruction **explicitly** says to add new columns/tables, that's the one case schema changes are allowed — normally the schema is fixed and worked around. The user has since directly authorized several schema/data changes themselves too (see §7) — their own explicit instruction always overrides the "ask before altering schema" default.
5. Never change the XP visual skin, even when copying a layout idea from a differently-styled reference.

Originally called **AISellH2O**, rebranded to **AISellProduct** partway through (UI text only — `MargallaProd` the database name, `.env` keys, and the legacy seed file were deliberately left alone as infrastructure identifiers, not display branding).

---

## 2. Tech Stack — DO NOT DEVIATE

| Layer | Technology |
|---|---|
| Frontend | HTML + vanilla JavaScript + Tailwind CSS via CDN (`<script src="https://cdn.tailwindcss.com"></script>`) |
| Backend | PHP, procedural style — **no frameworks** |
| Database | Microsoft SQL Server (local SQLEXPRESS instance) |
| PHP DB driver | `sqlsrv_connect`, `sqlsrv_query`, `sqlsrv_fetch_array`, `sqlsrv_begin_transaction`/`commit`/`rollback` |
| Local server | XAMPP (Apache), PHP CLI at `C:/xampp/php/php.exe` |
| Charting | No library — hand-built CSS |

**Hard rules:** No React/Vue/jQuery/MySQL/Bootstrap/charting libraries. No code comments except a single line when the WHY is genuinely non-obvious (never explain WHAT code does). Tailwind via CDN only, no build step. Every screen is one self-contained `.php` file (PHP structure/includes + inline `<style>` + inline `<script>` at the bottom) — no separate JS/CSS files, no bundler.

### File locations
```
C:\xampp\htdocs\Margalla3M-SellH2O\
├── .env                        ← DB_SERVER / DB_NAME_WATER / DB_NAME_MEDSTOCK / DB_USER / DB_PASSWORD
├── PROJECT_HANDOFF_CONTEXT.md  ← this file
└── public\
    ├── login.php                  ← Step 1: database picker (Water vs Med Stock)
    ├── user_login.php             ← Step 2: employee User ID + Password login
    ├── logout.php
    ├── index.php                  ← Redirects to login.php
    ├── pos.php                    ← Main POS screen (Sale/Transactions/Inventory/Suppliers/Booking/Reports tabs)
    ├── stock_receiving.php        ← Stock Receiving / "Purchase Order" screen
    ├── manufacture.php            ← Manufacture / Item entry screen
    ├── manufacture_list.php       ← Manufacturer CRUD popup
    ├── admin_users.php            ← Admin-only: Manage Users
    ├── admin_dashboard.php        ← Admin-only: profit dashboard, chart, Net Profit panel
    ├── admin_reports.php          ← Admin-only: Profit Reports (By Product / By Region / By Customer tabs)
    ├── item_details.php           ← Admin-only: item packaging details — UI shell only, no save logic
    ├── includes\
    │   ├── db.php                 ← THE real DB connection — session-aware AND login-gated
    │   ├── require_login.php
    │   └── require_admin.php
    └── api\                        ← all backend endpoints, one file per action
```

### The real DB connection pattern (`includes/db.php`)
```php
<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Refuses to connect unless someone's logged in (except user_login.php, which
// sets $SKIP_LOGIN_CHECK=true before requiring this, since it needs a
// connection before a session exists).
if (empty($SKIP_LOGIN_CHECK) && empty($_SESSION['emp_user_id'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$env = parse_ini_file(__DIR__ . '/../../.env');   // __DIR__-relative, not hardcoded
$activeDb = $_SESSION['active_db'] ?? $env['DB_NAME_WATER'];

$conn = sqlsrv_connect($env['DB_SERVER'], [
    "Database" => $activeDb, "UID" => $env['DB_USER'], "PWD" => $env['DB_PASSWORD'],
    "TrustServerCertificate" => true, "CharacterSet" => "UTF-8", "LoginTimeout" => 5,
]);
```
`CharacterSet=UTF-8` is required or `json_encode()` silently fails on non-UTF-8 bytes (Med Stock test data has some). `LoginTimeout` fails fast on an unreachable server instead of hanging the page.

**Throwaway diagnostic script pattern** (used constantly this session, always deleted after use):
```php
<?php
$SKIP_LOGIN_CHECK = true;
if (session_status() === PHP_SESSION_NONE) session_start();
$_SESSION['active_db'] = null; // null coalesces to DB_NAME_WATER (MargallaProd)
require_once __DIR__ . '/includes/db.php';
// ... run whatever SQL, echo results ...
```
Save into `public/_diag_whatever.php`, run via `php.exe C:/xampp/htdocs/Margalla3M-SellH2O/public/_diag_whatever.php` (works even if Apache isn't running), **delete it immediately after**. Never leave one lying around.

### API pattern
```php
<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';
// Admin-only data additionally needs:
// if (empty($_SESSION['emp_is_admin'])) { http_response_code(403); echo json_encode(['error'=>'Admin access required']); exit; }
$sql  = "SELECT ... FROM ... WHERE ... ORDER BY ...";
$stmt = sqlsrv_query($conn, $sql, [$param1]);
$rows = [];
if ($stmt) { while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) { $rows[] = $row; } }
sqlsrv_close($conn);
echo json_encode($rows);
```
`[Transaction].Trans_no` and `ST_STOCKRECEIPT.Invoice_no` have no `IDENTITY` — the app computes the next value itself under a table lock (`SELECT ISNULL(MAX(x),0)+1 ... WITH (TABLOCKX, HOLDLOCK)`), see `save_transaction.php`/`save_stock_receipt.php`.

**sqlsrv gotcha discovered this session:** DECIMAL/NUMERIC columns come back from `sqlsrv_fetch_array` as PHP **strings**, not numbers (INT columns come back as real ints). Any JS that calls `.toFixed()` on a value straight from a DECIMAL column without `parseFloat()` first will crash at runtime — this exact bug shipped once this session (`stock_receiving.php` reload crash on `Tax_Percentage`) before being caught and fixed. Always `parseFloat()` DECIMAL-sourced values in JS.

### JavaScript pattern — mandatory error handling
Every screen's `<script>`, right after `toast()`, has:
```javascript
const _nativeFetch = window.fetch;
window.fetch = function(...args) {
    return _nativeFetch.apply(this, args).catch(err => {
        toast('Network/Server error — check DB_SERVER in .env and that the database is reachable', 'err');
        throw err;
    });
};
```
Copy this into any new screen.

---

## 3. Visual Style — Windows XP Desktop Aesthetic

Copy verbatim into any new screen's `<style>`:
```css
* { font-family: Tahoma, 'Segoe UI', sans-serif; font-size: 12px; box-sizing: border-box; }
html, body { height: 100%; margin: 0; }
body { background: #d4d0c8; overflow: hidden; }

.win-inset  { border: 1px solid; border-color: #808080 #ffffff #ffffff #808080; background:#fff; }
.win-panel  { border: 1px solid; border-color: #808080 #ffffff #ffffff #808080; background:#ece9d8; }
.win-titlebar { background: linear-gradient(to right, #0a246a, #3a6ea5); color: white; font-weight: bold; padding: 4px 8px; display:flex; align-items:center; justify-content:space-between; }
.win-menubar { background: #d4d0c8; border-bottom: 1px solid #808080; display:flex; padding: 2px; }
.win-menu-item { padding: 3px 10px; cursor:pointer; font-size:12px; }
.win-menu-item:hover, .nav-active { background:#0a246a; color:white; }

input[type=text], input[type=number], input[type=date], select {
    border: 1px solid; border-color: #808080 #ffffff #ffffff #808080;
    background: #ffff99; padding: 2px 4px; height: 22px; font-size:12px; font-family: Tahoma, sans-serif;
}
input[readonly] { background: #d4d0c8 !important; color:#333; }

.win-btn { background:#d4d0c8; border:1px solid; border-color:#ffffff #808080 #808080 #ffffff; padding:2px 10px; cursor:pointer; height:23px; display:inline-flex; align-items:center; gap:3px; }
.win-btn:hover { background:#e8e4d8; }
.win-btn-blue  { background:#003087; color:white; border-color:#5599cc #002266 #002266 #5599cc; }
.win-btn-green { background:#1a7a1a; color:white; border-color:#44aa44 #0a500a #0a500a #44aa44; }
.win-btn-red   { background:#8b0000; color:white; border-color:#cc4444 #550000 #550000 #cc4444; }

.win-table { width:100%; border-collapse:collapse; font-size:11px; }
.win-table thead th { border:1px solid #808080; padding:3px 5px; background:#d4d0c8; font-weight:bold; text-align:left; position:sticky; top:0; }
.win-table tbody tr { background:#fff; }
.win-table tbody tr:nth-child(even) { background:#f5f3ee; }
.win-table tbody tr:hover { background:#c5d5e8 !important; cursor:pointer; }
.win-table tbody tr.row-selected { background:#0a246a !important; color:white; }
.win-table tbody tr.row-selected td { color:white !important; }
.win-table td { border:1px solid #d0ccc4; padding:3px 5px; white-space:nowrap; }

.win-section-label { background:#d4d0c8; font-weight:bold; padding:3px 8px; border-bottom:1px solid #808080; display:flex; align-items:center; justify-content:space-between; }
.win-scroll { overflow:auto; min-height:0; }
.win-statusbar { background:#d4d0c8; border-top:1px solid #808080; padding:3px 8px; display:flex; gap:14px; font-size:12px; }

.popup-overlay { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:8000; justify-content:center; align-items:center; }
.popup-overlay.open { display:flex; }
.popup-box { background:#ece9d8; border:2px solid #0a246a; box-shadow:4px 4px 16px rgba(0,0,0,0.5); display:flex; flex-direction:column; min-width:320px; max-height:85vh; }
.popup-titlebar { background:linear-gradient(to right,#0a246a,#3a6ea5); color:white; font-weight:bold; padding:4px 8px; display:flex; align-items:center; justify-content:space-between; cursor:move; user-select:none; }
```

| Colour | Hex | Used for |
|---|---|---|
| Navy | `#0a246a` | Title bar, active nav, selected rows |
| Grey background | `#d4d0c8` | Body, panels, buttons |
| Panel background | `#ece9d8` | Card/panel fill |
| Yellow | `#ffff99` | ALL editable inputs |
| Green button | `#1a7a1a` | Save actions |
| Blue button | `#003087` | Primary actions |
| Red button | `#8b0000` | Delete/remove |
| Purple | `#5b3a8a` | Admin-only nav links |

### The body/html scroll rule (deliberate, load-bearing, do not break)
`html, body { height:100%; margin:0; }`, `body { overflow:hidden; }`, and Tailwind `h-screen` (not `min-h-screen`) on `<body>`. Every internal scroll panel needs `min-height:0` at every link in its flex chain, or it silently grows the whole page instead of clipping. **Verified this session on `pos.php` at both 1280×720 (normal PC) and 1024×600 (small laptop) — zero page-level scroll needed at either size**; small screens just show fewer rows per panel (each panel scrolls internally), never the whole page. This is the established, tested pattern — don't switch to page-level/body scrolling even if a user describes wanting "the whole screen to scroll," that phrasing was confirmed to mean "the small-screen experience should still work," not "break the no-body-scroll rule."

### Rules for replicating any reference screenshot
1. Map every visible field to an actual DB column first; exclude/flag anything with no match.
2. Adapt labels to the water-business domain.
3. All popups draggable (`makeDraggable(barId, boxId)` pattern, already in `pos.php`/`stock_receiving.php`).
4. No native dropdowns for record selection — click-a-row-in-a-table/list popups instead, except where explicitly asked for a literal dropdown, or for a small fixed mutually-exclusive set (e.g. radio buttons for a role).
5. Editable fields yellow, readonly/calculated fields grey, `tabindex="-1"` on every readonly/disabled field.
6. Click a row in a table to load its data above (not inline-edit).
7. Nearest expiry sorts first wherever expiry is involved.
8. No code comments.
9. Never change the visual skin, even copying a layout idea from a differently-styled reference.

---

## 4. Authentication & Authorization
- **`login.php`** — pick Water (`MargallaProd`) or Med Stock Testing (`MargallaTesting`). Sets `$_SESSION['active_db']`/`active_db_label`. Not XP-skinned (deliberate landing-page exception). Picking always clears `emp_user_*` and redirects to `user_login.php` (switching DB always forces re-login).
- **`user_login.php`** — User ID + Password, validates against `Interface_User`/`Interface_GroupUser`/`Interface_Group`, `password_hash()`/`password_verify()` (bcrypt, never plaintext). Sets `emp_user_id`, `emp_user_name`, `emp_is_admin`, `emp_group_name`.
- **`logout.php`** — clears `emp_user_*` only.
- **Test login used throughout this session:** User ID `ejaz`, Password `12345` (admin/Administrator group). Other seed users: `qasim`/`zeeshan`/`anusha`/`sara`/`rafia` (Management group), same password.
- **Two enforcement layers**: screen-level guard (`require_login.php`/`require_admin.php`) AND API-level (`includes/db.php` itself refuses unauthenticated connections; admin-only APIs additionally check `emp_is_admin` and return 403). **Any new admin-only API must include this check explicitly** — it is not automatic.

**Live-testing gotcha:** the browser session/cookie expires between conversation turns in this environment fairly often — if a page redirects back to `login.php`/`user_login.php` unexpectedly, just re-run the two-step login (`login.php?select=water` → fill both User ID **and** Password fields, they don't persist between navigations — a repeated mistake this session was forgetting to fill User ID and only filling Password).

---

## 5. The Screens — Current Status

### 5a. `pos.php` — Point of Sale ✅ heavily refined this session
- Sale tab: item search → Available Products (now stacked directly above a new **Expiry Info** panel in the same column, not a separate side column — mirrors `stock_receiving.php`'s panel, same `get_stock_expiry_panel.php` data source, updates on item select/cart-edit/reset) → Unit Price/Qty → Add to Bill → cart.
- Customer field: text + `<datalist>`. "Bill Ref" separately PATCHable. Postpone/Held Bills in `localStorage`, keyed per active database.
- FEFO batch deduction, `QTY_INHAND` always reconciled from `SUM(ITEMS_AVAILABLE)`, Cash/Card sales blocked if `Paid < Net Total` (Credit exempt), money always recomputed server-side.
- **Calculator/Totals bar — fully rebuilt this session**, now a 3-block layout matching a MedPharma "MainPharma Retail" reference screenshot exactly:
  - **Left block**: item-count badge (live `cart.length`, corrected mid-session from a wrong first guess of "F5 hotkey hint"), List (refresh) button, **BarCode field** (real — `search_items.php` now also matches `Item_Stock.BARCODE` exactly, a column that existed but was never wired to anything before), User/Bill#/StockQTY+nearest-ExpDate line (all real, live-computed), Expiry Dates/Calculator/RemoveMed/Cancel buttons (Calculator is a real working arithmetic popup; RemoveMed/Remove clear the current item selection, not the cart).
  - **Explicitly inert placeholders, not fabricated data** — flagged clearly to the user and left as grey "—"/disabled controls: **Max. Discount%** (no per-user discount-limit column exists), **transaction Status** (no such column), **"Sale Mode"** checkbox (no matching concept anywhere in the schema), **Printer selection** (no printer feature exists). Do not invent real values for these without new instruction.
  - **Middle block**: Total/Net Total/Disc%/Cash/Disc Amt/Balance — the 4 result fields (Total, Disc Amt, Net Total, Balance) now use a `.calc-highlight` CSS class: **black background, red text (`#ff3333`), 20px font** (explicit teacher instruction; had to fix a CSS specificity fight against `input[readonly]`'s own `!important` rule — the class is `input.calc-highlight` specifically, not just `.calc-highlight`, to win the tie). Disc%/Cash stayed yellow (editable-field convention preserved) but also enlarged.
  - **Right block**: Print, inert Printer label, Remove.
- **Caught-and-fixed self-inflicted bug**: deleted the hidden `user-id` field during the bottom-bar rebuild; it's read by `saveSale()` to record who made the sale, and was in the Enter-key tab order. Restored as `type="hidden"`.

### 5b. `stock_receiving.php` — Stock Receiving / "Purchase Order" ✅ migrated, and this session's persistence work is done
This screen **is** what the professor/Qasim mean by both "update Stock Receiving" and "Purchase Order screen" — confirmed via a MedPharma reference screenshot ("Iqbal Pharmacy — Purchase Order") showing the same fields Stock Receiving already has. **The separate "Purchase Order screen" task on the checklist is very likely redundant with this one**, not a second screen — flag to the user/Qasim to confirm, but treat as resolved unless told otherwise.

Box-vs-bottle conversion is central: "QTY Received" entered in boxes, `× Pcs Per Box` once at save into pieces (`ITEMS_RECEIVED`/`ITEMS_AVAILABLE`). `PRICE_PERITEM`/`PPRICE_PERITEM` store **per-bottle** price.

**This session's real work — persistence that was previously UI-only is now fully wired and tested:**
- Confirmed live that `add_stock_receiving_fields.sql` (the additive migration for `PAYMENT_TYPE`, `SUPPLIER_INVOICE_NO`, `LOOSE_PURCHASE`, `ADDA_CHARGES`, `OTHER_CHARGES` on `ST_STOCKRECEIPT`, and `BONUS_QTY`, `LINE_DISC_PERCENT`, `LINE_DISC_AMOUNT` on `ST_STOCKRECEIPTDETAIL`) **has been run** — the columns exist. (The user initially thought they hadn't run it; live verification proved otherwise. Always verify, don't trust memory on this kind of thing.)
- `save_stock_receipt.php` now actually writes all of the above, plus `Tax_Percentage`/`Tax_amount` (GST, computed server-side from qty/price/pct, never trusted from client).
- `get_stock_receipt_detail.php` now reads all of it back, plus `Item_Stock.QTY_INHAND`, which is now its own column in the Received Items grid.
- **Business decision made and implemented**: Bonus boxes are free stock from the supplier — they **count toward `ITEMS_RECEIVED`/`ITEMS_AVAILABLE`/`QTY_INHAND`** (real, sellable inventory) but **not toward cost/Amount** (`qty_received × price` only, bonus excluded, since it's free).
- **Two real bugs found and fixed** while wiring this up:
  1. DECIMAL columns (Tax_Percentage etc.) came back from sqlsrv as strings; `.toFixed()` on reload crashed and silently blanked the whole Received Items table. Fixed with `parseFloat()`.
  2. More serious: reloading a saved invoice and re-saving it **without touching anything** would silently re-multiply stock counts — bonus got added a second time, and (a pre-existing bug, not newly introduced) any multi-pack item (`UNITS_PERITEM > 1`) had its pieces re-multiplied by units on every re-save. Fixed by reconstructing "paid boxes only" on reload (`ITEMS_RECEIVED / UNITS_PERITEM − BONUS_QTY`), verified via a real Modify→Save-with-no-changes cycle showing the DB values stay identical.
- Verified end-to-end live: saved a real test invoice with every new field populated, confirmed exact DB values, reloaded it, confirmed exact redisplay, did a no-op Modify+Save, confirmed no drift, then fully cleaned up the test invoice and restored `Item_Stock.QTY_INHAND`.
- **Still blocked on Qasim's explanation** (from the reference screenshot, no matching data yet): the **"Description"** header field, and **3 reorder-suggestion buttons** ("Purchase Order Against Items Short List" / "...Safety Level" / "...Against Sold Items" — would need a new "safety stock level" column that doesn't exist).

### 5c. `manufacture.php` + `manufacture_list.php` ✅ unchanged this session

### 5d. `admin_users.php` — Manage Users ✅ unchanged this session

### 5e. `admin_dashboard.php` — Admin Dashboard ✅ significantly reworked this session
- Stat tiles, CSS stacked-bar chart, monthly summary table, per-item breakdown — all still present.
- **Sale formula changed**: now trusts `SUM(trans_detail.amount)` directly (the stored ledger value) instead of recomputing `quantity × Price_PerItem` — **explicit user decision**, made after comparing against a teammate's numbers on the same underlying data (see §8 for the full reasoning and the 2 known-typo rows this affects: Trans_no 260 and 465).
- **Cost/Profit formula changed twice this session — the current one is the flat-rate model, not the earlier purchase-price-based fix.** See §8, this is the single most important "don't get confused" section in this whole file.
- **New: Net Profit panel.** Its own month picker, an Expenses field, live Net Profit = Total Profit − Expenses. Auto-fills Expenses from `SummarySalesExp` when a saved row exists for that month (labeled "(from database)"); if no row exists, calls `api/ensure_summary_sales_exp.php` which computes that month's Total_Sales live and inserts a fresh row (Expenses defaults to 0, labeled "(new row created — enter expenses)"). **Editing Expenses never writes back to the table** — it's read-only from the app's side; a genuine "save Expenses" feature was discussed but not built (flagged as still-open, see §9).

### 5f. `admin_reports.php` — Profit Reports ✅ new screen, built this session
3 tabs: **By Product** (simple list, one row per SKU), **By Region**, **By Customer** (both matrices: rows = Region/Customer, columns = 0.5L/1.5L/6L/12L/19L, cell = Profit, sorted by most-profitable-first, Customer tab has a live name filter). Each tab has its own independent month picker. Uses the same Sale/Cost formula as the dashboard (see §8 — **keep these in sync**, they were changed together every time this session). Reachable via a new "📈 Profit Reports" nav link added to all 6 other screens' menu bars.

### 5g. `item_details.php` — UI shell only, no save logic, unchanged this session. Do not wire it up without new instructions.

---

## 6. Client-Server Deployment — unchanged this session, still true
Multiple PCs pointing at one shared SQL Server. Fixed previously: `.env` paths made relative, the ~35 `fetch()` calls given `.catch()` handlers (the actual cause of silent hangs, not the `.env` path), dead `api/db.php` deleted, 5s `LoginTimeout` added. Still outstanding: actual SQL Server network configuration (TCP/IP, firewall, Mixed Mode Auth) is infrastructure work on the user's end, not something fixable from the codebase.

---

## 7. The Database — Schema, Location, and Every Change Made This Session

### 7.1 Where to look — and a real mixup to avoid
`C:\Users\hp\Desktop\SChemas\` has two subfolders and one loose file, and **they are easy to confuse**:
- **`Actual Schema\MargallaProd - 1.sql`** — the real, original DDL + seed script for the **water-distribution** database (`MargallaProd`), the one this whole app actually uses. Its `Item_Stock` seed values (0.5L/1.5L/6L/12L/19L, prices, purchase prices) were confirmed this session to still exactly match live data — this file is trustworthy as a baseline.
- **`New Schema\`** — despite the name, this is **not** an updated version of the water database. Every file in it (`insert_item_stock`, `insert_transaction`, `insert_trans_detail`, `insert_st_stockreceiptdetail`, `MargallaTesting - 1.sql`) starts with `USE MargallaTesting;` — it's the **separate pharmacy/Med Stock testing database**, a completely different product catalog (~9,000 pharmacy items like ZINACEF/VOLTRAL). **Do not treat this as relevant to the water-distribution work** unless a task is explicitly about the Med Stock dataset.
- **`add_stock_receiving_fields.sql`** (loose file in `SChemas\`, not in either subfolder) — the additive migration mentioned in §5b. **Confirmed run** this session (columns exist live) — this was previously assumed not-run and had to be verified.

Other reference material on Desktop: **`pasha-Uis\Stock Purchase Order.jpg`** (the MedPharma reference resolving the Stock Receiving/Purchase Order screen ambiguity, §5b), **`MedPharma\Admin - Sales Summary.jpg`** (mentioned once, not yet explored), **`Summarization.xlsx`** (§8 — the source of truth for the current live Profit/Cost formula, has `Sales Data`/`Profit`/`Comparison` sheets; ignore the `Comparison` sheet, sales were already fixed by the time it was made).

### 7.2 Every structural/destructive change made to `MargallaProd` this session, in order
This is real, already-applied history — don't redo any of it, and don't be surprised if live data doesn't match an even-older handoff doc:

1. **`Customer`, `[Transaction]`, `trans_detail` were fully dropped and recreated from scratch** (the user's explicit decision, made after being warned about the risks — proceeded anyway, deliberately). Rebuilt via exact `CREATE TABLE` scripts reconstructed from the *live* schema at the time (not the possibly-stale DDL file), with a `Region VARCHAR(100)` column added to `Customer` proactively. A full data backup was taken first (`C:\Users\hp\Desktop\MargallaProd_backup_<timestamp>.sql`).
2. **`Tansactions.sql`** (`C:\Users\hp\Desktop\Tansactions.sql`) was reloaded — 1,060 `Transaction` rows, 1,697 `trans_detail` rows. 77 of the 1,060 rows needed their `Customer_id` remapped (old duplicate-customer merge, using an already-resolved 8-group mapping table from a prior session) — this was done during load, verified row-by-row (100% match against expectation), and **`Tansactions.sql` itself was also rewritten in place** with the corrected IDs so a future reload doesn't need the remap step again (original backed up as `Tansactions_before_id_remap_backup.sql`).
3. **`Item_Stock.UNITS_PERITEM`** for stock 1003 (1.5L Large A) and 1004 (1.5L Small B) changed from **12 → 6** — this was a real data-correctness fix (a teammate's copy of the database had the correct value; the original seed script had it wrong for these two specific items), not a schema change. Confirmed this fix alone resolved 100% of a cost discrepancy against a teammate's numbers across all 13 months of data.
4. **`SummarySalesExp` table created** (`Sale_ID INT IDENTITY PK`, `Sale_Date DATE`, `Total_Sales DECIMAL(12,2)`, `Total_Expenses DECIMAL(12,2)`, unique constraint on `Sale_Date`) and populated with 13 months (Jul-2025 → Jul-2026). `Total_Sales` values were later **recalculated via a live subquery** (not left as the original hardcoded snapshot) per a teammate's correct architectural objection — see §9. `Total_Expenses` values are hardcoded per month from `Summarization.xlsx` (0 for the 4 earliest months — genuinely untracked then, not actually zero).
5. **`api/ensure_summary_sales_exp.php`** (new endpoint) now auto-inserts a new `SummarySalesExp` row with a live-calculated `Total_Sales` the first time any month without one is viewed in the Dashboard's Net Profit panel — so the table keeps growing correctly on its own, not just from the original 13-row backfill.

### 7.3 Live `Item_Stock` reference (7 real water products, as of this session — verify before trusting)
| Stock# | Item | Price | Purchase Price | Units/Item |
|---|---|---|---|---|
| 1001 | 0.5L Large A | 350 | 260 | 12 |
| 1002 | 0.5L Small B | 300 | 200 | 12 |
| 1003 | 1.5L Large A | 350 | 200 | **6** (was 12, fixed this session) |
| 1004 | 1.5L Small B | 300 | 180 | **6** (was 12, fixed this session) |
| 1005 | 6L | 130 | 60 | 1 |
| 1006 | 12L | 210 | 140 | 1 |
| 1007 | 19L | 180 | 30 | 1 |

---

## 8. Profit/Cost Calculation — Read This Before Touching Any Report

**This changed twice this session. The formula described in any earlier handoff doc (purchase-price-based) is superseded. The current, live, correct formula is the flat-rate one below — used in `admin_dashboard.php`, `get_dashboard_summary.php`, `get_dashboard_by_item.php`, `get_report_by_region.php`, and `get_report_by_customer.php`, kept in sync across all of them.**

### Sale (current, live)
```sql
SUM(trans_detail.amount)   -- the stored ledger value, trusted directly
```
Not recomputed from `quantity × Price_PerItem` anymore. There are 2 known rows where the stored `amount` doesn't match `quantity × Price_PerItem` (hand-transcription typos: `Trans_no 260` and `465`) — the user was shown this precisely and **explicitly chose to trust the stored ledger value as-is**, accepting those 2 rows stay "wrong" rather than silently auto-correcting real recorded data.

### Profit / "Cost" (current, live — the flat-rate model, verified against `Summarization.xlsx` exactly, multiple months, zero error)
```
Profit(0.5L) = Packs × 120
Profit(1.5L) = Packs × 150
Profit(6L)   = Packs × 60
Profit(12L)  = Packs × 50
Profit(19L)  = Sale (100% — the container is reusable/refillable, cost treated as negligible)

"Cost" (as still displayed/used internally, so the existing Sale=Cost+Profit chart math keeps working) = Sale − Profit
```
implemented as, per line item:
```sql
SUM(td.amount - CASE
    WHEN s.ITEM_NAME LIKE '0.5L%' THEN td.quantity * 120
    WHEN s.ITEM_NAME LIKE '1.5L%' THEN td.quantity * 150
    WHEN s.ITEM_NAME LIKE '6L%'   THEN td.quantity * 60
    WHEN s.ITEM_NAME LIKE '12L%'  THEN td.quantity * 50
    WHEN s.ITEM_NAME LIKE '19L%'  THEN td.amount
    ELSE 0
END) AS Cost
```
**These rates are hardcoded per explicit instruction, not derived from `Item_Stock.PURCHASE_PRICE`.** "Cost" here is *implied* (backed into existence so old chart/display code keeps working), not a real recorded cost — it can legitimately go negative for a single item/month if the flat rate exceeds that item's actual average sale price that month; this is expected, not a bug.

### The now-superseded, purchase-price-based formula (do not use, kept here only so you recognize it if you see it in old code/docs)
```sql
SUM(COALESCE(td.PPrice_amount, td.quantity * s.PURCHASE_PRICE / NULLIF(s.UNITS_PERITEM, 0)))
```
This *was* the correct, verified formula earlier in the session (and is what an even-older handoff doc describes) — it was verified against a teammate's numbers, and the `UNITS_PERITEM` fix in §7.2 point 3 was made specifically to fix it. It was then **replaced entirely**, not just adjusted, when the user's supervisor specified the flat-rate model instead and it verified exactly against real Excel data. If you're asked to "fix the cost formula" and find code using `PURCHASE_PRICE`, that's stale — the flat-rate model above is current.

### `trans_detail.PPrice_amount` is NULL on all 1,697 rows
Never populated for this historical bulk-loaded data (only the live running app populates it at sale time going forward). Not relevant to the current flat-rate formula at all, but worth knowing if it ever becomes relevant again.

---

## 9. This Week's Task Checklist (from the professor, relayed by Qasim)

### ✅ Done this session
- Refine/update customer data, modify customer table (§7.2)
- User Management screen UI (already existed, unchanged)
- Profit Report by Product / Region / Customer (§5f)
- Test the profit reports with sample data (extensively, cross-verified against real data and a teammate's independent numbers)
- Net Profit calculation, wired to a real (if minimal) `SummarySalesExp` table (§5e, §7.2)
- Stock Receiving screen update — the main form + persistence (§5b) — **partially done, see below for what's left**

### ⬜ Blocked — waiting on Qasim's explanation
- Stock Receiving's **"Description"** field and its **3 reorder-suggestion buttons** (§5b)
- **Supplier screen UI** — no spec yet
- **Booking screen UI** (dedicated screen + time-period/status filters + summary) — needs explaining, currently just a read-only tab inside `pos.php`

### ⬜ Ready to start, just needs a "go"
- **Customer screen UI** — straightforward CRUD, same pattern as `manufacture_list.php`
- **Purchase Order screen UI** — very likely the same thing as Stock Receiving (§5b), confirm with Qasim rather than build a duplicate screen
- "All screens in MedPharma" — umbrella item, broken into the ones above; ask if there's one beyond Supplier/Customer/Booking/Purchase Order

### 🆕 Explicitly flagged as unresolved, not yet decided
- **Persisting edited `Total_Expenses` back into `SummarySalesExp`** — currently the Net Profit panel is read/auto-create only; typing a new Expenses value for a month never saves it, so it has to be re-typed every time that month is viewed again. Ask the user if they want this built.

---

## 10. Working Conventions Learned This Session

- **Test everything live, always.** Real running app (XAMPP + real SQL Server data), never "looks right in the code" alone. This session used the `Claude_Browser` MCP tools extensively — navigate, read console errors, click through real flows, verify via direct SQL query afterward. This caught two real bugs (§5b) that pure code review would have missed.
- **Verify before asserting, especially about the user's own project.** Twice this session an assumption stated with confidence turned out to be wrong when actually checked (whether a migration had been run; the meaning of a UI element in a reference screenshot) — always run the check rather than trust memory/inference, and say so plainly when corrected.
- **`php -l <file>` after every single edit, no exceptions.**
- **Throwaway diagnostic/test scripts always get deleted immediately after use**, and any test data written to the real database gets cleaned up (deleted rows, reverted quantities) right after verification — this was done rigorously every time this session (e.g. a full test stock-receiving invoice was created, verified in detail, then completely deleted and `QTY_INHAND` restored).
- **Schema-altering scripts are hand-delivered as `.sql` files on the Desktop for the user to run themselves, by default** — this was the pattern for `create_summarysalesexp.sql`, `fix_summarysalesexp_dynamic.sql`, `1_drop_tables.sql`/`2_recreate_tables.sql`. Data-level fixes/loads have often been run directly instead, when the user asked for that explicitly. Pay attention to which the user is asking for.
- **The user gets frustrated by excessive clarifying questions** when a reasonable default exists — the working pattern that's held up well: make a sensible, clearly-flagged judgment call and proceed, rather than blocking on a question, *except* when the choice touches money/data-correctness/schema meaningfully, where asking first is still correct. Several genuine "ask first" moments this session were warranted and the user engaged with them properly (the DROP TABLE decision, the 12L profit-formula inconsistency, the Region/Customer report formula scope).
- **When a reference screenshot is given, don't guess at ambiguous fields** — but once the user has explicitly said "just replicate everything, don't leave gaps," fill in the *layout* densely and honestly using real/derivable data wherever it exists, and render anything with no backing data as an obviously-inert placeholder (grey, disabled) rather than either fabricating a value or leaving a visually-empty gap. Say plainly, in the same response, exactly which pieces are inert and why.
- **Business-logic correctness is paramount** — explicitly and repeatedly the user's top priority, stated stronger than for the rest of the app even. This means: when wiring up a new field (like Bonus Qty affecting stock), think through what it actually *means* for inventory/money before just piping a value into a column — the Bonus-Qty-affects-stock-but-not-cost decision and the resulting double-counting bug fix (§5b) are the clearest example this session of doing this right.
- Login for live testing: **User ID `ejaz`, Password `12345`** — remember to fill *both* fields, they reset on navigation.

---

## 11. Where Things Stand Right Now

The user is actively working through the "This Week" checklist in §9. The two live blockers are **Qasim's explanations** for (a) Stock Receiving's Description field + 3 reorder buttons, and (b) the Supplier and Booking screens' actual specs. Until those arrive, the ready-to-start items (Customer screen UI, confirming Purchase Order = Stock Receiving) are the sensible next things to pick up. If the user opens a new session and doesn't immediately say what's next, ask which of the checklist items in §9 to start on — don't assume.
