-- Table: products (Bitrix24 Products Cache)
CREATE TABLE IF NOT EXISTS products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    bitrix_product_id INTEGER NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    sku VARCHAR(100) DEFAULT '',
    current_stock DECIMAL(12,2) NOT NULL DEFAULT 0,
    reserved_stock DECIMAL(12,2) NOT NULL DEFAULT 0,
    unit VARCHAR(20) NOT NULL DEFAULT 'հատ',
    price DECIMAL(12,2) NOT NULL DEFAULT 0,
    cost_price DECIMAL(12,2) NOT NULL DEFAULT 0,
    min_stock DECIMAL(12,2) NOT NULL DEFAULT 0,
    max_stock DECIMAL(12,2) NOT NULL DEFAULT 0,
    delivery_days INTEGER NOT NULL DEFAULT 7,
    currency VARCHAR(10) NOT NULL DEFAULT 'AMD',
    updated_at DATETIME NOT NULL
);

-- Table: warehouses (Warehouses / Stores synced from Bitrix24)
CREATE TABLE IF NOT EXISTS warehouses (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    bitrix_store_id INTEGER NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    address VARCHAR(255) DEFAULT '',
    is_active INTEGER NOT NULL DEFAULT 1
);

-- Table: incoming_shipments (Expected future arrivals from suppliers)
-- Statuses: PLANNED, CONFIRMED, IN_TRANSIT, RECEIVED, CANCELLED
CREATE TABLE IF NOT EXISTS incoming_shipments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    bitrix_product_id INTEGER NOT NULL,
    supplier_name VARCHAR(255) NOT NULL DEFAULT 'Մատակարար',
    supplier_id VARCHAR(100) DEFAULT '',
    quantity DECIMAL(12,2) NOT NULL,
    expected_date DATE NOT NULL,
    warehouse_id INTEGER NOT NULL DEFAULT 1,
    status VARCHAR(30) NOT NULL DEFAULT 'CONFIRMED',
    notes TEXT DEFAULT '',
    bitrix_doc_id INTEGER DEFAULT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
);

-- Table: product_reservations (Reservations tied to Bitrix24 Deals / Customers)
-- Statuses: DRAFT, RESERVED, CONFIRMED, SHIPPED, CANCELLED, EXPIRED
CREATE TABLE IF NOT EXISTS product_reservations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    deal_id INTEGER NOT NULL,
    bitrix_product_id INTEGER NOT NULL,
    quantity DECIMAL(12,2) NOT NULL,
    delivery_date DATE NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'RESERVED',
    manager_name VARCHAR(255) DEFAULT 'Մենեջեր',
    customer_name VARCHAR(255) DEFAULT 'Հաdelays',
    warehouse_id INTEGER NOT NULL DEFAULT 1,
    notes TEXT DEFAULT '',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
);

-- Table: stock_movements (Stock change history for consumption/turnover analysis)
-- Types: SALE, SHIPMENT_RECEIVE, RESERVATION_SHIP, ADJUSTMENT, RETURN
CREATE TABLE IF NOT EXISTS stock_movements (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    bitrix_product_id INTEGER NOT NULL,
    movement_type VARCHAR(30) NOT NULL,
    quantity DECIMAL(12,2) NOT NULL,
    direction VARCHAR(5) NOT NULL DEFAULT 'OUT',
    warehouse_id INTEGER NOT NULL DEFAULT 1,
    reference_id INTEGER DEFAULT NULL,
    notes TEXT DEFAULT '',
    movement_date DATE NOT NULL,
    created_at DATETIME NOT NULL
);

-- Table: roles (Employee positions and permission lists)
CREATE TABLE IF NOT EXISTS roles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    description TEXT DEFAULT '',
    permissions TEXT NOT NULL -- JSON array of permission codes
);

-- Table: system_users (Employees / Users assigned to roles)
CREATE TABLE IF NOT EXISTS system_users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    bitrix_user_id INTEGER DEFAULT 0,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) DEFAULT '',
    role_code VARCHAR(50) NOT NULL,
    is_active INTEGER NOT NULL DEFAULT 1,
    FOREIGN KEY (role_code) REFERENCES roles(code)
);

-- Table: settings (Key-Value configuration storage)
CREATE TABLE IF NOT EXISTS settings (
    key VARCHAR(100) PRIMARY KEY,
    value TEXT NOT NULL,
    updated_at DATETIME NOT NULL
);

-- Table: stock_history_logs (Audit logs)
CREATE TABLE IF NOT EXISTS stock_history_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    event_type VARCHAR(50) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id INTEGER NOT NULL,
    description TEXT NOT NULL,
    created_at DATETIME NOT NULL
);

-- Table: crm_companies (Cached Bitrix24 CRM Companies)
CREATE TABLE IF NOT EXISTS crm_companies (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    bitrix_company_id INTEGER NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    company_type VARCHAR(100) DEFAULT 'CUSTOMER',
    phone VARCHAR(100) DEFAULT '',
    email VARCHAR(255) DEFAULT '',
    updated_at DATETIME NOT NULL
);

-- Table: crm_contacts (Cached Bitrix24 CRM Contacts)
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

-- Indices for rapid queries
CREATE INDEX IF NOT EXISTS idx_shipments_product_date ON incoming_shipments (bitrix_product_id, expected_date, status);
CREATE INDEX IF NOT EXISTS idx_reservations_product_date ON product_reservations (bitrix_product_id, delivery_date, status);
CREATE INDEX IF NOT EXISTS idx_reservations_deal ON product_reservations (deal_id);
CREATE INDEX IF NOT EXISTS idx_users_role ON system_users (role_code);
CREATE INDEX IF NOT EXISTS idx_movements_product_date ON stock_movements (bitrix_product_id, movement_date, direction);
CREATE INDEX IF NOT EXISTS idx_movements_date ON stock_movements (movement_date);
