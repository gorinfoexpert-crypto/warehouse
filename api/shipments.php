<?php
/**
 * API: Incoming Shipments Management & Warehouse Conduct
 * Pure Armenian Language Localization (Հայերեն) with Role-Based Permission Verification
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/ShipmentService.php';
require_once __DIR__ . '/../src/AuthService.php';

$auth = new AuthService();
$service = new ShipmentService();
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] === 'POST' || $action !== 'list') {
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true) ?: $_POST;
    $id = (int)($data['id'] ?? $_GET['id'] ?? 0);

    if ($action === 'receive') {
        $auth->requirePermission('receive_shipments');
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Նշված չէ մատակարարման համարը'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $res = $service->receiveShipment($id);
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        exit;
    } elseif ($action === 'cancel') {
        $auth->requirePermission('manage_shipments');
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Նշված չէ մատակարարման համարը'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $reason = $data['reason'] ?? '';
        $res = $service->cancelShipment($id, $reason);
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        exit;
    } elseif ($action === 'create') {
        $auth->requirePermission('manage_shipments');
        $productId = (int)($data['product_id'] ?? $data['productId'] ?? 0);
        $qty = (float)($data['quantity'] ?? 0);
        $expectedDate = $data['expected_date'] ?? $data['date'] ?? date('Y-m-d', strtotime('+7 days'));
        $supplier = $data['supplier_name'] ?? 'Մատակարար';
        $status = $data['status'] ?? 'CONFIRMED';
        $warehouseId = (int)($data['warehouse_id'] ?? 1);
        $notes = $data['notes'] ?? '';

        if ($productId <= 0 || $qty <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Նշեք ապրանքը և դրական քանակ'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $res = $service->createShipment($productId, $qty, $expectedDate, $supplier, $status, $warehouseId, $notes);
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        exit;
    } elseif ($action === 'update') {
        $auth->requirePermission('manage_shipments');
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Նշված չէ մատակարարման համարը'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $qty = isset($data['quantity']) ? (float)$data['quantity'] : null;
        $expectedDate = $data['expected_date'] ?? null;
        $status = $data['status'] ?? null;
        $supplier = $data['supplier_name'] ?? null;
        $notes = $data['notes'] ?? null;

        $res = $service->updateShipment($id, $qty, $expectedDate, $status, $supplier, $notes);
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// Default GET list
$auth->requirePermission('view_shipments');
$filters = [
    'bitrix_product_id' => $_GET['product_id'] ?? null,
    'status' => $_GET['status'] ?? null,
    'active_only' => isset($_GET['active_only']),
];

$list = $service->getShipments($filters);
echo json_encode([
    'success' => true,
    'count' => count($list),
    'shipments' => $list
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
