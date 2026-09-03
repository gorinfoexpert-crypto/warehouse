-- System Roles (RBAC)
INSERT OR IGNORE INTO roles (id, code, name, description, permissions) VALUES
(1, 'admin', 'Ադմինիստրատոր / Տնօրեն', 'Ամբողջական հասանելիություն բոլոր գործառույթներին', '["view_dashboard","view_simulator","view_products","sync_bitrix","view_shipments","manage_shipments","receive_shipments","view_reservations","create_reservations","manage_reservations","manage_settings","manage_roles"]'),
(2, 'manager', 'Վաճառքի մենեջեր', 'Հասանելիություն հաշվիչին և ամրագրումներին', '["view_dashboard","view_simulator","view_products","view_reservations","create_reservations","confirm_reservations"]'),
(3, 'storekeeper', 'Պահեստապետ / Լոգիստ', 'Հասանելիություն պահեստի մնացորդներին և մատակարարումներին', '["view_dashboard","view_simulator","view_products","sync_bitrix","view_shipments","manage_shipments","receive_shipments","view_reservations","ship_reservations"]'),
(4, 'viewer', 'Դիտորդ / Աուդիտոր', 'Միայն դիտման հասանելիություն', '["view_dashboard","view_simulator","view_products","view_shipments","view_reservations"]');

-- System Settings
INSERT OR IGNORE INTO settings (key, value, updated_at) VALUES
('bitrix_webhook_url', 'https://buyonline.bitrix24.ru/rest/47141/f1nw0e0nq54qc6ln/', datetime('now')),
('bitrix_domain', 'buyonline.bitrix24.ru', datetime('now')),
('default_store_id', '1', datetime('now')),
('reservation_ttl_days', '7', datetime('now')),
('auto_conduct_documents', '1', datetime('now')),
('consumption_period_days', '30', datetime('now')),
('critical_coverage_days', '3', datetime('now')),
('warning_coverage_days', '7', datetime('now'));
