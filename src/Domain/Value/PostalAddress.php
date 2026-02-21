<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Value;

/**
 * Postal Address Value Object.
 *
 * Represents a complete postal address.
 *
 * @psalm-immutable
*/
final class PostalAddress
{
    public function __construct(
        public readonly string $street,
        public readonly string $city,
        public readonly string $country,
        public readonly ?string $postalCode = null,
        public readonly ?string $state = null,
        public readonly ?string $poBox = null,
    ) {
    }

    /**
     * Create PostalAddress from components.
    */
    public static function create(
        string $street,
        string $city,
        string $country,
        ?string $postalCode = null,
        ?string $state = null,
        ?string $poBox = null,
    ): self {
        return new self(
            street: trim($street),
            city: trim($city),
            country: trim($country),
            postalCode: isset($postalCode) ? trim($postalCode) : null,
            state: isset($state) ? trim($state) : null,
            poBox: isset($poBox) ? trim($poBox) : null,
        );
    }

    /**
     * Get formatted single-line address.
     *
     * @return string Formatted address string
    */
    public function format(): string
    {
        $parts = [
            $this->street,
            $this->city,
        ];

        if ($this->state !== null) {
            $parts[] = $this->state;
        }

        if ($this->postalCode !== null) {
            $parts[] = $this->postalCode;
        }

        $parts[] = $this->country;

        return implode(', ', $parts);
    }

    /**
     * Check if address equals another.
    */
    public function equals(PostalAddress $other): bool
    {
        return $this->street === $other->street
            && $this->city === $other->city
            && $this->country === $other->country
            && $this->postalCode === $other->postalCode
            && $this->state === $other->state
            && $this->poBox === $other->poBox;
    }

    /**
     * String representation.
    */
    public function __toString(): string
    {
        return $this->format();
    }
}
