<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Utility;

/**
 * Swift Code Request.
 *
 * API: GET /swift-codes/{countryCode}
 * Path parameter for retrieving SWIFT/BIC codes by country (e.g., KE, US, GB).
 *
 * @psalm-immutable
*/
final class SwiftCodeRequest
{
    public function __construct(
        public readonly string $countryCode,
    ) {
    }

    /**
     * @param array<string, mixed> $data
    */
    public static function fromArray(array $data): self
    {
        return new self(
            countryCode: (string) ($data['countryCode'] ?? ''),
        );
    }

    /**
     * @return array<string, mixed>
    */
    public function toArray(): array
    {
        return [
            'countryCode' => $this->countryCode,
        ];
    }
}
