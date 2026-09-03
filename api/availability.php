<?php
/**
 * API: Check Product ATP Availability
 * GET /api/availability.php?product_id=381&date=2026-09-10&quantity=7
 * Armenian Language Localization (Հայերեն)
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/AvailabilityService.php';

$productId = (int)($_GET['product_id'] ?? $_GET['productId'] ?? 0);
$targetDate = $_GET['date'] ?? $_GET['delivery_date'] ?? date('Y-m-d');
$quantity = (float)($_GET['quantity'] ?? 1.0);
$excludeResId = isset($_GET['exclude_res_id']) ? (int)$_GET['exclude_res_id'] : null;

if ($productId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Նշված չէ product_id'], JSON_UNESCAPED_UNICODE);
    exit;
}

$service = new AvailabilityService();
$result = $service->calculateATP($productId, $targetDate, $quantity, $excludeResId);

if (!$result['success']) {
    http_response_code(404);
}

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
