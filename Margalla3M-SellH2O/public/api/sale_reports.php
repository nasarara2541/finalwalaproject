<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

// This endpoint only implements the reports/filters that have a real,
// confirmed mapping to actual columns (see sale_reports.php for the full
// list of fields still waiting on business-logic decisions -- those are
// disabled on the screen and never reach this file). Reports:
//   total_sale, users_bill_count, day_wise_sale -> Bills-panel filters
//   all_sold_items, item_group_summary          -> Items-panel filters
// All also honor the shared From/To date range and (if set) Time From/To.
//
// Rows are returned as plain arrays of already-formatted display strings
// (not raw DB values) so the frontend never has to guess whether a given
// column is a count vs a money amount -- money is always "X.XX", counts are
// always plain integers, matching the sqlsrv string-vs-int quirk noted
// elsewhere in this app rather than fighting it client-side.

$report      = $_GET['report'] ?? '';
$from        = trim($_GET['from'] ?? '');
$to          = trim($_GET['to'] ?? '');
$timeFrom    = trim($_GET['time_from'] ?? '');
$timeTo      = trim($_GET['time_to'] ?? '');
$saleType    = trim($_GET['sale_type'] ?? '');
$createdBy   = trim($_GET['created_by'] ?? '');
$customer    = trim($_GET['customer'] ?? '');
$address     = trim($_GET['address'] ?? '');
$ref         = trim($_GET['ref'] ?? '');
$supplier    = trim($_GET['supplier'] ?? '');
$itemCode    = trim($_GET['item_code'] ?? '');
$itemName    = trim($_GET['item_name'] ?? '');
$itemType    = trim($_GET['item_type'] ?? '');
$manufacture = trim($_GET['manufacture'] ?? '');
$company     = trim($_GET['company'] ?? '');
$batch       = trim($_GET['batch'] ?? '');

function addDateRange($col, $from, $to, &$where, &$params) {
    if ($from !== '') { $where[] = "$col >= ?"; $params[] = $from . ' 00:00:00'; }
    if ($to   !== '') { $where[] = "$col <= ?"; $params[] = $to   . ' 23:59:59'; }
}

// Trans_date already stores a full datetime -- GETDATE() has always written
// real time-of-day for every sale made through the live app (verified: the
// only reason every existing row shows 00:00:00 is that the historical bulk
// load only ever had date-level data, not a schema limitation). No new
// column needed for this filter, it just never had UI wired to it before.
function addTimeRange($col, $timeFrom, $timeTo, &$where, &$params) {
    if ($timeFrom !== '') { $where[] = "CAST($col AS TIME) >= ?"; $params[] = $timeFrom; }
    if ($timeTo   !== '') { $where[] = "CAST($col AS TIME) <= ?"; $params[] = $timeTo; }
}

function runQuery($conn, $sql, $params) {
    $stmt = sqlsrv_query($conn, $sql, $params);
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['error' => sqlsrv_errors()[0]['message'] ?? 'Query failed']);
        exit;
    }
    return $stmt;
}

function money($v) { return number_format((float)($v ?? 0), 2, '.', ''); }

