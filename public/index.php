<?php

declare(strict_types=1);

// Temporary smoke test for stage 1: checks Smarty rendering and the MySQL connection.
// Replaced by the real bootstrap (config, router, controllers) in stage 3.

use App\Core\Database;
use Smarty\Smarty;

require __DIR__ . '/../vendor/autoload.php';

$config = require __DIR__ . '/../config/config.php';

$smarty = new Smarty();
$smarty->setTemplateDir(__DIR__ . '/../templates');
$smarty->setCompileDir(__DIR__ . '/../var/templates_c');
$smarty->setCacheDir(__DIR__ . '/../var/cache');
$smarty->setEscapeHtml(true);

try {
    $pdo = Database::connect($config['db']);
    $dbStatus = 'connected, MySQL ' . $pdo->query('SELECT VERSION()')->fetchColumn();
} catch (PDOException $e) {
    $dbStatus = 'connection failed: ' . $e->getMessage();
}

$smarty->assign('phpVersion', PHP_VERSION);
$smarty->assign('dbStatus', $dbStatus);
$smarty->display('hello.tpl');
