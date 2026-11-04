<?php

declare(strict_types=1);

// Temporary smoke test for stage 1: checks Smarty rendering and the MySQL connection.
// Replaced by the real bootstrap (config, router, controllers) in stage 3.

use Smarty\Smarty;

require __DIR__ . '/../vendor/autoload.php';

$smarty = new Smarty();
$smarty->setTemplateDir(__DIR__ . '/../templates');
$smarty->setCompileDir(__DIR__ . '/../var/templates_c');
$smarty->setCacheDir(__DIR__ . '/../var/cache');
$smarty->setEscapeHtml(true);

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
    getenv('DB_HOST') ?: 'mysql',
    getenv('DB_PORT') ?: '3306',
    getenv('DB_NAME') ?: 'blog',
);

try {
    $pdo = new PDO($dsn, getenv('DB_USER') ?: '', getenv('DB_PASSWORD') ?: '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $dbStatus = 'connected, MySQL ' . $pdo->query('SELECT VERSION()')->fetchColumn();
} catch (PDOException $e) {
    $dbStatus = 'connection failed: ' . $e->getMessage();
}

$smarty->assign('phpVersion', PHP_VERSION);
$smarty->assign('dbStatus', $dbStatus);
$smarty->display('hello.tpl');
