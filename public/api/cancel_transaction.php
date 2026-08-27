<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

// Voids an already-saved bill: reverses its stock effect (same strategy as
// update_transaction.php's edit-reversal -- exact batch if known via
// trans_detail.Invoice_No, otherwise the latest-expiry remaining batch),
// then marks it Is_Cancelled rather than deleting it, so it stays in the
// historical record and reporting can distinguish real sales from voided
// ones. A bill can only be cancelled once.

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) { echo json_encode(['error' => 'No data received']); exit; }

$transNo = intval($data['trans_no'] ?? 0);
$reason  = trim($data['reason'] ?? '');
$userId  = $data['user_id'] ?? 'admin';

if (!$transNo) { echo json_encode(['error' => 'No Trans_no given']); exit; }

sqlsrv_begin_transaction($conn);

$stmtCheck = sqlsrv_query($conn, "SELECT Trans_no, Is_Cancelled FROM [Transaction] WHERE Trans_no = ?", [$transNo]);
$row = $stmtCheck ? sqlsrv_fetch_array($stmtCheck, SQLSRV_FETCH_ASSOC) : null;
if (!$row) {
    sqlsrv_rollback($conn);
    echo json_encode(['error' => 'Bill #' . $transNo . ' not found']);
    exit;
}
if ($row['Is_Cancelled']) {
    sqlsrv_rollback($conn);
    echo json_encode(['error' => 'Bill #' . $transNo . ' is already cancelled']);
    exit;
}

$skippedReversals = [];
$stmtLines = sqlsrv_query($conn, "SELECT stock_number, quantity, Invoice_No FROM trans_detail WHERE Trans_no = ?", [$transNo]);
$lines = [];
if ($stmtLines) { while ($r = sqlsrv_fetch_array($stmtLines, SQLSRV_FETCH_ASSOC)) { $lines[] = $r; } }

foreach ($lines as $line) {
    $stockNo = $line['stock_number'];
    $qty     = intval($line['quantity']);
    $invNo   = $line['Invoice_No'];

    if ($invNo !== null) {
        $upd = sqlsrv_query($conn,
            "UPDATE ST_STOCKRECEIPTDETAIL SET ITEMS_AVAILABLE = ITEMS_AVAILABLE + ? WHERE Invoice_no = ? AND STOCK_NUMBER = ?",
            [$qty, $invNo, $stockNo]);
        if (!$upd) { $skippedReversals[] = $stockNo; }
        continue;
    }

    $stmtLatest = sqlsrv_query($conn,
        "SELECT TOP 1 Invoice_no FROM ST_STOCKRECEIPTDETAIL WHERE STOCK_NUMBER = ? ORDER BY EXPIRY_DATE DESC", [$stockNo]);
    $latest = $stmtLatest ? sqlsrv_fetch_array($stmtLatest, SQLSRV_FETCH_ASSOC) : null;
    if ($latest) {
        sqlsrv_query($conn,
            "UPDATE ST_STOCKRECEIPTDETAIL SET ITEMS_AVAILABLE = ITEMS_AVAILABLE + ? WHERE Invoice_no = ? AND STOCK_NUMBER = ?",
            [$qty, $latest['Invoice_no'], $stockNo]);
    } else {
        $skippedReversals[] = $stockNo;
    }

    sqlsrv_query($conn,
        "UPDATE Item_Stock SET QTY_INHAND = ISNULL((SELECT SUM(ITEMS_AVAILABLE) FROM ST_STOCKRECEIPTDETAIL WHERE STOCK_NUMBER = ?),0) WHERE STOCK_NUMBER = ?",
        [$stockNo, $stockNo]);
}
foreach ($lines as $line) {
    sqlsrv_query($conn,
        "UPDATE Item_Stock SET QTY_INHAND = ISNULL((SELECT SUM(ITEMS_AVAILABLE) FROM ST_STOCKRECEIPTDETAIL WHERE STOCK_NUMBER = ?),0) WHERE STOCK_NUMBER = ?",
        [$line['stock_number'], $line['stock_number']]);
}

$stmtU = sqlsrv_query($conn,
    "UPDATE [Transaction] SET Is_Cancelled = 1, Cancelled_Date = GETDATE(), Cancelled_By = ?, Cancel_Reason = ? WHERE Trans_no = ?",
    [$userId, $reason, $transNo]);
if (!$stmtU) {
    sqlsrv_rollback($conn);
    $err = sqlsrv_errors();
    echo json_encode(['error' => $err[0]['message'] ?? 'Cancel update failed']);
    exit;
}

sqlsrv_commit($conn);
sqlsrv_close($conn);
echo json_encode(['success' => true, 'trans_no' => $transNo, 'skipped_reversals' => $skippedReversals]);
