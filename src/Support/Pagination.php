<?php

declare(strict_types=1);

namespace App\Support;

use InvalidArgumentException;

final class Pagination
{
    public function __construct(
        public readonly int $page,
        public readonly int $perPage,
        public readonly int $total,
    ) {
        if ($page < 1 || $perPage < 1 || $total < 0) {
            throw new InvalidArgumentException('Invalid pagination values.');
        }
    }

    /** An empty list still has one (empty) page. */
    public function totalPages(): int
    {
        return max(1, (int) ceil($this->total / $this->perPage));
    }

    public function isOutOfRange(): bool
    {
        return $this->page > $this->totalPages();
    }

    public function offset(): int
    {
        return ($this->page - 1) * $this->perPage;
    }

    public function hasPrevious(): bool
    {
        return $this->page > 1;
    }

    public function hasNext(): bool
    {
        return $this->page < $this->totalPages();
    }

    /**
     * Page numbers to show: first, last and $radius pages around the current one; null marks a gap.
     * A gap of a single page shows that page instead of "…". Example (page 6 of 20): 1 … 4 5 6 7 8 … 20.
     *
     * @return list<int|null>
     */
    public function links(int $radius = 2): array
    {
        $links = [];
        $previous = 0;

        for ($page = 1; $page <= $this->totalPages(); $page++) {
            $visible = $page === 1 || $page === $this->totalPages() || abs($page - $this->page) <= $radius;
            if (!$visible) {
                continue;
            }

            if ($page - $previous === 2) {
                $links[] = $page - 1;
            } elseif ($page - $previous > 2) {
                $links[] = null;
            }
            $links[] = $page;
            $previous = $page;
        }

        return $links;
    }
}
