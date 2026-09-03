<?php
/**
 * API: Dashboard KPIs
 * GET /api/dashboard.php
 * 
 * Returns aggregated warehouse metrics per TZ specification:
 * - Summary KPIs (total quantity, reserved, available, values)
 * - Per-product detail with coverage days, turnover, risk status
 * - Risk lists (shortage, critical, excess)
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$pdo = Database::getConnection();

// --- Load settings for threshold configuration ---
$settingsStmt = $pdo->query("SELECT key, value FROM settings WHERE key IN ('consumption_period_days', 'critical_coverage_days', 'warning_coverage_days')");
$settings = [];
while ($row = $settingsStmt->fetch()) {
    $settings[$row['key']] = (int) $row['value'];
}
$consumptionPeriod = $settings['consumption_period_days'] ?? 30;
$criticalDays = $settings['critical_coverage_days'] ?? 3;
$warningDays = $settings['warning_coverage_days'] ?? 7;

// --- Fetch all products with computed fields ---
$query = "
    SELECT 
        p.id,
        p.bitrix_product_id,
        p.name,
        p.sku,
        p.current_stock,
        p.reserved_stock,
        (p.current_stock - p.reserved_stock) AS available_stock,
        p.unit,
        p.price,
        p.cost_price,
        p.min_stock,
        p.max_stock,
        p.currency,
        p.updated_at,
        
        -- Warehouse value = current_stock * price
        (p.current_stock * p.price) AS warehouse_value,
        
        -- Free stock value = available * price
        ((p.current_stock - p.reserved_stock) * p.price) AS free_stock_value,
        
        -- Reserve percentage = reserved / current_stock * 100
        CASE WHEN p.current_stock > 0 
            THEN ROUND(p.reserved_stock * 100.0 / p.current_stock, 1)
            ELSE 0 
        END AS reserve_percent,
        
        -- Total consumption in period (OUT movements)
        COALESCE((
            SELECT SUM(sm.quantity) 
            FROM stock_movements sm 
            WHERE sm.bitrix_product_id = p.bitrix_product_id 
              AND sm.direction = 'OUT'
              AND sm.movement_date >= date('now', '-' || :period1 || ' days')
        ), 0) AS consumed_period,
        
        -- Number of days with actual movement data
        COALESCE((
            SELECT COUNT(DISTINCT sm.movement_date) 
            FROM stock_movements sm 
            WHERE sm.bitrix_product_id = p.bitrix_product_id 
              AND sm.direction = 'OUT'
              AND sm.movement_date >= date('now', '-' || :period2 || ' days')
        ), 0) AS active_days,
        
        -- Incoming confirmed
        COALESCE((
            SELECT SUM(quantity) 
            FROM incoming_shipments 
            WHERE bitrix_product_id = p.bitrix_product_id 
              AND status IN ('CONFIRMED', 'IN_TRANSIT')
        ), 0) AS total_incoming,
        
        -- Active reservations
        COALESCE((
            SELECT SUM(quantity) 
            FROM product_reservations 
            WHERE bitrix_product_id = p.bitrix_product_id 
              AND status IN ('RESERVED', 'CONFIRMED')
        ), 0) AS total_reserved_orders
        
    FROM products p
    ORDER BY p.id ASC
";

$stmt = $pdo->prepare($query);
$stmt->execute([
    ':period1' => $consumptionPeriod,
    ':period2' => $consumptionPeriod,
]);
$products = $stmt->fetchAll();

// --- Calculate derived KPIs per product ---
$summary = [
    'total_quantity'     => 0,
    'total_reserved'     => 0,
    'total_available'    => 0,
    'warehouse_value'    => 0,
    'free_stock_value'   => 0,
    'avg_reserve_pct'    => 0,
    'products_count'     => count($products),
    'shortage_count'     => 0,
    'critical_count'     => 0,
    'excess_count'       => 0,
    'warning_count'      => 0,
];

$riskShortage = [];
$riskCritical = [];
$riskExcess   = [];
$riskWarning  = [];

foreach ($products as &$p) {
    $available = (float) $p['available_stock'];
    $currentStock = (float) $p['current_stock'];
    $consumed = (float) $p['consumed_period'];
    $minStock = (float) $p['min_stock'];
    $maxStock = (float) $p['max_stock'];
    
    // Avg daily consumption
    $avgDaily = $consumptionPeriod > 0 ? $consumed / $consumptionPeriod : 0;
    $p['avg_daily_consumption'] = round($avgDaily, 2);
    
    // Stock Coverage (days) = available / avg_daily
    if ($avgDaily > 0) {
        $p['coverage_days'] = round($available / $avgDaily, 1);
    } else {
        $p['coverage_days'] = $available > 0 ? 999 : 0; // 999 = no consumption history
    }
    
    // Stock Turnover = consumed / avg_stock (avg_stock ~ current_stock for now)
    $avgStock = $currentStock > 0 ? $currentStock : 1;
    $p['turnover'] = round($consumed / $avgStock, 2);
    
    // Risk Status
    $p['risk_status'] = 'normal';
    $p['risk_label'] = '';
    
    if ($currentStock <= 0 && $consumed > 0) {
        $p['risk_status'] = 'critical';
        $p['risk_label'] = "\u{054A}\u{0561}\u{056F}\u{0561}\u{057D}"; // Deficit
        $riskCritical[] = $p;
    } elseif ($available < $minStock && $minStock > 0) {
        $p['risk_status'] = 'shortage';
        $p['risk_label'] = "\u{054A}\u{0561}\u{056F}\u{0561}\u{057D}\u{056B} \u{057E}\u{057F}\u{0561}\u{0576}\u{0563}"; // Shortage risk
        $riskShortage[] = $p;
    }
    
    if ($p['coverage_days'] >= 0 && $p['coverage_days'] <= $criticalDays && $avgDaily > 0) {
        if ($p['risk_status'] !== 'critical') {
            $p['risk_status'] = 'critical';
            $p['risk_label'] = "\u{053F}\u{0580}\u{056B}\u{057F}\u{056B}\u{056F}\u{0561}\u{056F}\u{0561}\u{0576}"; // Critical
            if (!in_array($p, $riskCritical)) {
                $riskCritical[] = $p;
            }
        }
    } elseif ($p['coverage_days'] > $criticalDays && $p['coverage_days'] <= $warningDays && $avgDaily > 0) {
        if ($p['risk_status'] === 'normal') {
            $p['risk_status'] = 'warning';
            $p['risk_label'] = "\u{0546}\u{0561}\u{056D}\u{0561}\u{0566}\u{0563}\u{0578}\u{0582}\u{0577}\u{0561}\u{0581}\u{0578}\u{0582}\u{0574}"; // Warning
            $riskWarning[] = $p;
        }
    }
    
    if ($available > $maxStock && $maxStock > 0) {
        if ($p['risk_status'] === 'normal') {
            $p['risk_status'] = 'excess';
            $p['risk_label'] = "\u{0531}\u{057E}\u{0565}\u{056C}\u{0581}\u{0578}\u{0582}\u{056F}\u{0561}\u{0575}\u{056B}\u{0576}"; // Excess
        }
        $riskExcess[] = $p;
    }
    
    // Aggregate summary
    $summary['total_quantity']   += $currentStock;
    $summary['total_reserved']   += (float) $p['reserved_stock'];
    $summary['total_available']  += $available;
    $summary['warehouse_value']  += (float) $p['warehouse_value'];
    $summary['free_stock_value'] += (float) $p['free_stock_value'];
}
unset($p);

// Reserve percentage overall
if ($summary['total_quantity'] > 0) {
    $summary['avg_reserve_pct'] = round($summary['total_reserved'] * 100.0 / $summary['total_quantity'], 1);
}

$summary['shortage_count'] = count($riskShortage);
$summary['critical_count'] = count($riskCritical);
$summary['excess_count']   = count($riskExcess);
$summary['warning_count']  = count($riskWarning);

// Format currency values
$summary['reserved_value'] = max(0, $summary['warehouse_value'] - $summary['free_stock_value']);
$summary['warehouse_value_fmt'] = number_format($summary['warehouse_value'], 0, '.', ' ');
$summary['free_stock_value_fmt'] = number_format($summary['free_stock_value'], 0, '.', ' ');
$summary['reserved_value_fmt'] = number_format($summary['reserved_value'], 0, '.', ' ');

echo json_encode([
    'success' => true,
    'summary' => $summary,
    'products' => $products,
    'risk_shortage' => $riskShortage,
    'risk_critical' => $riskCritical,
    'risk_excess'   => $riskExcess,
    'risk_warning'  => $riskWarning,
    'settings' => [
        'consumption_period_days' => $consumptionPeriod,
        'critical_coverage_days'  => $criticalDays,
        'warning_coverage_days'   => $warningDays,
    ],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
