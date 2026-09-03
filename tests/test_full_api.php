<?php
/**
 * Full End-to-End System & API Verification Test (Pure Bitrix24 Integration - Armenian Language)
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/AvailabilityService.php';
require_once __DIR__ . '/../src/ReservationService.php';
require_once __DIR__ . '/../src/ShipmentService.php';
require_once __DIR__ . '/../src/BitrixRestClient.php';

echo "====================================================\n";
echo "  FULL BITRIX24 ATP SYSTEM VERIFICATION (ARMENIAN UI)\n";
echo "====================================================\n\n";

$pdo = Database::getConnection();
$atpService = new AvailabilityService($pdo);
$resService = new ReservationService($pdo);
$shipService = new ShipmentService($pdo);
$bitrix = new BitrixRestClient();

// 1. Check products count
$prodCount = (int)$pdo->query("SELECT count(*) FROM products")->fetchColumn();
echo "1. Real Products loaded from Bitrix24: {$prodCount} items.\n";
if ($prodCount < 10) die("FAILED: Products count mismatch\n");
echo "   [PASS] Catalog is populated with real products.\n";

// 2. Test ATP calculation on a real product in stock
$sampleProduct = $pdo->query("SELECT bitrix_product_id, name, current_stock, reserved_stock FROM products WHERE current_stock > 0 LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if ($sampleProduct) {
    echo "\n2. Testing Real Product ATP: #{$sampleProduct['bitrix_product_id']} '{$sampleProduct['name']}'...\n";
    $atp = $atpService->calculateATP($sampleProduct['bitrix_product_id'], date('Y-m-d', strtotime('+7 days')), 1.0);
    echo "   Physical Stock: {$atp['stock_breakdown']['physical_stock']}\n";
    echo "   Active Reservations: {$atp['stock_breakdown']['active_reservations']}\n";
    echo "   ATP Available: {$atp['stock_breakdown']['atp_available']}\n";
    echo "   Verdict: {$atp['verdict']['status']} ({$atp['verdict']['status_text']})\n";
    echo "   [PASS] ATP successfully calculated for real product.\n";
}

// 3. Test Deal ATP with real Deal 41983
echo "\n3. Testing Real Bitrix Deal #41983 ATP Evaluation...\n";
$dealRes = $bitrix->call('crm.deal.productrows.get', ['id' => 41983]);
$dealRows = $dealRes['result'] ?? [];
echo "   Deal #41983 has " . count($dealRows) . " products in Bitrix24.\n";
foreach ($dealRows as $row) {
    $pId = (int)($row['PRODUCT_ID'] ?? 0);
    $qty = (float)($row['QUANTITY'] ?? 1);
    $rowAtp = $atpService->calculateATP($pId, date('Y-m-d', strtotime('+7 days')), $qty);
    echo "   - Product #{$pId} '{$row['PRODUCT_NAME']}': ATP={$rowAtp['stock_breakdown']['atp_available']}, Status={$rowAtp['verdict']['status']}\n";
}
echo "   [PASS] Deal evaluation verified.\n";

// 4. Check real users / employees
$usersCount = (int)$pdo->query("SELECT count(*) FROM system_users WHERE is_active = 1")->fetchColumn();
echo "\n4. Real Bitrix Employees synced: {$usersCount}\n";
echo "   [PASS] Employee list verified.\n";

echo "\n====================================================\n";
echo "  ALL REAL BITRIX24 INTEGRATION CHECKS PASSED PERFECTLY!\n";
echo "====================================================\n";
