<?php

declare(strict_types=1);

namespace App\Entity;

final class Category
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly ?string $description,
    ) {
    }

    /**
     * @param array<string, mixed> $row  a DB row; $prefix selects aliased columns in JOIN queries
     */
    public static function fromRow(array $row, string $prefix = ''): self
    {
        return new self(
            (int) $row[$prefix . 'id'],
            $row[$prefix . 'name'],
            $row[$prefix . 'slug'],
            $row[$prefix . 'description'],
        );
    }
}
