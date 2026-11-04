<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var list<array{pattern: string, handler: callable}> */
    private array $routes = [];

    /**
     * Registers a GET route. Placeholders like {slug} match lowercase slugs only,
     * so malformed URLs get a 404 before any controller or database call.
     */
    public function get(string $path, callable $handler): void
    {
        $pattern = preg_replace('#\{(\w+)\}#', '(?P<$1>[a-z0-9-]+)', $path);

        $this->routes[] = [
            'pattern' => '#^' . $pattern . '$#',
            'handler' => $handler,
        ];
    }

    /**
     * @throws NotFoundException when no route matches
     */
    public function dispatch(Request $request): Response
    {
        // HEAD is GET without a body; the web server drops the body.
        if ($request->method !== 'GET' && $request->method !== 'HEAD') {
            throw new NotFoundException();
        }

        $path = rtrim($request->path, '/') ?: '/';

        foreach ($this->routes as $route) {
            if (preg_match($route['pattern'], $path, $matches) === 1) {
                // Canonical URLs have no trailing slash: "/category/php/" -> 301 to "/category/php".
                // Redirecting only to a matched route (never to raw input) rules out open redirects
                // such as "/\evil.com/", which browsers would treat as "//evil.com".
                if ($path !== $request->path) {
                    $query = $request->queryString !== '' ? '?' . $request->queryString : '';

                    return new Response('', 301, ['Location' => $path . $query]);
                }

                // Named groups become named arguments: {slug} -> show(Request $request, string $slug).
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                return ($route['handler'])($request, ...$params);
            }
        }

        throw new NotFoundException();
    }
}
