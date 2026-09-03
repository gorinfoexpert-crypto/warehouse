<?php
/**
 * Bitrix24 Cloud REST API Client
 * Supports Webhook & OAuth, catalog, store documents, CRM deals, and placement handlers.
 * Natural Armenian Language Localization (Հայերեն)
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

class BitrixRestClient {
    private string $webhookUrl;
    private string $domain;
    private bool $isMockMode = false;

    public function __construct(?string $webhookUrl = null) {
        $this->webhookUrl = $webhookUrl ?: $this->getStoredWebhookUrl();
        if (empty($this->webhookUrl)) {
            $this->isMockMode = true;
        } else {
            $this->domain = parse_url($this->webhookUrl, PHP_URL_HOST) ?: '';
        }
    }

    private function getStoredWebhookUrl(): string {
        if (defined('BITRIX_WEBHOOK_URL') && !empty(BITRIX_WEBHOOK_URL)) {
            return BITRIX_WEBHOOK_URL;
        }
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("SELECT value FROM settings WHERE key = 'bitrix_webhook_url'");
            $stmt->execute();
            $val = $stmt->fetchColumn();
            return $val ?: '';
        } catch (Exception $e) {
            return '';
        }
    }

    public function isConfigured(): bool {
        return !$this->isMockMode && !empty($this->webhookUrl);
    }

    public function call(string $method, array $params = []): array {
        if ($this->isMockMode) {
            return $this->handleMockCall($method, $params);
        }

        $url = rtrim($this->webhookUrl, '/') . '/' . ltrim($method, '/') . '.json';

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($params),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT => 15,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false || !empty($curlError)) {
            error_log("BitrixRestClient cURL error on {$method}: " . $curlError);
            return [
                'result' => null,
                'error' => 'curl_error',
                'error_description' => $curlError,
            ];
        }

        $decoded = json_decode($response, true);
        if ($httpCode >= 400 || isset($decoded['error'])) {
            error_log("BitrixRestClient API error [{$httpCode}] on {$method}: " . ($decoded['error_description'] ?? 'Unknown error'));
        }

        return $decoded ?: ['result' => null, 'error' => 'invalid_json'];
    }

    // ==========================================
    // Warehouse & Catalog Methods
    // ==========================================

    /**
     * Get real warehouse stock for a product or list of products
     */
    public function getStoreProductList(array $filter = []): array {
        return $this->call('catalog.storeproduct.list', [
            'select' => ['id', 'productId', 'storeId', 'amount', 'quantityReserved'],
            'filter' => $filter,
        ]);
    }

    /**
     * Get list of warehouses
     */
    public function getStoreList(array $filter = []): array {
        return $this->call('catalog.store.list', [
            'select' => ['id', 'title', 'address', 'active'],
            'filter' => $filter,
        ]);
    }

    /**
     * Get all CRM products using lightning-fast Bitrix24 batch REST API
     */
    public function getAllCrmProducts(): array {
        if ($this->isMockMode) {
            $pdo = Database::getConnection();
            $stmt = $pdo->query("SELECT bitrix_product_id as ID, name as NAME, price as PRICE, sku as PROPERTY_SKU FROM products");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // 1. Get total count
        $initRes = $this->call('crm.product.list', [
            'select' => ['ID', 'NAME', 'PRICE', 'CODE'],
            'start' => 0
        ]);

        $products = $initRes['result'] ?? [];
        $total = (int)($initRes['total'] ?? count($products));

        if ($total <= 50) {
            return $products;
        }

        // 2. Prepare batch requests for remaining items (up to 50 calls per batch)
        $batchCmds = [];
        for ($start = 50; $start < $total; $start += 50) {
            $batchCmds['crm_' . $start] = 'crm.product.list?start=' . $start . '&select[0]=ID&select[1]=NAME&select[2]=PRICE&select[3]=CODE';
        }

        if (!empty($batchCmds)) {
            // Chunk by 50 in case total is very large
            $chunks = array_chunk($batchCmds, 50, true);
            foreach ($chunks as $chunk) {
                $batchRes = $this->call('batch', ['halt' => 0, 'cmd' => $chunk]);
                if (!empty($batchRes['result']['result'])) {
                    foreach ($batchRes['result']['result'] as $sub) {
                        if (is_array($sub)) {
                            $products = array_merge($products, $sub);
                        }
                    }
                }
            }
        }

        return $products;
    }

    /**
     * Get all store products using lightning-fast Bitrix24 batch REST API
     */
    public function getAllStoreProducts(array $filter = []): array {
        if ($this->isMockMode) {
            return $this->getStoreProductList($filter)['result']['storeProducts'] ?? [];
        }

        // 1. Get total count & first page
        $initRes = $this->call('catalog.storeproduct.list', [
            'select' => ['id', 'productId', 'storeId', 'amount', 'quantityReserved'],
            'filter' => $filter,
            'start' => 0
        ]);

        $storeProducts = $initRes['result']['storeProducts'] ?? $initRes['result'] ?? [];
        $total = (int)($initRes['total'] ?? count($storeProducts));

        if ($total <= 50) {
            return $storeProducts;
        }

        // 2. Prepare batch requests for remaining items
        $batchCmds = [];
        for ($start = 50; $start < $total; $start += 50) {
            $batchCmds['store_' . $start] = 'catalog.storeproduct.list?start=' . $start . '&select[0]=id&select[1]=productId&select[2]=storeId&select[3]=amount&select[4]=quantityReserved';
        }

        if (!empty($batchCmds)) {
            $chunks = array_chunk($batchCmds, 50, true);
            foreach ($chunks as $chunk) {
                $batchRes = $this->call('batch', ['halt' => 0, 'cmd' => $chunk]);
                if (!empty($batchRes['result']['result'])) {
                    foreach ($batchRes['result']['result'] as $sub) {
                        $items = $sub['storeProducts'] ?? $sub ?? [];
                        if (is_array($items)) {
                            $storeProducts = array_merge($storeProducts, $items);
                        }
                    }
                }
            }
        }

        return $storeProducts;
    }

    /**
     * Get detailed product catalog attributes (name, SKU, price)
     */
    public function getProductDetails(int $productId): ?array {
        // Try catalog.product.get (Bitrix24 Store Catalog REST API)
        $res = $this->call('catalog.product.get', ['id' => $productId]);
        if (isset($res['result']['product'])) {
            return $res['result']['product'];
        }
        if (isset($res['result']) && is_array($res['result']) && !empty($res['result'])) {
            return $res['result'];
        }

        // Try crm.product.get (CRM Product REST API)
        $resCrm = $this->call('crm.product.get', ['id' => $productId]);
        if (isset($resCrm['result']) && is_array($resCrm['result'])) {
            return $resCrm['result'];
        }

        return null;
    }

    /**
     * Create physical warehouse receipt document
     */
    public function createStoreReceiptDocument(int $storeId, string $title = '', ?string $comment = ''): array {
        return $this->call('catalog.document.add', [
            'fields' => [
                'docType' => 'S', // Store receipt
                'title' => $title ?: ('Մուտք մատակարարից ' . date('d.m.Y H:i')),
                'commentary' => $comment ?: 'Ստեղծվել է ավտոմատ պահեստային համակարգի կողմից',
                'responsibleId' => 1,
                'dateModify' => date('Y-m-d H:i:s'),
            ]
        ]);
    }

    /**
     * Add product item to warehouse document
     */
    public function addDocumentElement(int $documentId, int $storeToId, int $productId, float $amount, float $purchasingPrice = 0): array {
        return $this->call('catalog.document.element.add', [
            'fields' => [
                'docId' => $documentId,
                'storeToId' => $storeToId,
                'elementId' => $productId,
                'amount' => $amount,
                'purchasingPrice' => $purchasingPrice,
            ]
        ]);
    }

    /**
     * Conduct warehouse document to apply stock changes in Bitrix24
     */
    public function conductDocument(int $documentId): array {
        return $this->call('catalog.document.conduct', [
            'id' => $documentId
        ]);
    }

    // ==========================================
    // CRM Deal Methods
    // ==========================================

    /**
     * Fetch deal information
     */
    public function getDeal(int $dealId): array {
        return $this->call('crm.deal.get', ['id' => $dealId]);
    }

    /**
     * Fetch products associated with a deal
     */
    public function getDealProductRows(int $dealId): array {
        return $this->call('crm.deal.productrows.get', ['id' => $dealId]);
    }

    /**
     * Get list of users/employees from Bitrix24
     */
    public function getUserList(array $filter = []): array {
        return $this->call('user.get', [
            'FILTER' => $filter,
        ]);
    }

    /**
     * Add new user/employee in Bitrix24 (with local system user fallback if webhook lacks scope)
     */
    public function addUser(array $fields): array {
        $res = $this->call('user.add', $fields);
        if (!empty($res['result'])) {
            return $res;
        }
        try {
            $pdo = Database::getConnection();
            $name = trim(($fields['NAME'] ?? '') . ' ' . ($fields['LAST_NAME'] ?? ''));
            if (empty($name)) $name = trim($fields['EMAIL'] ?? 'Նոր Մենեջեր');
            $email = $fields['EMAIL'] ?? '';
            $pos = $fields['WORK_POSITION'] ?? 'manager';
            $mockId = rand(100, 999);
            $stmt = $pdo->prepare("INSERT INTO system_users (bitrix_user_id, name, email, role_code, is_active) VALUES (?, ?, ?, ?, 1)");
            $stmt->execute([$mockId, $name, $email, $pos]);
            $lid = $pdo->lastInsertId();
            return ['result' => $lid ?: $mockId, 'local' => true];
        } catch (Exception $e) {
            return $res;
        }
    }

    // ==========================================
    // CRM Companies & Contacts Methods
    // ==========================================

    /**
     * Get list of CRM companies from Bitrix24
     */
    public function getCompanyList(array $filter = [], array $select = ['ID', 'TITLE', 'COMPANY_TYPE', 'PHONE', 'EMAIL']): array {
        return $this->call('crm.company.list', [
            'filter' => $filter,
            'select' => $select,
            'order' => ['TITLE' => 'ASC'],
        ]);
    }

    /**
     * Get ALL CRM companies using Bitrix24 batch REST API
     */
    public function getAllCompanies(): array {
        if ($this->isMockMode) {
            $pdo = Database::getConnection();
            $stmt = $pdo->query("SELECT bitrix_company_id as ID, title as TITLE, company_type as COMPANY_TYPE, phone as PHONE, email as EMAIL FROM crm_companies ORDER BY title ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $initRes = $this->call('crm.company.list', [
            'select' => ['ID', 'TITLE', 'COMPANY_TYPE', 'PHONE', 'EMAIL'],
            'order' => ['TITLE' => 'ASC'],
            'start' => 0
        ]);

        $companies = $initRes['result'] ?? [];
        $total = (int)($initRes['total'] ?? count($companies));

        if ($total <= 50) {
            return $companies;
        }

        $batchCmds = [];
        for ($start = 50; $start < $total; $start += 50) {
            $batchCmds['comp_' . $start] = 'crm.company.list?start=' . $start . '&select[0]=ID&select[1]=TITLE&select[2]=COMPANY_TYPE&select[3]=PHONE&select[4]=EMAIL&order[TITLE]=ASC';
        }

        if (!empty($batchCmds)) {
            $chunks = array_chunk($batchCmds, 50, true);
            foreach ($chunks as $chunk) {
                $batchRes = $this->call('batch', ['halt' => 0, 'cmd' => $chunk]);
                if (!empty($batchRes['result']['result'])) {
                    foreach ($batchRes['result']['result'] as $sub) {
                        if (is_array($sub)) {
                            $companies = array_merge($companies, $sub);
                        }
                    }
                }
            }
        }

        return $companies;
    }

    /**
     * Get list of CRM contacts from Bitrix24
     */
    public function getContactList(array $filter = [], array $select = ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'PHONE', 'EMAIL', 'COMPANY_ID']): array {
        return $this->call('crm.contact.list', [
            'filter' => $filter,
            'select' => $select,
            'order' => ['LAST_NAME' => 'ASC', 'NAME' => 'ASC'],
        ]);
    }

    /**
     * Get ALL CRM contacts using Bitrix24 batch REST API
     */
    public function getAllContacts(): array {
        if ($this->isMockMode) {
            $pdo = Database::getConnection();
            $stmt = $pdo->query("SELECT bitrix_contact_id as ID, name as NAME, last_name as LAST_NAME, phone as PHONE, email as EMAIL, company_id as COMPANY_ID FROM crm_contacts ORDER BY last_name ASC, name ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $initRes = $this->call('crm.contact.list', [
            'select' => ['ID', 'NAME', 'LAST_NAME', 'PHONE', 'EMAIL', 'COMPANY_ID'],
            'order' => ['LAST_NAME' => 'ASC', 'NAME' => 'ASC'],
            'start' => 0
        ]);

        $contacts = $initRes['result'] ?? [];
        $total = (int)($initRes['total'] ?? count($contacts));

        if ($total <= 50) {
            return $contacts;
        }

        $batchCmds = [];
        for ($start = 50; $start < $total; $start += 50) {
            $batchCmds['cont_' . $start] = 'crm.contact.list?start=' . $start . '&select[0]=ID&select[1]=NAME&select[2]=LAST_NAME&select[3]=PHONE&select[4]=EMAIL&select[5]=COMPANY_ID';
        }

        if (!empty($batchCmds)) {
            $chunks = array_chunk($batchCmds, 50, true);
            foreach ($chunks as $chunk) {
                $batchRes = $this->call('batch', ['halt' => 0, 'cmd' => $chunk]);
                if (!empty($batchRes['result']['result'])) {
                    foreach ($batchRes['result']['result'] as $sub) {
                        if (is_array($sub)) {
                            $contacts = array_merge($contacts, $sub);
                        }
                    }
                }
            }
        }

        return $contacts;
    }

    /**
     * Add a new CRM Company in Bitrix24
     */
    public function addCompany(array $fields): array {
        return $this->call('crm.company.add', [
            'fields' => $fields,
            'params' => ['REGISTER_SONET_EVENT' => 'Y']
        ]);
    }

    /**
     * Add a new CRM Contact in Bitrix24
     */
    public function addContact(array $fields): array {
        return $this->call('crm.contact.add', [
            'fields' => $fields,
            'params' => ['REGISTER_SONET_EVENT' => 'Y']
        ]);
    }

    // ==========================================
    // App Placement Management
    // ==========================================

    /**
     * Register tab in Bitrix24 Deal Card
     */
    public function bindDealPlacement(string $handlerUrl, string $title = 'Ապրանքների հասանելիություն և Ամրագրում'): array {
        return $this->call('placement.bind', [
            'PLACEMENT' => 'CRM_DEAL_DETAIL_TAB',
            'HANDLER' => $handlerUrl,
            'TITLE' => $title,
            'DESCRIPTION' => 'Ապրանքների հասանելիության հաշվարկի և ամրագրման պատուհան'
        ]);
    }

    /**
     * Unbind placement tab
     */
    public function unbindDealPlacement(string $handlerUrl): array {
        return $this->call('placement.unbind', [
            'PLACEMENT' => 'CRM_DEAL_DETAIL_TAB',
            'HANDLER' => $handlerUrl
        ]);
    }

    // ==========================================
    // Mock / Sandbox Handler
    // ==========================================

    private function handleMockCall(string $method, array $params): array {
        $pdo = Database::getConnection();

        switch ($method) {
            case 'catalog.store.list':
                $stmt = $pdo->query("SELECT id, title, address, is_active as active FROM warehouses WHERE is_active = 1");
                return ['result' => ['stores' => $stmt->fetchAll()]];

            case 'catalog.storeproduct.list':
                $productId = $params['filter']['=productId'] ?? $params['filter']['productId'] ?? null;
                if ($productId) {
                    $stmt = $pdo->prepare("SELECT id, bitrix_product_id as productId, 1 as storeId, current_stock as amount, reserved_stock as quantityReserved FROM products WHERE bitrix_product_id = ?");
                    $stmt->execute([(int)$productId]);
                } else {
                    $stmt = $pdo->query("SELECT id, bitrix_product_id as productId, 1 as storeId, current_stock as amount, reserved_stock as quantityReserved FROM products");
                }
                return ['result' => ['storeProducts' => $stmt->fetchAll()]];

            case 'catalog.product.get':
            case 'crm.product.get':
                $productId = (int)($params['id'] ?? 0);
                $stmt = $pdo->prepare("SELECT id, name as name, sku as code, price as price FROM products WHERE bitrix_product_id = ?");
                $stmt->execute([$productId]);
                $p = $stmt->fetch();
                if ($p) {
                    return [
                        'result' => [
                            'product' => [
                                'id' => $productId,
                                'name' => $p['name'],
                                'code' => $p['code'],
                                'price' => (float)$p['price'],
                            ],
                            'ID' => (string)$productId,
                            'NAME' => $p['name'],
                            'CODE' => $p['code'],
                            'PRICE' => (float)$p['price'],
                        ]
                    ];
                }
                // Mock details fallback
                return [
                    'result' => [
                        'product' => [
                            'id' => $productId,
                            'name' => "Ապրանք #" . $productId,
                            'code' => "PROD-" . $productId,
                            'price' => 1000.0,
                        ],
                        'ID' => (string)$productId,
                        'NAME' => "Ապրանք #" . $productId,
                        'CODE' => "PROD-" . $productId,
                        'PRICE' => 1000.0,
                    ]
                ];

            case 'crm.deal.get':
                $dealId = (int)($params['id'] ?? 0);
                if ($dealId <= 0) {
                    return ['result' => null, 'error' => 'NOT_FOUND'];
                }
                $stmt = $pdo->prepare("SELECT deal_id, manager_name, customer_name, delivery_date FROM product_reservations WHERE deal_id = ? LIMIT 1");
                $stmt->execute([$dealId]);
                $d = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($d) {
                    return [
                        'result' => [
                            'ID' => (string)$dealId,
                            'TITLE' => "Գործարք #{$dealId}",
                            'STAGE_ID' => 'EXECUTING',
                            'OPPORTUNITY' => '0.00',
                            'CURRENCY_ID' => 'AMD',
                            'UF_CRM_DELIVERY_DATE' => $d['delivery_date'] ?? date('Y-m-d', strtotime('+7 days')),
                            'ASSIGNED_BY_NAME' => $d['manager_name'] ?? '',
                        ]
                    ];
                }
                return ['result' => null, 'error' => 'NOT_FOUND'];

            case 'crm.deal.productrows.get':
                $dealId = (int)($params['id'] ?? 0);
                if ($dealId <= 0) {
                    return ['result' => []];
                }
                $stmt = $pdo->prepare("
                    SELECT r.id as ID, r.bitrix_product_id as PRODUCT_ID, p.name as PRODUCT_NAME, r.quantity as QUANTITY, p.price as PRICE, 'հատ' as MEASURE_NAME
                    FROM product_reservations r
                    LEFT JOIN products p ON r.bitrix_product_id = p.bitrix_product_id
                    WHERE r.deal_id = ?
                ");
                $stmt->execute([$dealId]);
                return ['result' => $stmt->fetchAll(PDO::FETCH_ASSOC)];

            case 'catalog.document.add':
                return ['result' => null, 'error' => 'not_configured'];

            case 'catalog.document.element.add':
                return ['result' => null, 'error' => 'not_configured'];

            case 'catalog.document.conduct':
                return ['result' => null, 'error' => 'not_configured'];

            case 'placement.bind':
            case 'placement.unbind':
                return ['result' => true];

            case 'crm.company.list':
                try {
                    $stmt = $pdo->query("SELECT bitrix_company_id as ID, title as TITLE, company_type as COMPANY_TYPE, phone as PHONE, email as EMAIL FROM crm_companies ORDER BY title ASC");
                    $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    return ['result' => $companies ?: []];
                } catch (Exception $e) {
                    return ['result' => []];
                }

            case 'crm.contact.list':
                try {
                    $stmt = $pdo->query("SELECT bitrix_contact_id as ID, name as NAME, last_name as LAST_NAME, phone as PHONE, email as EMAIL, company_id as COMPANY_ID FROM crm_contacts ORDER BY last_name ASC, name ASC");
                    $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    return ['result' => $contacts ?: []];
                } catch (Exception $e) {
                    return ['result' => []];
                }

            case 'crm.company.add':
                $fields = $params['fields'] ?? [];
                $title = trim($fields['TITLE'] ?? 'Նոր Ընկերություն');
                $phone = $fields['PHONE'][0]['VALUE'] ?? $fields['PHONE'] ?? '';
                $email = $fields['EMAIL'][0]['VALUE'] ?? $fields['EMAIL'] ?? '';
                $newId = rand(100, 999);
                try {
                    $stmt = $pdo->prepare("INSERT INTO crm_companies (bitrix_company_id, title, company_type, phone, email, updated_at) VALUES (?, ?, 'CUSTOMER', ?, ?, datetime('now'))");
                    $stmt->execute([$newId, $title, $phone, $email]);
                } catch (Exception $e) {}
                return ['result' => $newId];

            case 'crm.contact.add':
                $fields = $params['fields'] ?? [];
                $name = trim($fields['NAME'] ?? 'Հաճախորդ');
                $lastName = trim($fields['LAST_NAME'] ?? '');
                $phone = $fields['PHONE'][0]['VALUE'] ?? $fields['PHONE'] ?? '';
                $email = $fields['EMAIL'][0]['VALUE'] ?? $fields['EMAIL'] ?? '';
                $companyId = (int)($fields['COMPANY_ID'] ?? 0);
                $newId = rand(100, 999);
                try {
                    $stmt = $pdo->prepare("INSERT INTO crm_contacts (bitrix_contact_id, name, last_name, phone, email, company_id, updated_at) VALUES (?, ?, ?, ?, ?, ?, datetime('now'))");
                    $stmt->execute([$newId, $name, $lastName, $phone, $email, $companyId]);
                } catch (Exception $e) {}
                return ['result' => $newId];

            case 'user.get':
                $stmt = $pdo->query("SELECT id as ID, name as NAME, email as EMAIL, role_code as WORK_POSITION, is_active as ACTIVE FROM system_users WHERE is_active = 1");
                $users = [];
                foreach ($stmt->fetchAll() as $u) {
                    $users[] = [
                        'ID' => (string)$u['ID'],
                        'NAME' => $u['NAME'],
                        'LAST_NAME' => '',
                        'EMAIL' => $u['EMAIL'],
                        'WORK_POSITION' => $u['WORK_POSITION'],
                        'ACTIVE' => $u['ACTIVE'] ? true : false,
                    ];
                }
                return ['result' => $users];

            case 'user.add':
                $fields = $params ?? [];
                $name = trim($fields['NAME'] ?? 'Նոր Աշխատակից');
                $email = $fields['EMAIL'] ?? '';
                $pos = $fields['WORK_POSITION'] ?? 'manager';
                $newId = rand(100, 999);
                try {
                    $stmt = $pdo->prepare("INSERT INTO system_users (bitrix_user_id, name, email, role_code, is_active) VALUES (?, ?, ?, ?, 1)");
                    $stmt->execute([$newId, $name, $email, $pos]);
                } catch (Exception $e) {}
                return ['result' => $newId];

            default:
                return ['result' => ['success' => true, 'mock' => true]];
        }
    }
}
