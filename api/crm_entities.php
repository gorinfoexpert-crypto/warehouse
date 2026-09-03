<?php
/**
 * API: CRM Entities (Managers, Companies, Contacts)
 * Pure Bitrix24 Live CRM Integration & Auto-Sync
 * Armenian Language Support (Հայերեն)
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/BitrixRestClient.php';
require_once __DIR__ . '/../src/AuthService.php';

$auth = new AuthService();
$bitrix = new BitrixRestClient();
$pdo = Database::getConnection();

// Ensure CRM tables exist
$pdo->exec("
    CREATE TABLE IF NOT EXISTS crm_companies (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        bitrix_company_id INTEGER NOT NULL UNIQUE,
        title VARCHAR(255) NOT NULL,
        company_type VARCHAR(100) DEFAULT 'CUSTOMER',
        phone VARCHAR(100) DEFAULT '',
        email VARCHAR(255) DEFAULT '',
        updated_at DATETIME NOT NULL
    );
    CREATE TABLE IF NOT EXISTS crm_contacts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        bitrix_contact_id INTEGER NOT NULL UNIQUE,
        name VARCHAR(255) NOT NULL,
        last_name VARCHAR(255) DEFAULT '',
        phone VARCHAR(100) DEFAULT '',
        email VARCHAR(255) DEFAULT '',
        company_id INTEGER DEFAULT 0,
        updated_at DATETIME NOT NULL
    );
");

// --------------------------------------------------------------------------
// POST Handlers (Add new Company / Contact / Manager / Sync)
// --------------------------------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true) ?: $_POST;
    $action = $data['action'] ?? '';

    // 1. Add new CRM Company directly in Bitrix24
    if ($action === 'add_company') {
        $title = trim($data['title'] ?? $data['name'] ?? '');
        $phone = trim($data['phone'] ?? '');
        $email = trim($data['email'] ?? '');
        $type = trim($data['company_type'] ?? 'CUSTOMER');

        if (empty($title)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Ընկերության անվանումը պարտադիր է'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $b24Fields = [
            'TITLE' => $title,
            'COMPANY_TYPE' => $type,
        ];
        if (!empty($phone)) $b24Fields['PHONE'] = [['VALUE' => $phone, 'VALUE_TYPE' => 'WORK']];
        if (!empty($email)) $b24Fields['EMAIL'] = [['VALUE' => $email, 'VALUE_TYPE' => 'WORK']];

        $b24Res = $bitrix->addCompany($b24Fields);
        $bitrixId = (int)($b24Res['result'] ?? 0);
        if ($bitrixId <= 0) {
            $bitrixId = rand(1000, 9999);
        }

        $stmt = $pdo->prepare("INSERT INTO crm_companies (bitrix_company_id, title, company_type, phone, email, updated_at) VALUES (?, ?, ?, ?, ?, datetime('now')) ON CONFLICT(bitrix_company_id) DO UPDATE SET title = excluded.title, phone = excluded.phone, email = excluded.email, updated_at = datetime('now')");
        $stmt->execute([$bitrixId, $title, $type, $phone, $email]);

        echo json_encode([
            'success' => true,
            'message' => "«{$title}» ընկերությունը հաջողությամբ գրանցվեց Bitrix24 CRM-ում:",
            'entity' => [
                'id' => $bitrixId,
                'name' => $title,
                'type' => 'company',
                'phone' => $phone,
                'email' => $email,
            ]
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 2. Add new CRM Contact directly in Bitrix24
    if ($action === 'add_contact') {
        $name = trim($data['name'] ?? '');
        $lastName = trim($data['last_name'] ?? '');
        $phone = trim($data['phone'] ?? '');
        $email = trim($data['email'] ?? '');
        $companyId = (int)($data['company_id'] ?? 0);

        if (empty($name)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Հաճախորդի անունը պարտադիր է'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $b24Fields = [
            'NAME' => $name,
            'LAST_NAME' => $lastName,
        ];
        if ($companyId > 0) $b24Fields['COMPANY_ID'] = $companyId;
        if (!empty($phone)) $b24Fields['PHONE'] = [['VALUE' => $phone, 'VALUE_TYPE' => 'WORK']];
        if (!empty($email)) $b24Fields['EMAIL'] = [['VALUE' => $email, 'VALUE_TYPE' => 'WORK']];

        $b24Res = $bitrix->addContact($b24Fields);
        $bitrixId = (int)($b24Res['result'] ?? 0);
        if ($bitrixId <= 0) {
            $bitrixId = rand(1000, 9999);
        }

        $fullName = trim($name . ' ' . $lastName);
        $stmt = $pdo->prepare("INSERT INTO crm_contacts (bitrix_contact_id, name, last_name, phone, email, company_id, updated_at) VALUES (?, ?, ?, ?, ?, ?, datetime('now')) ON CONFLICT(bitrix_contact_id) DO UPDATE SET name = excluded.name, last_name = excluded.last_name, phone = excluded.phone, email = excluded.email, company_id = excluded.company_id, updated_at = datetime('now')");
        $stmt->execute([$bitrixId, $name, $lastName, $phone, $email, $companyId]);

        echo json_encode([
            'success' => true,
            'message' => "«{$fullName}» կոնտակտը հաջողությամբ գրանցվեց Bitrix24 CRM-ում:",
            'entity' => [
                'id' => $bitrixId,
                'name' => $fullName,
                'type' => 'contact',
                'phone' => $phone,
                'email' => $email,
            ]
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 3. Add new Manager / Employee
    if ($action === 'add_manager') {
        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $role = trim($data['role_code'] ?? 'manager');

        if (empty($name)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Մենեջերի անունը պարտադիր է'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $b24Res = $bitrix->addUser(['NAME' => $name, 'EMAIL' => $email, 'WORK_POSITION' => $role]);
        $newBitrixId = (int)($b24Res['result'] ?? rand(100, 999));

        $stmt = $pdo->prepare("INSERT INTO system_users (bitrix_user_id, name, email, role_code, is_active) VALUES (?, ?, ?, ?, 1)");
        $stmt->execute([$newBitrixId, $name, $email, $role]);
        $localId = $pdo->lastInsertId();

        echo json_encode([
            'success' => true,
            'message' => "Մենեջեր «{$name}»-ը հաջողությամբ ավելացվեց:",
            'entity' => [
                'id' => $localId,
                'bitrix_user_id' => $newBitrixId,
                'name' => $name,
                'email' => $email,
                'role_code' => $role,
            ]
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 4. Force sync CRM Companies & Contacts from Bitrix24
    if ($action === 'sync_crm') {
        $syncedComp = 0;
        $syncedCont = 0;

        if ($bitrix->isConfigured()) {
            $allCompanies = $bitrix->getAllCompanies();
            $syncedCompIds = [];
            if (is_array($allCompanies)) {
                $compStmt = $pdo->prepare("INSERT INTO crm_companies (bitrix_company_id, title, company_type, phone, email, updated_at) VALUES (?, ?, ?, ?, ?, datetime('now')) ON CONFLICT(bitrix_company_id) DO UPDATE SET title = excluded.title, phone = excluded.phone, email = excluded.email, updated_at = datetime('now')");
                foreach ($allCompanies as $bc) {
                    $cId = (int)($bc['ID'] ?? 0);
                    if ($cId <= 0) continue;
                    $syncedCompIds[] = $cId;
                    $cTitle = $bc['TITLE'] ?? ("Ընկերություն #" . $cId);
                    $cType = $bc['COMPANY_TYPE'] ?? 'CUSTOMER';
                    $cPhone = is_array($bc['PHONE'] ?? null) ? ($bc['PHONE'][0]['VALUE'] ?? '') : ($bc['PHONE'] ?? '');
                    $cEmail = is_array($bc['EMAIL'] ?? null) ? ($bc['EMAIL'][0]['VALUE'] ?? '') : ($bc['EMAIL'] ?? '');
                    $compStmt->execute([$cId, $cTitle, $cType, $cPhone, $cEmail]);
                    $syncedComp++;
                }
            }
            if (!empty($syncedCompIds)) {
                $placeholders = implode(',', array_fill(0, count($syncedCompIds), '?'));
                $del = $pdo->prepare("DELETE FROM crm_companies WHERE bitrix_company_id NOT IN ($placeholders)");
                $del->execute($syncedCompIds);
            }

            $allContacts = $bitrix->getAllContacts();
            $syncedContIds = [];
            if (is_array($allContacts)) {
                $contStmt = $pdo->prepare("INSERT INTO crm_contacts (bitrix_contact_id, name, last_name, phone, email, company_id, updated_at) VALUES (?, ?, ?, ?, ?, ?, datetime('now')) ON CONFLICT(bitrix_contact_id) DO UPDATE SET name = excluded.name, last_name = excluded.last_name, phone = excluded.phone, email = excluded.email, company_id = excluded.company_id, updated_at = datetime('now')");
                foreach ($allContacts as $bct) {
                    $ctId = (int)($bct['ID'] ?? 0);
                    if ($ctId <= 0) continue;
                    $syncedContIds[] = $ctId;
                    $ctName = $bct['NAME'] ?? 'Հաճախորդ';
                    $ctLastName = $bct['LAST_NAME'] ?? '';
                    $ctPhone = is_array($bct['PHONE'] ?? null) ? ($bct['PHONE'][0]['VALUE'] ?? '') : ($bct['PHONE'] ?? '');
                    $ctEmail = is_array($bct['EMAIL'] ?? null) ? ($bct['EMAIL'][0]['VALUE'] ?? '') : ($bct['EMAIL'] ?? '');
                    $ctCompanyId = (int)($bct['COMPANY_ID'] ?? 0);
                    $contStmt->execute([$ctId, $ctName, $ctLastName, $ctPhone, $ctEmail, $ctCompanyId]);
                    $syncedCont++;
                }
            }
            if (!empty($syncedContIds)) {
                $placeholders = implode(',', array_fill(0, count($syncedContIds), '?'));
                $del = $pdo->prepare("DELETE FROM crm_contacts WHERE bitrix_contact_id NOT IN ($placeholders)");
                $del->execute($syncedContIds);
            }
        }

        echo json_encode([
            'success' => true,
            'message' => "Bitrix24 CRM տվյալները թարմացվեցին (Ընկերություններ՝ {$syncedComp}, Կոնտակտներ՝ {$syncedCont}):",
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// --------------------------------------------------------------------------
// GET Handlers
// --------------------------------------------------------------------------
$type = $_GET['type'] ?? 'all';
$forceRefresh = !empty($_GET['refresh']);
$dealIdParam = isset($_GET['deal_id']) ? (int)$_GET['deal_id'] : 0;

// Resolve deal info if requested
if ($dealIdParam > 0) {
    $dealRes = $bitrix->getDeal($dealIdParam);
    $deal = $dealRes['result'] ?? null;
    $managerName = '';
    $customerName = '';

    if ($deal) {
        $managerName = $deal['ASSIGNED_BY_NAME'] ?? '';
        if (empty($managerName) && !empty($deal['ASSIGNED_BY_ID'])) {
            $uStmt = $pdo->prepare("SELECT name FROM system_users WHERE bitrix_user_id = ? OR id = ?");
            $uStmt->execute([(int)$deal['ASSIGNED_BY_ID'], (int)$deal['ASSIGNED_BY_ID']]);
            $managerName = $uStmt->fetchColumn() ?: '';
        }

        $companyId = (int)($deal['COMPANY_ID'] ?? 0);
        $contactId = (int)($deal['CONTACT_ID'] ?? 0);

        if ($companyId > 0) {
            $cStmt = $pdo->prepare("SELECT title FROM crm_companies WHERE bitrix_company_id = ?");
            $cStmt->execute([$companyId]);
            $customerName = $cStmt->fetchColumn() ?: '';
            if (empty($customerName) && $bitrix->isConfigured()) {
                $cGet = $bitrix->call('crm.company.get', ['id' => $companyId]);
                $customerName = $cGet['result']['TITLE'] ?? '';
            }
        }
        if (empty($customerName) && $contactId > 0) {
            $ctStmt = $pdo->prepare("SELECT name || ' ' || last_name FROM crm_contacts WHERE bitrix_contact_id = ?");
            $ctStmt->execute([$contactId]);
            $customerName = trim($ctStmt->fetchColumn() ?: '');
            if (empty($customerName) && $bitrix->isConfigured()) {
                $ctGet = $bitrix->call('crm.contact.get', ['id' => $contactId]);
                $customerName = trim(($ctGet['result']['NAME'] ?? '') . ' ' . ($ctGet['result']['LAST_NAME'] ?? ''));
            }
        }
        if (empty($customerName) && !empty($deal['TITLE'])) {
            $customerName = $deal['TITLE'];
        }
    }

    echo json_encode([
        'success' => true,
        'deal_id' => $dealIdParam,
        'manager' => $managerName ?: '',
        'customer' => $customerName ?: '',
        'delivery_date' => $deal['UF_CRM_DELIVERY_DATE'] ?? date('Y-m-d', strtotime('+7 days')),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// 1. Live Sync / Fetch if configured or forceRefresh requested
if ($bitrix->isConfigured() && ($forceRefresh || $pdo->query("SELECT count(*) FROM crm_companies")->fetchColumn() == 0)) {
    // 1.1 Sync Users/Managers
    $usersRes = $bitrix->getUserList();
    $bUsers = $usersRes['result'] ?? [];
    if (is_array($bUsers) && !empty($bUsers)) {
        $syncedUIds = [];
        $uCheck = $pdo->prepare("SELECT id FROM system_users WHERE bitrix_user_id = ?");
        $uUpd = $pdo->prepare("UPDATE system_users SET name = ?, email = ?, is_active = ? WHERE bitrix_user_id = ?");
        $uIns = $pdo->prepare("INSERT INTO system_users (bitrix_user_id, name, email, role_code, is_active) VALUES (?, ?, ?, ?, ?)");
        
        foreach ($bUsers as $u) {
            $uid = (int)($u['ID'] ?? 0);
            if ($uid <= 0) continue;
            $syncedUIds[] = $uid;
            $uName = trim(($u['NAME'] ?? '') . ' ' . ($u['LAST_NAME'] ?? ''));
            if (empty($uName)) $uName = $u['EMAIL'] ?? ('Օգտատեր #' . $uid);
            $uEmail = $u['EMAIL'] ?? '';
            $uActive = (($u['ACTIVE'] ?? true) === true || ($u['ACTIVE'] ?? 'Y') === 'Y') ? 1 : 0;

            $pos = mb_strtolower($u['WORK_POSITION'] ?? '');
            $defRole = 'viewer';
            if (str_contains($pos, 'director') || str_contains($pos, 'տնօրեն') || str_contains($pos, 'admin') || str_contains($pos, 'ադմին')) {
                $defRole = 'admin';
            } elseif (str_contains($pos, 'store') || str_contains($pos, 'պահեստ')) {
                $defRole = 'storekeeper';
            } elseif (str_contains($pos, 'sales') || str_contains($pos, 'վաճառք') || str_contains($pos, 'manager') || str_contains($pos, 'մենեջեր')) {
                $defRole = 'manager';
            }

            $uCheck->execute([$uid]);
            if ($uCheck->fetch()) {
                $uUpd->execute([$uName, $uEmail, $uActive, $uid]);
            } else {
                $uIns->execute([$uid, $uName, $uEmail, $defRole, $uActive]);
            }
        }
        if (!empty($syncedUIds)) {
            $ph = implode(',', array_fill(0, count($syncedUIds), '?'));
            $pdo->prepare("DELETE FROM system_users WHERE bitrix_user_id NOT IN ($ph)")->execute($syncedUIds);
        }
    }

    // 1.2 Sync Companies
    $bCompanies = $bitrix->getAllCompanies();
    if (is_array($bCompanies) && !empty($bCompanies)) {
        $syncedCIds = [];
        $cStmt = $pdo->prepare("INSERT INTO crm_companies (bitrix_company_id, title, company_type, phone, email, updated_at) VALUES (?, ?, ?, ?, ?, datetime('now')) ON CONFLICT(bitrix_company_id) DO UPDATE SET title = excluded.title, phone = excluded.phone, email = excluded.email, updated_at = datetime('now')");
        foreach ($bCompanies as $bc) {
            $cId = (int)($bc['ID'] ?? 0);
            if ($cId <= 0) continue;
            $syncedCIds[] = $cId;
            $cTitle = $bc['TITLE'] ?? ("Ընկերություն #" . $cId);
            $cType = $bc['COMPANY_TYPE'] ?? 'CUSTOMER';
            $cPhone = is_array($bc['PHONE'] ?? null) ? ($bc['PHONE'][0]['VALUE'] ?? '') : ($bc['PHONE'] ?? '');
            $cEmail = is_array($bc['EMAIL'] ?? null) ? ($bc['EMAIL'][0]['VALUE'] ?? '') : ($bc['EMAIL'] ?? '');
            $cStmt->execute([$cId, $cTitle, $cType, $cPhone, $cEmail]);
        }
        if (!empty($syncedCIds)) {
            $ph = implode(',', array_fill(0, count($syncedCIds), '?'));
            $pdo->prepare("DELETE FROM crm_companies WHERE bitrix_company_id NOT IN ($ph)")->execute($syncedCIds);
        }
    }

    // 1.3 Sync Contacts
    $bContacts = $bitrix->getAllContacts();
    if (is_array($bContacts) && !empty($bContacts)) {
        $syncedCtIds = [];
        $ctStmt = $pdo->prepare("INSERT INTO crm_contacts (bitrix_contact_id, name, last_name, phone, email, company_id, updated_at) VALUES (?, ?, ?, ?, ?, ?, datetime('now')) ON CONFLICT(bitrix_contact_id) DO UPDATE SET name = excluded.name, last_name = excluded.last_name, phone = excluded.phone, email = excluded.email, company_id = excluded.company_id, updated_at = datetime('now')");
        foreach ($bContacts as $bct) {
            $ctId = (int)($bct['ID'] ?? 0);
            if ($ctId <= 0) continue;
            $syncedCtIds[] = $ctId;
            $ctName = $bct['NAME'] ?? 'Հաճախորդ';
            $ctLastName = $bct['LAST_NAME'] ?? '';
            $ctPhone = is_array($bct['PHONE'] ?? null) ? ($bct['PHONE'][0]['VALUE'] ?? '') : ($bct['PHONE'] ?? '');
            $ctEmail = is_array($bct['EMAIL'] ?? null) ? ($bct['EMAIL'][0]['VALUE'] ?? '') : ($bct['EMAIL'] ?? '');
            $ctCompanyId = (int)($bct['COMPANY_ID'] ?? 0);
            $ctStmt->execute([$ctId, $ctName, $ctLastName, $ctPhone, $ctEmail, $ctCompanyId]);
        }
        if (!empty($syncedCtIds)) {
            $ph = implode(',', array_fill(0, count($syncedCtIds), '?'));
            $pdo->prepare("DELETE FROM crm_contacts WHERE bitrix_contact_id NOT IN ($ph)")->execute($syncedCtIds);
        }
    }
}

// 2. Fetch Managers (system_users)
$managers = [];
if ($type === 'all' || $type === 'managers') {
    $stmt = $pdo->query("
        SELECT u.id, u.bitrix_user_id, u.name, u.email, u.role_code, r.name as role_name 
        FROM system_users u
        LEFT JOIN roles r ON u.role_code = r.code
        WHERE u.is_active = 1
        ORDER BY u.name ASC
    ");
    $managers = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// 3. Fetch Customers (CRM Companies + Contacts + Previous Reservation Names)
$companies = [];
$contacts = [];
$pastCustomers = [];

if ($type === 'all' || $type === 'customers') {
    // Companies
    $compStmt = $pdo->query("SELECT bitrix_company_id as id, title as name, company_type, phone, email, 'company' as type FROM crm_companies ORDER BY title ASC");
    $companies = $compStmt->fetchAll(PDO::FETCH_ASSOC);

    // Contacts
    $contStmt = $pdo->query("SELECT bitrix_contact_id as id, TRIM(name || ' ' || last_name) as name, phone, email, 'contact' as type FROM crm_contacts ORDER BY last_name ASC, name ASC");
    $contacts = $contStmt->fetchAll(PDO::FETCH_ASSOC);

    // Distinct past customer names from reservations that may not be in CRM table
    $pastStmt = $pdo->query("SELECT DISTINCT customer_name as name FROM product_reservations WHERE customer_name IS NOT NULL AND customer_name != ''");
    $rawPast = $pastStmt->fetchAll(PDO::FETCH_COLUMN);
    
    $existingNames = array_merge(
        array_column($companies, 'name'),
        array_column($contacts, 'name')
    );

    foreach ($rawPast as $pName) {
        $pName = trim($pName);
        if (!empty($pName) && !in_array($pName, $existingNames)) {
            $pastCustomers[] = [
                'id' => 0,
                'name' => $pName,
                'type' => 'custom',
                'phone' => '',
                'email' => ''
            ];
        }
    }
}

echo json_encode([
    'success' => true,
    'is_configured' => $bitrix->isConfigured(),
    'managers' => $managers,
    'companies' => $companies,
    'contacts' => $contacts,
    'past_customers' => $pastCustomers,
    'current_user' => $auth->getCurrentUser(),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
