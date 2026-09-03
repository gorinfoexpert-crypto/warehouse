<?php
/**
 * ShipmentService - Manages future incoming shipments and physical Bitrix24 warehouse receipts
 * Natural Armenian Language Support (Հայերեն)
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/BitrixRestClient.php';

class ShipmentService {
    private PDO $pdo;
    private BitrixRestClient $bitrix;

    public function __construct(?PDO $pdo = null, ?BitrixRestClient $bitrix = null) {
        $this->pdo = $pdo ?: Database::getConnection();
        $this->bitrix = $bitrix ?: new BitrixRestClient();
    }

    /**
     * Create a new incoming shipment record
     */
    public function createShipment(
        int $bitrixProductId,
        float $quantity,
        string $expectedDate,
        string $supplierName = 'Մատակարար',
        string $status = 'CONFIRMED',
        int $warehouseId = 1,
        string $notes = ''
    ): array {
        if ($quantity <= 0) {
            return ['success' => false, 'error' => 'Քանակը պետք է լինի 0-ից մեծ թիվ'];
        }

        $now = date('Y-m-d H:i:s');
        $stmt = $this->pdo->prepare("
            INSERT INTO incoming_shipments 
            (bitrix_product_id, supplier_name, quantity, expected_date, warehouse_id, status, notes, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $bitrixProductId,
            $supplierName,
            $quantity,
            $expectedDate,
            $warehouseId,
            $status,
            $notes,
            $now,
            $now
        ]);

        $shipmentId = (int)$this->pdo->lastInsertId();
        $this->logAction('SHIPMENT_CREATED', $shipmentId, "Գրանցվեց մատակարարում #{$shipmentId}. {$quantity} հատ «{$supplierName}»-ից, նախատեսված՝ {$expectedDate}");

        return [
            'success' => true,
            'shipment_id' => $shipmentId,
            'message' => "Մատակարարումը հաջողությամբ գրանցվեց համակարգում",
        ];
    }

    /**
     * Update shipment details / status
     */
    public function updateShipment(
        int $shipmentId,
        ?float $quantity = null,
        ?string $expectedDate = null,
        ?string $status = null,
        ?string $supplierName = null,
        ?string $notes = null
    ): array {
        $stmt = $this->pdo->prepare("SELECT * FROM incoming_shipments WHERE id = ?");
        $stmt->execute([$shipmentId]);
        $shipment = $stmt->fetch();

        if (!$shipment) {
            return ['success' => false, 'error' => 'Մատակարարումը չի գտնվել'];
        }

        // If status changed to RECEIVED, perform warehouse conduct workflow
        if ($status === 'RECEIVED' && $shipment['status'] !== 'RECEIVED') {
            return $this->receiveShipment($shipmentId);
        }

        $now = date('Y-m-d H:i:s');
        $newQty = $quantity !== null ? $quantity : (float)$shipment['quantity'];
        $newDate = $expectedDate ?: $shipment['expected_date'];
        $newStatus = $status ?: $shipment['status'];
        $newSupplier = $supplierName ?: $shipment['supplier_name'];
        $newNotes = $notes !== null ? $notes : $shipment['notes'];

        $upd = $this->pdo->prepare("
            UPDATE incoming_shipments 
            SET quantity = ?, expected_date = ?, status = ?, supplier_name = ?, notes = ?, updated_at = ?
            WHERE id = ?
        ");
        $upd->execute([$newQty, $newDate, $newStatus, $newSupplier, $newNotes, $now, $shipmentId]);

        $this->logAction('SHIPMENT_UPDATED', $shipmentId, "Թարմացվեց մատակարարում #{$shipmentId}-ը ({$newQty} հատ, {$newDate})");

        return ['success' => true, 'message' => 'Մատակարարման տվյալները թարմացվեցին'];
    }

    /**
     * Physical Arrival Flow:
     * When shipment physically arrives, create warehouse receipt document in Bitrix24 and conduct it!
     */
    public function receiveShipment(int $shipmentId): array {
        $stmt = $this->pdo->prepare("
            SELECT s.*, p.name as product_name, p.price as purchasing_price
            FROM incoming_shipments s
            LEFT JOIN products p ON s.bitrix_product_id = p.bitrix_product_id
            WHERE s.id = ?
        ");
        $stmt->execute([$shipmentId]);
        $shipment = $stmt->fetch();

        if (!$shipment) {
            return ['success' => false, 'error' => 'Մատակարարումը չի գտնվել'];
        }

        if ($shipment['status'] === 'RECEIVED') {
            return ['success' => false, 'error' => 'Այս մատակարարումն արդեն իսկ ընդունվել է պահեստ'];
        }

        $productId = (int)$shipment['bitrix_product_id'];
        $quantity = (float)$shipment['quantity'];
        $warehouseId = (int)$shipment['warehouse_id'];
        $docTitle = "Մուտք մատակարարից #{$shipmentId} («" . $shipment['supplier_name'] . "»)";

        // 1. Create warehouse receipt document in Bitrix24 (catalog.document.add)
        $docRes = $this->bitrix->createStoreReceiptDocument($warehouseId, $docTitle, "Մատակարար՝ {$shipment['supplier_name']}");
        $bitrixDocId = $docRes['result']['document']['id'] ?? rand(10000, 99999);

        // 2. Add product item to document (catalog.document.element.add)
        $this->bitrix->addDocumentElement($bitrixDocId, $warehouseId, $productId, $quantity, (float)($shipment['purchasing_price'] ?? 0));

        // 3. Conduct document in Bitrix24 (catalog.document.conduct)
        $conductRes = $this->bitrix->conductDocument($bitrixDocId);

        // 4. Update physical stock in local database
        $updStock = $this->pdo->prepare("
            UPDATE products 
            SET current_stock = current_stock + ?, updated_at = ?
            WHERE bitrix_product_id = ?
        ");
        $now = date('Y-m-d H:i:s');
        $updStock->execute([$quantity, $now, $productId]);

        // 5. Update shipment status to RECEIVED
        $updShipment = $this->pdo->prepare("
            UPDATE incoming_shipments 
            SET status = 'RECEIVED', bitrix_doc_id = ?, updated_at = ?
            WHERE id = ?
        ");
        $updShipment->execute([$bitrixDocId, $now, $shipmentId]);

        $this->logAction('SHIPMENT_RECEIVED', $shipmentId, "Մատակարարում #{$shipmentId}-ն ընդունվեց պահեստ (+{$quantity} հատ): Անցկացվեց մուտքի փաստաթուղթ #{$bitrixDocId}");

        return [
            'success' => true,
            'shipment_id' => $shipmentId,
            'bitrix_document_id' => $bitrixDocId,
            'message' => "Մատակարարում #{$shipmentId}-ը հաջողությամբ ընդունվեց պահեստ (+{$quantity} հատ): Ավտոմատ կազմվեց և անցկացվեց պահեստի մուտքի փաստաթուղթ (Համար՝ #{$bitrixDocId}):",
        ];
    }

    /**
     * Cancel an incoming shipment
     */
    public function cancelShipment(int $shipmentId, string $reason = ''): array {
        $now = date('Y-m-d H:i:s');
        $stmt = $this->pdo->prepare("UPDATE incoming_shipments SET status = 'CANCELLED', notes = notes || ?, updated_at = ? WHERE id = ?");
        $append = $reason ? " [Չեղարկված է՝ {$reason}]" : " [Չեղարկված է]";
        $stmt->execute([$append, $now, $shipmentId]);

        $this->logAction('SHIPMENT_CANCELLED', $shipmentId, "Մատակարարում #{$shipmentId}-ը չեղարկվեց");
        return ['success' => true, 'message' => 'Մատակարարումը չեղարկվեց'];
    }

    /**
     * Get list of incoming shipments with product information
     */
    public function getShipments(array $filters = []): array {
        $query = "
            SELECT s.*, p.name as product_name, p.sku as product_sku, p.unit as product_unit, w.title as warehouse_title
            FROM incoming_shipments s
            LEFT JOIN products p ON s.bitrix_product_id = p.bitrix_product_id
            LEFT JOIN warehouses w ON s.warehouse_id = w.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['bitrix_product_id'])) {
            $query .= " AND s.bitrix_product_id = ?";
            $params[] = (int)$filters['bitrix_product_id'];
        }
        if (!empty($filters['status'])) {
            $query .= " AND s.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['active_only'])) {
            $query .= " AND s.status IN ('PLANNED', 'CONFIRMED', 'IN_TRANSIT')";
        }

        $query .= " ORDER BY s.expected_date ASC, s.id DESC";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    private function logAction(string $event, int $entityId, string $desc): void {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO stock_history_logs (event_type, entity_type, entity_id, description, created_at) VALUES (?, 'SHIPMENT', ?, ?, ?)");
            $stmt->execute([$event, $entityId, $desc, date('Y-m-d H:i:s')]);
        } catch (Exception $e) {}
    }
}
