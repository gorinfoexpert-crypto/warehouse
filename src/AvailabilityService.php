<?php
/**
 * AvailabilityService - Core ATP (Available To Promise) Engine
 * 
 * Performs chronological timeline calculations for product availability
 * taking into account physical stock, confirmed future shipments, and active reservations.
 * Natural Armenian Language Support (Հայերեն)
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/BitrixRestClient.php';

class AvailabilityService {
    private PDO $pdo;

    public function __construct(?PDO $pdo = null) {
        $this->pdo = $pdo ?: Database::getConnection();
    }

    /**
     * Calculate availability for a product on a specific target date and quantity.
     * 
     * @param int $bitrixProductId Product ID
     * @param string $targetDate Required delivery date (YYYY-MM-DD)
     * @param float $requestedQuantity Quantity customer wants to order
     * @param int|null $excludeReservationId Optional reservation ID to exclude (e.g. when updating existing)
     * @return array Comprehensive calculation result with timeline, breakdown, and fulfillment verdict
     */
    public function calculateATP(
        int $bitrixProductId,
        string $targetDate,
        float $requestedQuantity = 1.0,
        ?int $excludeReservationId = null
    ): array {
        $today = date('Y-m-d');
        $targetDate = empty($targetDate) ? $today : date('Y-m-d', strtotime($targetDate));

        // 1. Fetch product basic physical stock
        $stmt = $this->pdo->prepare("SELECT * FROM products WHERE bitrix_product_id = ?");
        $stmt->execute([$bitrixProductId]);
        $product = $stmt->fetch();

        if (!$product) {
            // Fetch dynamically from Bitrix24 REST API
            $bitrix = new BitrixRestClient();
            $details = $bitrix->getProductDetails($bitrixProductId);
            if ($details) {
                $productName = $details['name'] ?? $details['NAME'] ?? ("Ապրանք #" . $bitrixProductId);
                $sku = $details['code'] ?? $details['CODE'] ?? "";
                $price = (float)($details['price'] ?? $details['PRICE'] ?? 0);
                
                // Fetch stock
                $stockRes = $bitrix->getStoreProductList(['=productId' => $bitrixProductId]);
                $stockItems = $stockRes['result']['storeProducts'] ?? $stockRes['result'] ?? [];
                $amount = 0.0;
                $reserved = 0.0;
                if (!empty($stockItems) && is_array($stockItems)) {
                    foreach ($stockItems as $si) {
                        $amount += (float)($si['amount'] ?? $si['AMOUNT'] ?? 0);
                        $reserved += (float)($si['quantityReserved'] ?? $si['QUANTITY_RESERVED'] ?? 0);
                    }
                }
                
                $ins = $this->pdo->prepare("
                    INSERT INTO products (bitrix_product_id, name, sku, current_stock, reserved_stock, unit, price, currency, updated_at)
                    VALUES (?, ?, ?, ?, ?, 'հատ', ?, 'AMD', datetime('now'))
                ");
                $ins->execute([$bitrixProductId, $productName, $sku, $amount, $reserved, $price]);
                
                // Refetch
                $stmt->execute([$bitrixProductId]);
                $product = $stmt->fetch();
            }
        }

        if (!$product) {
            return [
                'success' => false,
                'error' => 'Ապրանքը չի գտնվել համակարգում',
                'bitrix_product_id' => $bitrixProductId,
            ];
        }

        $physicalStock = (float)$product['current_stock'];
        $physicalReserved = (float)$product['reserved_stock'];
        $freeNow = max(0.0, $physicalStock - $physicalReserved);
        $unit = $product['unit'] ?: 'հատ';

        // 2. Fetch all confirmed/in-transit incoming shipments up to target date
        $shipmentsStmt = $this->pdo->prepare("
            SELECT id, bitrix_product_id, supplier_name, quantity, expected_date, status, notes
            FROM incoming_shipments
            WHERE bitrix_product_id = ?
              AND status IN ('CONFIRMED', 'IN_TRANSIT')
              AND expected_date <= ?
            ORDER BY expected_date ASC, id ASC
        ");
        $shipmentsStmt->execute([$bitrixProductId, $targetDate]);
        $incomingShipments = $shipmentsStmt->fetchAll();

        // 3. Fetch all active reservations up to target date
        $resQuery = "
            SELECT id, deal_id, bitrix_product_id, quantity, delivery_date, status, manager_name, customer_name, notes
            FROM product_reservations
            WHERE bitrix_product_id = ?
              AND status IN ('RESERVED', 'CONFIRMED')
              AND delivery_date <= ?
        ";
        $resParams = [$bitrixProductId, $targetDate];
        if ($excludeReservationId !== null) {
            $resQuery .= " AND id != ?";
            $resParams[] = $excludeReservationId;
        }
        $resQuery .= " ORDER BY delivery_date ASC, id ASC";

        $resStmt = $this->pdo->prepare($resQuery);
        $resStmt->execute($resParams);
        $activeReservations = $resStmt->fetchAll();

        // 4. Fetch planned (unconfirmed) shipments for advisory notice
        $plannedStmt = $this->pdo->prepare("
            SELECT id, supplier_name, quantity, expected_date, notes
            FROM incoming_shipments
            WHERE bitrix_product_id = ?
              AND status = 'PLANNED'
              AND expected_date <= ?
            ORDER BY expected_date ASC
        ");
        $plannedStmt->execute([$bitrixProductId, $targetDate]);
        $plannedShipments = $plannedStmt->fetchAll();

        // 5. Build unified chronological timeline
        $timelineEvents = [];

        // Initial state at T0 (Today): Physical stock
        $timelineEvents[] = [
            'date' => $today,
            'type' => 'INITIAL',
            'title' => 'Առկա մնացորդ պահեստում',
            'change' => $physicalStock,
            'details' => "Ֆիզիկապես պահեստում կա՝ {$physicalStock} {$unit}",
            'source_id' => null,
            'status' => 'ACTIVE',
        ];

        // Add confirmed incoming shipments to timeline
        $totalConfirmedIncoming = 0.0;
        foreach ($incomingShipments as $shipment) {
            $qty = (float)$shipment['quantity'];
            $totalConfirmedIncoming += $qty;
            $statusLabel = $shipment['status'] === 'IN_TRANSIT' ? 'Ճանապարհին է' : 'Հաստատված է';
            $timelineEvents[] = [
                'date' => $shipment['expected_date'],
                'type' => 'INCOMING',
                'title' => "Սպասվող մուտք՝ «{$shipment['supplier_name']}»",
                'change' => +$qty,
                'details' => "Մատակարարման համար՝ #{$shipment['id']} ({$statusLabel})",
                'source_id' => $shipment['id'],
                'status' => $shipment['status'],
            ];
        }

        // Add active reservations to timeline
        $totalReservations = 0.0;
        foreach ($activeReservations as $res) {
            $qty = (float)$res['quantity'];
            $totalReservations += $qty;
            $timelineEvents[] = [
                'date' => $res['delivery_date'],
                'type' => 'RESERVATION',
                'title' => "Ամրագրում Գործարք #{$res['deal_id']}-ի համար",
                'change' => -$qty,
                'details' => "Հաճախորդ՝ {$res['customer_name']} (Մենեջեր՝ {$res['manager_name']})",
                'source_id' => $res['id'],
                'status' => $res['status'],
            ];
        }

        // Sort timeline strictly by date
        usort($timelineEvents, function ($a, $b) {
            if ($a['date'] === $b['date']) {
                $priority = ['INITIAL' => 1, 'INCOMING' => 2, 'RESERVATION' => 3, 'REQUEST' => 4];
                return ($priority[$a['type']] ?? 5) <=> ($priority[$b['type']] ?? 5);
            }
            return strcmp($a['date'], $b['date']);
        });

        // Compute running balance step-by-step
        $runningBalance = 0.0;
        $timelineWithBalances = [];
        $minRunningBalance = PHP_FLOAT_MAX;

        foreach ($timelineEvents as $event) {
            $runningBalance += (float)$event['change'];
            $event['balance_after'] = $runningBalance;
            $timelineWithBalances[] = $event;
            if ($runningBalance < $minRunningBalance) {
                $minRunningBalance = $runningBalance;
            }
        }

        // The available stock on target date
        $atpAvailable = max(0.0, $runningBalance);

        // 6. Detailed Fulfillment Analysis
        $coveredFromCurrent = min($freeNow, $requestedQuantity);
        $coveredFromShipments = max(0.0, min($requestedQuantity - $coveredFromCurrent, $atpAvailable - $coveredFromCurrent));
        $shortage = max(0.0, $requestedQuantity - $atpAvailable);

        $status = 'AVAILABLE';
        $statusText = 'Ապրանքը լիարժեք հասանելի է';
        $statusClass = 'success';
        $canFulfill = true;

        if ($atpAvailable <= 0) {
            $status = 'UNAVAILABLE';
            $statusText = 'Նշված օրվա դրությամբ ապրանք չկա';
            $statusClass = 'danger';
            $canFulfill = false;
        } elseif ($atpAvailable < $requestedQuantity) {
            $status = 'PARTIAL';
            $statusText = "Հասանելի է մասամբ ({$atpAvailable} / {$requestedQuantity} {$unit})";
            $statusClass = 'warning';
            $canFulfill = false;
        }

        // Generate Human-friendly Armenian explanation message
        $messageLines = [];
        $targetFormatted = $this->formatDateArm($targetDate);

        if ($canFulfill) {
            $messageLines[] = "Պահանջվող {$requestedQuantity} {$unit}-ը հնարավոր է առաքել մինչև {$targetFormatted} թ.:";
            if ($coveredFromCurrent >= $requestedQuantity) {
                $messageLines[] = "• Ամբողջ քանակն այս պահին ազատ է պահեստում և պատրաստ է ամրագրման:";
            } else {
                if ($coveredFromCurrent > 0) {
                    $messageLines[] = "• {$coveredFromCurrent} {$unit}-ն առկա է պահեստում հենց հիմա:";
                }
                $needFromShipments = $requestedQuantity - $coveredFromCurrent;
                $messageLines[] = "• {$needFromShipments} {$unit}-ը կհամալրվի հաստատված մատակարարումից մինչև {$targetFormatted}:";
            }
        } else {
            if ($atpAvailable > 0) {
                $messageLines[] = "Հնարավոր է ապահովել միայն {$atpAvailable} {$unit}՝ պահանջվող {$requestedQuantity} {$unit}-ից:";
                $messageLines[] = "• Պակասորդը {$targetFormatted}-ի դրությամբ կազմում է՝ {$shortage} {$unit}:";
            } else {
                $messageLines[] = "Նշված օրվա դրությամբ ազատ ապրանք չկա (0 {$unit}):";
            }
        }

        // 7. Find earliest future date where full requested quantity becomes available
        $earliestFullDate = null;
        if (!$canFulfill) {
            $earliestFullDate = $this->findEarliestFullFulfillmentDate($bitrixProductId, $requestedQuantity, $excludeReservationId);
        }

        return [
            'success' => true,
            'product' => [
                'id' => (int)$product['id'],
                'bitrix_product_id' => (int)$product['bitrix_product_id'],
                'name' => $product['name'],
                'sku' => $product['sku'],
                'unit' => $product['unit'],
                'price' => (float)$product['price'],
                'currency' => $product['currency'],
            ],
            'request' => [
                'target_date' => $targetDate,
                'target_date_formatted' => $targetFormatted,
                'requested_quantity' => $requestedQuantity,
            ],
            'stock_breakdown' => [
                'physical_stock' => $physicalStock,
                'physical_reserved' => $physicalReserved,
                'free_now' => $freeNow,
                'incoming_confirmed' => $totalConfirmedIncoming,
                'active_reservations' => $totalReservations,
                'atp_available' => $atpAvailable,
                'covered_from_current' => $coveredFromCurrent,
                'covered_from_shipments' => $coveredFromShipments,
                'shortage' => $shortage,
            ],
            'verdict' => [
                'status' => $status,
                'status_text' => $statusText,
                'status_class' => $statusClass,
                'can_fulfill' => $canFulfill,
                'message' => implode("\n", $messageLines),
                'earliest_full_date' => $earliestFullDate,
                'earliest_full_date_formatted' => $earliestFullDate ? $this->formatDateArm($earliestFullDate) : null,
            ],
            'planned_shipments' => array_map(function($ps) {
                return [
                    'id' => $ps['id'],
                    'supplier_name' => $ps['supplier_name'],
                    'quantity' => (float)$ps['quantity'],
                    'expected_date' => $ps['expected_date'],
                    'expected_date_formatted' => $this->formatDateArm($ps['expected_date']),
                    'notes' => $ps['notes'],
                ];
            }, $plannedShipments),
            'timeline' => $timelineWithBalances,
        ];
    }

    /**
     * Find earliest date in the future when a given quantity will be available
     */
    private function findEarliestFullFulfillmentDate(int $bitrixProductId, float $requestedQuantity, ?int $excludeReservationId = null): ?string {
        $stmt = $this->pdo->prepare("
            SELECT expected_date 
            FROM incoming_shipments 
            WHERE bitrix_product_id = ? 
              AND status IN ('CONFIRMED', 'IN_TRANSIT')
              AND expected_date >= date('now')
            ORDER BY expected_date ASC
        ");
        $stmt->execute([$bitrixProductId]);
        $dates = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($dates as $date) {
            $check = $this->calculateATP($bitrixProductId, $date, $requestedQuantity, $excludeReservationId);
            if ($check['stock_breakdown']['atp_available'] >= $requestedQuantity) {
                return $date;
            }
        }
        return null;
    }

    /**
     * Helper to format date (DD.MM.YYYY)
     */
    public function formatDateArm(string $dateStr): string {
        $timestamp = strtotime($dateStr);
        if (!$timestamp) return $dateStr;
        return date('d.m.Y', $timestamp);
    }
}
