<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../includes/access.php';
require_once __DIR__ . '/../../src/Models/PurchaseOrderModel.php';

if (!canAccess('inventory')) {
    echo json_encode(['success' => false, 'error' => 'Access denied.']);
    exit;
}

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

switch ($action) {
    case 'suppliers':
        echo json_encode(PurchaseOrderModel::getSuppliers());
        break;

    case 'next_trans_no':
        echo json_encode(PurchaseOrderModel::getNextTransNo());
        break;

    case 'get_invoice':
        $invoiceNo = isset($_GET['invoice_no']) ? (int)$_GET['invoice_no'] : 0;
        echo json_encode(PurchaseOrderModel::getInvoice($invoiceNo));
        break;

    case 'get_invoices':
        $search = trim($_GET['search'] ?? '');
        echo json_encode(PurchaseOrderModel::getInvoices($search));
        break;

    case 'save':
        $body = json_decode(file_get_contents('php://input'), true);
        if (!$body) {
            echo json_encode(['success' => false, 'error' => 'Invalid JSON.']);
            break;
        }
        $headerResult = PurchaseOrderModel::saveHeader($body);
        if (!$headerResult['success']) {
            echo json_encode($headerResult);
            break;
        }
        $detailResult = PurchaseOrderModel::saveDetails($headerResult['invoice_no'], $body['items'] ?? []);
        if (!$detailResult['success']) {
            echo json_encode($detailResult);
            break;
        }
        echo json_encode(['success' => true, 'invoice_no' => $headerResult['invoice_no'], 'trans_no' => $headerResult['trans_no']]);
        break;

    case 'modify':
        $body = json_decode(file_get_contents('php://input'), true);
        if (!$body) {
            echo json_encode(['success' => false, 'error' => 'Invalid JSON.']);
            break;
        }
        $invoiceNo = (int)($body['invoice_no'] ?? 0);
        echo json_encode(PurchaseOrderModel::updateStock($invoiceNo, $body, $body['items'] ?? []));
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action.']);
}