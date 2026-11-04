<?php

declare(strict_types=1);

namespace App\Repository;

/**
 * Allowed article orderings. ORDER BY cannot be a bound parameter, so this enum is the whitelist:
 * only the SQL fragments below ever reach the query, never raw user input.
 */
enum ArticleSort: string
{
    case Date = 'date';
    case Views = 'views';

    public function orderBy(): string
    {
        // a.id DESC keeps the order stable when dates or view counts are equal.
        return match ($this) {
            self::Date => 'a.published_at DESC, a.id DESC',
            self::Views => 'a.views DESC, a.id DESC',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Date => 'Новіші',
            self::Views => 'Популярні',
        };
    }
}
