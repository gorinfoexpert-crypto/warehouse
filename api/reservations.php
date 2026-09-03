<?php
/**
 * API: Reservations List & Actions
 * Pure Armenian Language Localization (Հայերեն) with Role-Based Permission Verification
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/ReservationService.php';
require_once __DIR__ . '/../src/AuthService.php';

$auth = new AuthService();
$service = new ReservationService();
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] === 'POST' || $action !== 'list') {
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true) ?: $_POST;
    $id = (int)($data['id'] ?? $_GET['id'] ?? 0);

    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Նշված չէ ամրագրման համարը'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($action === 'cancel') {
        $auth->requirePermission('manage_reservations');
        $reason = $data['reason'] ?? 'Չեղարկված է օգտատիրոջ կողմից';
        $res = $service->cancelReservation($id, $reason);
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        exit;
    } elseif ($action === 'confirm') {
        if (!$auth->can('confirm_reservations') && !$auth->can('manage_reservations')) {
            $auth->requirePermission('confirm_reservations');
        }
        $res = $service->confirmReservation($id);
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        exit;
    } elseif ($action === 'ship') {
        if (!$auth->can('ship_reservations') && !$auth->can('manage_reservations')) {
            $auth->requirePermission('ship_reservations');
        }
        $res = $service->shipReservation($id);
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// Default GET list
$auth->requirePermission('view_reservations');
$filters = [
    'deal_id' => $_GET['deal_id'] ?? null,
    'bitrix_product_id' => $_GET['product_id'] ?? null,
    'status' => $_GET['status'] ?? null,
    'active_only' => isset($_GET['active_only']),
];

$list = $service->getReservations($filters);
echo json_encode([
    'success' => true,
    'count' => count($list),
    'reservations' => $list
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
