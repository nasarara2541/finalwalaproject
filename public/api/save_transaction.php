<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) { echo json_encode(['error' => 'No data received']); exit; }

$custName  = $data['cust_name']       ?? '';
$custTel   = $data['cust_telno']      ?? '';
$transType = $data['trans_type']      ?? 'Cash';
$discPct   = floatval($data['disc_percentage'] ?? 0);
$gross     = floatval($data['gross_amount']    ?? 0);
$net       = floatval($data['trans_amount']    ?? 0);
$paid      = floatval($data['paid_amount']     ?? 0);
$balance   = floatval($data['balance_amount']  ?? 0);
$userId    = $data['user_id']         ?? 'admin';
$taxStatus = $data['tax_status']      ?? 'N';
$items     = $data['items']           ?? [];

sqlsrv_begin_transaction($conn);

$sqlT = "INSERT INTO [Transaction]
             (Cust_name, Cust_telno, Trans_date, Trans_type,
              Trans_amount, Disc_percentage, Tax_status,
              Gross_amount, Paid_amount, Balance_amount, User_id)
         VALUES (?, ?, GETDATE(), ?, ?, ?, ?, ?, ?, ?, ?);
         SELECT SCOPE_IDENTITY() AS new_id;";

$stmtT = sqlsrv_query($conn, $sqlT, [
    $custName, $custTel, $transType,
    $net, $discPct, $taxStatus,
    $gross, $paid, $balance, $userId
]);

if (!$stmtT) {
    sqlsrv_rollback($conn);
    $err = sqlsrv_errors();
    echo json_encode(['error' => $err[0]['message'] ?? 'Insert failed']);
    exit;
}

sqlsrv_next_result($stmtT);
$idRow   = sqlsrv_fetch_array($stmtT, SQLSRV_FETCH_ASSOC);
$transNo = intval($idRow['new_id'] ?? 0);

if (!$transNo) {
    sqlsrv_rollback($conn);
    echo json_encode(['error' => 'Could not get transaction ID']);
    exit;
}

foreach ($items as $item) {
    $sqlD  = "INSERT INTO trans_detail
                  (Trans_no, stock_number, quantity, Price_PerItem, amount, User_id, Status)
              VALUES (?, ?, ?, ?, ?, ?, 'Active')";
    $stmtD = sqlsrv_query($conn, $sqlD, [
        $transNo,
        $item['stock_number'],
        intval($item['quantity']),
        floatval($item['price']),
        floatval($item['amount']),
        $userId,
    ]);
    if (!$stmtD) {
        sqlsrv_rollback($conn);
        $err = sqlsrv_errors();
        echo json_encode(['error' => $err[0]['message'] ?? 'Detail insert failed']);
        exit;
    }

    $sqlU = "UPDATE Item_Stock SET QTY_INHAND = QTY_INHAND - ? WHERE STOCK_NUMBER = ?";
    sqlsrv_query($conn, $sqlU, [intval($item['quantity']), $item['stock_number']]);
}

sqlsrv_commit($conn);
sqlsrv_close($conn);
echo json_encode(['success' => true, 'trans_no' => $transNo]);