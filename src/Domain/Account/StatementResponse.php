<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Account;

/**
 * Account statement response with paginated transactions.
 *
 * @psalm-immutable
*/
final class StatementResponse
{
    /**
     * @param int $totalElements Total number of transactions in the period
     * @param array<Transaction> $transactions List of transaction items
     * @param int $page Current page number
     * @param int $pageSize Number of items per page
     * @param int $totalPages Total number of pages
     * @param string|null $accountNumber Account identifier
    */
    public function __construct(
        public readonly int $totalElements,
        public readonly array $transactions,
        public readonly int $page = 0,
        public readonly int $pageSize = 0,
        public readonly int $totalPages = 0,
        public readonly ?string $accountNumber = null,
    ) {
    }

    /**
     * Create from API response array.
     *
     * @param array<string, mixed> $data
    */
    public static function fromArray(array $data): self
    {
        $transactions = [];
        /** @var mixed $itemsRaw */
        $itemsRaw = $data['transactions'] ?? $data['items'] ?? $data['content'] ?? [];
        /** @var list<Transaction|array<string, mixed>> $items */
        $items = [];

        if (is_array($itemsRaw)) {
            /** @var mixed $raw */
            foreach ($itemsRaw as $raw) {
                if ($raw instanceof Transaction) {
                    $items[] = $raw;
                    continue;
                }

                if (is_array($raw)) {
                    /** @var array<string, mixed> $raw */
                    $items[] = $raw;
                }
            }
        }

        foreach ($items as $item) {
            if ($item instanceof Transaction) {
                $transactions[] = $item;
                continue;
            }

            $transactions[] = Transaction::fromArray($item);
        }

        return new self(
            totalElements: (int) (
                $data['totalElements']
                ?? $data['total_elements']
                ?? $data['total']
                ?? count($transactions)
            ),
            transactions: $transactions,
            page: (int) ($data['page'] ?? $data['pageNumber'] ?? $data['page_number'] ?? 0),
            pageSize: (int) ($data['pageSize'] ?? $data['page_size'] ?? $data['size'] ?? count($transactions)),
            totalPages: (int) ($data['totalPages'] ?? $data['total_pages'] ?? 1),
            accountNumber: isset($data['accountNumber']) || isset($data['account_number'])
                ? (string) ($data['accountNumber'] ?? $data['account_number'])
                : null,
        );
    }

    /**
     * Convert to array representation.
     *
     * @return array<string, mixed>
    */
    public function toArray(): array
    {
        return [
            'totalElements' => $this->totalElements,
            'transactions' => array_map(
                static fn(Transaction $t) => $t->toArray(),
                $this->transactions
            ),
            'page' => $this->page,
            'pageSize' => $this->pageSize,
            'totalPages' => $this->totalPages,
            'accountNumber' => $this->accountNumber,
        ];
    }

    /**
     * Get number of transactions in this page.
    */
    public function count(): int
    {
        return count($this->transactions);
    }

    /**
     * Check if there is a next page.
    */
    public function hasNext(): bool
    {
        return $this->page < ($this->totalPages - 1);
    }

    /**
     * Check if there is a previous page.
    */
    public function hasPrevious(): bool
    {
        return $this->page > 0;
    }

    /**
     * Check if statement is empty.
    */
    public function isEmpty(): bool
    {
        return $this->totalElements === 0;
    }

    /**
     * Get all credit transactions.
     *
     * @return array<Transaction>
    */
    public function getCredits(): array
    {
        return array_filter($this->transactions, static fn(Transaction $t) => $t->isCredit());
    }

    /**
     * Get all debit transactions.
     *
     * @return array<Transaction>
    */
    public function getDebits(): array
    {
        return array_filter($this->transactions, static fn(Transaction $t) => $t->isDebit());
    }

    /**
     * Calculate total credits amount.
    */
    public function getTotalCredits(): float
    {
        return array_reduce(
            $this->getCredits(),
            static fn(float $sum, Transaction $t) => $sum + $t->amount,
            0.0
        );
    }

    /**
     * Calculate total debits amount.
    */
    public function getTotalDebits(): float
    {
        return array_reduce(
            $this->getDebits(),
            static fn(float $sum, Transaction $t) => $sum + $t->amount,
            0.0
        );
    }
}
