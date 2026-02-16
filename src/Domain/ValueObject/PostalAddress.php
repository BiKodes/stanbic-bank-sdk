<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\ValueObject;

/**
 * Postal address details.
 *
 * @psalm-immutable
*/
final class PostalAddress
{
    public function __construct(
        public readonly ?string $addressLine = null,
        public readonly ?string $addressLine1 = null,
        public readonly ?string $addressLine2 = null,
        public readonly ?string $postCode = null,
        public readonly ?string $town = null,
        public readonly ?string $country = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
    */
    public static function fromArray(array $data): self
    {
        return new self(
            addressLine: isset($data['addressLine']) || isset($data['address_line'])
                ? (string) ($data['addressLine'] ?? $data['address_line'])
                : null,
            addressLine1: isset($data['addressLine1']) || isset($data['address_line1'])
                ? (string) ($data['addressLine1'] ?? $data['address_line1'])
                : null,
            addressLine2: isset($data['addressLine2']) || isset($data['address_line2'])
                ? (string) ($data['addressLine2'] ?? $data['address_line2'])
                : null,
            postCode: isset($data['postCode']) || isset($data['post_code'])
                ? (string) ($data['postCode'] ?? $data['post_code'])
                : null,
            town: isset($data['town']) ? (string) $data['town'] : null,
            country: isset($data['country']) ? (string) $data['country'] : null,
        );
    }

    /**
     * @return array<string, mixed>
    */
    public function toArray(): array
    {
        $data = [];

        if ($this->addressLine !== null) {
            $data['addressLine'] = $this->addressLine;
        }

        if ($this->addressLine1 !== null) {
            $data['addressLine1'] = $this->addressLine1;
        }

        if ($this->addressLine2 !== null) {
            $data['addressLine2'] = $this->addressLine2;
        }

        if ($this->postCode !== null) {
            $data['postCode'] = $this->postCode;
        }

        if ($this->town !== null) {
            $data['town'] = $this->town;
        }

        if ($this->country !== null) {
            $data['country'] = $this->country;
        }

        return $data;
    }
}
