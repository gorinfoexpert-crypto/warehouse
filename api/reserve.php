<?php
/**
 * API: Create Reservation
 * Pure Armenian Language Localization (Հայերեն) with Role-Based Permission Verification
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/ReservationService.php';
require_once __DIR__ . '/../src/AuthService.php';

$auth = new AuthService();
$auth->requirePermission('create_reservations');

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true) ?: $_POST;

$currentUser = $auth->getCurrentUser();

$dealId = (int)($data['deal_id'] ?? $data['dealId'] ?? 0);
$productId = (int)($data['product_id'] ?? $data['productId'] ?? 0);
$quantity = (float)($data['quantity'] ?? 0);
$deliveryDate = $data['delivery_date'] ?? $data['date'] ?? date('Y-m-d');
$managerName = $data['manager_name'] ?? ($currentUser['name'] ?? 'Մենեջեր');
$customerName = $data['customer_name'] ?? 'Հաճախորդ';
$notes = $data['notes'] ?? '';

if ($dealId <= 0 || $productId <= 0 || $quantity <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Անհրաժեշտ է նշել գործարքի համարը, ապրանքը և դրական քանակ'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$service = new ReservationService();
$result = $service->createReservation(
    $dealId,
    $productId,
    $quantity,
    $deliveryDate,
    $managerName,
    $customerName,
    $notes
);

if (!$result['success']) {
    http_response_code(422);
}

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
