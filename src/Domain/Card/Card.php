<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Card;

/**
 * Card value object.
 *
 * @psalm-immutable
*/
final class Card
{
    public function __construct(
        public readonly ?string $pan = null,
        public readonly ?string $status = null,
        public readonly ?string $cardholdername = null,
        public readonly ?string $cardholderName = null,
        public readonly ?string $paymentScheme = null,
        public readonly ?string $expiryYear = null,
        public readonly ?string $expiryMonth = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
    */
    public static function fromArray(array $data): self
    {
        return new self(
            pan: isset($data['pan']) ? (string) $data['pan'] : null,
            status: isset($data['status']) ? (string) $data['status'] : null,
            cardholdername: isset($data['cardholdername']) ? (string) $data['cardholdername'] : null,
            cardholderName: isset($data['cardholderName']) ? (string) $data['cardholderName'] : null,
            paymentScheme: isset($data['paymentScheme']) ? (string) $data['paymentScheme'] : null,
            expiryYear: isset($data['expiryYear']) ? (string) $data['expiryYear'] : null,
            expiryMonth: isset($data['expiryMonth']) ? (string) $data['expiryMonth'] : null,
        );
    }

    /**
     * @return array<string, mixed>
    */
    public function toArray(): array
    {
        $data = [];

        if ($this->pan !== null) {
            $data['pan'] = $this->pan;
        }

        if ($this->status !== null) {
            $data['status'] = $this->status;
        }

        if ($this->cardholdername !== null) {
            $data['cardholdername'] = $this->cardholdername;
        }

        if ($this->cardholderName !== null) {
            $data['cardholderName'] = $this->cardholderName;
        }

        if ($this->paymentScheme !== null) {
            $data['paymentScheme'] = $this->paymentScheme;
        }

        if ($this->expiryYear !== null) {
            $data['expiryYear'] = $this->expiryYear;
        }

        if ($this->expiryMonth !== null) {
            $data['expiryMonth'] = $this->expiryMonth;
        }

        return $data;
    }
}
