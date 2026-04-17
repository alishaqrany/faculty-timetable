<?php
/**
 * Database Migration Runner
 *
 * Usage:
 *   php database/migrate.php                  — Run pending migrations
 *   php database/migrate.php --seed           — Run migrations + seeds
 *   php database/migrate.php --fresh          — Drop all tables, re-run everything
 *   php database/migrate.php --status         — Show migration status
 */

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/core/Database.php';
require_once APP_ROOT . '/core/Session.php';
require_once APP_ROOT . '/core/Request.php';
require_once APP_ROOT . '/app/Helpers/functions.php';

// Load config
$GLOBALS['__app_config'] = [];
$GLOBALS['__app_config']['app'] = require APP_ROOT . '/config/app.php';
$GLOBALS['__app_config']['database'] = require APP_ROOT . '/config/database.php';

$cfg = $GLOBALS['__app_config']['database'];

echo "=== Migration Runner ===\n";
echo "Database: {$cfg['database']}\n\n";

// Connect
try {
    // First try connecting without database name to create it if needed
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $conn = new mysqli($cfg['host'], $cfg['username'], $cfg['password']);
    $conn->set_charset('utf8mb4');

    // Create database if not exists
    $dbname = $conn->real_escape_string($cfg['database']);
    $conn->query("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $conn->select_db($cfg['database']);

    // Now initialize the Database singleton
    Database::connectWith($cfg['host'], $cfg['username'], $cfg['password'], $cfg['database']);
    $db = Database::getInstance();
} catch (Exception $e) {
    echo "ERROR: Cannot connect to database: " . $e->getMessage() . "\n";
    exit(1);
}

// Create migrations tracking table
$db->raw("CREATE TABLE IF NOT EXISTS `migrations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `migration` VARCHAR(255) NOT NULL,
    `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `migration_unique` (`migration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$command = $argv[1] ?? '';

// --status
if ($command === '--status') {
    $executed = $db->fetchAll("SELECT migration, executed_at FROM migrations ORDER BY id");
    echo "Executed migrations:\n";
    foreach ($executed as $row) {
        echo "  ✓ {$row['migration']} ({$row['executed_at']})\n";
    }
    $files = glob(APP_ROOT . '/database/migrations/*.php');
    sort($files);
    $executedNames = array_column($executed, 'migration');
    echo "\nPending:\n";
    $pending = 0;
    foreach ($files as $file) {
        $name = basename($file, '.php');
        if (!in_array($name, $executedNames)) {
            echo "  ○ $name\n";
            $pending++;
        }
    }
    if ($pending === 0) echo "  (none)\n";
    exit(0);
}

// --fresh: drop all tables
if ($command === '--fresh') {
    echo "Dropping all tables...\n";
    $db->raw("SET FOREIGN_KEY_CHECKS = 0");
    $tables = $db->fetchAll("SHOW TABLES");
    foreach ($tables as $row) {
        $tableName = reset($row);
        $db->raw("DROP TABLE IF EXISTS `$tableName`");
        echo "  Dropped: $tableName\n";
    }
    $db->raw("SET FOREIGN_KEY_CHECKS = 1");
    // Re-create migrations table
    $db->raw("CREATE TABLE `migrations` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `migration` VARCHAR(255) NOT NULL,
        `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY `migration_unique` (`migration`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "\n";
}

// Run pending migrations
$files = glob(APP_ROOT . '/database/migrations/*.php');
sort($files);

$executed = $db->fetchAll("SELECT migration FROM migrations");
$executedNames = array_column($executed, 'migration');
$ran = 0;

foreach ($files as $file) {
    $name = basename($file, '.php');
    if (in_array($name, $executedNames)) continue;

    echo "Migrating: $name ... ";
    try {
        $migration = require $file;
        if (is_callable($migration)) {
            $migration($db);
        }
        $db->insert('migrations', ['migration' => $name]);
        echo "✓\n";
        $ran++;
    } catch (Exception $e) {
        echo "✗ ERROR: " . $e->getMessage() . "\n";
        exit(1);
    }
}

if ($ran === 0) {
    echo "Nothing to migrate.\n";
} else {
    echo "\nMigrated $ran file(s) successfully.\n";
}

// --seed
if ($command === '--seed' || ($command === '--fresh' && in_array('--seed', $argv))) {
    echo "\n=== Running Seeds ===\n";
    $seedFiles = glob(APP_ROOT . '/database/seeds/*.php');
    sort($seedFiles);
    foreach ($seedFiles as $seedFile) {
        $seedName = basename($seedFile, '.php');
        echo "Seeding: $seedName ... ";
        try {
            $seed = require $seedFile;
            if (is_callable($seed)) {
                $seed($db);
            }
            echo "✓\n";
        } catch (Exception $e) {
            echo "✗ ERROR: " . $e->getMessage() . "\n";
        }
    }
    echo "\nSeeding complete.\n";
}

echo "\nDone.\n";
