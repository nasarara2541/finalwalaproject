<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

$itemCode = trim($_GET['item_code'] ?? '');
$itemName = trim($_GET['item_name'] ?? '');
$type     = trim($_GET['type'] ?? '');
$company  = trim($_GET['company'] ?? '');
$manuBy   = trim($_GET['manufacture_by'] ?? '');
$location = trim($_GET['location'] ?? '');
$generic  = trim($_GET['generic'] ?? '');
$view     = $_GET['view'] ?? 'all';

$where  = [];
$params = [];

if ($itemCode !== '') { $where[] = "CAST(s.STOCK_NUMBER AS VARCHAR(20)) LIKE ?"; $params[] = '%'.$itemCode.'%'; }
if ($itemName !== '') { $where[] = "(s.ITEM_NAME LIKE ? OR s.BRAND_NAME LIKE ?)"; $params[] = '%'.$itemName.'%'; $params[] = '%'.$itemName.'%'; }
if ($type     !== '') { $where[] = "s.ITEM_TYPE LIKE ?"; $params[] = '%'.$type.'%'; }
if ($company  !== '') { $where[] = "sup.SUPPLIER_NAME LIKE ?"; $params[] = '%'.$company.'%'; }
if ($manuBy   !== '') { $where[] = "m.M_Name LIKE ?"; $params[] = '%'.$manuBy.'%'; }
if ($location !== '') { $where[] = "s.LOCATION LIKE ?"; $params[] = '%'.$location.'%'; }
if ($generic  !== '') { $where[] = "s.ITEM_NAME LIKE ?"; $params[] = '%'.$generic.'%'; }

// The 5 bottom filter buttons on the screen — mutually exclusive, "all" (All
// Record) applies no extra condition. "Inactive" also catches NULL since a
// never-set status shouldn't silently count as Active. "Bonus" is a
// semi-join against stock-receipt history since BONUS_QTY only ever existed
// per receipt line, never as an item-level flag -- an item counts as
// "Bonus QTY" if it has ever actually received bonus stock. "Narcotics"
// naturally returns nothing on real water items (none have the flag set) —
// same honest-empty-result pattern as Disc/Bonus, not disabled, since the
// user confirmed this should just be a normal filter like the others.
if ($view === 'inactive') {
    $where[] = "(s.AVAILABLE_STATUS <> 'Active' OR s.AVAILABLE_STATUS IS NULL)";
} elseif ($view === 'disc') {
    $where[] = "s.DISC_STATUS = '1'";
} elseif ($view === 'bonus') {
    $where[] = "EXISTS (SELECT 1 FROM ST_STOCKRECEIPTDETAIL d WHERE d.stock_number = s.STOCK_NUMBER AND d.BONUS_QTY > 0)";
} elseif ($view === 'narcotics') {
    $where[] = "s.NARCOTICS_STATUS = '1'";
}

$sql = "SELECT TOP 1000
            s.STOCK_NUMBER,
            s.BARCODE,
            s.BRAND_NAME,
            s.ITEM_NAME,
            s.ITEM_TYPE,
            sup.SUPPLIER_NAME AS COMPANY_NAME,
            m.M_Name           AS MANUFACTURE_NAME,
            s.LOCATION,
            s.UNITS_PERITEM,
            s.SAFETY_LEVEL,
            s.AVAILABLE_STATUS,
            s.DISC_STATUS,
            s.SALE_DISCOUNT,
            s.NARCOTICS_STATUS
        FROM Item_Stock s
        LEFT JOIN ST_Supplier sup ON sup.SUPPLIER_CODE  = s.SUPPLIER_CODE
        LEFT JOIN Manufacture m   ON m.Manufacture_no   = s.MANUFACTURE_NO";

if ($where) { $sql .= " WHERE " . implode(' AND ', $where); }
$sql .= " ORDER BY s.ITEM_NAME";

$stmt = sqlsrv_query($conn, $sql, $params);
$rows = [];
if ($stmt) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $rows[] = $row;
    }
} else {
    http_response_code(500);
    echo json_encode(['error' => sqlsrv_errors()[0]['message'] ?? 'Query failed']);
    exit;
}
sqlsrv_close($conn);
echo json_encode($rows);
