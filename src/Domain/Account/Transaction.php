<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Account;

use DateTimeImmutable;

/**
 * Transaction detail from account statement.
 *
 * @psalm-immutable
*/
final class Transaction
{
    /**
     * @param string $transactionId Unique transaction identifier
     * @param DateTimeImmutable $date Transaction booking date
     * @param float $amount Transaction amount
     * @param string $currency Currency code (e.g., KES, USD)
     * @param string $type Transaction type (DEBIT, CREDIT)
     * @param string|null $counterparty Counterparty name or account
     * @param string|null $description Transaction description
     * @param string|null $reference Transaction reference number
     * @param float|null $balance Balance after transaction
    */
    public function __construct(
        public readonly string $transactionId,
        public readonly DateTimeImmutable $date,
        public readonly float $amount,
        public readonly string $currency,
        public readonly string $type,
        public readonly ?string $counterparty = null,
        public readonly ?string $description = null,
        public readonly ?string $reference = null,
        public readonly ?float $balance = null,
    ) {
    }

    /**
     * Create from API response array.
     *
     * @param array<string, mixed> $data
    */
    public static function fromArray(array $data): self
    {
        /** @var mixed $dateValue */
        $dateValue = $data['date'] ?? $data['bookingDate'] ?? $data['booking_date'] ?? 'now';
        $date = $dateValue instanceof DateTimeImmutable
            ? $dateValue
            : new DateTimeImmutable((string) $dateValue);

        return new self(
            transactionId: (string) ($data['transactionId'] ?? $data['transaction_id'] ?? $data['id'] ?? ''),
            date: $date,
            amount: (float) ($data['amount'] ?? 0.0),
            currency: (string) ($data['currency'] ?? 'KES'),
            type: (string) ($data['type'] ?? $data['transactionType'] ?? $data['transaction_type'] ?? 'DEBIT'),
            counterparty: isset($data['counterparty']) || isset($data['counterParty']) || isset($data['counter_party'])
                ? (string) ($data['counterparty'] ?? $data['counterParty'] ?? $data['counter_party'])
                : null,
            description: isset($data['description']) || isset($data['narrative'])
                ? (string) ($data['description'] ?? $data['narrative'])
                : null,
            reference: isset($data['reference']) || isset($data['referenceNumber']) || isset($data['reference_number'])
                ? (string) ($data['reference'] ?? $data['referenceNumber'] ?? $data['reference_number'])
                : null,
            balance: isset($data['balance']) ? (float) $data['balance'] : null,
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
            'transactionId' => $this->transactionId,
            'date' => $this->date->format('Y-m-d\TH:i:s\Z'),
            'amount' => $this->amount,
            'currency' => $this->currency,
            'type' => $this->type,
            'counterparty' => $this->counterparty,
            'description' => $this->description,
            'reference' => $this->reference,
            'balance' => $this->balance,
        ];
    }

    /**
     * Check if transaction is a debit.
    */
    public function isDebit(): bool
    {
        return strtoupper($this->type) === 'DEBIT';
    }

    /**
     * Check if transaction is a credit.
    */
    public function isCredit(): bool
    {
        return strtoupper($this->type) === 'CREDIT';
    }
}
