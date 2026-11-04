<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;

final class Article
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly string $slug,
        public readonly string $description,
        public readonly string $content,
        public readonly ?string $image,
        public readonly int $views,
        public readonly DateTimeImmutable $publishedAt,
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromRow(array $row): self
    {
        return new self(
            (int) $row['id'],
            $row['title'],
            $row['slug'],
            $row['description'],
            $row['content'],
            $row['image'],
            (int) $row['views'],
            new DateTimeImmutable($row['published_at']),
        );
    }

    /**
     * Content paragraphs (separated by blank lines), rendered one <p> each with auto-escaping.
     *
     * @return list<string>
     */
    public function paragraphs(): array
    {
        return preg_split('/\R\s*\R/', trim($this->content), -1, PREG_SPLIT_NO_EMPTY);
    }

    public function withViews(int $views): self
    {
        return new self(
            $this->id,
            $this->title,
            $this->slug,
            $this->description,
            $this->content,
            $this->image,
            $views,
            $this->publishedAt,
        );
    }
}
