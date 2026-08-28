<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/access.php';
require_once __DIR__ . '/../includes/db.php';

// AI Insights: a simple, fully-explainable demand forecast, not a black box.
// For every product with recent sales, we take its last 3 months of real
// packs-sold and compute a weighted moving average (most recent month
// weighted heaviest) as next month's forecast. That gets compared against
// what's actually on the shelf (Item_Stock.QTY_INHAND) to flag what needs
// reordering soon, what's overstocked, and what's trending up or down.
// No external AI service, no invented numbers -- every figure here is
// computed directly from real sale/stock rows, so it can never "hallucinate".

if (!canAccess('admin_area')) {
    http_response_code(403);
    echo json_encode(['error' => 'Admin/Management access required']);
    exit;
}

// Step 1: which 3 real calendar months (most recent first) actually have
// sales -- cheap, one row per month, same technique as Profit Reports' month
// list, so this never depends on "today" lining up with real data (the real
// data here tops out in 2024, not the current date).
$monthSql = "SELECT DISTINCT TOP 3 YEAR(Trans_date) AS Yr, MONTH(Trans_date) AS Mo
             FROM [Transaction] WHERE Is_Cancelled = 0
             ORDER BY Yr DESC, Mo DESC";
$monthStmt = sqlsrv_query($conn, $monthSql);
$months = [];
if ($monthStmt) {
    while ($r = sqlsrv_fetch_array($monthStmt, SQLSRV_FETCH_ASSOC)) {
        $months[] = ['Yr' => (int)$r['Yr'], 'Mo' => (int)$r['Mo']];
    }
}
if (!$months) {
    echo json_encode(['months' => [], 'products' => [], 'summary' => null]);
    exit;
}
// Oldest-to-newest for the weighted average below; pad to 3 with nulls if
// the data doesn't go back that far yet.
$months = array_reverse($months);
while (count($months) < 3) array_unshift($months, null);
$earliest = $months[0] ?? $months[array_key_first($months)];
foreach ($months as $m) { if ($m) { $earliest = $m; break; } }
$rangeStart = sprintf('%04d-%02d-01', $earliest['Yr'], $earliest['Mo']);

// Step 2: real packs sold per product per month, bounded to just that
// 3-month window -- not a full trans_detail scan.
$sql = "SELECT
            YEAR(t.Trans_date)  AS Yr,
            MONTH(t.Trans_date) AS Mo,
            td.stock_number     AS StockNo,
            COALESCE(NULLIF(s.SIZE_DESC, ''), s.ITEM_NAME) AS Item,
            s.BRAND_NAME        AS Brand,
            SUM(td.quantity)    AS Packs
        FROM trans_detail td
        JOIN [Transaction] t ON td.Trans_no     = t.Trans_no
        JOIN Item_Stock  s   ON td.stock_number = s.STOCK_NUMBER
        WHERE t.Is_Cancelled = 0 AND t.Trans_date >= ?
        GROUP BY YEAR(t.Trans_date), MONTH(t.Trans_date), td.stock_number,
                 COALESCE(NULLIF(s.SIZE_DESC, ''), s.ITEM_NAME), s.BRAND_NAME
        OPTION (MAXDOP 1, MIN_GRANT_PERCENT = 0, MAX_GRANT_PERCENT = 1)";
$stmt = sqlsrv_query($conn, $sql, [$rangeStart]);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => sqlsrv_errors()[0]['message'] ?? 'Query failed']);
    exit;
}

// Pivot into one series per product: [oldest, middle, newest] packs.
$byProduct = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $no = $row['StockNo'];
    if (!isset($byProduct[$no])) {
        $byProduct[$no] = [
            'stock_no' => $no,
            'label'    => trim(($row['Brand'] ?? '') . ' ' . $row['Item']),
            'series'   => [0, 0, 0],
        ];
    }
    foreach ($months as $i => $m) {
        if ($m && (int)$row['Yr'] === $m['Yr'] && (int)$row['Mo'] === $m['Mo']) {
            $byProduct[$no]['series'][$i] = (int)$row['Packs'];
        }
    }
}

if (!$byProduct) {
    echo json_encode(['months' => $months, 'products' => [], 'summary' => null]);
    exit;
}

