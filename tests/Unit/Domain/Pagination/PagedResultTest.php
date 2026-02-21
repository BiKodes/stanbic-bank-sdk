<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\Pagination;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\Pagination\Page;
use Stanbic\SDK\Domain\Pagination\PagedResult;

final class PagedResultTest extends TestCase
{
    public function testCanBeCreatedWithItems(): void
    {
        $items = [1, 2, 3];
        $page = Page::default();
        $result = new PagedResult(
            items: $items,
            totalElements: 100,
            page: $page,
        );

        $this->assertSame($items, $result->items);
        $this->assertSame(100, $result->totalElements);
        $this->assertSame($page, $result->page);
    }

    public function testOfFactory(): void
    {
        $items = ['a', 'b', 'c'];
        $page = Page::of(0, 20);
        $result = PagedResult::of($items, 50, $page);

        $this->assertSame($items, $result->getItems());
        $this->assertSame(50, $result->getTotalElements());
        $this->assertSame($page, $result->getPage());
    }

    public function testGetItems(): void
    {
        $items = [10, 20, 30];
        $result = PagedResult::of($items, 100, Page::default());

        $this->assertSame($items, $result->getItems());
    }

    public function testGetTotalElements(): void
    {
        $result = PagedResult::of([1, 2], 250, Page::default());

        $this->assertSame(250, $result->getTotalElements());
    }

    public function testGetPage(): void
    {
        $page = Page::of(10, 50);
        $result = PagedResult::of([1, 2, 3], 100, $page);

        $this->assertSame($page, $result->getPage());
    }

    public function testGetSize(): void
    {
        $result = PagedResult::of([1, 2, 3, 4, 5], 100, Page::default());

        $this->assertSame(5, $result->getSize());
    }

    public function testGetSizeForEmptyItems(): void
    {
        $result = PagedResult::of([], 0, Page::default());

        $this->assertSame(0, $result->getSize());
    }

    public function testHasNextReturnsTrueWhenMorePagesExist(): void
    {
        $page = Page::of(0, 20);
        $result = PagedResult::of([1, 2, 3], totalElements: 100, page: $page);

        $this->assertTrue($result->hasNext());
    }

    public function testHasNextReturnsFalseWhenOnLastPage(): void
    {
        $page = Page::of(80, 20);
        $result = PagedResult::of([1, 2, 3], totalElements: 100, page: $page);

        $this->assertFalse($result->hasNext());
    }

    public function testHasNextReturnsFalseWhenExactlyOnLastBoundary(): void
    {
        $page = Page::of(100, 20);
        $result = PagedResult::of([], totalElements: 100, page: $page);

        $this->assertFalse($result->hasNext());
    }

    public function testIsFirstReturnsTrueForFirstPage(): void
    {
        $page = Page::of(0, 20);
        $result = PagedResult::of([1, 2, 3], 100, $page);

        $this->assertTrue($result->isFirst());
    }

    public function testIsFirstReturnsFalseForNonFirstPage(): void
    {
        $page = Page::of(20, 20);
        $result = PagedResult::of([1, 2, 3], 100, $page);

        $this->assertFalse($result->isFirst());
    }

    public function testIsLastReturnsTrueWhenOnLastPage(): void
    {
        $page = Page::of(80, 20);
        $result = PagedResult::of([1, 2, 3], totalElements: 100, page: $page);

        $this->assertTrue($result->isLast());
    }

    public function testIsLastReturnsFalseWhenNotOnLastPage(): void
    {
        $page = Page::of(0, 20);
        $result = PagedResult::of([1, 2, 3], totalElements: 100, page: $page);

        $this->assertFalse($result->isLast());
    }

    public function testGetTotalPagesCalculatesCorrectly(): void
    {
        $page = Page::of(0, 20);
        $result = PagedResult::of([1, 2, 3], totalElements: 100, page: $page);

        $this->assertSame(5, $result->getTotalPages());
    }

    public function testGetTotalPagesRoundsUpForPartialPage(): void
    {
        $page = Page::of(0, 20);
        $result = PagedResult::of([1, 2, 3], totalElements: 95, page: $page);

        $this->assertSame(5, $result->getTotalPages());
    }

    public function testGetTotalPagesReturnsZeroForEmptyResult(): void
    {
        $page = Page::of(0, 20);
        $result = PagedResult::of([], totalElements: 0, page: $page);

        $this->assertSame(0, $result->getTotalPages());
    }

    public function testGetTotalPagesForSinglePage(): void
    {
        $page = Page::of(0, 20);
        $result = PagedResult::of([1, 2, 3], totalElements: 15, page: $page);

        $this->assertSame(1, $result->getTotalPages());
    }

    public function testIsEmptyReturnsTrueForEmptyItems(): void
    {
        $result = PagedResult::of([], totalElements: 0, page: Page::default());

        $this->assertTrue($result->isEmpty());
    }

    public function testIsEmptyReturnsFalseForNonEmptyItems(): void
    {
        $result = PagedResult::of([1, 2, 3], totalElements: 100, page: Page::default());

        $this->assertFalse($result->isEmpty());
    }

    public function testWorksWithDifferentPageSizes(): void
    {
        $page = Page::of(0, 50);
        $result = PagedResult::of([1, 2, 3], totalElements: 200, page: $page);

        $this->assertSame(4, $result->getTotalPages());
        $this->assertTrue($result->hasNext());
    }

    public function testWorksWithCustomObjects(): void
    {
        $items = [
            (object) ['id' => 1, 'name' => 'Item 1'],
            (object) ['id' => 2, 'name' => 'Item 2'],
        ];
        $result = PagedResult::of($items, 10, Page::default());

        $this->assertSame($items, $result->getItems());
        $this->assertSame(2, $result->getSize());
    }
}
