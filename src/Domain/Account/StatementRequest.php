<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Account;

use DateTimeImmutable;

/**
 * Account statement request parameters.
 *
 * @psalm-immutable
*/
final class StatementRequest
{
    /**
     * @param string $accountNumber Account identifier
     * @param DateTimeImmutable $bookingDateGreaterThan Start date for statement period
     * @param DateTimeImmutable $bookingDateLessThan End date for statement period
     * @param int $page Page number for pagination (0-based)
     * @param int|null $pageSize Number of transactions per page
    */
    public function __construct(
        public readonly string $accountNumber,
        public readonly DateTimeImmutable $bookingDateGreaterThan,
        public readonly DateTimeImmutable $bookingDateLessThan,
        public readonly int $page = 0,
        public readonly ?int $pageSize = null,
    ) {
    }

    /**
     * Create from array parameters.
     *
     * @param array<string, mixed> $data
    */
    public static function fromArray(array $data): self
    {
        /** @var mixed $startDateRaw */
        $startDateRaw = $data['bookingDateGreaterThan']
            ?? $data['booking_date_greater_than']
            ?? $data['startDate']
            ?? null;
        /** @var mixed $endDateRaw */
        $endDateRaw = $data['bookingDateLessThan']
            ?? $data['booking_date_less_than']
            ?? $data['endDate']
            ?? null;

        if ($startDateRaw instanceof DateTimeImmutable) {
            $startDate = $startDateRaw;
        } else {
            $startDate = new DateTimeImmutable((string) ($startDateRaw ?? '-30 days'));
        }

        if ($endDateRaw instanceof DateTimeImmutable) {
            $endDate = $endDateRaw;
        } else {
            $endDate = new DateTimeImmutable((string) ($endDateRaw ?? 'now'));
        }

        return new self(
            accountNumber: (string) ($data['accountNumber'] ?? $data['account_number'] ?? ''),
            bookingDateGreaterThan: $startDate,
            bookingDateLessThan: $endDate,
            page: (int) ($data['page'] ?? 0),
            pageSize: isset($data['pageSize']) || isset($data['page_size'])
                ? (int) ($data['pageSize'] ?? $data['page_size'])
                : null,
        );
    }

    /**
     * Convert to array for API request.
     *
     * @return array<string, mixed>
    */
    public function toArray(): array
    {
        $data = [
            'accountNumber' => $this->accountNumber,
            'bookingDateGreaterThan' => $this->bookingDateGreaterThan->format('Y-m-d'),
            'bookingDateLessThan' => $this->bookingDateLessThan->format('Y-m-d'),
            'page' => $this->page,
        ];

        if ($this->pageSize !== null) {
            $data['pageSize'] = $this->pageSize;
        }

        return $data;
    }

    /**
     * Create request for last N days.
    */
    public static function forLastDays(string $accountNumber, int $days, int $page = 0): self
    {
        return new self(
            accountNumber: $accountNumber,
            bookingDateGreaterThan: new DateTimeImmutable("-{$days} days"),
            bookingDateLessThan: new DateTimeImmutable(),
            page: $page,
        );
    }

    /**
     * Create request for current month.
    */
    public static function forCurrentMonth(string $accountNumber, int $page = 0): self
    {
        return new self(
            accountNumber: $accountNumber,
            bookingDateGreaterThan: new DateTimeImmutable('first day of this month'),
            bookingDateLessThan: new DateTimeImmutable('last day of this month'),
            page: $page,
        );
    }
}
