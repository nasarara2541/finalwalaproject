<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) { echo json_encode(['error'=>'No data received']); exit; }

$stockNo  = trim($data['stock_number']    ?? '');
$brand    = trim($data['brand_name']      ?? '');
$item     = trim($data['item_name']       ?? '');
$type     = trim($data['item_type']       ?? '');
$stype    = trim($data['stock_type']      ?? '');
$vol      = trim($data['volume_ml']       ?? '');
$status   = trim($data['available_status']?? 'Active');
$sizeDesc = trim($data['size_desc']       ?? '');
$barcode  = trim($data['barcode']         ?? '');
$otcQty   = intval($data['otc_qty']       ?? 0);
$unitType = trim($data['unit_type']       ?? '');
$units    = intval($data['units_peritem'] ?? 1);
$price    = floatval($data['price']       ?? 0);
$qty      = intval($data['qty_inhand']    ?? 0);
$disc     = floatval($data['percentage_disc'] ?? 0);
$supList  = trim($data['suppliers_list']  ?? '');
$location = trim($data['location']        ?? '');

if (!$stockNo || !$brand || !$item) {
    echo json_encode(['error'=>'Stock Number, Brand Name and Item Name are required']);
    exit;
}

$checkSql  = "SELECT COUNT(*) AS cnt FROM Item_Stock WHERE STOCK_NUMBER = ?";
$checkStmt = sqlsrv_query($conn, $checkSql, [$stockNo]);
$checkRow  = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC);
if ($checkRow['cnt'] > 0) {
    echo json_encode(['error'=>'A stock item with this Stock Number already exists: ' . $stockNo]);
    exit;
}

$sql  = "INSERT INTO Item_Stock
            (STOCK_NUMBER, BRAND_NAME, ITEM_NAME, ITEM_TYPE, STOCK_TYPE, VOLUME_ML,
             AVAILABLE_STATUS, SIZE_DESC, BARCODE, OTC_QTY, UNIT_TYPE, UNITS_PERITEM,
             PRICE, QTY_INHAND, LOCATION, PERCENTAGE_DISC, SUPPLIERS_LIST)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

$stmt = sqlsrv_query($conn, $sql, [
    $stockNo, $brand, $item, $type, $stype, $vol,
    $status, $sizeDesc, $barcode, $otcQty, $unitType, $units,
    $price, $qty, $location, $disc, $supList
]);

if (!$stmt) {
    $err = sqlsrv_errors();
    echo json_encode(['error' => $err[0]['message'] ?? 'Insert failed']);
    exit;
}

sqlsrv_close($conn);
echo json_encode(['success' => true, 'stock_number' => $stockNo]);
