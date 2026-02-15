<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\Account;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\Account\StatementRequest;

final class StatementRequestTest extends TestCase
{
    public function testCreateStatementRequest(): void
    {
        $start = new DateTimeImmutable('2026-01-15');
        $end = new DateTimeImmutable('2026-02-15');

        $request = new StatementRequest(
            accountNumber: '1234567890',
            bookingDateGreaterThan: $start,
            bookingDateLessThan: $end,
            page: 0,
            pageSize: 50
        );

        $this->assertSame('1234567890', $request->accountNumber);
        $this->assertSame($start, $request->bookingDateGreaterThan);
        $this->assertSame($end, $request->bookingDateLessThan);
        $this->assertSame(0, $request->page);
        $this->assertSame(50, $request->pageSize);
    }

    public function testCreateStatementRequestWithoutPageSize(): void
    {
        $start = new DateTimeImmutable('2026-02-01');
        $end = new DateTimeImmutable('2026-02-28');

        $request = new StatementRequest(
            accountNumber: '9876543210',
            bookingDateGreaterThan: $start,
            bookingDateLessThan: $end
        );

        $this->assertSame('9876543210', $request->accountNumber);
        $this->assertSame(0, $request->page);
        $this->assertNull($request->pageSize);
    }

    public function testFromArrayWithCamelCase(): void
    {
        $data = [
            'accountNumber' => '1111111111',
            'bookingDateGreaterThan' => '2026-01-01',
            'bookingDateLessThan' => '2026-02-15',
            'page' => 2,
            'pageSize' => 100
        ];

        $request = StatementRequest::fromArray($data);

        $this->assertSame('1111111111', $request->accountNumber);
        $this->assertSame(2, $request->page);
        $this->assertSame(100, $request->pageSize);
    }

    public function testFromArrayWithSnakeCase(): void
    {
        $data = [
            'account_number' => '2222222222',
            'booking_date_greater_than' => '2026-02-01',
            'booking_date_less_than' => '2026-02-15',
            'page' => 1,
            'page_size' => 75
        ];

        $request = StatementRequest::fromArray($data);

        $this->assertSame('2222222222', $request->accountNumber);
        $this->assertSame(1, $request->page);
        $this->assertSame(75, $request->pageSize);
    }

    public function testFromArrayWithDateObjects(): void
    {
        $start = new DateTimeImmutable('2026-01-20');
        $end = new DateTimeImmutable('2026-02-20');

        $data = [
            'accountNumber' => '3333333333',
            'bookingDateGreaterThan' => $start,
            'bookingDateLessThan' => $end,
            'page' => 0
        ];

        $request = StatementRequest::fromArray($data);

        $this->assertSame($start, $request->bookingDateGreaterThan);
        $this->assertSame($end, $request->bookingDateLessThan);
    }

    public function testFromArrayWithDefaults(): void
    {
        $data = [
            'accountNumber' => '4444444444'
        ];

        $request = StatementRequest::fromArray($data);

        $this->assertSame('4444444444', $request->accountNumber);
        $this->assertSame(0, $request->page);
        $this->assertNull($request->pageSize);

        $daysAgo = $request->bookingDateGreaterThan->diff(new DateTimeImmutable())->days;
        $this->assertGreaterThanOrEqual(29, $daysAgo);
        $this->assertLessThanOrEqual(31, $daysAgo);
    }

    public function testForLastDays(): void
    {
        $request = StatementRequest::forLastDays('5555555555', 7, 0);

        $this->assertSame('5555555555', $request->accountNumber);
        $this->assertSame(0, $request->page);

        $daysAgo = $request->bookingDateGreaterThan->diff(new DateTimeImmutable())->days;
        $this->assertGreaterThanOrEqual(6, $daysAgo);
        $this->assertLessThanOrEqual(8, $daysAgo);
    }

    public function testForLastDaysWithPages(): void
    {
        $request = StatementRequest::forLastDays('6666666666', 30, 2);

        $this->assertSame('6666666666', $request->accountNumber);
        $this->assertSame(2, $request->page);
    }

    public function testForCurrentMonth(): void
    {
        $request = StatementRequest::forCurrentMonth('7777777777', 0);

        $this->assertSame('7777777777', $request->accountNumber);
        $this->assertSame(0, $request->page);

        $this->assertSame(1, (int) $request->bookingDateGreaterThan->format('d'));
        $this->assertGreaterThan(1, (int) $request->bookingDateLessThan->format('d'));
    }

    public function testForCurrentMonthWithPageNumber(): void
    {
        $request = StatementRequest::forCurrentMonth('8888888888', 1);

        $this->assertSame('8888888888', $request->accountNumber);
        $this->assertSame(1, $request->page);
    }

    public function testToArray(): void
    {
        $start = new DateTimeImmutable('2026-01-10');
        $end = new DateTimeImmutable('2026-02-10');

        $request = new StatementRequest(
            accountNumber: '9999999999',
            bookingDateGreaterThan: $start,
            bookingDateLessThan: $end,
            page: 1,
            pageSize: 25
        );

        $array = $request->toArray();

        $this->assertArrayHasKey('accountNumber', $array);
        $this->assertArrayHasKey('bookingDateGreaterThan', $array);
        $this->assertArrayHasKey('bookingDateLessThan', $array);
        $this->assertArrayHasKey('page', $array);
        $this->assertArrayHasKey('pageSize', $array);
        $this->assertSame('9999999999', $array['accountNumber']);
        $this->assertSame('2026-01-10', $array['bookingDateGreaterThan']);
        $this->assertSame('2026-02-10', $array['bookingDateLessThan']);
        $this->assertSame(1, $array['page']);
        $this->assertSame(25, $array['pageSize']);
    }

    public function testToArrayWithoutPageSize(): void
    {
        $start = new DateTimeImmutable('2026-02-05');
        $end = new DateTimeImmutable('2026-02-15');

        $request = new StatementRequest(
            accountNumber: '1010101010',
            bookingDateGreaterThan: $start,
            bookingDateLessThan: $end
        );

        $array = $request->toArray();

        $this->assertArrayNotHasKey('pageSize', $array);
        $this->assertArrayHasKey('page', $array);
    }

    public function testRoundTripSerialization(): void
    {
        $start = new DateTimeImmutable('2026-01-05');
        $end = new DateTimeImmutable('2026-02-05');

        $original = new StatementRequest(
            accountNumber: '1111222233',
            bookingDateGreaterThan: $start,
            bookingDateLessThan: $end,
            page: 3,
            pageSize: 50
        );

        $restored = StatementRequest::fromArray($original->toArray());

        $this->assertSame($original->accountNumber, $restored->accountNumber);
        $this->assertSame($original->page, $restored->page);
        $this->assertSame($original->pageSize, $restored->pageSize);
        $this->assertEquals(
            $original->bookingDateGreaterThan->format('Y-m-d'),
            $restored->bookingDateGreaterThan->format('Y-m-d')
        );
        $this->assertEquals(
            $original->bookingDateLessThan->format('Y-m-d'),
            $restored->bookingDateLessThan->format('Y-m-d')
        );
    }
}
