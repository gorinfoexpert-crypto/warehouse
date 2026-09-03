<?php
/**
 * API: Sync Products and Warehouses via REST API
 * Pure Natural Armenian Language (Հայերեն) with Role-Based Permission Verification
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/BitrixRestClient.php';
require_once __DIR__ . '/../src/AuthService.php';
require_once __DIR__ . '/../config/database.php';

$auth = new AuthService();
$auth->requirePermission('sync_bitrix');

$bitrix = new BitrixRestClient();
$pdo = Database::getConnection();

$action = $_GET['action'] ?? 'all';

$syncedStores = 0;
$syncedUsers = 0;
$syncedProducts = 0;

// 1. Fetch warehouses
if ($action === 'all') {
    $storesRes = $bitrix->getStoreList();
    $stores = $storesRes['result']['stores'] ?? $storesRes['result'] ?? [];
    $syncedStoreIds = [];

    if (is_array($stores)) {
        foreach ($stores as $store) {
            $storeId = (int)($store['id'] ?? $store['ID'] ?? 1);
            $title = $store['title'] ?? $store['TITLE'] ?? ('Պահեստ ' . $storeId);
            $address = $store['address'] ?? $store['ADDRESS'] ?? '';
            $active = (($store['active'] ?? $store['ACTIVE'] ?? 'Y') === 'Y') ? 1 : 0;
            $syncedStoreIds[] = $storeId;

            $check = $pdo->prepare("SELECT id FROM warehouses WHERE bitrix_store_id = ?");
            $check->execute([$storeId]);
            if ($check->fetch()) {
                $upd = $pdo->prepare("UPDATE warehouses SET title = ?, address = ?, is_active = ? WHERE bitrix_store_id = ?");
                $upd->execute([$title, $address, $active, $storeId]);
            } else {
                $ins = $pdo->prepare("INSERT INTO warehouses (bitrix_store_id, title, address, is_active) VALUES (?, ?, ?, ?)");
                $ins->execute([$storeId, $title, $address, $active]);
            }
            $syncedStores++;
        }

        // Purge mock warehouses not found in real Bitrix24
        if ($bitrix->isConfigured() && !empty($syncedStoreIds)) {
            $placeholders = implode(',', array_fill(0, count($syncedStoreIds), '?'));
            $del = $pdo->prepare("DELETE FROM warehouses WHERE bitrix_store_id NOT IN ($placeholders)");
            $del->execute($syncedStoreIds);
        }
    }
}

// 1b. Fetch and sync system users/employees from Bitrix24
$usersRes = $bitrix->getUserList();
$b24Users = $usersRes['result'] ?? [];
$syncedUserIds = [];

if (is_array($b24Users)) {
    foreach ($b24Users as $u) {
        $userId = (int)($u['ID'] ?? 0);
        if ($userId <= 0) continue;
        $syncedUserIds[] = $userId;

        $name = trim(($u['NAME'] ?? '') . ' ' . ($u['LAST_NAME'] ?? ''));
        if (empty($name)) {
            $name = $u['EMAIL'] ?? ('Օգտատեր #' . $userId);
        }
        $email = $u['EMAIL'] ?? '';
        $active = (($u['ACTIVE'] ?? true) === true || ($u['ACTIVE'] ?? 'Y') === 'Y') ? 1 : 0;
        
        $pos = mb_strtolower($u['WORK_POSITION'] ?? '');
        $defaultRole = 'viewer';
        if (str_contains($pos, 'director') || str_contains($pos, 'տնօրեն') || str_contains($pos, 'admin') || str_contains($pos, 'ադմին')) {
            $defaultRole = 'admin';
        } elseif (str_contains($pos, 'store') || str_contains($pos, 'պահեստ')) {
            $defaultRole = 'storekeeper';
        } elseif (str_contains($pos, 'sales') || str_contains($pos, 'վաճառք') || str_contains($pos, 'manager') || str_contains($pos, 'մենեջեր')) {
            $defaultRole = 'manager';
        }

        $checkU = $pdo->prepare("SELECT id, role_code FROM system_users WHERE bitrix_user_id = ?");
        $checkU->execute([$userId]);
        $existing = $checkU->fetch();
        if ($existing) {
            $upd = $pdo->prepare("UPDATE system_users SET name = ?, email = ?, is_active = ? WHERE bitrix_user_id = ?");
            $upd->execute([$name, $email, $active, $userId]);
        } else {
            $hasAdmin = $pdo->query("SELECT count(*) FROM system_users WHERE role_code = 'admin'")->fetchColumn() > 0;
            if (!$hasAdmin || $userId == 1 || $userId == 47141) {
                $defaultRole = 'admin';
            }
            $ins = $pdo->prepare("INSERT INTO system_users (bitrix_user_id, name, email, role_code, is_active) VALUES (?, ?, ?, ?, ?)");
            $ins->execute([$userId, $name, $email, $defaultRole, $active]);
        }
        $syncedUsers++;
    }

    // Purge mock users not found in real Bitrix24
    if ($bitrix->isConfigured() && !empty($syncedUserIds)) {
        $placeholders = implode(',', array_fill(0, count($syncedUserIds), '?'));
        $del = $pdo->prepare("DELETE FROM system_users WHERE bitrix_user_id NOT IN ($placeholders)");
        $del->execute($syncedUserIds);
    }
}

// 1c. Fetch and sync CRM companies & contacts from Bitrix24
$syncedCompanies = 0;
$syncedContacts = 0;
if ($action === 'all' || $action === 'crm') {
    $syncedCompIds = [];
    $allB24Companies = $bitrix->getAllCompanies();
    if (is_array($allB24Companies)) {
        $cStmt = $pdo->prepare("INSERT INTO crm_companies (bitrix_company_id, title, company_type, phone, email, updated_at) VALUES (?, ?, ?, ?, ?, datetime('now')) ON CONFLICT(bitrix_company_id) DO UPDATE SET title = excluded.title, company_type = excluded.company_type, phone = excluded.phone, email = excluded.email, updated_at = datetime('now')");
        foreach ($allB24Companies as $bc) {
            $cId = (int)($bc['ID'] ?? 0);
            if ($cId <= 0) continue;
            $syncedCompIds[] = $cId;
            $cTitle = $bc['TITLE'] ?? ("Ընկերություն #" . $cId);
            $cType = $bc['COMPANY_TYPE'] ?? 'CUSTOMER';
            $cPhone = is_array($bc['PHONE'] ?? null) ? ($bc['PHONE'][0]['VALUE'] ?? '') : ($bc['PHONE'] ?? '');
            $cEmail = is_array($bc['EMAIL'] ?? null) ? ($bc['EMAIL'][0]['VALUE'] ?? '') : ($bc['EMAIL'] ?? '');
            $cStmt->execute([$cId, $cTitle, $cType, $cPhone, $cEmail]);
            $syncedCompanies++;
        }
    }
    if ($bitrix->isConfigured() && !empty($syncedCompIds)) {
        $placeholders = implode(',', array_fill(0, count($syncedCompIds), '?'));
        $del = $pdo->prepare("DELETE FROM crm_companies WHERE bitrix_company_id NOT IN ($placeholders)");
        $del->execute($syncedCompIds);
    }

    $syncedContIds = [];
    $allB24Contacts = $bitrix->getAllContacts();
    if (is_array($allB24Contacts)) {
        $ctStmt = $pdo->prepare("INSERT INTO crm_contacts (bitrix_contact_id, name, last_name, phone, email, company_id, updated_at) VALUES (?, ?, ?, ?, ?, ?, datetime('now')) ON CONFLICT(bitrix_contact_id) DO UPDATE SET name = excluded.name, last_name = excluded.last_name, phone = excluded.phone, email = excluded.email, company_id = excluded.company_id, updated_at = datetime('now')");
        foreach ($allB24Contacts as $bct) {
            $ctId = (int)($bct['ID'] ?? 0);
            if ($ctId <= 0) continue;
            $syncedContIds[] = $ctId;
            $ctName = $bct['NAME'] ?? 'Հաճախորդ';
            $ctLastName = $bct['LAST_NAME'] ?? '';
            $ctPhone = is_array($bct['PHONE'] ?? null) ? ($bct['PHONE'][0]['VALUE'] ?? '') : ($bct['PHONE'] ?? '');
            $ctEmail = is_array($bct['EMAIL'] ?? null) ? ($bct['EMAIL'][0]['VALUE'] ?? '') : ($bct['EMAIL'] ?? '');
            $ctCompanyId = (int)($bct['COMPANY_ID'] ?? 0);
            $ctStmt->execute([$ctId, $ctName, $ctLastName, $ctPhone, $ctEmail, $ctCompanyId]);
            $syncedContacts++;
        }
    }
    if ($bitrix->isConfigured() && !empty($syncedContIds)) {
        $placeholders = implode(',', array_fill(0, count($syncedContIds), '?'));
        $del = $pdo->prepare("DELETE FROM crm_contacts WHERE bitrix_contact_id NOT IN ($placeholders)");
        $del->execute($syncedContIds);
    }
}

// 2. Fetch store products
if ($action === 'all') {
    // 2.1 Fetch ALL products from CRM to get complete catalog
    $allCrmProducts = $bitrix->getAllCrmProducts();
    $crmProductMap = [];
    $syncedProductIds = [];
    
    foreach ($allCrmProducts as $crmProd) {
        $id = (int)($crmProd['ID'] ?? 0);
        if ($id <= 0) continue;
        $crmProductMap[$id] = [
            'id' => $id,
            'name' => $crmProd['NAME'] ?? "Ապրանք #{$id}",
            'price' => (float)($crmProd['PRICE'] ?? 0),
            'sku' => $crmProd['PROPERTY_SKU'] ?? '',
            'amount' => 0,
            'reserved' => 0
        ];
        $syncedProductIds[] = $id;
    }

    // 2.2 Fetch Store product list to get current stock and reserved amounts
    $stockList = $bitrix->getAllStoreProducts();
    $stockByOfferId = [];
    
    if (is_array($stockList)) {
        foreach ($stockList as $item) {
            $productId = (int)($item['productId'] ?? $item['PRODUCT_ID'] ?? 0);
            $amt = (float)($item['amount'] ?? $item['AMOUNT'] ?? 0);
            $resv = (float)($item['quantityReserved'] ?? $item['QUANTITY_RESERVED'] ?? 0);
            if ($productId > 0) {
                $stockByOfferId[$productId] = [
                    'amount' => $amt,
                    'reserved' => $resv
                ];
            }
        }
    }

    // 2.3 Resolve Offer variation parent IDs using batch
    $activeStockIds = array_keys(array_filter($stockByOfferId, fn($v) => $v['amount'] > 0 || $v['reserved'] > 0));
    $batches = array_chunk($activeStockIds, 50);

    foreach ($batches as $batchIds) {
        $cmds = [];
        foreach ($batchIds as $bid) {
            $cmds['p_' . $bid] = 'catalog.product.get?id=' . $bid;
        }
        $bRes = $bitrix->call('batch', ['halt' => 0, 'cmd' => $cmds]);
        if (!empty($bRes['result']['result'])) {
            foreach ($bRes['result']['result'] as $sub) {
                $p = $sub['product'] ?? null;
                if ($p) {
                    $offerId = (int)$p['id'];
                    $parentId = isset($p['parentId']['value']) && $p['parentId']['value'] > 0 
                        ? (int)$p['parentId']['value'] 
                        : $offerId;
                    
                    $amt = $stockByOfferId[$offerId]['amount'] ?? 0;
                    $resv = $stockByOfferId[$offerId]['reserved'] ?? 0;
                    $cost = (float)($p['purchasingPrice'] ?? 0);

                    if (isset($crmProductMap[$parentId])) {
                        $crmProductMap[$parentId]['amount'] += $amt;
                        $crmProductMap[$parentId]['reserved'] += $resv;
                        if ($cost > 0) {
                            $crmProductMap[$parentId]['cost_price'] = $cost;
                        }
                    }
                }
            }
        }
    }

    // 2.4 Bulk sync to SQLite
    $pdo->beginTransaction();
    try {
        $checkStmt = $pdo->prepare("SELECT id FROM products WHERE bitrix_product_id = ?");
        $updStmt = $pdo->prepare("UPDATE products SET name = ?, sku = ?, current_stock = ?, reserved_stock = ?, price = ?, cost_price = COALESCE(?, cost_price), updated_at = datetime('now') WHERE bitrix_product_id = ?");
        $insStmt = $pdo->prepare("INSERT INTO products (bitrix_product_id, name, sku, current_stock, reserved_stock, unit, price, cost_price, currency, updated_at) VALUES (?, ?, ?, ?, ?, 'հատ', ?, ?, 'AMD', datetime('now'))");
        
        $syncedProducts = 0;
        foreach ($crmProductMap as $id => $p) {
            $costPrice = $p['cost_price'] ?? ($p['price'] * 0.7);
            $checkStmt->execute([$id]);
            if ($checkStmt->fetch()) {
                $updStmt->execute([$p['name'], $p['sku'], $p['amount'], $p['reserved'], $p['price'], $costPrice, $id]);
            } else {
                $insStmt->execute([$id, $p['name'], $p['sku'], $p['amount'], $p['reserved'], $p['price'], $costPrice]);
            }
            $syncedProducts++;
        }

        // Purge mock products not found in real Bitrix24 CRM
        if ($bitrix->isConfigured() && !empty($syncedProductIds)) {
            $placeholders = implode(',', array_fill(0, count($syncedProductIds), '?'));
            $del = $pdo->prepare("DELETE FROM products WHERE bitrix_product_id NOT IN ($placeholders)");
            $del->execute($syncedProductIds);
        }
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Failed to sync products: " . $e->getMessage());
    }
    // 3. Fetch Real Bitrix Deal Reservations
    $syncedReservations = 0;
    try {
        $dealsRes = $bitrix->call('crm.deal.list', [
            'select' => ['ID', 'TITLE', 'STAGE_ID', 'ASSIGNED_BY_ID', 'COMPANY_ID', 'CONTACT_ID', 'DATE_CREATE', 'UF_CRM_DELIVERY_DATE'],
            'order' => ['ID' => 'DESC'],
            'limit' => 50
        ]);
        $deals = $dealsRes['result'] ?? [];

        // Build company and contact lookup
        $compLookup = $pdo->query("SELECT bitrix_company_id, title FROM crm_companies")->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
        $contLookup = $pdo->query("SELECT bitrix_contact_id, TRIM(name || ' ' || last_name) FROM crm_contacts")->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
        $userLookup = $pdo->query("SELECT bitrix_user_id, name FROM system_users")->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];

        // Purge mock reservations
        $pdo->exec("DELETE FROM product_reservations WHERE deal_id IN (1401, 1450, 1490, 1495, 1521) OR customer_name LIKE '%ԱրմՏեք%' OR manager_name LIKE '%Արմեն Սարգսյան%'");

        $resIns = $pdo->prepare("
            INSERT INTO product_reservations 
            (deal_id, bitrix_product_id, quantity, delivery_date, status, manager_name, customer_name, warehouse_id, notes, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($deals as $deal) {
            $dealId = (int)$deal['ID'];
            $stageId = $deal['STAGE_ID'] ?? '';
            $assignedId = (int)($deal['ASSIGNED_BY_ID'] ?? 0);
            $companyId = (int)($deal['COMPANY_ID'] ?? 0);
            $contactId = (int)($deal['CONTACT_ID'] ?? 0);
            $managerName = $userLookup[$assignedId] ?? 'Մենեջեր';
            
            $customerName = '';
            if ($companyId > 0 && isset($compLookup[$companyId])) {
                $customerName = $compLookup[$companyId];
            } elseif ($contactId > 0 && isset($contLookup[$contactId])) {
                $customerName = $contLookup[$contactId];
            } else {
                $customerName = $deal['TITLE'] ?? ("Գործարք #" . $dealId);
            }

            $dealRows = $bitrix->call('crm.deal.productrows.get', ['id' => $dealId]);
            $rows = $dealRows['result'] ?? [];
            
            foreach ($rows as $row) {
                $pId = (int)($row['PRODUCT_ID'] ?? 0);
                $qty = (float)($row['QUANTITY'] ?? 0);
                if ($pId <= 0 || $qty <= 0) continue;

                // Check if already in reservations
                $chkRes = $pdo->prepare("SELECT id FROM product_reservations WHERE deal_id = ? AND bitrix_product_id = ?");
                $chkRes->execute([$dealId, $pId]);
                if ($chkRes->fetch()) continue;

                $reserveDate = null;
                if (!empty($row['DATE_RESERVE_END'])) {
                    $reserveDate = date('Y-m-d', strtotime($row['DATE_RESERVE_END']));
                } elseif (!empty($deal['UF_CRM_DELIVERY_DATE'])) {
                    $reserveDate = date('Y-m-d', strtotime($deal['UF_CRM_DELIVERY_DATE']));
                } else {
                    $reserveDate = date('Y-m-d', strtotime('+7 days'));
                }

                $resStatus = 'RESERVED';
                if (str_contains($stageId, 'WON')) {
                    $resStatus = 'SHIPPED';
                } elseif (str_contains($stageId, 'PREPAYMENT') || str_contains($stageId, 'EXECUTING')) {
                    $resStatus = 'CONFIRMED';
                }

                $warehouseId = !empty($row['STORE_ID']) ? (int)$row['STORE_ID'] : 1;
                $notes = "Գործարք #{$dealId}: " . ($deal['TITLE'] ?? '');
                $dateCreate = !empty($deal['DATE_CREATE']) ? date('Y-m-d H:i:s', strtotime($deal['DATE_CREATE'])) : date('Y-m-d H:i:s');

                $resIns->execute([
                    $dealId,
                    $pId,
                    $qty,
                    $reserveDate,
                    $resStatus,
                    $managerName,
                    $customerName,
                    $warehouseId,
                    $notes,
                    $dateCreate,
                    $dateCreate
                ]);
                $syncedReservations++;

                if ($resStatus === 'SHIPPED') {
                    $pdo->prepare("
                        INSERT INTO stock_movements (bitrix_product_id, movement_type, quantity, direction, warehouse_id, reference_id, notes, movement_date, created_at)
                        VALUES (?, 'SALE', ?, 'OUT', ?, ?, ?, ?, ?)
                    ")->execute([
                        $pId,
                        $qty,
                        $warehouseId,
                        $dealId,
                        "Վաճառք Գործարք #{$dealId}-ով",
                        substr($dateCreate, 0, 10),
                        $dateCreate
                    ]);
                }
            }
        }
    } catch (Exception $e) {
        error_log("Failed to sync reservations: " . $e->getMessage());
    }

    // 4. Fetch Real Bitrix Incoming Shipments / Documents
    $syncedShipments = 0;
    try {
        $pdo->exec("DELETE FROM incoming_shipments WHERE supplier_id LIKE 'SUP-%' OR supplier_name LIKE '%Էլեկտրոնիկս Դիստրիբյուշն%'");

        $docList = $bitrix->call('catalog.document.list', [
            'select' => ['id', 'title', 'docType', 'status', 'dateDocument', 'total', 'contractorId'],
            'order' => ['id' => 'DESC'],
            'limit' => 30
        ]);
        $docs = $docList['result']['documents'] ?? [];

        $shipIns = $pdo->prepare("
            INSERT INTO incoming_shipments 
            (bitrix_product_id, supplier_name, supplier_id, quantity, expected_date, warehouse_id, status, notes, bitrix_doc_id, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($docs as $doc) {
            $docId = (int)$doc['id'];
            $docTitle = trim($doc['title'] ?? ("Փաստաթուղթ #" . $docId));
            $docStatus = ($doc['status'] === 'Y') ? 'RECEIVED' : 'CONFIRMED';
            $docDate = !empty($doc['dateDocument']) ? substr($doc['dateDocument'], 0, 10) : date('Y-m-d');
            $createdDt = !empty($doc['dateDocument']) ? date('Y-m-d H:i:s', strtotime($doc['dateDocument'])) : date('Y-m-d H:i:s');

            $elemRes = $bitrix->call('catalog.document.element.list', ['filter' => ['=docId' => $docId]]);
            $elems = $elemRes['result']['documentElements'] ?? [];
            
            foreach ($elems as $el) {
                $pId = (int)($el['elementId'] ?? 0);
                $qty = (float)($el['amount'] ?? 0);
                $wId = (int)($el['storeTo'] ?? 1);
                if ($pId <= 0 || $qty <= 0) continue;

                $chkShip = $pdo->prepare("SELECT id FROM incoming_shipments WHERE bitrix_doc_id = ? AND bitrix_product_id = ?");
                $chkShip->execute([$docId, $pId]);
                if ($chkShip->fetch()) continue;

                $shipIns->execute([
                    $pId,
                    $docTitle,
                    "DOC-" . $docId,
                    $qty,
                    $docDate,
                    $wId,
                    $docStatus,
                    "Պահեստի մուտքի փաստաթուղթ #{$docId}",
                    $docId,
                    $createdDt,
                    $createdDt
                ]);
                $syncedShipments++;

                if ($docStatus === 'RECEIVED') {
                    $pdo->prepare("
                        INSERT INTO stock_movements (bitrix_product_id, movement_type, quantity, direction, warehouse_id, reference_id, notes, movement_date, created_at)
                        VALUES (?, 'SHIPMENT_RECEIVE', ?, 'IN', ?, ?, ?, ?, ?)
                    ")->execute([
                        $pId,
                        $qty,
                        $wId,
                        $docId,
                        "Մուտքագրում փաստաթուղթ #{$docId}-ով",
                        $docDate,
                        $createdDt
                    ]);
                }
            }
        }
    } catch (Exception $e) {
        error_log("Failed to sync shipments: " . $e->getMessage());
    }
}

$msg = $action === 'users' 
    ? "Աշխատակիցները հաջողությամբ համաժամեցվեցին ({$syncedUsers} հոգի):" 
    : "Համակարգը հաջողությամբ համաժամեցվեց (Պահեստներ՝ {$syncedStores}, Ապրանքներ՝ {$syncedProducts}, Աշխատակիցներ՝ {$syncedUsers}, Ընկերություններ՝ {$syncedCompanies}, Ամրագրումներ՝ {$syncedReservations}, Մուտքեր՝ {$syncedShipments}):";

echo json_encode([
    'success' => true,
    'message' => $msg,
    'is_mock_mode' => !$bitrix->isConfigured(),
    'stats' => [
        'stores' => $syncedStores,
        'products' => $syncedProducts,
        'users' => $syncedUsers,
        'companies' => $syncedCompanies ?? 0,
        'reservations' => $syncedReservations ?? 0,
        'shipments' => $syncedShipments ?? 0,
    ]
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

