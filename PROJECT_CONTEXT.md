# AISellH2O — Project Context & Handoff Document

## Overview

This is a **full-stack Point of Sale and Stock Management System** built for **Margalla 3M Industries**, a water/beverage distribution company based in Islamabad, Pakistan. The system is built as a university internship project under a professor's supervision.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Frontend | HTML + vanilla JavaScript + Tailwind CSS (CDN) |
| Backend | PHP (procedural, no framework) |
| Database | Microsoft SQL Server |
| PHP DB Driver | `sqlsrv_*` functions (Microsoft's official PHP extension) |
| Local Server | XAMPP on Windows |
| Styling approach | Windows XP/2000 classic desktop app aesthetic — grey panels, inset/raised borders, yellow editable fields |

**Important:** No React, no Vue, no jQuery, no MySQL. Everything uses `sqlsrv_connect`, `sqlsrv_query`, `sqlsrv_fetch_array`. Tailwind is loaded via CDN script tag for quick prototyping (not compiled).

---

## Project Location

```
C:\xampp\htdocs\Margalla3M-SellH2O\public\
```

The `.env` file sits one level above `public/`:
```
C:\xampp\htdocs\Margalla3M-SellH2O\.env
```

`.env` format:
```
DB_SERVER=localhost
DB_NAME=AISellH2O
DB_USER=sa
DB_PASSWORD=yourpassword
```

All API files load the DB connection like this:
```php
$env  = parse_ini_file('C:/xampp/htdocs/Margalla3M-SellH2O/.env');
$conn = sqlsrv_connect($env['DB_SERVER'], [
    "Database"               => $env['DB_NAME'],
    "UID"                    => $env['DB_USER'],
    "PWD"                    => $env['DB_PASSWORD'],
    "TrustServerCertificate" => true,
]);
```

---

## File Structure

```
public/
├── pos.php                        ← Main POS billing screen (primary page)
├── stock_receiving.php            ← Stock receiving form
├── add_item.php                   ← Register new stock item (opens as popup)
│
├── api/
│   ├── search_items.php           ← Search Item_Stock (filters QTY_INHAND > 0)
│   ├── get_all_stock_items.php    ← Search Item_Stock (NO qty filter — for stock receiving)
│   ├── get_transactions.php       ← Get all transactions list
│   ├── get_transaction_detail.php ← Get one transaction + its detail lines
│   ├── save_transaction.php       ← Save new sale + update stock quantities
│   ├── get_inventory.php          ← Full Item_Stock list for Inventory tab
│   ├── get_suppliers.php          ← Full ST_Supplier list
│   ├── get_bookings.php           ← Full Item_Booking list
│   ├── get_reports.php            ← Sales summary grouped by Month + Size
│   ├── get_stock_receipts.php     ← All ST_STOCKRECEIPT headers
│   ├── get_stock_receipt_detail.php ← One receipt header + its detail lines
│   ├── get_stock_expiry_panel.php ← ST_STOCKRECEIPTDETAIL joined with Item_Stock, sorted by EXPIRY_DATE ASC
│   ├── get_all_stock_items.php    ← All active items regardless of qty (for stock receiving page)
│   ├── save_stock_receipt.php     ← Save stock receipt + increment QTY_INHAND
│   ├── update_receipt_status.php  ← Update ST_STOCKRECEIPT.STATUS only
│   └── add_item.php               ← Insert new row into Item_Stock
│
└── includes/
    └── db.php                     ← DB connection (required by all api files)
```

---

## Database — AISellH2O

### Full DDL Summary

```sql
CREATE TABLE Share_Inventory (
    INV_No INT NOT NULL IDENTITY(1,1),
    INV_Name VARCHAR(100), INV_Desc VARCHAR(255),
    Initial_Cost DECIMAL(12,2), INV_Type VARCHAR(50), ACC_NO VARCHAR(20),
    CONSTRAINT PK_Share_Inventory PRIMARY KEY (INV_No)
);

CREATE TABLE Share_Calculation (
    Share_Year INT NOT NULL, Share_Month VARCHAR(20) NOT NULL, INV_no INT NOT NULL,
    Amount DECIMAL(12,2), Filing_Date DATE, User_id VARCHAR(50),
    CONSTRAINT PK_Share_Calculation PRIMARY KEY (Share_Year, Share_Month, INV_no),
    CONSTRAINT FK_ShareCalc_Inventory FOREIGN KEY (INV_no) REFERENCES Share_Inventory(INV_No)
);

CREATE TABLE Share_Holders (
    ShareHolder_No INT NOT NULL IDENTITY(1,1),
    ShareHolder_Name VARCHAR(100) NOT NULL,
    Address VARCHAR(255), City VARCHAR(100), Tel_No VARCHAR(20),
    Mobile_No VARCHAR(20), Email VARCHAR(100),
    Invested_Amount DECIMAL(12,2), Status VARCHAR(20),
    CONSTRAINT PK_Share_Holders PRIMARY KEY (ShareHolder_No)
);

CREATE TABLE ShareHolder_Investment (
    Share_Year INT NOT NULL, Share_Month VARCHAR(20) NOT NULL,
    ShareHolder_No INT NOT NULL,
    Share_Amount DECIMAL(12,2), Share_NewAmount DECIMAL(12,2),
    Share_Percent DECIMAL(5,2), Sharable_NetIncome DECIMAL(12,2),
    ShareRec_Amount DECIMAL(12,2), Invest_Date DATE,
    CONSTRAINT PK_ShareHolder_Investment PRIMARY KEY (Share_Year, Share_Month, ShareHolder_No),
    CONSTRAINT FK_ShareHolderInv_Holder FOREIGN KEY (ShareHolder_No) REFERENCES Share_Holders(ShareHolder_No)
);

CREATE TABLE Manufacture (
    Manufacture_no INT NOT NULL IDENTITY(1,1),
    M_Name VARCHAR(100) NOT NULL, M_ShortName VARCHAR(50),
    Address VARCHAR(255), City VARCHAR(100), Tel_no VARCHAR(20),
    CONSTRAINT PK_Manufacture PRIMARY KEY (Manufacture_no)
);

CREATE TABLE ST_Supplier (
    SUPPLIER_CODE VARCHAR(20) NOT NULL,
    SUPPLIER_NAME VARCHAR(100) NOT NULL,
    CONTACT_PERSON VARCHAR(100), ADDRESS VARCHAR(255),
    CITY VARCHAR(100), REGION VARCHAR(100), POSTAL_CODE VARCHAR(20),
    TELEPHONE_NO VARCHAR(20), MOBILE_NO VARCHAR(20),
    FAX_NO VARCHAR(20), EMAIL VARCHAR(100),
    CONSTRAINT PK_ST_Supplier PRIMARY KEY (SUPPLIER_CODE)
);

CREATE TABLE Item_Booking (
    ID INT NOT NULL IDENTITY(1,1),
    Item_name VARCHAR(100), Demand_qty INT,
    Booking_date DATE, Demand_date DATE,
    Supplier_code VARCHAR(20), Comments VARCHAR(255),
    Status VARCHAR(20), Prod_Type VARCHAR(50),
    CONSTRAINT PK_Item_Booking PRIMARY KEY (ID),
    CONSTRAINT FK_ItemBooking_Supplier FOREIGN KEY (Supplier_code) REFERENCES ST_Supplier(SUPPLIER_CODE)
);

CREATE TABLE ST_STOCKRECEIPT (
    Invoice_no INT NOT NULL IDENTITY(1,1),
    INVOICE_DATE DATETIME, SUPPLIER_CODE VARCHAR(20),
    RECEIVED_DATE DATETIME, TOTAL_AMOUNT DECIMAL(12,2),
    DISCOUNT DECIMAL(10,2), STATUS VARCHAR(20),
    RECEIVED_BY VARCHAR(100), User_id VARCHAR(50),
    CONSTRAINT PK_ST_STOCKRECEIPT PRIMARY KEY (Invoice_no),
    CONSTRAINT FK_StockReceipt_Supplier FOREIGN KEY (SUPPLIER_CODE) REFERENCES ST_Supplier(SUPPLIER_CODE)
);

CREATE TABLE Item_Stock (
    STOCK_NUMBER VARCHAR(20) NOT NULL,
    BRAND_NAME VARCHAR(100), ITEM_NAME VARCHAR(100),
    ITEM_TYPE VARCHAR(50), STOCK_TYPE VARCHAR(50),
    VOLUME_ML VARCHAR(20), AVAILABLE_STATUS VARCHAR(20),
    SIZE_DESC VARCHAR(100), BARCODE VARCHAR(50),
    OTC_QTY INT, UNIT_TYPE VARCHAR(20),
    UNITS_PERITEM INT, PRICE DECIMAL(10,2),
    QTY_INHAND INT, MANUFACTURE_NO INT,
    LOCATION VARCHAR(100), PERCENTAGE_DISC DECIMAL(5,2),
    SUPPLIERS_LIST VARCHAR(255),
    CONSTRAINT PK_Item_Stock PRIMARY KEY (STOCK_NUMBER),
    CONSTRAINT FK_ItemStock_Manufacture FOREIGN KEY (MANUFACTURE_NO) REFERENCES Manufacture(Manufacture_no)
);

CREATE TABLE ST_STOCKRECEIPTDETAIL (
    Invoice_no INT NOT NULL, STOCK_NUMBER VARCHAR(20) NOT NULL,
    PRICE_PERITEM DECIMAL(10,2), ITEMS_RECEIVED INT,
    ITEMS_AVAILABLE INT, EXPIRY_DATE DATE,
    BATCH_NO VARCHAR(50), UNITS_PERITEM INT,
    WPRICE_PERITEM DECIMAL(10,2), PPRICE_PERITEM DECIMAL(10,2),
    Update_Status VARCHAR(20), Price_PerUnit DECIMAL(10,2),
    PPrice_PerUnit DECIMAL(10,2), Record_date DATE,
    Tax_Percentage DECIMAL(5,2), Tax_amount DECIMAL(10,2),
    Serial_No INT,
    CONSTRAINT PK_ST_STOCKRECEIPTDETAIL PRIMARY KEY (Invoice_no, STOCK_NUMBER),
    CONSTRAINT FK_StockReceiptDetail_Receipt FOREIGN KEY (Invoice_no) REFERENCES ST_STOCKRECEIPT(Invoice_no),
    CONSTRAINT FK_StockReceiptDetail_Stock FOREIGN KEY (STOCK_NUMBER) REFERENCES Item_Stock(STOCK_NUMBER)
);

CREATE TABLE [Transaction] (
    Trans_no INT NOT NULL IDENTITY(1,1),
    Cust_name VARCHAR(100), Cust_telno VARCHAR(20),
    Trans_date DATETIME, Trans_type VARCHAR(20),
    Trans_amount DECIMAL(12,2), Disc_percentage DECIMAL(5,2),
    Tax_status VARCHAR(20), Branch_code VARCHAR(20),
    Gross_amount DECIMAL(12,2), Paid_amount DECIMAL(12,2),
    Balance_amount DECIMAL(12,2), User_id VARCHAR(50),
    CONSTRAINT PK_Transaction PRIMARY KEY (Trans_no)
);

CREATE TABLE trans_detail (
    Trans_no INT NOT NULL, stock_number VARCHAR(20) NOT NULL,
    quantity INT, Price_PerItem DECIMAL(10,2),
    amount DECIMAL(12,2), User_id VARCHAR(50),
    Status VARCHAR(20), Invoice_No INT,
    PPrice_amount DECIMAL(10,2),
    CONSTRAINT PK_trans_detail PRIMARY KEY (Trans_no, stock_number),
    CONSTRAINT FK_transdetail_Transaction FOREIGN KEY (Trans_no) REFERENCES [Transaction](Trans_no),
    CONSTRAINT FK_transdetail_Stock FOREIGN KEY (stock_number) REFERENCES Item_Stock(STOCK_NUMBER)
);
```

### Test Data

Test data was generated and saved as `test_data.sql`. It inserts:
- 2 Manufacturers (Margalla 3M Industries, Nestle Pakistan)
- 4 Suppliers (Karachi, Lahore, Islamabad, Rawalpindi)
- 7 Item_Stock rows (WTR-0001 to WTR-0007 — water bottles and jars in various sizes)
- 3 ST_STOCKRECEIPT invoices
- 8 ST_STOCKRECEIPTDETAIL lines across those invoices
- 4 Item_Booking rows
- 3 Share_Holders
- 3 Share_Inventory items

---

## Screen 1: `pos.php` — Point of Sale

### What it does
Main cashier/billing screen. Cashier searches for water products, adds them to a bill, enters cash paid, saves the transaction.

### Layout
- **Top bar:** Bill#, Customer Name, Mobile, Date (live clock), Sale Type, Branch, New/Save/Print/View Challan/Cancel buttons
- **Search bar:** Live search input + auto-fill fields (Stock No, Volume, Type, Unit Price, Qty, Amount, In-Hand)
- **Main area (3 columns):**
  - Left (largest): Bill Items cart table
  - Middle: Available Products — always-visible scrollable list of all active stock items, click to select
  - Right: removed (was invoice preview, now replaced by View Challan popup)
- **Bottom bar:** Total / Discount % / Discount AMT / Net Total / Cash / Balance calculator row
- **Recent Transactions table** at the very bottom of the POS view

### Navigation tabs (top menu bar)
Sale | Transactions | Inventory | Stock Receiving | Booking | Reports

### Key features
- **Search:** typing in search box filters the Available Products middle panel live. Selecting item from middle panel fills entry fields
- **Keyboard navigation:** Tab/Arrow keys move between fields, Enter confirms and moves forward, F2=search, F5=cash, F8=save, F9=new, F10=challan
- **All editable fields are yellow** (`#ffff99`)
- **Readonly/calculated fields are grey**
- **Cart:** add items, remove with X button, quantities and amounts auto-calculate
- **Discount:** Disc% → auto-calculates Disc Amount and Net Total
- **Cash/Balance:** user types cash paid → balance = cash − net total (green if positive/change, red if negative/still owes)
- **Save:** writes to `[Transaction]` + `trans_detail`, decrements `QTY_INHAND` in `Item_Stock`
- **View Challan button:** opens a draggable on-screen popup with formatted receipt. Has Print button inside that opens browser print dialog (user can save as PDF)
- **Transactions tab:** full transaction list with search bar at top. Search by: All Fields / Transaction ID / Customer Name / Mobile / Date / Type / User. Click any row → detail panel opens below with items + receipt side by side
- **Inventory tab:** full Item_Stock table with qty colour-coded (red ≤10, amber ≤50, green otherwise)
- **Booking tab:** full Item_Booking table with status colour-coded
- **Reports tab:** sales grouped by Month + Size with COUNT and SUM

### Calculations
```
Amount per line    = Qty × Unit Price
Gross Total        = SUM of all line amounts
Discount Amount    = Gross Total × (Disc% / 100)
Net Total          = Gross Total − Discount Amount
Balance            = Cash Paid − Net Total
```

### Stock logic
- `search_items.php` filters `QTY_INHAND > 0` — items with zero stock don't appear in POS
- On save: `QTY_INHAND = QTY_INHAND - quantity` for each item sold

---

## Screen 2: `stock_receiving.php` — Stock Receiving Form

### What it does
Records incoming stock deliveries. Based on a reference UI from a pharmacy software (MedPharma) — replicated for water products. Every incoming shipment gets an invoice, with line items per product batch.

### Layout
- **Top header row:** Invoice# (+ ⊞ popup), Inv Date, Supplier Code (+ ⊞ popup), Total Amount, Aggr. Amt, Save-Stock, Modify buttons
- **Second header row:** Discount Amt, Status, Received Date, Received By
- **Item entry row:** Brand/Item search (+ ⊞ popup), Stock No, QTY Received, Expiry Date, Batch No, Qty Available, Qty Per Item, Sales Price/Item, Purch. Price/Item, Amount, Save Line, Clear
- **Main area (2 columns using CSS grid 1fr 280px):**
  - Left: Stock Receipt Details table (sorted nearest expiry first)
  - Right: Two stacked panels
    - Top: SELECT s.S box (Invoice# + Price/Unit + Brand/ExpDate/Qty table — updates when row clicked)
    - Bottom: Stock Directory notebook (Stock# | Brand Name | Type | Location)
- **Bottom status bar:** Update Status input + button, status message, keyboard shortcuts

### Three popups (all draggable)
1. **Invoice List popup** (⊞ next to Invoice#): shows all past ST_STOCKRECEIPT records. Click row → loads that invoice into the form
2. **Supplier popup** (⊞ next to Supplier Code): shows all ST_Supplier records with search. Click row → fills Supplier Code field
3. **Stock Item popup** (⊞ next to Brand/Item Name): shows all Item_Stock records. Click row → fills all item entry fields

### Key rules your professor specified
- **Invoice Date** = date on the supplier's paper/challan (NOT today's date — entered manually)
- **Received Date** = date goods physically arrived (may differ from Invoice Date — e.g. goods came on 5th, you enter it on 9th, you type 5th)
- **Nearest expiry at top** — left detail table always sorted `ORDER BY EXPIRY_DATE ASC`
- **No dropdowns, no search** for invoice selection — plain list, click to load
- **If item not found** in Item_Stock during search → shows "Register New Item" button → opens `add_item.php` as a small popup window

### Expiry colour coding
- 🔴 Red row background: expires ≤ 30 days
- 🟡 Amber row background: expires ≤ 90 days
- White: > 90 days

### Calculations
```
Amount per line  = QTY Received × Purch. Price/Item
Total Amount     = SUM of all line amounts
Aggr. Amt        = Total Amount − Discount
Price/Unit       = Sales Price/Item ÷ Qty Per Item  (shown in SELECT s.S panel)
```

### Stock logic on Save-Stock
```
For each detail line:
    Item_Stock.QTY_INHAND += ITEMS_RECEIVED
    Item_Stock.PRICE       = new Sales Price/Item

ST_STOCKRECEIPT row inserted/updated
ST_STOCKRECEIPTDETAIL rows inserted
```

### Modify logic (prevents double-counting)
```
Step 1: Read existing detail lines → subtract their qty from Item_Stock
Step 2: DELETE old ST_STOCKRECEIPTDETAIL rows
Step 3: INSERT new detail lines → add new qty to Item_Stock
Step 4: UPDATE ST_STOCKRECEIPT header
```

### Validation before Save-Stock
- Supplier Code required
- At least one line required
- Inv Date required
- Received Date required
- Each line: Expiry Date required, Batch No required, Sales Price > 0, Qty > 0
- Duplicate Stock + Batch in same invoice is blocked

---

## Screen 3: `add_item.php` — Register New Item

Opens as a small popup window (700×500) when an item is not found during stock receiving search. Inserts a new row into `Item_Stock`. All columns from `Item_Stock` are present, grouped into three sections: Identity, Packaging, Pricing & Stock. F8 saves, Escape closes.

---

## Visual Style Guide

The entire app uses a **Windows XP/2000 classic desktop aesthetic:**

```css
/* Inset border (sunken look) */
.win-inset  { border: 1px solid; border-color: #808080 #ffffff #ffffff #808080; }

/* Raised border (button look) */
.win-raised { border: 1px solid; border-color: #ffffff #808080 #808080 #ffffff; }

/* Panel background */
body { background: #d4d0c8; }
.win-panel { background: #ece9d8; }

/* All editable inputs = yellow */
input[type=text], input[type=number], input[type=date], select {
    background: #ffff99;
}

/* Readonly fields = grey */
input[readonly] { background: #d4d0c8 !important; }

/* Table rows */
.win-table tbody tr { background: #fff; }
.win-table tbody tr:nth-child(even) { background: #f5f3ee; }
.win-table tbody tr:hover { background: #c5d5e8 !important; }
.win-table tbody tr.row-selected { background: #0a246a !important; color: white; }

/* Title bar */
.win-titlebar { background: linear-gradient(to right, #0a246a, #3a6ea5); color: white; }

/* Menu bar */
.win-menubar { background: #d4d0c8; }
.nav-active { background: #0a246a !important; color: white !important; }

/* Notebook style (right panel in stock receiving) */
background-image: repeating-linear-gradient(
    to bottom, transparent, transparent 22px, #b8d4f0 22px, #b8d4f0 23px
);
```

---

## API Pattern (used consistently across all files)

```php
<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';  // loads $conn

$sql  = "SELECT ... FROM ... WHERE ... ORDER BY ...";
$stmt = sqlsrv_query($conn, $sql, [$param1, $param2]);  // always parameterized
$rows = [];
if ($stmt) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $rows[] = $row;
    }
}
sqlsrv_close($conn);
echo json_encode($rows);
```

For INSERT with SCOPE_IDENTITY (getting the new auto-generated ID):
```php
$sql = "INSERT INTO [Table] (...) VALUES (...); SELECT SCOPE_IDENTITY() AS new_id;";
$stmt = sqlsrv_query($conn, $sql, [...]);
sqlsrv_next_result($stmt);  // move to second result set
$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
$newId = intval($row['new_id']);
```

For transactions (multiple related writes):
```php
sqlsrv_begin_transaction($conn);
// ... multiple sqlsrv_query calls ...
if ($allGood) sqlsrv_commit($conn);
else { sqlsrv_rollback($conn); echo json_encode(['error' => '...']); exit; }
```

---

## JavaScript Pattern (used consistently in all pages)

```javascript
// Fetch data from API
fetch('api/endpoint.php?param=' + encodeURIComponent(value))
    .then(r => r.json())
    .then(data => {
        // data is a JS array/object
        // loop and build HTML rows dynamically
        data.forEach(item => {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td>${item.COLUMN_NAME}</td>`;
            tr.onclick = () => handleRowClick(item);
            tbody.appendChild(tr);
        });
    });

