<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Account;

/**
 * Balance response from account balance inquiry.
 *
 * @psalm-immutable
*/
final class BalanceResponse
{
    /**
     * @param string $accountNumber Account identifier
     * @param string $currency Currency code (e.g., KES, USD)
     * @param float $availableBalance Available balance amount
     * @param float $currentBalance Current balance amount
     * @param string|null $accountName Optional account name
     * @param string|null $accountType Optional account type (e.g., SAVINGS, CURRENT)
    */
    public function __construct(
        public readonly string $accountNumber,
        public readonly string $currency,
        public readonly float $availableBalance,
        public readonly float $currentBalance,
        public readonly ?string $accountName = null,
        public readonly ?string $accountType = null,
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
            accountNumber: (string) ($data['accountNumber'] ?? $data['account_number'] ?? ''),
            currency: (string) ($data['currency'] ?? 'KES'),
            availableBalance: (float) ($data['availableBalance'] ?? $data['available_balance'] ?? 0.0),
            currentBalance: (float) ($data['currentBalance'] ?? $data['current_balance'] ?? 0.0),
            accountName: isset($data['accountName']) || isset($data['account_name'])
                ? (string) ($data['accountName'] ?? $data['account_name'])
                : null,
            accountType: isset($data['accountType']) || isset($data['account_type'])
                ? (string) ($data['accountType'] ?? $data['account_type'])
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
            'accountNumber' => $this->accountNumber,
            'currency' => $this->currency,
            'availableBalance' => $this->availableBalance,
            'currentBalance' => $this->currentBalance,
            'accountName' => $this->accountName,
            'accountType' => $this->accountType,
        ];
    }
}
