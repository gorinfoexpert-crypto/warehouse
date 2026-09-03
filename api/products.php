<?php
/**
 * API: Products Catalog & Stock Matrix
 * GET /api/products.php
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$pdo = Database::getConnection();

// POST: Update Product Thresholds (Min stock alert & delivery lead time days)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true) ?: $_POST;
    $action = $input['action'] ?? '';

    if ($action === 'update_thresholds') {
        $productId = (int)($input['bitrix_product_id'] ?? 0);
        $minStock = max(0, (float)($input['min_stock'] ?? 0));
        $maxStock = max(0, (float)($input['max_stock'] ?? 0));
        $deliveryDays = max(1, (int)($input['delivery_days'] ?? 7));

        if (!$productId) {
            echo json_encode(['success' => false, 'error' => 'Ապրանքի ID-ն նշված չէ']);
            exit;
        }

        $stmt = $pdo->prepare("
            UPDATE products 
            SET min_stock = ?, max_stock = ?, delivery_days = ?, updated_at = ? 
            WHERE bitrix_product_id = ?
        ");
        $stmt->execute([$minStock, $maxStock, $deliveryDays, date('Y-m-d H:i:s'), $productId]);

        echo json_encode([
            'success' => true, 
            'message' => 'Ապրանքի նվազագույն շեմն ու մատակարարման ժամկետները հաջողությամբ պահպանվեցին'
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
}

$query = "
    SELECT 
        p.*,
        (p.current_stock - p.reserved_stock) AS free_stock,
        COALESCE((
            SELECT SUM(quantity) 
            FROM incoming_shipments 
            WHERE bitrix_product_id = p.bitrix_product_id 
              AND status IN ('CONFIRMED', 'IN_TRANSIT')
        ), 0) AS total_incoming_confirmed,
        COALESCE((
            SELECT SUM(quantity) 
            FROM incoming_shipments 
            WHERE bitrix_product_id = p.bitrix_product_id 
              AND status = 'PLANNED'
        ), 0) AS total_incoming_planned,
        COALESCE((
            SELECT SUM(quantity) 
            FROM product_reservations 
            WHERE bitrix_product_id = p.bitrix_product_id 
              AND status IN ('RESERVED', 'CONFIRMED')
        ), 0) AS total_active_reserved
    FROM products p
    ORDER BY p.id ASC
";

$stmt = $pdo->query($query);
$products = $stmt->fetchAll();

echo json_encode([
    'success' => true,
    'count' => count($products),
    'products' => $products
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
