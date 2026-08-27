# AISellProduct — Margalla3M-SellH2O

A POS, stock-management, and business-intelligence system built for **Margalla 3M Industries** (a bottled water/juice manufacturer in Islamabad), as a FAST-NUCES internship project ("AI SellProd Development"). Built by a 5-person team — Sara Nasir, Zeeshan Azeem, Rafia Ali, Anoosha Ahsan, Muhammad Qasim — each contributing screens that are integrated into this one codebase.

## Tech stack

| Layer | Technology |
|---|---|
| Frontend | HTML + vanilla JavaScript + Tailwind CSS (via CDN) |
| Backend | PHP, procedural style |
| Database | Microsoft SQL Server |
| PHP DB driver | `sqlsrv_*` (this project's own screens); a couple of integrated teammate screens use PDO — both point at the same real server |
| Local server | XAMPP (Apache) |

No frameworks, no build step, no Node/npm required to run it — just XAMPP + SQL Server.

## Getting it running on your own machine

This is the **only thing that needs to change** to run the app on a different PC — everything else (every screen, including every teammate's integrated screens) reads its database connection from this one file.

1. **Clone this repo directly into your XAMPP `htdocs` folder**, so the path is:
   ```
   C:\xampp\htdocs\Margalla3M-SellH2O\
   ```
2. **Copy `.env.example` to `.env`** — same folder, i.e. the project root (`Margalla3M-SellH2O\.env`, *not* inside `public\`).
3. **Edit `.env`** and fill in your own SQL Server details:
   ```
   DB_SERVER=YOUR_PC_NAME\SQLEXPRESS
   DB_NAME_WATER=MargallaProd
   DB_NAME_MEDSTOCK=MargallaTesting
   DB_USER=
   DB_PASSWORD=
   ```
   - `DB_SERVER` — your own SQL Server instance name. Usually `YOUR-PC-NAME\SQLEXPRESS`. Find your PC's name by running `hostname` in a terminal.
   - `DB_NAME_WATER` / `DB_NAME_MEDSTOCK` — leave these exactly as shown; they're the two real database names this app expects (Water Distribution data, and a Med Stock/pharmacy test dataset).
   - `DB_USER` / `DB_PASSWORD` — leave both **blank** if your SQL Server uses Windows Authentication (the default for a local dev setup). Don't write anything else in their place.
4. **Get the actual database onto your machine.** The `.env` file only tells the app *where* to connect — it ships no data. You need `MargallaProd` (and `MargallaTesting` if you want the Med Stock test side) actually restored on your own SQL Server first, via a `.bak` backup or the schema+seed scripts. Ask Sara for the current backup — the schema has grown a lot since `database/AISellH2O.sql` in this repo, which is only the very first, long-superseded version and won't have most of what's actually in production (any of the newer columns/tables the team's screens rely on).
5. **Start Apache** in XAMPP.
6. Visit **`http://localhost/Margalla3M-SellH2O/public/login.php`**.

You should not need to edit a single line of PHP anywhere in the project — not in `public/`, not in any teammate's own subfolder — to get it running. If something still doesn't connect after filling in `.env` correctly and restoring the database, it's almost certainly that SQL Server isn't accepting the connection (wrong instance name, or SQL Server not configured for the auth mode you're using) — check that before assuming the code is broken.

## Logging in

Pick a database (Water Distribution or Med Stock Testing), then log in as an employee. Test login:

- **User ID:** `ejaz` **Password:** `12345` (Administrator — full access, including Manage Users)

A few other seed accounts (`qasim`, `zeeshan`, `anusha`, `sara`, `rafia`) also use `12345`, all under the `Management` role by default. Roles (`Admin` / `Management` / `Cashier` / `Inventory` / `Booking`) control which screens are visible — see `public/includes/access.php`.

## Project structure

```
public/
├── login.php, user_login.php, logout.php     ← auth
├── pos.php                                    ← main Sale/Booking/Reports screen
├── stock_receiving.php, stock_search.php, manufacture.php, ...
├── sale_reports.php, sale_items.php
├── admin_users.php, admin_dashboard.php, item_details.php
├── reports/                                   ← Profit Reports (By Product/Region/Customer)
├── includes/                                  ← db.php (real DB connection), access.php (role gating)
├── api/                                       ← this project's own backend endpoints
├── anoosha/     ← Anoosha's integrated screens (Purchase Report, Short Items, Search Items)
├── zeeshan/     ← Zeeshan's integrated screens (Group Wise Stock Search, Dead Items, Manage Users)
├── qasim/       ← Qasim's integrated screens (Purchase Order, Sales Report)
└── rafia/       ← Rafia's integrated screens (Narcotics Register, Stock In Hand, Purchase & Return Summary)
```

Every screen — this project's own and every teammate's integrated one — shares the same visual style (a Windows XP desktop look) and the same real database connection and login/role system.

## Known gaps

A running list of unfixed bugs and honest data/feature gaps, by whose screen they're in, is not part of this repo but has been shared with the team separately — ask Sara if you need it.
