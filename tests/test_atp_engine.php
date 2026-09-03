<?php
/**
 * Automated ATP Engine Verification Test (Armenian Language)
 * Runs against an isolated in-memory SQLite database to preserve real live Bitrix data.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/AvailabilityService.php';
require_once __DIR__ . '/../src/ReservationService.php';
require_once __DIR__ . '/../src/ShipmentService.php';

// Create an isolated in-memory database for mathematical validation
$memPdo = new PDO('sqlite::memory:', null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

// Run schema
$schema = file_get_contents(__DIR__ . '/../database/schema.sql');
$memPdo->exec($schema);

// Seed isolated test case for mathematical ATP calculation
$memPdo->exec("
    INSERT INTO products (bitrix_product_id, name, sku, current_stock, reserved_stock, unit, price, cost_price, updated_at)
    VALUES (999, 'Թեստային մոդել', 'TST-01', 10.0, 8.0, 'հատ', 100000, 70000, datetime('now'));

    INSERT INTO product_reservations (deal_id, bitrix_product_id, quantity, delivery_date, status, created_at, updated_at)
    VALUES (1001, 999, 8.0, '2026-09-01', 'RESERVED', datetime('now'), datetime('now'));

    INSERT INTO incoming_shipments (bitrix_product_id, supplier_name, supplier_id, quantity, expected_date, warehouse_id, status, created_at, updated_at)
    VALUES (999, 'Մատակարար', 'SUP-TEST', 20.0, '2026-09-05', 1, 'CONFIRMED', datetime('now'), datetime('now'));
");

$atpService = new AvailabilityService($memPdo);
$resService = new ReservationService($memPdo);
$shipmentService = new ShipmentService($memPdo);

$passed = 0;
$total = 0;

function assertCondition(bool $cond, string $title, string $details = '') {
    global $passed, $total;
    $total++;
    if ($cond) {
        $passed++;
        echo "  [PASS] {$title}\n";
    } else {
        echo "  [FAIL] {$title} -- {$details}\n";
    }
}

echo "====================================================\n";
echo "  RUNNING ATP ENGINE VALIDATION TESTS (ISOLATED)\n";
echo "====================================================\n\n";

// Today: Stock=10, Reserved=8 -> Free=2
// Shipment 05.09: +20
echo "--- TEST 1: Request BEFORE shipment date (Target: 2026-09-03, Qty: 7) ---\n";
$atpBefore = $atpService->calculateATP(999, '2026-09-03', 7.0);
assertCondition(
    $atpBefore['stock_breakdown']['atp_available'] == 2.0,
    "ATP on 03.09 is exactly 2.0 հատ (Shipment from 05.09 must NOT be counted)",
    "Got: " . $atpBefore['stock_breakdown']['atp_available']
);
assertCondition(
    $atpBefore['verdict']['status'] === 'PARTIAL' && !$atpBefore['verdict']['can_fulfill'],
    "Status is PARTIAL, full fulfillment is rejected",
    "Verdict: " . $atpBefore['verdict']['status']
);
assertCondition(
    $atpBefore['stock_breakdown']['shortage'] == 5.0,
    "Shortage is exactly 5.0 հատ",
    "Got: " . $atpBefore['stock_breakdown']['shortage']
);
assertCondition(
    $atpBefore['verdict']['earliest_full_date'] === '2026-09-05',
    "Earliest full fulfillment date is detected as 2026-09-05",
    "Got: " . ($atpBefore['verdict']['earliest_full_date'] ?? 'null')
);

echo "\n--- TEST 2: Request AFTER shipment date (Target: 2026-09-10, Qty: 7) ---\n";
$atpAfter = $atpService->calculateATP(999, '2026-09-10', 7.0);
assertCondition(
    $atpAfter['stock_breakdown']['atp_available'] == 22.0,
    "ATP on 10.09 is exactly 22.0 հատ (Free: 2 + Shipment: 20)",
    "Got: " . $atpAfter['stock_breakdown']['atp_available']
);
assertCondition(
    $atpAfter['verdict']['can_fulfill'] === true && $atpAfter['verdict']['status'] === 'AVAILABLE',
    "Status is AVAILABLE, full order is approved",
    "Verdict: " . $atpAfter['verdict']['status']
);

echo "\n--- TEST 3: Create Reservation (10 հատ on 10.09) ---\n";
$reserveRes = $resService->createReservation(41983, 999, 10.0, '2026-09-10', 'Մենեջեր', 'Հաճախորդ');
assertCondition($reserveRes['success'] === true, "Reservation for 10 հատ successfully created");

$atpSubsequent = $atpService->calculateATP(999, '2026-09-10', 15.0);
assertCondition(
    $atpSubsequent['stock_breakdown']['atp_available'] == 12.0,
    "Subsequent manager sees available on 10.09 as 22 - 10 = 12 հատ",
    "Got: " . $atpSubsequent['stock_breakdown']['atp_available']
);

echo "\n====================================================\n";
echo "  SUMMARY: {$passed} / {$total} TESTS PASSED\n";
echo "====================================================\n";
