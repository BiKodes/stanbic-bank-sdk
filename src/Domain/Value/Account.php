<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Value;

/**
 * Account Value Object.
 *
 * Represents a bank account with number and bank code.
 *
 * @psalm-immutable
*/
final class Account
{
    public function __construct(
        public readonly string $number,
        public readonly string $bankCode,
    ) {
    }

    /**
     * Create Account from account number and bank code.
     *
     * @param string $number Account number
     * @param string $bankCode Bank code (e.g., "01" for Stanbic)
    */
    public static function create(string $number, string $bankCode): self
    {
        return new self(
            number: trim($number),
            bankCode: trim($bankCode),
        );
    }

    /**
     * Get account number.
    */
    public function getNumber(): string
    {
        return $this->number;
    }

    /**
     * Get bank code.
    */
    public function getBankCode(): string
    {
        return $this->bankCode;
    }

    /**
     * Check if account equals another.
    */
    public function equals(Account $other): bool
    {
        return $this->number === $other->number
            && $this->bankCode === $other->bankCode;
    }

    /**
     * String representation: BANKCODE:NUMBER (e.g., "01:123456789").
    */
    public function __toString(): string
    {
        return $this->bankCode . ':' . $this->number;
    }
}
