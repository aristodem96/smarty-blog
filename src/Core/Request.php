<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    /**
     * @param array<string, mixed> $query
     * @param array<string, mixed> $cookies
     * @param string $queryString  raw query without "?", kept for redirects
     */
    public function __construct(
        public readonly string $method,
        public readonly string $path,
        private readonly array $query,
        private readonly array $cookies = [],
        public readonly string $queryString = '',
    ) {
    }

    public static function fromGlobals(): self
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH);
        $queryString = parse_url($uri, PHP_URL_QUERY);

        return new self(
            strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'),
            is_string($path) && $path !== '' ? $path : '/',
            $_GET,
            $_COOKIE,
            is_string($queryString) ? $queryString : '',
        );
    }

    /**
     * Returns a query parameter only if it is a plain string (?page[]=1 yields null, not an array).
     */
    public function query(string $key): ?string
    {
        $value = $this->query[$key] ?? null;

        return is_string($value) ? $value : null;
    }

    public function cookie(string $name): ?string
    {
        $value = $this->cookies[$name] ?? null;

        return is_string($value) ? $value : null;
    }
}
