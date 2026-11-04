<?php

declare(strict_types=1);

// Applies new SQL files from database/migrations in filename order.
// Usage: php bin/migrate.php [--fresh]
//   --fresh  drop all tables first and re-apply every migration.

use App\Core\Database;

require __DIR__ . '/../vendor/autoload.php';

$config = require __DIR__ . '/../config/config.php';
$pdo = Database::connect($config['db']);

if (in_array('--fresh', $argv, true)) {
    dropAllTables($pdo);
    echo "Dropped all tables.\n";
}

$pdo->exec(
    'CREATE TABLE IF NOT EXISTS migrations (
        name       VARCHAR(255) NOT NULL,
        applied_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (name)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci'
);

$applied = $pdo->query('SELECT name FROM migrations')->fetchAll(PDO::FETCH_COLUMN);

$files = glob(__DIR__ . '/../database/migrations/*.sql');
sort($files);

$insert = $pdo->prepare('INSERT INTO migrations (name) VALUES (:name)');
$count = 0;

foreach ($files as $file) {
    $name = basename($file);
    if (in_array($name, $applied, true)) {
        continue;
    }

    // DDL in MySQL commits implicitly, so a migration cannot be wrapped in a transaction:
    // keep one statement per file and record it only after it succeeds.
    $pdo->exec(file_get_contents($file));
    $insert->execute(['name' => $name]);

    echo "Migrated: {$name}\n";
    $count++;
}

echo $count === 0 ? "Nothing to migrate.\n" : "Done: {$count} migration(s).\n";

function dropAllTables(PDO $pdo): void
{
    $tables = $pdo
        ->query('SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE()')
        ->fetchAll(PDO::FETCH_COLUMN);

    // Identifiers cannot be bound as parameters; names come from information_schema, not user input.
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
    foreach ($tables as $table) {
        $pdo->exec('DROP TABLE `' . str_replace('`', '``', $table) . '`');
    }
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
}
