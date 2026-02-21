<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Utility;

/**
 * Sort Code Response.
 *
 * API: POST /fetch-sortcodes
 * Response contains bank name and sort code ID for transaction types (PESALINK, SWIFT, RTGS).
 *
 * @psalm-immutable
*/
final class SortCodeResponse
{
    public function __construct(
        public readonly string $bankName,
        public readonly string $sortCodeId,
    ) {
    }

    /**
     * @param array<string, mixed> $data
    */
    public static function fromArray(array $data): self
    {
        return new self(
            bankName: (string) ($data['bankName'] ?? ''),
            sortCodeId: (string) ($data['sortCodeId'] ?? ''),
        );
    }

    /**
     * @return array<string, mixed>
    */
    public function toArray(): array
    {
        return [
            'bankName' => $this->bankName,
            'sortCodeId' => $this->sortCodeId,
        ];
    }
}
