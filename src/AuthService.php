<?php
/**
 * AuthService - Role-Based Access Control (RBAC)
 * Pure Armenian Language Support (Հայերեն)
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

class AuthService {
    private PDO $pdo;

    // Available permission codes and Armenian names
    public const PERMISSIONS = [
        'view_simulator'       => 'Հասանելիության հաշվիչի օգտագործում',
        'view_products'        => 'Պահեստային մնացորդների դիտում',
        'sync_bitrix'          => 'Մնացորդների թարմացում',
        'view_shipments'       => 'Մատակարարումների դիտում',
        'manage_shipments'     => 'Մատակարարման գրանցում և խմբագրում',
        'receive_shipments'    => 'Մատակարարման ընդունում պահեստ (մուտքի փաստաթուղթ)',
        'view_reservations'    => 'Գործարքների ամրագրումների դիտում',
        'create_reservations'  => 'Գործարքի համար նոր ամրագրման ստեղծում',
        'confirm_reservations' => 'Ամրագրման հաստատում',
        'ship_reservations'    => 'Ապրանքի առաքում ամրագրումով',
        'manage_reservations'  => 'Ամրագրումների լիարժեք կառավարում / չեղարկում',
        'manage_settings'      => 'Համակարգի կարգավորումների փոփոխում',
        'manage_roles'         => 'Դերերի և աշխատակիցների իրավունքների կառավարում',
    ];

    public function __construct(?PDO $pdo = null) {
        $this->pdo = $pdo ?: Database::getConnection();
    }

    /**
     * Get all roles
     */
    public function getRoles(): array {
        $stmt = $this->pdo->query("SELECT * FROM roles ORDER BY id ASC");
        $roles = $stmt->fetchAll();
        foreach ($roles as &$role) {
            $role['permissions_array'] = json_decode($role['permissions'] ?? '[]', true) ?: [];
        }
        return $roles;
    }

    /**
     * Get role by code
     */
    public function getRole(string $code): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM roles WHERE code = ?");
        $stmt->execute([$code]);
        $role = $stmt->fetch();
        if ($role) {
            $role['permissions_array'] = json_decode($role['permissions'] ?? '[]', true) ?: [];
        }
        return $role ?: null;
    }

    /**
     * Get all system users with their role details
     */
    public function getUsers(): array {
        $stmt = $this->pdo->query("
            SELECT u.*, r.name as role_name, r.permissions as role_permissions
            FROM system_users u
            LEFT JOIN roles r ON u.role_code = r.code
            ORDER BY u.id ASC
        ");
        $users = $stmt->fetchAll();
        foreach ($users as &$u) {
            $u['permissions_array'] = json_decode($u['role_permissions'] ?? '[]', true) ?: [];
        }
        return $users;
    }

    /**
     * Get current active user (supports session, header, cookie or fallback)
     */
    public function getCurrentUser(): array {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }

        $userId = null;

        // 1. Query param override (for testing/switching)
        if (isset($_GET['user_id'])) {
            $userId = (int)$_GET['user_id'];
            if (session_status() === PHP_SESSION_ACTIVE) {
                $_SESSION['current_user_id'] = $userId;
            }
        } elseif (isset($_GET['role'])) {
            $roleCode = $_GET['role'];
            $stmt = $this->pdo->prepare("SELECT id FROM system_users WHERE role_code = ? AND is_active = 1 LIMIT 1");
            $stmt->execute([$roleCode]);
            $foundId = $stmt->fetchColumn();
            if ($foundId) {
                $userId = (int)$foundId;
                if (session_status() === PHP_SESSION_ACTIVE) {
                    $_SESSION['current_user_id'] = $userId;
                }
            }
        }

        // 2. HTTP Header
        if (!$userId && isset($_SERVER['HTTP_X_USER_ID'])) {
            $userId = (int)$_SERVER['HTTP_X_USER_ID'];
        }

        // 3. Session
        if (!$userId && isset($_SESSION['current_user_id'])) {
            $userId = (int)$_SESSION['current_user_id'];
        }

        // 4. Default to active Admin or first user
        if (!$userId) {
            $adminStmt = $this->pdo->query("
                SELECT u.id
                FROM system_users u
                WHERE u.is_active = 1
                ORDER BY CASE WHEN u.role_code = 'admin' THEN 0 ELSE 1 END, u.id ASC
                LIMIT 1
            ");
            $userId = (int)($adminStmt->fetchColumn() ?: 1);
        }

        $stmt = $this->pdo->prepare("
            SELECT u.*, r.name as role_name, r.permissions as role_permissions
            FROM system_users u
            LEFT JOIN roles r ON u.role_code = r.code
            WHERE u.id = ?
        ");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!$user) {
            // Fallback to first available active user, prioritizing admin
            $stmt = $this->pdo->query("
                SELECT u.*, r.name as role_name, r.permissions as role_permissions
                FROM system_users u
                LEFT JOIN roles r ON u.role_code = r.code
                WHERE u.is_active = 1
                ORDER BY CASE WHEN u.role_code = 'admin' THEN 0 ELSE 1 END, u.id ASC
                LIMIT 1
            ");
            $user = $stmt->fetch();
        }

        if ($user) {
            $user['permissions_array'] = json_decode($user['role_permissions'] ?? '[]', true) ?: [];
        }

        return $user ?: [
            'id' => 1,
            'bitrix_user_id' => 47141,
            'name' => 'Ադմինիստրատոր',
            'role_code' => 'admin',
            'role_name' => 'Ադմինիստրատոր / Տնօրեն',
            'permissions_array' => array_keys(self::PERMISSIONS),
        ];
    }

    /**
     * Set active user ID in session
     */
    public function setCurrentUserId(int $userId): bool {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }
        $_SESSION['current_user_id'] = $userId;
        return true;
    }

    /**
     * Check if user has specific permission
     */
    public function can(string $permissionCode, ?int $userId = null): bool {
        if ($userId !== null) {
            $stmt = $this->pdo->prepare("
                SELECT r.permissions 
                FROM system_users u
                LEFT JOIN roles r ON u.role_code = r.code
                WHERE u.id = ?
            ");
            $stmt->execute([$userId]);
            $permJson = $stmt->fetchColumn();
            $perms = json_decode($permJson ?: '[]', true) ?: [];
            return in_array($permissionCode, $perms, true);
        }

        $currentUser = $this->getCurrentUser();
        return in_array($permissionCode, $currentUser['permissions_array'] ?? [], true);
    }

    /**
     * Require permission or abort with 403 JSON error
     */
    public function requirePermission(string $permissionCode): void {
        if (!$this->can($permissionCode)) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'error' => 'Մուտքն արգելված է: Ձեր պաշտոնը չունի «' . (self::PERMISSIONS[$permissionCode] ?? $permissionCode) . '» իրավունքը:',
                'required_permission' => $permissionCode,
                'current_user' => $this->getCurrentUser(),
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }
    }

    /**
     * Update user role
     */
    public function updateUserRole(int $userId, string $roleCode): array {
        $stmt = $this->pdo->prepare("SELECT id FROM roles WHERE code = ?");
        $stmt->execute([$roleCode]);
        if (!$stmt->fetch()) {
            return ['success' => false, 'error' => 'Անվավեր դեր'];
        }

        $upd = $this->pdo->prepare("UPDATE system_users SET role_code = ? WHERE id = ?");
        $upd->execute([$roleCode, $userId]);

        return ['success' => true, 'message' => 'Օգտատիրոջ պաշտոնը հաջողությամբ թարմացվեց'];
    }

    /**
     * Update role permissions
     */
    public function updateRolePermissions(string $roleCode, array $permissions): array {
        $validPerms = array_keys(self::PERMISSIONS);
        $filtered = array_values(array_intersect($permissions, $validPerms));

        $json = json_encode($filtered, JSON_UNESCAPED_UNICODE);
        $stmt = $this->pdo->prepare("UPDATE roles SET permissions = ? WHERE code = ?");
        $stmt->execute([$json, $roleCode]);

        return ['success' => true, 'message' => 'Դերի իրավունքները հաջողությամբ պահպանվեցին'];
    }
}
