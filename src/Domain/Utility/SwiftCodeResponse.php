<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Utility;

/**
 * Swift Code Response.
 *
 * API: GET /swift-codes/{countryCode}
 * Response contains SWIFT/BIC codes for a given country.
 *
 * @psalm-immutable
*/
final class SwiftCodeResponse
{
    public function __construct(
        public readonly string $swiftCode,
        public readonly string $bankName,
    ) {
    }

    /**
     * @param array<string, mixed> $data
    */
    public static function fromArray(array $data): self
    {
        return new self(
            swiftCode: (string) ($data['swiftCode'] ?? ''),
            bankName: (string) ($data['bankName'] ?? ''),
        );
    }

    /**
     * @return array<string, mixed>
    */
    public function toArray(): array
    {
        return [
            'swiftCode' => $this->swiftCode,
            'bankName' => $this->bankName,
        ];
    }
}