// POST data to API
fetch('api/save.php', {
    method: 'POST',
    body: JSON.stringify(payload)
})
.then(r => r.json())
.then(res => {
    if (res.success) { /* success */ }
    else { /* show error */ }
});
```

---

## Known Issues / Things to Be Aware Of

1. **`get_stock_by_expiry.php`** — old unused file, was deleted. Replaced by `get_stock_expiry_panel.php`
2. **`[Transaction]`** — note the square brackets in all SQL. `Transaction` is a reserved word in SQL Server
3. **Tailwind via CDN** — loaded as a script tag, not compiled. Fine for this project but not production-ready
4. **`.env` path** — hardcoded as `'C:/xampp/htdocs/Margalla3M-SellH2O/.env'` in `includes/db.php`. If the project moves, this needs updating
5. **Stock receiving page** — `search_items.php` (used by POS) filters `QTY_INHAND > 0`. The stock receiving page uses `get_all_stock_items.php` which has no qty filter — this is intentional because you need to receive stock into items that may currently have zero
6. **Keyboard shortcuts on POS:** F2=Search, F5=Cash, F8=Save, F9=New, F10=Challan, Enter moves to next field, Arrow keys navigate product list and fields

---

## What's NOT built yet

- Share management module (Share_Inventory, Share_Calculation, ShareHolder_Investment tables exist in DB but no UI screens)
- User authentication / login screen
- Admin panel for managing users
- Proper responsive design (intentionally not built — this is a fixed desktop system)

---

## How to run

1. Start XAMPP (Apache must be running; SQL Server runs separately as a Windows service)
2. Make sure SQL Server is running and `AISellH2O` database exists
3. Make sure `.env` has correct credentials
4. Open browser: `http://localhost/Margalla3M-SellH2O/public/pos.php`
5. Navigate using the menu bar at the top

---

## Reference UIs

The project replicates two reference screens from a pharmacy software called **MedPharma** (built for medicines), adapted for water products:

1. **POS screen** — billing/cashier screen with item search, cart, totals, receipt popup
2. **Stock Receiving screen** — invoice-based stock intake form with supplier selection, batch tracking, expiry date management

Key adaptations:
- "Medicine" → "Water Product / Item"
- "Tablet Price" → "Price/Unit"
- "Number of tablets in box" → "Units Per Item (e.g. 24 bottles per carton)"
- "Dosage Description" column excluded (not in ERD)
- Expiry date logic kept and emphasized (nearest expiry at top in all stock views)
