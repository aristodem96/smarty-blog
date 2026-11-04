<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    private const DEFAULT_HEADERS = ['Content-Type' => 'text/html; charset=utf-8'];

    /** @var array<string, string> */
    public readonly array $headers;

    /**
     * @param array<string, string> $headers  merged over the defaults
     */
    public function __construct(
        public readonly string $body,
        public readonly int $status = 200,
        array $headers = [],
    ) {
        $this->headers = $headers + self::DEFAULT_HEADERS;
    }

    public function send(): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }
        echo $this->body;
    }
}
