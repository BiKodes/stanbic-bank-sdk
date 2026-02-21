<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\Pagination;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\Pagination\Page;
use Stanbic\SDK\Domain\Pagination\PagedResult;
use Stanbic\SDK\Domain\Pagination\StatementIterator;

final class StatementIteratorTest extends TestCase
{
    public function testCanBeCreatedWithFetcherAndPage(): void
    {
        /** @var callable(Page): PagedResult<int> $fetcher */
        $fetcher = fn(Page $page): PagedResult => PagedResult::of([1, 2, 3], 10, $page);
        $page = Page::default();

        $iterator = new StatementIterator($fetcher, $page);

        $this->assertInstanceOf(StatementIterator::class, $iterator);
    }

    public function testCreateFactory(): void
    {
        /** @var callable(Page): PagedResult<int> $fetcher */
        $fetcher = fn(Page $page): PagedResult => PagedResult::of([1, 2, 3], 10, $page);

        $iterator = StatementIterator::create($fetcher);

        $this->assertInstanceOf(StatementIterator::class, $iterator);
    }

    public function testCreateFactoryWithCustomPage(): void
    {
        /** @var callable(Page): PagedResult<int> $fetcher */
        $fetcher = fn(Page $page): PagedResult => PagedResult::of([1, 2, 3], 10, $page);
        $page = Page::of(10, 50);

        $iterator = StatementIterator::create($fetcher, $page);

        $this->assertInstanceOf(StatementIterator::class, $iterator);
    }

    public function testIterationOverSinglePage(): void
    {
        $items = [1, 2, 3, 4, 5];
        /** @var callable(Page): PagedResult<int> $fetcher */
        $fetcher = fn(Page $page): PagedResult => PagedResult::of($items, count($items), $page);

        $iterator = StatementIterator::create($fetcher);

        $result = [];
        foreach ($iterator as $item) {
            $result[] = $item;
        }

        $this->assertSame($items, $result);
    }

    public function testIterationOverMultiplePages(): void
    {
        $allItems = [
            [1, 2, 3],  // Page 0
            [4, 5, 6],  // Page 1
            [7, 8, 9],  // Page 2
        ];

        /** @var callable(Page): PagedResult<int> $fetcher */
        $fetcher = function (Page $page) use ($allItems): PagedResult {
            $pageIndex = $page->from / $page->size;
            $items = $allItems[$pageIndex] ?? [];
            return PagedResult::of($items, 9, $page);
        };

        $iterator = StatementIterator::create($fetcher, Page::of(0, 3));

        $result = [];
        foreach ($iterator as $item) {
            $result[] = $item;
        }

        $this->assertSame([1, 2, 3, 4, 5, 6, 7, 8, 9], $result);
    }

    public function testGetTotalElementsAfterIteration(): void
    {
        /** @var callable(Page): PagedResult<int> $fetcher */
        $fetcher = fn(Page $page): PagedResult => PagedResult::of([1, 2, 3], totalElements: 100, page: $page);

        $iterator = StatementIterator::create($fetcher);

        // Trigger iteration
        foreach ($iterator as $item) {
            break;
        }

        $this->assertSame(100, $iterator->getTotalElements());
    }

    public function testGetTotalElementsBeforeIterationReturnsNull(): void
    {
        /** @var callable(Page): PagedResult<int> $fetcher */
        $fetcher = fn(Page $page): PagedResult => PagedResult::of([1, 2, 3], 100, $page);

        $iterator = StatementIterator::create($fetcher);

        $this->assertNull($iterator->getTotalElements());
    }

    public function testToArrayLoadsAllPages(): void
    {
        $allItems = [
            [1, 2],  // Page 0
            [3, 4],  // Page 1
            [5],     // Page 2
        ];

        /** @var callable(Page): PagedResult<int> $fetcher */
        $fetcher = function (Page $page) use ($allItems): PagedResult {
            $pageIndex = $page->from / $page->size;
            $items = $allItems[$pageIndex] ?? [];
            return PagedResult::of($items, 5, $page);
        };

        $iterator = StatementIterator::create($fetcher, Page::of(0, 2));

        $result = $iterator->toArray();

        $this->assertSame([1, 2, 3, 4, 5], $result);
    }

    public function testRewindResetsIterator(): void
    {
        $items = [1, 2, 3];
        /** @var callable(Page): PagedResult<int> $fetcher */
        $fetcher = fn(Page $page): PagedResult => PagedResult::of($items, count($items), $page);

        $iterator = StatementIterator::create($fetcher);

        // First iteration
        $firstRun = [];
        foreach ($iterator as $item) {
            $firstRun[] = $item;
        }

        // Rewind and iterate again
        $iterator->rewind();
        $secondRun = [];
        foreach ($iterator as $item) {
            $secondRun[] = $item;
        }

        $this->assertSame($firstRun, $secondRun);
        $this->assertSame($items, $secondRun);
    }

