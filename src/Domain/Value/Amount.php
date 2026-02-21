<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Value;

/**
 * Amount Value Object.
 *
 * Represents a monetary amount with currency.
 * Immutable and supports comparison operations.
 *
 * @psalm-immutable
*/
final class Amount
{
    /**
     * @param string|int|float $value Numeric amount
     * @param Currency $currency Currency object
    */
    public function __construct(
        public readonly string|int|float $value,
        public readonly Currency $currency,
    ) {
    }

    /**
     * Create Amount from value and currency code.
     *
     * @param string|int|float $value
     * @param string $currencyCode ISO 4217 currency code
    */
    public static function of(string|int|float $value, string $currencyCode): self
    {
        return new self(
            value: $value,
            currency: Currency::fromCode($currencyCode),
        );
    }

    /**
     * Get numeric value as string.
    */
    public function getValue(): string
    {
        return (string) $this->value;
    }

    /**
     * Get numeric value as float.
    */
    public function toFloat(): float
    {
        return (float) $this->value;
    }

    /**
     * Get numeric value as int.
    */
    public function toInt(): int
    {
        return (int) $this->value;
    }

    /**
     * Get currency code.
    */
    public function getCurrencyCode(): string
    {
        return $this->currency->getCode();
    }

    /**
     * Check if amount equals another (same value and currency).
    */
    public function equals(Amount $other): bool
    {
        return $this->toFloat() === $other->toFloat()
            && $this->currency->equals($other->currency);
    }

    /**
     * Check if amount is greater than another.
    */
    public function isGreaterThan(Amount $other): bool
    {
        if (!$this->currency->equals($other->currency)) {
            throw new \InvalidArgumentException(
                'Cannot compare amounts with different currencies: '
                . $this->getCurrencyCode() . ' vs ' . $other->getCurrencyCode(),
            );
        }

        return $this->toFloat() > $other->toFloat();
    }

    /**
     * Check if amount is less than another.
    */
    public function isLessThan(Amount $other): bool
    {
        if (!$this->currency->equals($other->currency)) {
            throw new \InvalidArgumentException(
                'Cannot compare amounts with different currencies: '
                . $this->getCurrencyCode() . ' vs ' . $other->getCurrencyCode(),
            );
        }

        return $this->toFloat() < $other->toFloat();
    }

    /**
     * String representation: VALUE CURRENCY (e.g., "1000.50 KES").
    */
    public function __toString(): string
    {
        return $this->getValue() . ' ' . $this->getCurrencyCode();
    }
}
