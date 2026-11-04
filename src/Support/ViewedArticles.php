<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Ids of articles this browser has already viewed, kept in a cookie so a page refresh
 * does not count as a new view. The cookie is user input: only positive integers are accepted.
 */
final class ViewedArticles
{
    public const COOKIE_NAME = 'viewed_articles';
    private const MAX_IDS = 50;
    private const TTL_SECONDS = 86400;
    // RFC 6265 forbids commas in cookie values, so ids are joined with dots.
    private const SEPARATOR = '.';

    /**
     * @param list<int> $ids  oldest first
     */
    private function __construct(private readonly array $ids)
    {
    }

    public static function fromCookie(?string $value): self
    {
        $ids = [];
        foreach (explode(self::SEPARATOR, $value ?? '') as $part) {
            $id = filter_var($part, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($id !== false) {
                $ids[] = $id;
            }
        }

        return new self(array_slice(array_values(array_unique($ids)), -self::MAX_IDS));
    }

    public function has(int $id): bool
    {
        return in_array($id, $this->ids, true);
    }

    /** Keeps only the latest MAX_IDS ids so the cookie cannot grow without limit. */
    public function with(int $id): self
    {
        return new self(array_slice([...$this->ids, $id], -self::MAX_IDS));
    }

    public function toCookieHeader(): string
    {
        return sprintf(
            '%s=%s; Path=/; Max-Age=%d; HttpOnly; SameSite=Lax',
            self::COOKIE_NAME,
            implode(self::SEPARATOR, $this->ids),
            self::TTL_SECONDS,
        );
    }
}
