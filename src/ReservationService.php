<?php
/**
 * ReservationService - Handles product reservations for Bitrix24 Deals
 * Natural Armenian Language Support (Հայերեն)
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/AvailabilityService.php';

class ReservationService {
    private PDO $pdo;
    private AvailabilityService $availabilityService;

    public function __construct(?PDO $pdo = null) {
        $this->pdo = $pdo ?: Database::getConnection();
        $this->availabilityService = new AvailabilityService($this->pdo);
    }

    /**
     * Create a new reservation for a Bitrix24 Deal
     */
    public function createReservation(
        int $dealId,
        int $bitrixProductId,
        float $quantity,
        string $deliveryDate,
        string $managerName = 'Մենեջեր',
        string $customerName = 'Հաճախորդ',
        string $notes = '',
        string $status = 'RESERVED'
    ): array {
        if ($quantity <= 0) {
            return ['success' => false, 'error' => 'Քանակը պետք է լինի 0-ից մեծ'];
        }

        // Validate availability before reserving
        $atp = $this->availabilityService->calculateATP($bitrixProductId, $deliveryDate, $quantity);
        if (!$atp['success']) {
            return ['success' => false, 'error' => $atp['error'] ?? 'Ապրանքը չի գտնվել'];
        }

        $availableQty = (float)$atp['stock_breakdown']['atp_available'];
        if ($availableQty < $quantity) {
            $formattedDate = $this->availabilityService->formatDateArm($deliveryDate);
            return [
                'success' => false,
                'error' => "Անբավարար քանակ {$formattedDate} թ. դրությամբ: Առկա է ընդամենը՝ {$availableQty} հատ, պահանջվում է՝ {$quantity} հատ:",
                'available_quantity' => $availableQty,
                'shortage' => $quantity - $availableQty,
            ];
        }

        $now = date('Y-m-d H:i:s');
        $stmt = $this->pdo->prepare("
            INSERT INTO product_reservations 
            (deal_id, bitrix_product_id, quantity, delivery_date, status, manager_name, customer_name, warehouse_id, notes, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?, ?, ?)
        ");
        $stmt->execute([
            $dealId,
            $bitrixProductId,
            $quantity,
            $deliveryDate,
            $status,
            $managerName,
            $customerName,
            $notes,
            $now,
            $now
        ]);

        $resId = (int)$this->pdo->lastInsertId();
        $formattedDate = $this->availabilityService->formatDateArm($deliveryDate);

        // Audit Log
        $this->logAction('RESERVATION_CREATED', $resId, "Ամրագրվեց {$quantity} հատ ապրանք (#{$bitrixProductId}) Գործարք #{$dealId}-ի համար մինչև {$formattedDate}");

        return [
            'success' => true,
            'reservation_id' => $resId,
            'message' => "Ապրանքը հաջողությամբ ամրագրվեց ({$quantity} հատ մինչև {$formattedDate}):",
        ];
    }

    /**
     * Update existing reservation
     */
    public function updateReservation(
        int $reservationId,
        float $quantity,
        string $deliveryDate,
        ?string $status = null,
        ?string $notes = null
    ): array {
        $stmt = $this->pdo->prepare("SELECT * FROM product_reservations WHERE id = ?");
        $stmt->execute([$reservationId]);
        $res = $stmt->fetch();

        if (!$res) {
            return ['success' => false, 'error' => 'Ամրագրումը չի գտնվել'];
        }

        // Validate availability excluding this reservation
        $atp = $this->availabilityService->calculateATP((int)$res['bitrix_product_id'], $deliveryDate, $quantity, $reservationId);
        $availableQty = (float)$atp['stock_breakdown']['atp_available'];

        if ($availableQty < $quantity) {
            return [
                'success' => false,
                'error' => "Ապրանքի քանակը բավարար չէ նոր օրվա/քանակի համար: Հասանելի է ընդամենը՝ {$availableQty} հատ:",
            ];
        }

        $now = date('Y-m-d H:i:s');
        $newStatus = $status ?: $res['status'];
        $newNotes = $notes !== null ? $notes : $res['notes'];

        $upd = $this->pdo->prepare("
            UPDATE product_reservations 
            SET quantity = ?, delivery_date = ?, status = ?, notes = ?, updated_at = ?
            WHERE id = ?
        ");
        $upd->execute([$quantity, $deliveryDate, $newStatus, $newNotes, $now, $reservationId]);

        $this->logAction('RESERVATION_UPDATED', $reservationId, "Թարմացվեց ամրագրում #{$reservationId}-ը. {$quantity} հատ, ամսաթիվ՝ {$deliveryDate}");

        return ['success' => true, 'message' => 'Ամրագրման տվյալները թարմացվեցին'];
    }

    /**
     * Cancel a reservation
     */
    public function cancelReservation(int $reservationId, string $reason = ''): array {
        $stmt = $this->pdo->prepare("SELECT * FROM product_reservations WHERE id = ?");
        $stmt->execute([$reservationId]);
        $res = $stmt->fetch();

        if (!$res) {
            return ['success' => false, 'error' => 'Ամրագրումը չի գտնվել'];
        }

        $now = date('Y-m-d H:i:s');
        $upd = $this->pdo->prepare("UPDATE product_reservations SET status = 'CANCELLED', notes = notes || ?, updated_at = ? WHERE id = ?");
        $noteAppend = $reason ? " [Չեղարկված է՝ {$reason}]" : " [Չեղարկված է]";
        $upd->execute([$noteAppend, $now, $reservationId]);

        $this->logAction('RESERVATION_CANCELLED', $reservationId, "Չեղարկվեց ամրագրում #{$reservationId}-ը Գործարք #{$res['deal_id']}-ի համար");

        return ['success' => true, 'message' => 'Ամրագրումը հաջողությամբ չեղարկվեց'];
    }

    /**
     * Confirm a reservation
     */
    public function confirmReservation(int $reservationId): array {
        $now = date('Y-m-d H:i:s');
        $stmt = $this->pdo->prepare("UPDATE product_reservations SET status = 'CONFIRMED', updated_at = ? WHERE id = ?");
        $stmt->execute([$now, $reservationId]);

        $this->logAction('RESERVATION_CONFIRMED', $reservationId, "Ամրագրում #{$reservationId}-ը հաստատվեց");
        return ['success' => true, 'message' => 'Ամրագրումը հաստատվեց'];
    }

    /**
     * Mark reservation as shipped
     */
    public function shipReservation(int $reservationId): array {
        $now = date('Y-m-d H:i:s');
        $stmt = $this->pdo->prepare("UPDATE product_reservations SET status = 'SHIPPED', updated_at = ? WHERE id = ?");
        $stmt->execute([$now, $reservationId]);

        $this->logAction('RESERVATION_SHIPPED', $reservationId, "Ապրանքը ամրագրում #{$reservationId}-ով առաքվեց");
        return ['success' => true, 'message' => 'Ապրանքը նշվեց որպես առաքված'];
    }

    /**
     * Get list of reservations with product names
     */
    public function getReservations(array $filters = []): array {
        $query = "
            SELECT r.*, p.name as product_name, p.sku as product_sku, p.unit as product_unit
            FROM product_reservations r
            LEFT JOIN products p ON r.bitrix_product_id = p.bitrix_product_id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['deal_id'])) {
            $query .= " AND r.deal_id = ?";
            $params[] = (int)$filters['deal_id'];
        }
        if (!empty($filters['bitrix_product_id'])) {
            $query .= " AND r.bitrix_product_id = ?";
            $params[] = (int)$filters['bitrix_product_id'];
        }
        if (!empty($filters['status'])) {
            $query .= " AND r.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['active_only'])) {
            $query .= " AND r.status IN ('RESERVED', 'CONFIRMED')";
        }

        $query .= " ORDER BY r.delivery_date ASC, r.id DESC";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    private function logAction(string $event, int $entityId, string $desc): void {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO stock_history_logs (event_type, entity_type, entity_id, description, created_at) VALUES (?, 'RESERVATION', ?, ?, ?)");
            $stmt->execute([$event, $entityId, $desc, date('Y-m-d H:i:s')]);
        } catch (Exception $e) {}
    }
}
