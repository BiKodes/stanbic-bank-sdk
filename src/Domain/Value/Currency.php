<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Value;

/**
 * Currency Value Object.
 *
 * Represents ISO 4217 currency codes (e.g., KES, USD, GBP).
 *
 * @psalm-immutable
*/
final class Currency
{
    public function __construct(
        public readonly string $code,
    ) {
    }

    /**
     * Create Currency from ISO code string.
     *
     * @param string $code ISO 4217 currency code (e.g., "KES", "USD")
    */
    public static function fromCode(string $code): self
    {
        return new self(code: strtoupper($code));
    }

    /**
     * Get currency code.
    */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * Check if currency equals another.
    */
    public function equals(Currency $other): bool
    {
        return $this->code === $other->code;
    }

    /**
     * String representation.
    */
    public function __toString(): string
    {
        return $this->code;
    }
}
