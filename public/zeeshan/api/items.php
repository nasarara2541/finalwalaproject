<?php
require_once "../db.php";
require_once __DIR__ . '/../../includes/access.php';
header("Content-Type: application/json");

if (!canAccess('inventory')) {
    http_response_code(403);
    echo json_encode(["error" => "Access denied"]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        $stmt = $conn->query("SELECT STOCK_NUMBER, BRAND_NAME, ITEM_NAME, ITEM_TYPE, STOCK_TYPE, VOLUME_L, SIZE_DESC, AVAILABLE_STATUS, MANUFACTURE_NO, SAFETY_LEVEL, PRICE_2, PRICE_3, WS_Price, RETAIL_PRICE, SALE_DISCOUNT, NARCOTICS_STATUS, AvgPrice, DISC_STATUS FROM Item_Stock ORDER BY STOCK_NUMBER ASC");
        $items = [];
        while ($row = $stmt->fetch()) {
            $items[] = [
                "stockNo" => $row["STOCK_NUMBER"],
                "brandName" => $row["BRAND_NAME"],
                "itemName" => $row["ITEM_NAME"],
                "itemType" => $row["ITEM_TYPE"],
                "stockType" => $row["STOCK_TYPE"],
                "volume" => $row["VOLUME_L"],
                "sizeDesc" => $row["SIZE_DESC"],
                "availStatus" => $row["AVAILABLE_STATUS"],
                "manufactureNo" => $row["MANUFACTURE_NO"],
                "safetyLevel" => $row["SAFETY_LEVEL"],
                "price2" => $row["PRICE_2"],
                "price3" => $row["PRICE_3"],
                "wsPrice" => $row["WS_Price"],
                "retailPrice" => $row["RETAIL_PRICE"],
                "saleDiscount" => $row["SALE_DISCOUNT"],
                "narcoticsStatus" => $row["NARCOTICS_STATUS"],
                "avgPrice" => $row["AvgPrice"],
                "discStatus" => $row["DISC_STATUS"]
            ];
        }
        echo json_encode($items);
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (isset($data['action']) && $data['action'] === 'delete') {
        if (!isset($data['stockNo'])) {
            echo json_encode(["error" => "No stock number provided"]);
            exit;
        }
        try {
            $stmt = $conn->prepare("DELETE FROM Item_Stock WHERE STOCK_NUMBER = ?");
            $stmt->execute([$data['stockNo']]);
            echo json_encode(["success" => true]);
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => $e->getMessage()]);
        }
    } else {
        if (!$data) {
            echo json_encode(["error" => "No data provided"]);
            exit;
        }

        try {
            if ($data['stockNo']) {
                $checkStmt = $conn->prepare("SELECT 1 FROM Item_Stock WHERE STOCK_NUMBER = ?");
                $checkStmt->execute([$data['stockNo']]);
                $exists = $checkStmt->fetch();
            } else {
                $exists = false;
            }
            
            $barcodeVal = (!empty($data['location']) && is_numeric($data['location'])) ? $data['location'] : null;

            if ($exists) {
                $stmt = $conn->prepare("UPDATE Item_Stock SET BRAND_NAME=?, ITEM_NAME=?, ITEM_TYPE=?, STOCK_TYPE=?, VOLUME_L=?, UNITS_PERITEM=?, SIZE_DESC=?, AVAILABLE_STATUS=?, UNIT_TYPE=?, MANUFACTURE_NO=?, BARCODE=?, SAFETY_LEVEL=?, PRICE_2=?, PRICE_3=?, WS_Price=?, RETAIL_PRICE=?, SALE_DISCOUNT=?, NARCOTICS_STATUS=?, AvgPrice=?, DISC_STATUS=? WHERE STOCK_NUMBER=?");
                $stmt->execute([
                    $data['brandName'],
                    $data['itemName'],
                    $data['itemType'],
                    $data['stockType'],
                    $data['volume'],
                    $data['unitsPerItem'] ?: 0,
                    $data['size'],
                    $data['status'],
                    $data['unitType'],
                    $data['manufacturerNo'] ?: null,
                    $barcodeVal, // Using BARCODE for Location
                    $data['safetyLevel'] ?? null,
                    $data['price2'] ?? 0,
                    $data['price3'] ?? 0,
                    $data['wsPrice'] ?? 0,
                    $data['retailPrice'] ?? 0,
                    $data['saleDiscount'] ?? '0',
                    $data['narcoticsStatus'] ?? '0',
                    $data['avgPrice'] ?? 0,
                    $data['discStatus'] ?? '0',
                    $data['stockNo']
                ]);
            } else {
                $stmt = $conn->prepare("INSERT INTO Item_Stock (BRAND_NAME, ITEM_NAME, ITEM_TYPE, STOCK_TYPE, VOLUME_L, UNITS_PERITEM, SIZE_DESC, AVAILABLE_STATUS, UNIT_TYPE, MANUFACTURE_NO, BARCODE, SAFETY_LEVEL, PRICE_2, PRICE_3, WS_Price, RETAIL_PRICE, SALE_DISCOUNT, NARCOTICS_STATUS, AvgPrice, DISC_STATUS) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                $stmt->execute([
                    $data['brandName'],
                    $data['itemName'],
                    $data['itemType'],
                    $data['stockType'],
                    $data['volume'],
                    $data['unitsPerItem'] ?: 0,
                    $data['size'],
                    $data['status'],
                    $data['unitType'],
                    $data['manufacturerNo'] ?: null,
                    $barcodeVal, // Using BARCODE for Location
                    $data['safetyLevel'] ?? null,
                    $data['price2'] ?? 0,
                    $data['price3'] ?? 0,
                    $data['wsPrice'] ?? 0,
                    $data['retailPrice'] ?? 0,
                    $data['saleDiscount'] ?? '0',
                    $data['narcoticsStatus'] ?? '0',
                    $data['avgPrice'] ?? 0,
                    $data['discStatus'] ?? '0'
                ]);
            }

            echo json_encode(["success" => true]);
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }
}
?>
