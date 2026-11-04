<?php

declare(strict_types=1);

// Front controller: every non-static request ends up here (see docker/nginx/default.conf).

use App\Controller\ArticleController;
use App\Controller\CategoryController;
use App\Controller\HomeController;
use App\Core\Database;
use App\Core\NotFoundException;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\View;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;

require __DIR__ . '/../vendor/autoload.php';

$config = require __DIR__ . '/../config/config.php';

// Turn warnings and notices into exceptions so they are handled like any other error.
set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
    if ((error_reporting() & $severity) === 0) {
        return false;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// Cache busting: the CSS URL changes whenever the file is rebuilt.
$css = __DIR__ . '/assets/css/app.css';
$view = new View(__DIR__ . '/../templates', __DIR__ . '/../var/templates_c', [
    'assetVersion' => is_file($css) ? (string) filemtime($css) : '0',
]);

try {
    $pdo = Database::connect($config['db']);
    $categoryRepository = new CategoryRepository($pdo);
    $articleRepository = new ArticleRepository($pdo);

    $home = new HomeController($view, $categoryRepository);
    $category = new CategoryController($view, $categoryRepository, $articleRepository);
    $article = new ArticleController($view, $articleRepository, $categoryRepository);

    $router = new Router();
    $router->get('/', [$home, 'index']);
    $router->get('/category/{slug}', [$category, 'show']);
    $router->get('/article/{slug}', [$article, 'show']);

    $response = $router->dispatch(Request::fromGlobals());
} catch (NotFoundException) {
    $response = new Response($view->render('errors/404.tpl'), 404);
} catch (Throwable $e) {
    error_log((string) $e);
    $response = new Response(
        $view->render('errors/500.tpl', ['error' => $config['app']['debug'] ? (string) $e : null]),
        500,
    );
}

$response->send();
