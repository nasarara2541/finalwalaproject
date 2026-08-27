<?php
require_once '../db.php';
require_once __DIR__ . '/../../includes/access.php';
header('Content-Type: application/json');

// Matches the screen's own bucket (Admin/Management/Inventory).
if (!canAccess('inventory')) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

try {
    // A dead item is an item that hasn't been sold in the last 60 days.
    // It can either have NO sales at all, or its MAX(Trans_date) is < 60 days ago.
    // Let's filter out items with QTY_INHAND <= 0 if they want actual dead stock, 
    // but the prompt didn't specify. We'll include all items that match the 60-day rule.

    $sql = "
        SELECT 
            s.STOCK_NUMBER, 
            ISNULL(s.ITEM_NAME, 'Unknown') AS ITEM_NAME, 
            ISNULL(s.BRAND_NAME, '-') AS BRAND_NAME,
            ISNULL(s.Retail_Price, 0) AS Retail_Price,
            ISNULL(s.QTY_INHAND, 0) AS QTY_INHAND, 
            CONVERT(varchar, MAX(t.Trans_date), 23) as LastSoldDate
        FROM Item_Stock s
        LEFT JOIN trans_detail td ON s.STOCK_NUMBER = td.stock_number
        LEFT JOIN [Transaction] t ON td.Trans_no = t.Trans_no
        GROUP BY s.STOCK_NUMBER, s.ITEM_NAME, s.BRAND_NAME, s.Retail_Price, s.QTY_INHAND
        HAVING MAX(t.Trans_date) IS NULL OR MAX(t.Trans_date) < DATEADD(day, -60, GETDATE())
        ORDER BY s.ITEM_NAME
    ";

    $stmt = $conn->query($sql);
    $dead_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $dead_items
    ]);

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>