    public function testKeyReturnsCurrentPosition(): void
    {
        $items = [10, 20, 30];
        /** @var callable(Page): PagedResult<int> $fetcher */
        $fetcher = fn(Page $page): PagedResult => PagedResult::of($items, count($items), $page);

        $iterator = StatementIterator::create($fetcher);

        $keys = [];
        foreach ($iterator as $key => $item) {
            $keys[] = $key;
        }

        $this->assertSame([0, 1, 2], $keys);
    }

    public function testCurrentReturnsCurrentItem(): void
    {
        $items = ['a', 'b', 'c'];
        /** @var callable(Page): PagedResult<string> $fetcher */
        $fetcher = fn(Page $page): PagedResult => PagedResult::of($items, count($items), $page);

        $iterator = StatementIterator::create($fetcher);
        $iterator->rewind();

        $this->assertSame('a', $iterator->current());

        $iterator->next();
        $this->assertSame('b', $iterator->current());

        $iterator->next();
        $this->assertSame('c', $iterator->current());
    }

    public function testValidReturnsTrueWhenItemExists(): void
    {
        $items = [1, 2, 3];
        /** @var callable(Page): PagedResult<int> $fetcher */
        $fetcher = fn(Page $page): PagedResult => PagedResult::of($items, count($items), $page);

        $iterator = StatementIterator::create($fetcher);
        $iterator->rewind();

        $this->assertTrue($iterator->valid());
    }

    public function testValidReturnsFalseWhenIterationComplete(): void
    {
        $items = [1, 2];
        /** @var callable(Page): PagedResult<int> $fetcher */
        $fetcher = fn(Page $page): PagedResult => PagedResult::of($items, count($items), $page);

        $iterator = StatementIterator::create($fetcher);

        foreach ($iterator as $item) {
            // Consume all items
        }

        $this->assertFalse($iterator->valid());
    }

    public function testIterationWithEmptyResult(): void
    {
        /** @var callable(Page): PagedResult<never> $fetcher */
        $fetcher = fn(Page $page): PagedResult => PagedResult::of([], 0, $page);

        $iterator = StatementIterator::create($fetcher);

        $result = [];
        foreach ($iterator as $item) {
            $result[] = $item;
        }

        $this->assertSame([], $result);
    }

    public function testIterationStopsWhenNoMoreItems(): void
    {
        $callCount = 0;
        /** @var callable(Page): PagedResult<int> $fetcher */
        $fetcher = function (Page $page) use (&$callCount): PagedResult {
            $callCount++;
            if ($page->from === 0) {
                return PagedResult::of([1, 2, 3], totalElements: 3, page: $page);
            }
            return PagedResult::of([], totalElements: 3, page: $page);
        };

        $iterator = StatementIterator::create($fetcher, Page::of(0, 3));

        $result = [];
        foreach ($iterator as $item) {
            $result[] = $item;
        }

        $this->assertSame([1, 2, 3], $result);
        $this->assertSame(1, $callCount); // Should only call fetcher once
    }

    public function testFetcherCalledMultipleTimesForMultiplePages(): void
    {
        $callCount = 0;
        $allItems = [[1, 2], [3, 4], [5]];

        /** @var callable(Page): PagedResult<int> $fetcher */
        $fetcher = function (Page $page) use (&$callCount, $allItems): PagedResult {
            $pageIndex = $page->from / $page->size;
            $items = $allItems[$pageIndex] ?? [];
            $callCount++;
            return PagedResult::of($items, 5, $page);
        };

        $iterator = StatementIterator::create($fetcher, Page::of(0, 2));

        $iterator->toArray();

        $this->assertSame(3, $callCount); // Should call fetcher for each page
    }

    public function testWorksWithCustomObjects(): void
    {
        $items = [
            (object) ['id' => 1, 'name' => 'Transaction 1'],
            (object) ['id' => 2, 'name' => 'Transaction 2'],
        ];

        /** @var callable(Page): PagedResult $fetcher */
        $fetcher = fn(Page $page): PagedResult => PagedResult::of($items, count($items), $page);

        $iterator = StatementIterator::create($fetcher);

        $result = [];
        foreach ($iterator as $item) {
            $result[] = $item;
        }

        $this->assertCount(2, $result);
        $this->assertSame(1, $result[0]->id);
        $this->assertSame(2, $result[1]->id);
    }
}