if ($report === 'total_sale' || $report === 'users_bill_count' || $report === 'day_wise_sale') {
    $where = []; $params = [];
    addDateRange('t.Trans_date', $from, $to, $where, $params);
    addTimeRange('t.Trans_date', $timeFrom, $timeTo, $where, $params);
    if ($saleType  !== '') { $where[] = "t.Trans_type = ?";        $params[] = $saleType; }
    if ($createdBy !== '') { $where[] = "t.User_id LIKE ?";        $params[] = '%'.$createdBy.'%'; }
    if ($customer  !== '') { $where[] = "t.Cust_name LIKE ?";      $params[] = '%'.$customer.'%'; }
    if ($ref       !== '') { $where[] = "t.Invoice_reference LIKE ?"; $params[] = '%'.$ref.'%'; }

    $join = '';
    if ($address !== '') {
        $join = "LEFT JOIN Customer c ON c.Customer_id = t.Customer_id";
        $where[] = "c.Address LIKE ?"; $params[] = '%'.$address.'%';
    }

    // Supplier isn't tracked on the bill itself, but it doesn't need to be
    // -- every line item already knows its supplier via Item_Stock.
    // SUPPLIER_CODE (same join Stock Search/Company already use). A bill
    // matches if ANY of its items came from that supplier. Currently no
    // real item has SUPPLIER_CODE set yet, so this will return nothing
    // until items actually get a supplier assigned.
    if ($supplier !== '') {
        $where[] = "EXISTS (
            SELECT 1 FROM trans_detail d2
            JOIN Item_Stock s2   ON s2.STOCK_NUMBER    = d2.stock_number
            JOIN ST_Supplier sup2 ON sup2.SUPPLIER_CODE = s2.SUPPLIER_CODE
            WHERE d2.Trans_no = t.Trans_no AND sup2.SUPPLIER_NAME LIKE ?
        )";
        $params[] = '%'.$supplier.'%';
    }

    // [Transaction].Trans_amount is NULL on every one of the 1,060 real rows
    // (confirmed live) -- same historical-bulk-load gap documented for
    // trans_detail.PPrice_amount. The real net-sale figure has to come from
    // SUM(trans_detail.amount), same as admin_dashboard.php already does.
    // Pre-aggregated as a subquery and LEFT JOINed so it contributes one row
    // per transaction, not one row per line item (which would otherwise
    // multiply Gross_amount/COUNT(*) by however many items were on the bill).
    $detailJoin = "LEFT JOIN (SELECT Trans_no, SUM(amount) AS line_total FROM trans_detail GROUP BY Trans_no) dt ON dt.Trans_no = t.Trans_no";

    if ($report === 'total_sale') {
        $sql = "SELECT COUNT(*) AS bill_count, ISNULL(SUM(dt.line_total),0) AS total_sale, ISNULL(SUM(t.Gross_amount),0) AS total_gross
                FROM [Transaction] t $detailJoin $join";
        if ($where) $sql .= " WHERE " . implode(' AND ', $where);
        $stmt = runQuery($conn, $sql, $params);
        $r    = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        echo json_encode(['title' => 'Total Sale', 'columns' => ['Bills','Total Sale','Total Gross'],
            'rows' => [[ (int)$r['bill_count'], money($r['total_sale']), money($r['total_gross']) ]]]);
        exit;
    }

    if ($report === 'day_wise_sale') {
        // "Return" half of the button's name isn't built -- no returns/
        // refunds concept exists anywhere in this app yet (you said that's
        // coming as its own screen). This is the Sale half only, which
        // needed no new logic to build.
        $sql = "SELECT CAST(t.Trans_date AS DATE) AS sale_date_raw, CONVERT(VARCHAR(10), t.Trans_date, 103) AS sale_date,
                        COUNT(*) AS bill_count, ISNULL(SUM(dt.line_total),0) AS total_sale
                FROM [Transaction] t $detailJoin $join";
        if ($where) $sql .= " WHERE " . implode(' AND ', $where);
        $sql .= " GROUP BY CAST(t.Trans_date AS DATE), CONVERT(VARCHAR(10), t.Trans_date, 103) ORDER BY sale_date_raw DESC";
        $stmt = runQuery($conn, $sql, $params);
        $rows = [];
        while ($r = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $rows[] = [ $r['sale_date'], (int)$r['bill_count'], money($r['total_sale']) ];
        }
        echo json_encode(['title' => 'Day Wise Sale (Return pending future Returns screen)', 'columns' => ['Date','Bills','Total Sale'], 'rows' => $rows]);
        exit;
    }

    // users_bill_count — User_id is also NULL on every real [Transaction]
    // row today (bulk-loaded historical data never recorded who created each
    // bill), so this will honestly show a single "—" row with the full
    // count until bills start being created by the live app going forward.
    $sql = "SELECT t.User_id, COUNT(*) AS bill_count, ISNULL(SUM(dt.line_total),0) AS total_sale
            FROM [Transaction] t $detailJoin $join";
    if ($where) $sql .= " WHERE " . implode(' AND ', $where);
    $sql .= " GROUP BY t.User_id ORDER BY bill_count DESC";
    $stmt = runQuery($conn, $sql, $params);
    $rows = [];
    while ($r = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $rows[] = [ $r['User_id'] ?? '—', (int)$r['bill_count'], money($r['total_sale']) ];
    }
    echo json_encode(['title' => "Users Bill(s) Count", 'columns' => ['User','Bills','Total Sale'], 'rows' => $rows]);
    exit;
}

