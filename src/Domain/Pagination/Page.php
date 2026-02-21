<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Pagination;

/**
 * Page Value Object.
 *
 * Represents pagination parameters for API requests.
 *
 * @psalm-immutable
*/
final class Page
{
    /**
     * @param int $from Starting index (0-based)
     * @param int $size Number of items per page
    */
    public function __construct(
        public readonly int $from = 0,
        public readonly int $size = 20,
    ) {
        if ($from < 0) {
            throw new \InvalidArgumentException('Page "from" must be >= 0');
        }

        if ($size < 1) {
            throw new \InvalidArgumentException('Page "size" must be >= 1');
        }

        if ($size > 1000) {
            throw new \InvalidArgumentException('Page "size" must be <= 1000');
        }
    }

    /**
     * Create Page with default values.
    */
    public static function default(): self
    {
        return new self();
    }

    /**
     * Create Page with specific offset and size.
    */
    public static function of(int $from, int $size): self
    {
        return new self(from: $from, size: $size);
    }

    /**
     * Get starting index.
    */
    public function getFrom(): int
    {
        return $this->from;
    }

    /**
     * Get page size.
    */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * Get next page.
    */
    public function next(): self
    {
        return new self(
            from: $this->from + $this->size,
            size: $this->size,
        );
    }

    /**
     * Get previous page (returns current page if already at start).
    */
    public function previous(): self
    {
        $newFrom = max(0, $this->from - $this->size);
        return new self(from: $newFrom, size: $this->size);
    }

    /**
     * Check if this is the first page.
    */
    public function isFirst(): bool
    {
        return $this->from === 0;
    }

    /**
     * Convert to array for API requests.
     *
     * @return array<string, int>
    */
    public function toArray(): array
    {
        return [
            'from' => $this->from,
            'size' => $this->size,
        ];
    }
}
