<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    /**
     * @param array<string, mixed> $query
     * @param array<string, mixed> $cookies
     */
    public function __construct(
        public readonly string $method,
        public readonly string $path,
        private readonly array $query,
        private readonly array $cookies = [],
    ) {
    }

    public static function fromGlobals(): self
    {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        return new self(
            strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'),
            is_string($path) && $path !== '' ? $path : '/',
            $_GET,
            $_COOKIE,
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
