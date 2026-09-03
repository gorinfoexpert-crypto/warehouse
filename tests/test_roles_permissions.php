<?php
/**
 * Automated Verification Test for Role-Based Access Control (RBAC)
 * Pure Armenian Language (Հայերեն)
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/AuthService.php';
require_once __DIR__ . '/../src/ReservationService.php';
require_once __DIR__ . '/../src/ShipmentService.php';

$pdo = Database::getConnection();
$auth = new AuthService($pdo);

$adminId = (int)($pdo->query("SELECT id FROM system_users WHERE role_code = 'admin' LIMIT 1")->fetchColumn() ?: 1);
$managerId = (int)($pdo->query("SELECT id FROM system_users WHERE role_code = 'manager' LIMIT 1")->fetchColumn() ?: 2);
$storekeeperId = (int)($pdo->query("SELECT id FROM system_users WHERE role_code = 'storekeeper' LIMIT 1")->fetchColumn() ?: 3);
$viewerId = (int)($pdo->query("SELECT id FROM system_users WHERE role_code = 'viewer' LIMIT 1")->fetchColumn() ?: 4);

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
echo "  RUNNING ROLES & PERMISSIONS (RBAC) TEST SUITE\n";
echo "====================================================\n\n";

// 1. Test Admin
echo "--- TEST 1: Admin Permissions Verification ---\n";
assertCondition($auth->can('view_simulator', $adminId), "Admin can view simulator");
assertCondition($auth->can('receive_shipments', $adminId), "Admin can receive and conduct shipments");
assertCondition($auth->can('create_reservations', $adminId), "Admin can create reservations");
assertCondition($auth->can('manage_settings', $adminId), "Admin can manage settings");
assertCondition($auth->can('manage_roles', $adminId), "Admin can manage roles and permissions");

// 2. Test Sales Manager
echo "\n--- TEST 2: Sales Manager Permissions Verification ---\n";
assertCondition($auth->can('view_simulator', $managerId), "Manager can use simulator");
assertCondition($auth->can('create_reservations', $managerId), "Manager can create reservations");
assertCondition(!$auth->can('receive_shipments', $managerId), "Manager CANNOT receive shipments (restricted)");
assertCondition(!$auth->can('manage_settings', $managerId), "Manager CANNOT manage system settings (restricted)");
assertCondition(!$auth->can('manage_roles', $managerId), "Manager CANNOT manage roles (restricted)");

// 3. Test Storekeeper / Logistics
echo "\n--- TEST 3: Storekeeper Permissions Verification ---\n";
assertCondition($auth->can('view_shipments', $storekeeperId), "Storekeeper can view incoming shipments");
assertCondition($auth->can('receive_shipments', $storekeeperId), "Storekeeper can receive and conduct shipments in Bitrix24");
assertCondition($auth->can('sync_bitrix', $storekeeperId), "Storekeeper can sync stock with Bitrix24");
assertCondition($auth->can('ship_reservations', $storekeeperId), "Storekeeper can ship reservations");
assertCondition(!$auth->can('create_reservations', $storekeeperId), "Storekeeper CANNOT create sales reservations (restricted)");
assertCondition(!$auth->can('manage_settings', $storekeeperId), "Storekeeper CANNOT manage system settings (restricted)");

// 4. Test Viewer / Auditor
echo "\n--- TEST 4: Viewer Permissions Verification ---\n";
assertCondition($auth->can('view_products', $viewerId), "Viewer can view products catalog");
assertCondition($auth->can('view_reservations', $viewerId), "Viewer can view reservations");
assertCondition(!$auth->can('create_reservations', $viewerId), "Viewer CANNOT create reservations");
assertCondition(!$auth->can('receive_shipments', $viewerId), "Viewer CANNOT receive shipments");
assertCondition(!$auth->can('sync_bitrix', $viewerId), "Viewer CANNOT trigger sync");

// 5. Test Role Update & Reassignment
echo "\n--- TEST 5: Dynamic Role Reassignment ---\n";
$auth->updateUserRole($viewerId, 'manager');
assertCondition($auth->can('create_reservations', $viewerId), "Viewer now has manager permissions after role reassignment");
$auth->updateUserRole($viewerId, 'viewer'); // restore

echo "\n====================================================\n";
echo "  SUMMARY: {$passed} / {$total} TESTS PASSED\n";
echo "====================================================\n";
