<?php
/**
 * Application Configuration
 * Armenian Language Support (Հայերեն)
 */

define('APP_NAME', 'Պահեստ և Ապրանքների Ամրագրում');
define('APP_VERSION', '2.0.0');
define('APP_ROOT', dirname(__DIR__));
define('APP_SRC', APP_ROOT . '/src');
define('APP_CONFIG', APP_ROOT . '/config');
define('APP_DB', APP_ROOT . '/database');

// Database configuration (SQLite by default, MySQL supported)
define('DB_DRIVER', 'sqlite'); // 'sqlite' or 'mysql'
define('DB_SQLITE_PATH', APP_DB . '/sklad.sqlite');

// MySQL credentials (if DB_DRIVER is mysql)
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'sklad_bitrix');
define('DB_USER', 'root');
define('DB_PASSWORD', '');

// Bitrix24 default webhook (can also be saved in database settings table)
define('BITRIX_WEBHOOK_URL', 'https://buyonline.bitrix24.ru/rest/47141/f1nw0e0nq54qc6ln/'); 
define('BITRIX_DOMAIN', 'buyonline.bitrix24.ru');

// Reservation TTL in days (auto-expire unconfirmed reservations)
define('DEFAULT_RESERVATION_TTL_DAYS', 7);
