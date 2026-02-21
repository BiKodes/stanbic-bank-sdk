<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Utility;

/**
 * Sort Code Request.
 *
 * API: POST /fetch-sortcodes
 * Request to fetch bank sort codes by transaction type (PESALINK, SWIFT, RTGS, EFT).
 *
 * @psalm-immutable
*/
final class SortCodeRequest
{
    public function __construct(
        public readonly string $transactionType,
    ) {
    }

    /**
     * @param array<string, mixed> $data
    */
    public static function fromArray(array $data): self
    {
        return new self(
            transactionType: (string) ($data['transactionType'] ?? ''),
        );
    }

    /**
     * @return array<string, mixed>
    */
    public function toArray(): array
    {
        return [
            'transactionType' => $this->transactionType,
        ];
    }
}
