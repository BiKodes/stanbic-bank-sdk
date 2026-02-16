<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\ValueObject;

/**
 * Originator identification details.
 *
 * @psalm-immutable
*/
final class OriginatorIdentification
{
    public function __construct(
        public readonly ?string $identification = null,
        public readonly ?string $debitCurrency = null,
        public readonly ?string $mobileNumber = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
    */
    public static function fromArray(array $data): self
    {
        return new self(
            identification: isset($data['identification']) ? (string) $data['identification'] : null,
            debitCurrency: isset($data['debitCurrency']) || isset($data['debit_currency'])
                ? (string) ($data['debitCurrency'] ?? $data['debit_currency'])
                : null,
            mobileNumber: isset($data['mobileNumber']) || isset($data['mobile_number'])
                ? (string) ($data['mobileNumber'] ?? $data['mobile_number'])
                : null,
        );
    }

    /**
     * @return array<string, mixed>
    */
    public function toArray(): array
    {
        $data = [];

        if ($this->identification !== null) {
            $data['identification'] = $this->identification;
        }

        if ($this->debitCurrency !== null) {
            $data['debitCurrency'] = $this->debitCurrency;
        }

        if ($this->mobileNumber !== null) {
            $data['mobileNumber'] = $this->mobileNumber;
        }

        return $data;
    }
}
