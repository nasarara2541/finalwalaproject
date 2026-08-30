<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'includes/auth_guard.php';
require_once 'config/db.php';

$type = $_GET['type'] ?? '';
$validTypes = ['daywise', 'summary', 'groupwise', 'cancelled', 'unposted'];
if (!in_array($type, $validTypes)) { die('Invalid report type.'); }

$from       = $_GET['from'] ?? date('Y-m-d');
$to         = $_GET['to'] ?? date('Y-m-d');
$supplier   = trim($_GET['supplier'] ?? '');
$company    = trim($_GET['company'] ?? '');
$invoiceNo  = trim($_GET['invoice'] ?? '');
$transRid   = trim($_GET['transrid'] ?? '');
$itemName   = trim($_GET['item'] ?? '');
$barCode    = trim($_GET['barcode'] ?? '');
$groupName  = trim($_GET['group'] ?? '');

// Header-level filters shared by every report that queries ST_STOCKRECEIPT directly
// (daywise, summary, cancelled, unposted). Group-wise builds its own separately below.
$headerWhere  = ["r.INVOICE_DATE >= ?", "r.INVOICE_DATE < DATEADD(DAY, 1, ?)"];
$headerParams = [$from, $to];

if ($supplier !== '') { $headerWhere[] = "s.SUPPLIER_NAME LIKE ?"; $headerParams[] = "%$supplier%"; }
if ($company  !== '') { $headerWhere[] = "s.SUPPLIER_NAME LIKE ?"; $headerParams[] = "%$company%"; }
if ($invoiceNo !== '') { $headerWhere[] = "r.Invoice_no = ?"; $headerParams[] = $invoiceNo; }
if ($transRid !== '') { $headerWhere[] = "r.Trans_no = ?"; $headerParams[] = $transRid; }
if ($itemName !== '' || $barCode !== '' || $groupName !== '') {
    $sub = "EXISTS (SELECT 1 FROM ST_STOCKRECEIPTDETAIL d JOIN Item_Stock i ON i.STOCK_NUMBER = d.STOCK_NUMBER WHERE d.Invoice_no = r.Invoice_no";
    if ($itemName !== '')  { $sub .= " AND i.ITEM_NAME LIKE ?"; $headerParams[] = "%$itemName%"; }
    if ($barCode !== '')   { $sub .= " AND i.BARCODE = ?"; $headerParams[] = $barCode; }
    if ($groupName !== '') { $sub .= " AND d.GROUP_NAME LIKE ?"; $headerParams[] = "%$groupName%"; }
    $sub .= ")";
    $headerWhere[] = $sub;
}
$headerWhereSql = implode(' AND ', $headerWhere);

$title = '';
$rows = [];
$columns = [];
$totalsRow = null;

