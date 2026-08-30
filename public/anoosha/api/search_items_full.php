<?php
require '../config/db.php';
require_once __DIR__ . '/../../includes/access.php';
header('Content-Type: application/json');
if (!canAccess('inventory')) { http_response_code(403); echo json_encode(['error' => 'Access denied']); exit; }

$q      = isset($_GET['q']) ? $_GET['q'] : '';
$offset = isset($_GET['offset']) ? max(0, (int)$_GET['offset']) : 0;
$limit  = 100;

$countSql = "SELECT COUNT(*) AS TOTAL FROM Item_Stock i WHERE i.ITEM_NAME LIKE ?";
$countStmt = sqlsrv_query($conn, $countSql, ["%$q%"]);
$totalCount = 0;
if ($countStmt !== false) {
    $countRow = sqlsrv_fetch_array($countStmt, SQLSRV_FETCH_ASSOC);
    $totalCount = $countRow ? (int)$countRow['TOTAL'] : 0;
    sqlsrv_free_stmt($countStmt);
}

$sql = "SELECT
            i.STOCK_NUMBER,
            ISNULL(i.BRAND_NAME,'')        AS BRAND_NAME,
            ISNULL(i.ITEM_NAME,'')         AS ITEM_NAME,
            ISNULL(i.ITEM_TYPE,'')         AS ITEM_TYPE,
            ISNULL(i.STOCK_TYPE,'')        AS STOCK_TYPE,
            ISNULL(i.VOLUME_L,'')          AS VOLUME_L,
            ISNULL(i.AVAILABLE_STATUS,'Y') AS AVAILABLE_STATUS,
            ISNULL(i.SIZE_DESC,'')         AS SIZE_DESC,
            ISNULL(i.BARCODE,'')           AS BARCODE,
            ISNULL(i.OTC_QTY,0)            AS OTC_QTY,
            ISNULL(i.UNIT_TYPE,'')         AS UNIT_TYPE,
            ISNULL(i.UNITS_PERITEM,0)      AS UNITS_PERITEM,
            ISNULL(i.PRICE,0)              AS PRICE,
            ISNULL(i.PRICE_2,0)            AS PRICE_2,
            ISNULL(i.PRICE_3,0)            AS PRICE_3,
            ISNULL(i.WS_Price,0)           AS WS_Price,
            ISNULL(i.RETAIL_PRICE,0)       AS RETAIL_PRICE,
            ISNULL(i.AvgPrice,0)           AS AvgPrice,
            ISNULL(i.PURCHASE_PRICE,0)     AS PURCHASE_PRICE,
            ISNULL(i.QTY_INHAND,0)         AS QTY_INHAND,
            i.MANUFACTURE_NO,
            ISNULL(m.M_Name,'')            AS MANUFACTURER_NAME,
            ISNULL(i.LOCATION,'')          AS LOCATION,
            ISNULL(i.PERCENTAGE_DISC,0)    AS PERCENTAGE_DISC,
            ISNULL(i.SUPPLIERS_LIST,'')    AS SUPPLIERS_LIST,
            i.SUPPLIER_CODE,
            ISNULL(s.SUPPLIER_NAME,'')     AS SUPPLIER_NAME,
            ISNULL(i.SAFETY_LEVEL,'')      AS SAFETY_LEVEL,
            ISNULL(i.SALE_DISCOUNT,'0')    AS SALE_DISCOUNT,
            ISNULL(i.NARCOTICS_STATUS,'0') AS NARCOTICS_STATUS,
            ISNULL(i.DISC_STATUS,'0')      AS DISC_STATUS
        FROM Item_Stock i
        LEFT JOIN Manufacture m ON m.Manufacture_no = i.MANUFACTURE_NO
        LEFT JOIN ST_Supplier s ON s.SUPPLIER_CODE = i.SUPPLIER_CODE
                WHERE i.ITEM_NAME LIKE ?
        ORDER BY TRY_CAST(i.STOCK_NUMBER AS INT), i.STOCK_NUMBER
        OFFSET ? ROWS FETCH NEXT ? ROWS ONLY
        OPTION (MAXDOP 1, MIN_GRANT_PERCENT = 0, MAX_GRANT_PERCENT = 1)";
$params = ["%$q%", $offset, $limit + 1];
$stmt = sqlsrv_query($conn, $sql, $params);
if ($stmt === false) {
    echo json_encode(["success" => false, "message" => "Query failed", "errors" => sqlsrv_errors()]);
    exit;
}
$results = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $results[] = $row;
}
$hasMore = count($results) > $limit;
if ($hasMore) { array_pop($results); }
$json = json_encode(['results' => $results, 'hasMore' => $hasMore, 'totalCount' => $totalCount], JSON_INVALID_UTF8_SUBSTITUTE);
echo $json !== false ? $json : json_encode(["success" => false, "message" => json_last_error_msg()]);
?>