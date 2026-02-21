<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Pagination;

use Iterator;

/**
 * Statement Iterator.
 *
 * Lazy iterator for paginated statement/transaction results.
 * Automatically fetches next pages as needed.
 *
 * @template T
 * @implements Iterator<int, T>
*/
final class StatementIterator implements Iterator
{
    private int $position = 0;
    private int $pageIndex = 0;

    /** @var array<T> */
    private array $currentPageItems = [];

    private ?int $totalElements = null;

    /**
     * @param callable(Page): PagedResult<T> $fetcher Function to fetch a page
     * @param Page $initialPage Starting page
    */
    public function __construct(
        private readonly mixed $fetcher,
        private Page $currentPage,
    ) {
    }

    /**
     * Create iterator with fetcher function.
     *
     * @template U
     * @param callable(Page): PagedResult<U> $fetcher
     * @param Page|null $initialPage
     * @return self<U>
    */
    public static function create(callable $fetcher, ?Page $initialPage = null): self
    {
        return new self(
            fetcher: $fetcher,
            currentPage: $initialPage ?? Page::default(),
        );
    }

    /**
     * Rewind to the beginning.
    */
    public function rewind(): void
    {
        $this->position = 0;
        $this->pageIndex = 0;
        $this->currentPageItems = [];
        $this->totalElements = null;
        $this->loadCurrentPage();
    }

    /**
     * Get current item.
     *
     * @return T|null
    */
    public function current(): mixed
    {
        return $this->currentPageItems[$this->pageIndex] ?? null;
    }

    /**
     * Get current position.
    */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * Move to next item.
    */
    public function next(): void
    {
        $this->position++;
        $this->pageIndex++;

        if ($this->pageIndex >= count($this->currentPageItems) && $this->hasMorePages()) {
            $this->currentPage = $this->currentPage->next();
            $this->loadCurrentPage();
            $this->pageIndex = 0;
        }
    }

    /**
     * Check if current position is valid.
    */
    public function valid(): bool
    {
        return isset($this->currentPageItems[$this->pageIndex]);
    }

    /**
     * Get total number of elements across all pages.
    */
    public function getTotalElements(): ?int
    {
        return $this->totalElements;
    }

    /**
     * Convert iterator to array (loads all pages).
     *
     * @return array<T>
    */
    public function toArray(): array
    {
        $items = [];
        foreach ($this as $item) {
            $items[] = $item;
        }
        return $items;
    }

    /**
     * Load current page using the fetcher function.
    */
    private function loadCurrentPage(): void
    {
        $result = ($this->fetcher)($this->currentPage);

        $this->currentPageItems = $result->getItems();
        $this->totalElements = $result->getTotalElements();
    }

    /**
     * Check if there are more pages to fetch.
    */
    private function hasMorePages(): bool
    {
        if ($this->totalElements === null) {
            return false;
        }

        $nextFrom = $this->currentPage->from + $this->currentPage->size;
        return $nextFrom < $this->totalElements;
    }
}
