<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\Pagination;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\Pagination\Page;

final class PageTest extends TestCase
{
    public function testCanBeCreatedWithDefaultValues(): void
    {
        $page = new Page();

        $this->assertSame(0, $page->from);
        $this->assertSame(20, $page->size);
    }

    public function testCanBeCreatedWithCustomValues(): void
    {
        $page = new Page(from: 10, size: 50);

        $this->assertSame(10, $page->from);
        $this->assertSame(50, $page->size);
    }

    public function testDefaultFactory(): void
    {
        $page = Page::default();

        $this->assertSame(0, $page->from);
        $this->assertSame(20, $page->size);
    }

    public function testOfFactory(): void
    {
        $page = Page::of(from: 40, size: 100);

        $this->assertSame(40, $page->from);
        $this->assertSame(100, $page->size);
    }

    public function testGetFrom(): void
    {
        $page = new Page(from: 15, size: 25);

        $this->assertSame(15, $page->getFrom());
    }

    public function testGetSize(): void
    {
        $page = new Page(from: 0, size: 30);

        $this->assertSame(30, $page->getSize());
    }

    public function testNext(): void
    {
        $page = new Page(from: 0, size: 20);
        $nextPage = $page->next();

        $this->assertSame(20, $nextPage->from);
        $this->assertSame(20, $nextPage->size);
        $this->assertNotSame($page, $nextPage);
    }

    public function testNextPreservesSize(): void
    {
        $page = new Page(from: 10, size: 50);
        $nextPage = $page->next();

        $this->assertSame(60, $nextPage->from);
        $this->assertSame(50, $nextPage->size);
    }

    public function testPrevious(): void
    {
        $page = new Page(from: 40, size: 20);
        $prevPage = $page->previous();

        $this->assertSame(20, $prevPage->from);
        $this->assertSame(20, $prevPage->size);
        $this->assertNotSame($page, $prevPage);
    }

    public function testPreviousFromFirstPageStaysAtZero(): void
    {
        $page = new Page(from: 0, size: 20);
        $prevPage = $page->previous();

        $this->assertSame(0, $prevPage->from);
        $this->assertSame(20, $prevPage->size);
    }

    public function testPreviousWithFromLessThanSizeGoesToZero(): void
    {
        $page = new Page(from: 10, size: 20);
        $prevPage = $page->previous();

        $this->assertSame(0, $prevPage->from);
        $this->assertSame(20, $prevPage->size);
    }

    public function testIsFirstReturnsTrueForFirstPage(): void
    {
        $page = new Page(from: 0, size: 20);

        $this->assertTrue($page->isFirst());
    }

    public function testIsFirstReturnsFalseForNonFirstPage(): void
    {
        $page = new Page(from: 20, size: 20);

        $this->assertFalse($page->isFirst());
    }

    public function testToArray(): void
    {
        $page = new Page(from: 10, size: 25);
        $array = $page->toArray();

        $this->assertSame(['from' => 10, 'size' => 25], $array);
    }

    public function testThrowsExceptionForNegativeFrom(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Page "from" must be >= 0');

        new Page(from: -1, size: 20);
    }

    public function testThrowsExceptionForZeroSize(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Page "size" must be >= 1');

        new Page(from: 0, size: 0);
    }

    public function testThrowsExceptionForNegativeSize(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Page "size" must be >= 1');

        new Page(from: 0, size: -5);
    }

    public function testThrowsExceptionForSizeGreaterThan1000(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Page "size" must be <= 1000');

        new Page(from: 0, size: 1001);
    }

    public function testAllowsSizeOf1000(): void
    {
        $page = new Page(from: 0, size: 1000);

        $this->assertSame(1000, $page->size);
    }

    public function testAllowsSizeOf1(): void
    {
        $page = new Page(from: 0, size: 1);

        $this->assertSame(1, $page->size);
    }
}
