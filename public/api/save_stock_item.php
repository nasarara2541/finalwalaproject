<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) { echo json_encode(['error' => 'No data received']); exit; }

$stockNo    = (isset($data['stock_number']) && $data['stock_number']) ? intval($data['stock_number']) : null;
$brand      = trim($data['brand_name']       ?? '');
$itemName   = trim($data['item_name']        ?? '');
$itemType   = trim($data['item_type']        ?? '');
$stockType  = trim($data['stock_type']       ?? '01');
$volumeL    = trim($data['volume_ml']        ?? '');
$units      = intval($data['units_peritem']  ?? 0);
$barcodeRaw = trim($data['barcode']          ?? '');
$barcode    = $barcodeRaw !== '' ? intval($barcodeRaw) : null;
$sizeDesc   = trim($data['size_desc']        ?? '');
$available  = trim($data['available_status'] ?? 'Active');
$unitType   = trim($data['unit_type']        ?? '');
$location   = trim($data['location']         ?? '');
$mfgNo      = intval($data['manufacture_no'] ?? 0);

if (!$brand)    { echo json_encode(['error' => 'Brand Name is required']); exit; }
if (!$mfgNo)    { echo json_encode(['error' => 'Manufacturer is required']); exit; }
if ($units < 1) { echo json_encode(['error' => 'Units Per Item must be greater than 0']); exit; }

if ($stockNo) {
    // stock_number was supplied by the client (a record previously loaded via
    // Find or the grid) — this is an update to that existing row. STOCK_NUMBER
    // itself is never written; it's an identity column and can't change.
    $checkStmt = sqlsrv_query($conn, "SELECT COUNT(*) AS cnt FROM Item_Stock WHERE STOCK_NUMBER = ?", [$stockNo]);
    $checkRow  = $checkStmt ? sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC) : null;
    if (!$checkRow || $checkRow['cnt'] == 0) {
        echo json_encode(['error' => 'Stock Number ' . $stockNo . ' no longer exists']);
        exit;
    }

    $sql  = "UPDATE Item_Stock SET
                BRAND_NAME=?, ITEM_NAME=?, ITEM_TYPE=?, STOCK_TYPE=?, VOLUME_L=?,
                UNITS_PERITEM=?, BARCODE=?, SIZE_DESC=?, AVAILABLE_STATUS=?, UNIT_TYPE=?,
                LOCATION=?, MANUFACTURE_NO=?
             WHERE STOCK_NUMBER=?";
    $stmt = sqlsrv_query($conn, $sql, [
        $brand, $itemName, $itemType, $stockType, $volumeL,
        $units, $barcode, $sizeDesc, $available, $unitType,
        $location, $mfgNo, $stockNo
    ]);
    if (!$stmt) {
        echo json_encode(['error' => sqlsrv_errors()[0]['message'] ?? 'Update failed']);
        exit;
    }
    sqlsrv_close($conn);
    echo json_encode(['success' => true, 'stock_number' => $stockNo, 'mode' => 'updated']);
} else {
    // No stock_number supplied — a brand new item. STOCK_NUMBER is an identity
    // column now (was manually-typed VARCHAR before), so it's left out of the
    // insert list entirely and read back via SCOPE_IDENTITY().
    $sql  = "INSERT INTO Item_Stock
                (BRAND_NAME, ITEM_NAME, ITEM_TYPE, STOCK_TYPE, VOLUME_L,
                 UNITS_PERITEM, BARCODE, SIZE_DESC, AVAILABLE_STATUS, UNIT_TYPE, LOCATION,
                 MANUFACTURE_NO, PRICE, QTY_INHAND, PERCENTAGE_DISC, OTC_QTY, SUPPLIERS_LIST)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,0,0,0,0,'');
             SELECT SCOPE_IDENTITY() AS new_id;";
    $stmt = sqlsrv_query($conn, $sql, [
        $brand, $itemName, $itemType, $stockType, $volumeL,
        $units, $barcode, $sizeDesc, $available, $unitType, $location,
        $mfgNo
    ]);
    if (!$stmt) {
        echo json_encode(['error' => sqlsrv_errors()[0]['message'] ?? 'Insert failed']);
        exit;
    }
    sqlsrv_next_result($stmt);
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    $newStockNo = intval($row['new_id']);
    sqlsrv_close($conn);
    echo json_encode(['success' => true, 'stock_number' => $newStockNo, 'mode' => 'inserted']);
}
