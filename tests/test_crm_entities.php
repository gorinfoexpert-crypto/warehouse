<?php
/**
 * Automated Test: Bitrix24 CRM Entities (Managers, Companies, Contacts)
 * Pure Natural Armenian Localization (Հայերեն)
 * Read-only against real Bitrix24 portal to preserve production data cleanliness.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/BitrixRestClient.php';
require_once __DIR__ . '/../src/ReservationService.php';

function assertTest(bool $condition, string $testName) {
    if ($condition) {
        echo "  [PASS] {$testName}\n";
    } else {
        echo "  [FAIL] {$testName}\n";
        exit(1);
    }
}

echo "=========================================================\n";
echo "  BITRIX24 CRM MANAGERS & CUSTOMERS INTEGRATION TEST\n";
echo "=========================================================\n\n";

$bitrix = new BitrixRestClient();
$pdo = Database::getConnection();

// 1. Test fetching companies
echo "1. Testing Bitrix24 Company List...\n";
$compRes = $bitrix->getCompanyList();
assertTest(!empty($compRes['result']), "Companies fetched from Bitrix24 client");
$firstComp = $compRes['result'][0] ?? null;
assertTest(!empty($firstComp['TITLE']), "Company title exists: " . ($firstComp['TITLE'] ?? 'N/A'));
echo "   Found Company: {$firstComp['TITLE']}\n";

// 2. Test fetching contacts
echo "\n2. Testing Bitrix24 Contact List...\n";
$contRes = $bitrix->getContactList();
assertTest(!empty($contRes['result']), "Contacts fetched from Bitrix24 client");
$firstCont = $contRes['result'][0] ?? null;
assertTest(!empty($firstCont['NAME']), "Contact name exists: " . ($firstCont['NAME'] ?? 'N/A'));
echo "   Found Contact: {$firstCont['NAME']} " . ($firstCont['LAST_NAME'] ?? '') . "\n";

// 3. Test real employees in system_users
echo "\n3. Testing Real Bitrix Employees in System Users...\n";
$users = $pdo->query("SELECT id, name, role_code, email FROM system_users WHERE is_active = 1 LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
assertTest(!empty($users), "Active system users found in database");
foreach ($users as $u) {
    echo "   - {$u['name']} ({$u['role_code']}, {$u['email']})\n";
}

// 4. Test real reservations
echo "\n4. Testing Real Reservations in Database...\n";
$reservations = $pdo->query("SELECT r.id, r.deal_id, r.customer_name, r.manager_name, p.name as product_name FROM product_reservations r LEFT JOIN products p ON r.bitrix_product_id = p.bitrix_product_id LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
assertTest(!empty($reservations), "Real reservations found in database");
foreach ($reservations as $r) {
    echo "   - Res #{$r['id']}: Deal #{$r['deal_id']}, Customer: {$r['customer_name']}, Manager: {$r['manager_name']}, Product: {$r['product_name']}\n";
}

echo "\n=========================================================\n";
echo "  ALL BITRIX24 CRM MANAGERS & CUSTOMERS TESTS PASSED!\n";
echo "=========================================================\n";
