<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/auth_check.php';
require_once __DIR__ . '/../../../includes/access.php';
require_once __DIR__ . '/../../src/Models/SalesReportModel.php';

// Same bucket as the screen itself (admin_area = Admin + Management).
if (!canAccess('admin_area')) {
    echo json_encode(['success' => false, 'error' => 'Access denied.']);
    exit;
}

$mode = $_GET['mode'] ?? 'summary';
$start = $_GET['start_date'] ?? date('Y-m-d');
$end = $_GET['end_date'] ?? date('Y-m-d');

if ($mode === 'summary') {
    $result = SalesReportModel::getProfitSummary($start, $end);
} elseif ($mode === 'per_transaction') {
    $result = SalesReportModel::getProfitPerTransaction($start, $end);
} elseif ($mode === 'daywise') {
    $result = SalesReportModel::getDaywiseReport($start, $end);
} else {
    $result = ['success' => false, 'error' => 'Invalid mode.'];
}
echo json_encode($result);