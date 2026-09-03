<?php
/**
 * Database Connection & Migration Handler
 */

require_once __DIR__ . '/config.php';

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            self::$instance = self::createConnection();
            self::ensureTables();
        }
        return self::$instance;
    }

    private static function createConnection(): PDO {
        $driver = DB_DRIVER;

        if ($driver === 'mysql') {
            try {
                $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME);
                $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
                return $pdo;
            } catch (PDOException $e) {
                // If MySQL is not reachable or DB does not exist, fallback gracefully to SQLite
                error_log("MySQL connection failed: " . $e->getMessage() . ". Falling back to SQLite.");
            }
        }

        // SQLite initialization
        $dbDir = dirname(DB_SQLITE_PATH);
        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0777, true);
        }

        $dsn = 'sqlite:' . DB_SQLITE_PATH;
        $pdo = new PDO($dsn, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $pdo->exec('PRAGMA journal_mode = WAL;');
        $pdo->exec('PRAGMA foreign_keys = ON;');

        return $pdo;
    }

    public static function ensureTables(): void {
        $pdo = self::$instance;
        if (!$pdo) return;

        // Check if tables exist & run column migrations
        try {
            $check = $pdo->query("SELECT 1 FROM products LIMIT 1");
            if ($check !== false) {
                // Ensure delivery_days column exists
                try {
                    $pdo->exec("ALTER TABLE products ADD COLUMN delivery_days INTEGER NOT NULL DEFAULT 7");
                } catch (Exception $ex) {}
                return; // Already initialized
            }
        } catch (Exception $e) {
            // Tables do not exist yet, create schema and seed
        }

        self::runMigration();
    }

    public static function runMigration(): void {
        $pdo = self::$instance;
        $schemaFile = APP_DB . '/schema.sql';
        $seedFile = APP_DB . '/seed.sql';

        if (file_exists($schemaFile)) {
            $sql = file_get_contents($schemaFile);
            $pdo->exec($sql);
        }

        if (file_exists($seedFile)) {
            $seedSql = file_get_contents($seedFile);
            $pdo->exec($seedSql);
        }
    }

    public static function resetDatabase(): void {
        $pdo = self::getConnection();
        $tables = ['stock_movements', 'system_users', 'roles', 'product_reservations', 'incoming_shipments', 'warehouses', 'products', 'settings', 'stock_history_logs', 'crm_companies', 'crm_contacts'];
        foreach ($tables as $table) {
            $pdo->exec("DROP TABLE IF EXISTS {$table}");
        }
        self::runMigration();
    }
}
