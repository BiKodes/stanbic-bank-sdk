<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Account;

/**
 * Transaction status response.
 *
 * @psalm-immutable
*/
final class TransactionStatusResponse
{
    /**
     * @param string $bankStatus Bank processing status (e.g., SUCCESS, PENDING, FAILED)
     * @param string $bankReferenceId Bank reference identifier
     * @param float|null $transferFee Transaction fee charged
     * @param string|null $transactionId Original transaction identifier
     * @param string|null $statusDescription Status description or reason
     * @param string|null $timestamp Status timestamp
    */
    public function __construct(
        public readonly string $bankStatus,
        public readonly string $bankReferenceId,
        public readonly ?float $transferFee = null,
        public readonly ?string $transactionId = null,
        public readonly ?string $statusDescription = null,
        public readonly ?string $timestamp = null,
    ) {
    }

    /**
     * Create from API response array.
     *
     * @param array<string, mixed> $data
    */
    public static function fromArray(array $data): self
    {
        return new self(
            bankStatus: (string) ($data['bankStatus'] ?? $data['bank_status'] ?? $data['status'] ?? 'UNKNOWN'),
            bankReferenceId: (string) (
                $data['bankReferenceId']
                ?? $data['bank_reference_id']
                ?? $data['referenceId']
                ?? ''
            ),
            transferFee: isset($data['transferFee']) || isset($data['transfer_fee'])
                ? (float) ($data['transferFee'] ?? $data['transfer_fee'])
                : null,
            transactionId: isset($data['transactionId']) || isset($data['transaction_id'])
                ? (string) ($data['transactionId'] ?? $data['transaction_id'])
                : null,
            statusDescription: isset($data['statusDescription'])
                || isset($data['status_description'])
                || isset($data['description'])
                ? (string) ($data['statusDescription'] ?? $data['status_description'] ?? $data['description'])
                : null,
            timestamp: isset($data['timestamp']) || isset($data['statusDate']) || isset($data['status_date'])
                ? (string) ($data['timestamp'] ?? $data['statusDate'] ?? $data['status_date'])
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
            'bankStatus' => $this->bankStatus,
            'bankReferenceId' => $this->bankReferenceId,
            'transferFee' => $this->transferFee,
            'transactionId' => $this->transactionId,
            'statusDescription' => $this->statusDescription,
            'timestamp' => $this->timestamp,
        ];
    }

    /**
     * Check if transaction is successful.
    */
    public function isSuccess(): bool
    {
        return strtoupper($this->bankStatus) === 'SUCCESS';
    }

    /**
     * Check if transaction is pending.
    */
    public function isPending(): bool
    {
        return in_array(strtoupper($this->bankStatus), ['PENDING', 'PROCESSING', 'IN_PROGRESS'], true);
    }

    /**
     * Check if transaction failed.
    */
    public function isFailed(): bool
    {
        return in_array(strtoupper($this->bankStatus), ['FAILED', 'REJECTED', 'ERROR'], true);
    }
}
