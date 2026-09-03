<?php
/**
 * API: Deal Products ATP Evaluation
 * GET /api/deal_products.php?deal_id=1521&delivery_date=2026-09-10
 * Armenian Language Localization (Հայերեն)
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/BitrixRestClient.php';
require_once __DIR__ . '/../src/AvailabilityService.php';
require_once __DIR__ . '/../src/ReservationService.php';

$dealId = (int)($_GET['deal_id'] ?? $_GET['dealId'] ?? 1521);
$customDate = $_GET['delivery_date'] ?? null;

$bitrix = new BitrixRestClient();
$atpService = new AvailabilityService();
$reservationService = new ReservationService();

// 1. Fetch deal details
$dealRes = $bitrix->getDeal($dealId);
$dealData = $dealRes['result'] ?? [
    'ID' => (string)$dealId,
    'TITLE' => "Գործարք #{$dealId}",
    'UF_CRM_DELIVERY_DATE' => date('Y-m-d', strtotime('+12 days')),
    'ASSIGNED_BY_NAME' => 'Մենեջեր',
];

// Determine target delivery date (query param > deal field > default +12 days)
$deliveryDate = $customDate ?: ($dealData['UF_CRM_DELIVERY_DATE'] ?? date('Y-m-d', strtotime('+12 days')));

// 2. Fetch deal product rows
$rowsRes = $bitrix->getDealProductRows($dealId);
$rows = $rowsRes['result'] ?? [];

// 3. Fetch existing reservations for this deal
$existingReservations = $reservationService->getReservations(['deal_id' => $dealId]);
$reservationsByProduct = [];
foreach ($existingReservations as $res) {
    $reservationsByProduct[(int)$res['bitrix_product_id']][] = $res;
}

// 4. Calculate ATP for each product row
$evaluatedProducts = [];
$overallFeasible = true;

foreach ($rows as $row) {
    $productId = (int)($row['PRODUCT_ID'] ?? 0);
    $qty = (float)($row['QUANTITY'] ?? 1.0);
    $productName = $row['PRODUCT_NAME'] ?? "Ապրանք #{$productId}";

    // Check if product exists in local catalog cache
    $atp = $atpService->calculateATP($productId, $deliveryDate, $qty);
    $productReservations = $reservationsByProduct[$productId] ?? [];
    $totalReservedForDeal = 0.0;
    foreach ($productReservations as $pr) {
        if (in_array($pr['status'], ['RESERVED', 'CONFIRMED'])) {
            $totalReservedForDeal += (float)$pr['quantity'];
        }
    }

    if (!$atp['verdict']['can_fulfill'] && $totalReservedForDeal < $qty) {
        $overallFeasible = false;
    }

    $evaluatedProducts[] = [
        'row_id' => $row['ID'] ?? null,
        'product_id' => $productId,
        'product_name' => $productName,
        'quantity' => $qty,
        'price' => (float)($row['PRICE'] ?? 0),
        'measure_name' => $row['MEASURE_NAME'] ?? 'հատ',
        'atp' => $atp,
        'existing_reservations' => $productReservations,
        'reserved_for_deal' => $totalReservedForDeal,
        'is_fully_reserved_for_deal' => ($totalReservedForDeal >= $qty),
    ];
}

echo json_encode([
    'success' => true,
    'deal' => [
        'id' => $dealId,
        'title' => $dealData['TITLE'] ?? "Գործարք #{$dealId}",
        'delivery_date' => $deliveryDate,
        'delivery_date_formatted' => $atpService->formatDateArm($deliveryDate),
        'assigned_by' => $dealData['ASSIGNED_BY_NAME'] ?? '',
        'opportunity' => $dealData['OPPORTUNITY'] ?? 0,
        'currency' => $dealData['CURRENCY_ID'] ?? 'AMD',
    ],
    'overall_feasible' => $overallFeasible,
    'products_count' => count($evaluatedProducts),
    'products' => $evaluatedProducts,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