if ($report === 'all_sold_items' || $report === 'item_group_summary') {
    $where = []; $params = [];
    addDateRange('t.Trans_date', $from, $to, $where, $params);
    addTimeRange('t.Trans_date', $timeFrom, $timeTo, $where, $params);
    if ($customer    !== '') { $where[] = "t.Cust_name LIKE ?"; $params[] = '%'.$customer.'%'; }
    if ($createdBy   !== '') { $where[] = "t.User_id LIKE ?";   $params[] = '%'.$createdBy.'%'; }
    if ($itemCode    !== '') { $where[] = "CAST(s.STOCK_NUMBER AS VARCHAR(20)) LIKE ?"; $params[] = '%'.$itemCode.'%'; }
    if ($itemName    !== '') { $where[] = "(s.ITEM_NAME LIKE ? OR s.BRAND_NAME LIKE ?)"; $params[] = '%'.$itemName.'%'; $params[] = '%'.$itemName.'%'; }
    if ($itemType    !== '') { $where[] = "s.ITEM_TYPE LIKE ?"; $params[] = '%'.$itemType.'%'; }
    if ($manufacture !== '') { $where[] = "m.M_Name LIKE ?";    $params[] = '%'.$manufacture.'%'; }
    if ($company     !== '') { $where[] = "sup.SUPPLIER_NAME LIKE ?"; $params[] = '%'.$company.'%'; }
    // trans_detail.Invoice_No now gets written at sale time (see
    // save_transaction.php) linking a sold line to the specific stock-
    // receipt batch FEFO drew it from -- only true for sales made from now
    // on, every pre-existing historical line still has Invoice_No = NULL.
    if ($batch       !== '') { $where[] = "bd.BATCH_NO LIKE ?"; $params[] = '%'.$batch.'%'; }

    $joins = "JOIN [Transaction] t         ON t.Trans_no       = d.Trans_no
              JOIN Item_Stock s            ON s.STOCK_NUMBER   = d.stock_number
              LEFT JOIN Manufacture m      ON m.Manufacture_no = s.MANUFACTURE_NO
              LEFT JOIN ST_Supplier sup    ON sup.SUPPLIER_CODE = s.SUPPLIER_CODE
              LEFT JOIN ST_STOCKRECEIPTDETAIL bd ON bd.Invoice_no = d.Invoice_No AND bd.STOCK_NUMBER = d.stock_number";

    if ($report === 'all_sold_items') {
        $sql = "SELECT t.Trans_no, CONVERT(VARCHAR(20), t.Trans_date, 103) AS Trans_date,
                        s.STOCK_NUMBER, s.BRAND_NAME, s.ITEM_NAME, d.quantity, d.Price_PerItem, d.amount, bd.BATCH_NO
                 FROM trans_detail d $joins";
        if ($where) $sql .= " WHERE " . implode(' AND ', $where);
        $sql .= " ORDER BY t.Trans_date DESC";
        $stmt = runQuery($conn, $sql, $params);
        $rows = [];
        while ($r = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $rows[] = [
                $r['Trans_no'], $r['Trans_date'], $r['STOCK_NUMBER'],
                $r['BRAND_NAME'] ?? '—', $r['ITEM_NAME'] ?? '—',
                (int)$r['quantity'], money($r['Price_PerItem']), money($r['amount']),
                $r['BATCH_NO'] ?? '—'
            ];
        }
        echo json_encode(['title' => 'All Sold Items Detail', 'columns' => ['Bill#','Date','Stock#','Brand','Item','Qty','Price','Amount','Batch#'], 'rows' => $rows]);
        exit;
    }

    // item_group_summary — "Group" has no real column anywhere in the
    // schema (confirmed live) and is a constant for the whole active
    // database (Water or Medicine, same convention as stock_search.php), so
    // this always returns exactly one row rather than a real breakdown.
    $groupLabel = (($_SESSION['active_db_label'] ?? '') === 'Water Distribution') ? 'Water' : 'Medicine';
    $sql = "SELECT COUNT(*) AS line_count, ISNULL(SUM(d.quantity),0) AS total_qty, ISNULL(SUM(d.amount),0) AS total_sale
            FROM trans_detail d $joins";
    if ($where) $sql .= " WHERE " . implode(' AND ', $where);
    $stmt = runQuery($conn, $sql, $params);
    $r    = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    echo json_encode(['title' => 'Item Group Summary', 'columns' => ['Group','Lines','Total Qty','Total Sale'],
        'rows' => [[ $groupLabel, (int)$r['line_count'], (int)$r['total_qty'], money($r['total_sale']) ]]]);
    exit;
}

sqlsrv_close($conn);
http_response_code(400);
echo json_encode(['error' => 'Unknown report']);
