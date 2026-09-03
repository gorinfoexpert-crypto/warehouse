<?php
/**
 * Settings API Endpoint
 * Handles fetching, updating system settings, and testing REST API connection
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/AuthService.php';
require_once __DIR__ . '/../src/BitrixRestClient.php';

$pdo = Database::getConnection();
$auth = new AuthService($pdo);

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $action = $_GET['action'] ?? '';

    // Action: Test REST Connection
    if ($action === 'test_connection') {
        $webhookUrl = trim($_GET['webhook_url'] ?? '');
        if (empty($webhookUrl)) {
            $stmt = $pdo->prepare("SELECT value FROM settings WHERE key = 'bitrix_webhook_url'");
            $stmt->execute();
            $webhookUrl = $stmt->fetchColumn() ?: '';
        }

        if (empty($webhookUrl)) {
            echo json_encode([
                'success' => false,
                'connected' => false,
                'error' => 'Վեբհուկի հասցեն լրացված չէ: Մուտքագրեք վեբհուկի URL-ը:'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $client = new BitrixRestClient($webhookUrl);
        
        // Test basic REST call: catalog.store.list or crm.deal.get or app.info
        $testRes = $client->call('catalog.store.list', ['select' => ['id', 'title']]);

        if (isset($testRes['error'])) {
            // Try another basic CRM call in case catalog scope is separate
            $crmTest = $client->call('crm.deal.get', ['id' => 1]);
            if (isset($crmTest['error']) && $crmTest['error'] !== 'NOT_FOUND') {
                echo json_encode([
                    'success' => false,
                    'connected' => false,
                    'error' => 'REST կապի սխալ. ' . ($testRes['error_description'] ?? $crmTest['error_description'] ?? 'Անհայտ սխալ'),
                    'raw' => $testRes
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
        }

        $domain = parse_url($webhookUrl, PHP_URL_HOST) ?: 'CRM';
        echo json_encode([
            'success' => true,
            'connected' => true,
            'domain' => $domain,
            'message' => "REST API կապը հաջողությամբ հաստատված է ({$domain}): Համակարգը պատրաստ է աշխատանքի:"
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    try {
        $stmt = $pdo->query("SELECT key, value FROM settings");
        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        echo json_encode([
            'success' => true,
            'settings' => [
                'bitrix_webhook_url' => $settings['bitrix_webhook_url'] ?? '',
                'default_store_id' => $settings['default_store_id'] ?? '1',
                'reservation_ttl_days' => $settings['reservation_ttl_days'] ?? '7',
            ]
        ], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }

    $action = $input['action'] ?? '';
    if ($action === 'purge_test_data') {
        $auth->requirePermission('manage_settings');
        
        $tables = ['stock_movements', 'product_reservations', 'incoming_shipments', 'stock_history_logs'];
        foreach ($tables as $table) {
            $pdo->exec("DELETE FROM {$table}");
        }
        // Purge mock users with bitrix_user_id = 0
        $pdo->exec("DELETE FROM system_users WHERE bitrix_user_id = 0 OR email LIKE '%@company.am'");

        echo json_encode([
            'success' => true,
            'message' => 'Բոլոր թեստային ապրանքները, մատակարարումները, ամրագրումները և օգտատերերը հաջողությամբ ջնջվեցին: Համակարգը պատրաստ է Bitrix24-ի հետ աշխատանքին:'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $webhook = trim($input['webhook_url'] ?? $input['bitrix_webhook_url'] ?? '');
    $storeId = trim($input['default_store'] ?? $input['default_store_id'] ?? '1');
    $ttlDays = trim($input['ttl_days'] ?? $input['reservation_ttl_days'] ?? '7');

    try {
        $upsert = function($key, $value) use ($pdo) {
            $now = date('Y-m-d H:i:s');
            try {
                $stmt = $pdo->prepare("INSERT INTO settings (key, value, updated_at) VALUES (?, ?, ?) ON CONFLICT(key) DO UPDATE SET value = excluded.value, updated_at = excluded.updated_at");
                $stmt->execute([$key, $value, $now]);
            } catch (Exception $e) {
                $stmt = $pdo->prepare("UPDATE settings SET value = ?, updated_at = ? WHERE key = ?");
                $stmt->execute([$value, $now, $key]);
            }
        };

        $upsert('bitrix_webhook_url', $webhook);
        $upsert('default_store_id', $storeId);
        $upsert('reservation_ttl_days', $ttlDays);

        echo json_encode([
            'success' => true,
            'message' => 'Կարգավորումները հաջողությամբ պահպանվեցին:'
        ], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}
