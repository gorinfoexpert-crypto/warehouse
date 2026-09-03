<?php
/**
 * API: Roles, Permissions & User Management
 * Pure Armenian Language Localization (Հայերեն)
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/AuthService.php';

$auth = new AuthService();
$action = $_GET['action'] ?? $_POST['action'] ?? 'get_current';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true) ?: $_POST;
    $action = $data['action'] ?? $action;

    if ($action === 'switch_user') {
        $userId = (int)($data['user_id'] ?? 0);
        if ($userId > 0) {
            $auth->setCurrentUserId($userId);
            echo json_encode([
                'success' => true,
                'message' => 'Ընթացիկ օգտատերը փոխվեց',
                'current_user' => $auth->getCurrentUser(),
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    } elseif ($action === 'update_user_role') {
        $auth->requirePermission('manage_roles');
        $userId = (int)($data['user_id'] ?? 0);
        $roleCode = $data['role_code'] ?? '';
        $res = $auth->updateUserRole($userId, $roleCode);
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        exit;
    } elseif ($action === 'update_permissions') {
        $auth->requirePermission('manage_roles');
        $roleCode = $data['role_code'] ?? '';
        $perms = (array)($data['permissions'] ?? []);
        $res = $auth->updateRolePermissions($roleCode, $perms);
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// GET queries
if ($action === 'list_all') {
    echo json_encode([
        'success' => true,
        'current_user' => $auth->getCurrentUser(),
        'roles' => $auth->getRoles(),
        'users' => $auth->getUsers(),
        'all_permissions' => AuthService::PERMISSIONS,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// Default: get current user and permissions
echo json_encode([
    'success' => true,
    'current_user' => $auth->getCurrentUser(),
    'all_permissions' => AuthService::PERMISSIONS,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