switch ($type) {

    case 'daywise':
        $title = 'Day/s Wise Purchase';
        $sql = "SELECT CAST(r.INVOICE_DATE AS DATE) AS PurchaseDate,
                       COUNT(*) AS InvoiceCount,
                       SUM(r.TOTAL_ITEMS) AS TotalItems,
                       SUM(r.TOTAL_UNITS) AS TotalUnits,
                       SUM(r.TOTAL_BONUS) AS TotalBonus,
                       SUM(r.TOTAL_AMOUNT) AS TotalAmount
                FROM ST_STOCKRECEIPT r
                LEFT JOIN ST_Supplier s ON s.SUPPLIER_CODE = r.SUPPLIER_CODE
                WHERE r.STATUS = 'Y' AND $headerWhereSql
                GROUP BY CAST(r.INVOICE_DATE AS DATE)
                ORDER BY PurchaseDate ASC";
        $stmt = sqlsrv_query($conn, $sql, $headerParams);
        $columns = ['Date', 'Invoices', 'Items', 'Units', 'Bonus', 'Amount'];
        $grandInv = 0; $grandItems = 0; $grandUnits = 0; $grandBonus = 0; $grandAmt = 0;
        if ($stmt) {
            while ($r = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $rows[] = [
                    $r['PurchaseDate'] instanceof DateTime ? $r['PurchaseDate']->format('d M Y') : $r['PurchaseDate'],
                    $r['InvoiceCount'], $r['TotalItems'], $r['TotalUnits'], $r['TotalBonus'],
                    number_format((float)$r['TotalAmount'], 2)
                ];
                $grandInv += $r['InvoiceCount']; $grandItems += $r['TotalItems'];
                $grandUnits += $r['TotalUnits']; $grandBonus += $r['TotalBonus']; $grandAmt += $r['TotalAmount'];
            }
        }
        $totalsRow = ['TOTAL', $grandInv, $grandItems, $grandUnits, $grandBonus, number_format($grandAmt, 2)];
        break;

    case 'summary':
        $title = 'Purchase Summary';
        $sql = "SELECT FORMAT(r.INVOICE_DATE, 'yyyy-MM') AS PurchaseMonth,
                       COUNT(*) AS InvoiceCount,
                       SUM(r.TOTAL_ITEMS) AS TotalItems,
                       SUM(r.TOTAL_UNITS) AS TotalUnits,
                       SUM(r.TOTAL_BONUS) AS TotalBonus,
                       SUM(r.TOTAL_AMOUNT) AS TotalAmount
                FROM ST_STOCKRECEIPT r
                LEFT JOIN ST_Supplier s ON s.SUPPLIER_CODE = r.SUPPLIER_CODE
                WHERE r.STATUS = 'Y' AND $headerWhereSql
                GROUP BY FORMAT(r.INVOICE_DATE, 'yyyy-MM')
                ORDER BY PurchaseMonth ASC";
        $stmt = sqlsrv_query($conn, $sql, $headerParams);
        $columns = ['Month', 'Invoices', 'Total Items', 'Total Units', 'Total Bonus', 'Total Amount Spent'];
        $grandInv = 0; $grandItems = 0; $grandUnits = 0; $grandBonus = 0; $grandAmt = 0;
        if ($stmt) {
            while ($r = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $rows[] = [
                    date('F Y', strtotime($r['PurchaseMonth'] . '-01')),
                    $r['InvoiceCount'], $r['TotalItems'], $r['TotalUnits'], $r['TotalBonus'],
                    number_format((float)$r['TotalAmount'], 2)
                ];
                $grandInv += $r['InvoiceCount']; $grandItems += $r['TotalItems'];
                $grandUnits += $r['TotalUnits']; $grandBonus += $r['TotalBonus']; $grandAmt += $r['TotalAmount'];
            }
        }
        $totalsRow = ['GRAND TOTAL', $grandInv, $grandItems, $grandUnits, $grandBonus, number_format($grandAmt, 2)];
        break;

    case 'groupwise':
        $title = 'Group Wise Purchase';
        $detailWhere  = ["r.STATUS = 'Y'", "r.INVOICE_DATE >= ?", "r.INVOICE_DATE < DATEADD(DAY, 1, ?)"];
        $detailParams = [$from, $to];
        if ($supplier !== '') { $detailWhere[] = "s.SUPPLIER_NAME LIKE ?"; $detailParams[] = "%$supplier%"; }
        if ($company  !== '') { $detailWhere[] = "s.SUPPLIER_NAME LIKE ?"; $detailParams[] = "%$company%"; }
        if ($invoiceNo !== '') { $detailWhere[] = "r.Invoice_no = ?"; $detailParams[] = $invoiceNo; }
        if ($transRid !== '') { $detailWhere[] = "r.Trans_no = ?"; $detailParams[] = $transRid; }
        if ($itemName !== '') { $detailWhere[] = "i.ITEM_NAME LIKE ?"; $detailParams[] = "%$itemName%"; }
        if ($barCode !== '')  { $detailWhere[] = "i.BARCODE = ?"; $detailParams[] = $barCode; }
        if ($groupName !== '') { $detailWhere[] = "d.GROUP_NAME LIKE ?"; $detailParams[] = "%$groupName%"; }
        $detailWhereSql = implode(' AND ', $detailWhere);

        $sql = "SELECT ISNULL(NULLIF(LTRIM(RTRIM(d.GROUP_NAME)), ''), '(No Group)') AS GroupName,
                       SUM(d.ITEMS_RECEIVED) AS TotalQty,
                       SUM(d.BONUS_QTY) AS TotalBonus,
                       SUM(d.ITEMS_RECEIVED * d.PPRICE_PERITEM) AS TotalAmount
                FROM ST_STOCKRECEIPTDETAIL d
                JOIN ST_STOCKRECEIPT r ON r.Invoice_no = d.Invoice_no
                JOIN Item_Stock i ON i.STOCK_NUMBER = d.STOCK_NUMBER
                LEFT JOIN ST_Supplier s ON s.SUPPLIER_CODE = r.SUPPLIER_CODE
                WHERE $detailWhereSql
                GROUP BY d.GROUP_NAME
                ORDER BY TotalAmount DESC";
        $stmt = sqlsrv_query($conn, $sql, $detailParams);
        $columns = ['Group', 'Total Qty', 'Total Bonus', 'Total Amount'];
        $grandQty = 0; $grandBonus = 0; $grandAmt = 0;
        if ($stmt) {
            while ($r = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $rows[] = [$r['GroupName'], $r['TotalQty'], $r['TotalBonus'], number_format((float)$r['TotalAmount'], 2)];
                $grandQty += $r['TotalQty']; $grandBonus += $r['TotalBonus']; $grandAmt += $r['TotalAmount'];
            }
        }
        $totalsRow = ['TOTAL', $grandQty, $grandBonus, number_format($grandAmt, 2)];
        break;

    case 'cancelled':
        $title = 'Cancelled Invoice(s)';
        $sql = "SELECT r.Invoice_no, r.INVOICE_DATE, s.SUPPLIER_NAME, r.TOTAL_ITEMS, r.TOTAL_UNITS, r.TOTAL_AMOUNT
                FROM ST_STOCKRECEIPT r
                LEFT JOIN ST_Supplier s ON s.SUPPLIER_CODE = r.SUPPLIER_CODE
                WHERE r.STATUS = 'N' AND $headerWhereSql
                ORDER BY r.INVOICE_DATE DESC";
        $stmt = sqlsrv_query($conn, $sql, $headerParams);
        $columns = ['Invoice #', 'Date', 'Supplier', 'Items', 'Units', 'Amount'];
        if ($stmt) {
            while ($r = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $rows[] = [
                    $r['Invoice_no'],
                    $r['INVOICE_DATE'] instanceof DateTime ? $r['INVOICE_DATE']->format('d M Y') : $r['INVOICE_DATE'],
                    $r['SUPPLIER_NAME'] ?? '-', $r['TOTAL_ITEMS'], $r['TOTAL_UNITS'],
                    number_format((float)$r['TOTAL_AMOUNT'], 2)
                ];
            }
        }
        break;

    case 'unposted':
        $title = 'Un-Posted Invoice(s)';
        $sql = "SELECT r.Invoice_no, r.INVOICE_DATE, s.SUPPLIER_NAME, r.RECEIVED_BY, r.TOTAL_AMOUNT
                FROM ST_STOCKRECEIPT r
                LEFT JOIN ST_Supplier s ON s.SUPPLIER_CODE = r.SUPPLIER_CODE
                WHERE r.Posted_On IS NULL AND $headerWhereSql
                ORDER BY r.INVOICE_DATE DESC";
        $stmt = sqlsrv_query($conn, $sql, $headerParams);
        $columns = ['Invoice #', 'Date', 'Supplier', 'Received By', 'Amount'];
        if ($stmt) {
            while ($r = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $rows[] = [
                    $r['Invoice_no'],
                    $r['INVOICE_DATE'] instanceof DateTime ? $r['INVOICE_DATE']->format('d M Y') : $r['INVOICE_DATE'],
                    $r['SUPPLIER_NAME'] ?? '-', $r['RECEIVED_BY'] ?? '-',
                    number_format((float)$r['TOTAL_AMOUNT'], 2)
                ];
            }
        }
        break;
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title><?php echo htmlspecialchars($title); ?></title>
<style>
    body { font-family: Tahoma, Arial, sans-serif; font-size: 13px; color: #000; margin: 20px; }
    h2 { margin: 0 0 2px 0; color: #aa0000; }
    .sub { color: #555; font-size: 12px; margin-bottom: 14px; }
    table { width: 100%; border-collapse: collapse; margin-top: 8px; }
    th, td { border: 1px solid #999; padding: 6px 8px; font-size: 12px; text-align: left; }
    th { background: #d4d0c8; }
    td.num, th.num { text-align: right; }
    tfoot td { font-weight: bold; background: #f0f0f0; }
    .empty { padding: 20px; text-align: center; color: #888; }
    @media print {
        button { display: none; }
    }
</style>
</head>
<body>
    <h2><?php echo htmlspecialchars($_SESSION['company_name'] ?? ''); ?> - <?php echo htmlspecialchars($title); ?></h2>
    <div class="sub">
        Period: <?php echo htmlspecialchars($from); ?> to <?php echo htmlspecialchars($to); ?>
        <?php if ($supplier) echo ' | Supplier: ' . htmlspecialchars($supplier); ?>
        <?php if ($itemName) echo ' | Item: ' . htmlspecialchars($itemName); ?>
        &nbsp; | Generated: <?php echo date('d M Y, H:i'); ?>
    </div>

    <?php if (empty($rows)): ?>
        <div class="empty">No records found for the selected filters.</div>
    <?php else: ?>
    <table>
        <thead>
            <tr><?php foreach ($columns as $i => $c) echo '<th' . ($i > 0 ? ' class="num"' : '') . '>' . htmlspecialchars($c) . '</th>'; ?></tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
            <tr><?php foreach ($row as $i => $val) echo '<td' . ($i > 0 ? ' class="num"' : '') . '>' . htmlspecialchars((string)$val) . '</td>'; ?></tr>
            <?php endforeach; ?>
        </tbody>
        <?php if ($totalsRow): ?>
        <tfoot>
            <tr><?php foreach ($totalsRow as $i => $val) echo '<td' . ($i > 0 ? ' class="num"' : '') . '>' . htmlspecialchars((string)$val) . '</td>'; ?></tr>
        </tfoot>
        <?php endif; ?>
    </table>
    <?php endif; ?>

<script>
window.onload = function() { window.print(); };
</script>
</body>
</html>