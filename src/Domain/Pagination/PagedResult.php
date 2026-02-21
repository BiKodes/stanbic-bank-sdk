<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Pagination;

/**
 * Paged Result Value Object.
 *
 * Represents a paginated response with items and metadata.
 *
 * @template T
 * @psalm-immutable
*/
final class PagedResult
{
    /**
     * @param array<T> $items Items in current page
     * @param int $totalElements Total number of elements across all pages
     * @param Page $page Current page information
    */
    public function __construct(
        public readonly array $items,
        public readonly int $totalElements,
        public readonly Page $page,
    ) {
    }

    /**
     * Create PagedResult from items and total count.
     *
     * @template U
     * @param array<U> $items
     * @param int $totalElements
     * @param Page $page
     * @return self<U>
    */
    public static function of(array $items, int $totalElements, Page $page): self
    {
        return new self(
            items: $items,
            totalElements: $totalElements,
            page: $page,
        );
    }

    /**
     * Get items in current page.
     *
     * @return array<T>
    */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Get total number of elements.
    */
    public function getTotalElements(): int
    {
        return $this->totalElements;
    }

    /**
     * Get current page information.
    */
    public function getPage(): Page
    {
        return $this->page;
    }

    /**
     * Get number of items in current page.
    */
    public function getSize(): int
    {
        return count($this->items);
    }

    /**
     * Check if there are more pages.
    */
    public function hasNext(): bool
    {
        return ($this->page->from + $this->page->size) < $this->totalElements;
    }

    /**
     * Check if this is the first page.
    */
    public function isFirst(): bool
    {
        return $this->page->isFirst();
    }

    /**
     * Check if this is the last page.
    */
    public function isLast(): bool
    {
        return !$this->hasNext();
    }

    /**
     * Get total number of pages.
    */
    public function getTotalPages(): int
    {
        if ($this->totalElements === 0) {
            return 0;
        }

        return (int) ceil($this->totalElements / $this->page->size);
    }

    /**
     * Check if result is empty.
    */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }
}