// Step 3: current stock for exactly the products involved -- a targeted
// lookup, not a full Item_Stock scan.
$stockNos = array_keys($byProduct);
$placeholders = implode(',', array_fill(0, count($stockNos), '?'));
$stockSql = "SELECT STOCK_NUMBER, QTY_INHAND FROM Item_Stock WHERE STOCK_NUMBER IN ($placeholders)";
$stockStmt = sqlsrv_query($conn, $stockSql, $stockNos);
$stockOnHand = [];
if ($stockStmt) {
    while ($r = sqlsrv_fetch_array($stockStmt, SQLSRV_FETCH_ASSOC)) {
        $stockOnHand[$r['STOCK_NUMBER']] = (int)($r['QTY_INHAND'] ?? 0);
    }
}
sqlsrv_close($conn);

// Step 4: forecast + status per product. Weighted moving average -- most
// recent month counts for half the forecast, since it best reflects current
// demand; the older two months smooth out one unusually busy/slow month.
$products = [];
$totalForecast = 0;
$reorderCount = 0;
foreach ($byProduct as $no => $p) {
    [$m1, $m2, $m3] = $p['series']; // oldest -> newest
    $forecast = (int)round($m1 * 0.2 + $m2 * 0.3 + $m3 * 0.5);
    $onHand = $stockOnHand[$no] ?? 0;
    $trendPct = $m1 > 0 ? round((($m3 - $m1) / $m1) * 100) : ($m3 > 0 ? null : 0); // null = "new" (no earlier baseline)

    if ($forecast === 0 && $m1 === 0 && $m2 === 0) {
        $status = 'quiet'; $suggested = 0;
    } elseif ($onHand < $forecast) {
        $status = 'reorder';
        $suggested = max(0, (int)ceil($forecast * 1.2) - $onHand); // forecast + 20% buffer
        $reorderCount++;
    } elseif ($forecast > 0 && $onHand > $forecast * 3) {
        $status = 'overstocked'; $suggested = 0;
    } else {
        $status = 'sufficient'; $suggested = 0;
    }
    $totalForecast += $forecast;

    $products[] = [
        'stock_no' => $no, 'label' => $p['label'], 'series' => $p['series'],
        'forecast' => $forecast, 'on_hand' => $onHand, 'trend_pct' => $trendPct,
        'status' => $status, 'suggested_reorder' => $suggested,
    ];
}

// Most urgent first: reorder (biggest shortfall first), then sufficient,
// overstocked, quiet.
$statusOrder = ['reorder' => 0, 'sufficient' => 1, 'overstocked' => 2, 'quiet' => 3];
usort($products, function ($a, $b) use ($statusOrder) {
    $oa = $statusOrder[$a['status']]; $ob = $statusOrder[$b['status']];
    if ($oa !== $ob) return $oa <=> $ob;
    if ($a['status'] === 'reorder') return $b['suggested_reorder'] <=> $a['suggested_reorder'];
    return $b['forecast'] <=> $a['forecast'];
});

// "Fastest Growing" / "Slowest / Declining" only count a product if its
// trend is actually positive/negative -- picking the least-bad decline and
// still calling it "growing" would be a real, if small, honesty problem
// (early in a calendar month, most-recent-month sales are naturally still
// low simply because the month isn't over yet, which can make every single
// product look "down" -- that's a real pattern in the data, not a bug, but
// it shouldn't get mislabeled as growth).
$growing = null; $declining = null;
foreach ($products as $p) {
    if ($p['trend_pct'] === null) continue;
    if ($p['trend_pct'] > 0 && ($growing === null || $p['trend_pct'] > $growing['trend_pct']))   $growing = $p;
    if ($p['trend_pct'] < 0 && ($declining === null || $p['trend_pct'] < $declining['trend_pct'])) $declining = $p;
}

echo json_encode([
    'months' => $months,
    'products' => $products,
    'summary' => [
        'reorder_count'   => $reorderCount,
        'total_forecast'  => $totalForecast,
        'growing_label'   => $growing ? $growing['label'] : null,
        'growing_pct'     => $growing ? $growing['trend_pct'] : null,
        'declining_label' => $declining ? $declining['label'] : null,
        'declining_pct'   => $declining ? $declining['trend_pct'] : null,
    ],
]);
