<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Payment;

use Stanbic\SDK\Domain\ValueObject\MobileMoneyMno;

/**
 * Transfer transaction information.
 *
 * @psalm-immutable
*/
final class TransferTransactionInformation
{
    public function __construct(
        public readonly float $amount,
        public readonly string $currency,
        public readonly CounterpartyAccount $counterpartyAccount,
        public readonly ?Counterparty $counterparty = null,
        public readonly ?string $counterpartyName = null,
        public readonly ?RemittanceInformation $remittanceInformation = null,
        public readonly ?string $paymentPurpose = null,
        public readonly ?MobileMoneyMno $mobileMoneyMno = null,
        public readonly ?string $endToEndIdentification = null,
        public readonly string $instructedCurrencyKey = 'currencyCode',
    ) {
    }

    /**
     * @param array<string, mixed> $data
    */
    public static function fromArray(array $data): self
    {
        /** @var array<string, mixed>|null $amountData */
        $amountData = $data['instructedAmount'] ?? $data['instructed_amount'] ?? null;
        $amount = null;
        $currency = null;
        $currencyKey = 'currencyCode';

        if (is_array($amountData)) {
            /** @var array<string, mixed> $amountArray */
            $amountArray = $amountData;
            $amount = (float) ($amountArray['amount'] ?? $amountArray['value'] ?? 0.0);
            if (isset($amountArray['creditCurrency'])) {
                $currencyKey = 'creditCurrency';
                $currency = (string) $amountArray['creditCurrency'];
            } else {
                $currency = (string) ($amountArray['currencyCode'] ?? $amountArray['currency'] ?? 'KES');
            }
        }

        if ($amount === null) {
            $amount = (float) ($data['amount'] ?? $data['instructedAmount'] ?? 0.0);
        }

        if ($currency === null) {
            $currency = (string) ($data['currency'] ?? $data['instructedCurrency'] ?? 'KES');
        }

        /** @var array<string, mixed>|CounterpartyAccount|null $accountData */
        $accountData = $data['counterpartyAccount'] ?? $data['counterparty_account'] ?? null;
        if ($accountData instanceof CounterpartyAccount) {
            $account = $accountData;
        } elseif (is_array($accountData)) {
            /** @var array<string, mixed> $accountArray */
            $accountArray = $accountData;
            $account = CounterpartyAccount::fromArray($accountArray);
        } else {
            $account = CounterpartyAccount::fromArray([]);
        }

        /** @var array<string, mixed>|Counterparty|null $counterpartyData */
        $counterpartyData = $data['counterparty'] ?? null;
        $counterparty = null;
        if ($counterpartyData instanceof Counterparty) {
            $counterparty = $counterpartyData;
        } elseif (is_array($counterpartyData)) {
            /** @var array<string, mixed> $counterpartyArray */
            $counterpartyArray = $counterpartyData;
            $counterparty = Counterparty::fromArray($counterpartyArray);
        }

        /** @var array<string, mixed>|RemittanceInformation|null $remittanceData */
        $remittanceData = $data['remittanceInformation'] ?? $data['remittance_information'] ?? null;
        $remittance = null;
        if ($remittanceData instanceof RemittanceInformation) {
            $remittance = $remittanceData;
        } elseif (is_array($remittanceData)) {
            /** @var array<string, mixed> $remittanceArray */
            $remittanceArray = $remittanceData;
            $remittance = RemittanceInformation::fromArray($remittanceArray);
        }

        /** @var array<string, mixed>|MobileMoneyMno|null $mnoData */
        $mnoData = $data['mobileMoneyMno'] ?? $data['mobile_money_mno'] ?? null;
        $mobileMoneyMno = null;
        if ($mnoData instanceof MobileMoneyMno) {
            $mobileMoneyMno = $mnoData;
        } elseif (is_array($mnoData)) {
            /** @var array<string, mixed> $mnoArray */
            $mnoArray = $mnoData;
            $mobileMoneyMno = MobileMoneyMno::fromArray($mnoArray);
        }

        $counterpartyName = null;
        if (isset($data['counterpartyName']) || isset($data['counterparty_name'])) {
            $counterpartyName = (string) ($data['counterpartyName'] ?? $data['counterparty_name']);
        } elseif ($counterparty !== null && $counterparty->name !== '') {
            $counterpartyName = $counterparty->name;
        }

        return new self(
            amount: $amount,
            currency: $currency,
            counterpartyAccount: $account,
            counterparty: $counterparty,
            counterpartyName: $counterpartyName,
            remittanceInformation: $remittance,
            paymentPurpose: isset($data['paymentPurpose']) || isset($data['payment_purpose'])
                ? (string) ($data['paymentPurpose'] ?? $data['payment_purpose'])
                : null,
            mobileMoneyMno: $mobileMoneyMno,
            endToEndIdentification: isset($data['endToEndIdentification']) || isset($data['end_to_end_identification'])
                ? (string) ($data['endToEndIdentification'] ?? $data['end_to_end_identification'])
                : null,
            instructedCurrencyKey: $currencyKey,
        );
    }

    /**
     * @return array<string, mixed>
    */
    public function toArray(): array
    {
        $data = [
            'instructedAmount' => [
                'amount' => $this->amount,
                $this->instructedCurrencyKey => $this->currency,
            ],
        ];

        $accountArray = $this->counterpartyAccount->toArray();
        if ($accountArray !== []) {
            $data['counterpartyAccount'] = $accountArray;
        }

        if ($this->counterparty !== null) {
            $data['counterparty'] = $this->counterparty->toArray();
        } elseif ($this->counterpartyName !== null) {
            $data['counterparty'] = ['name' => $this->counterpartyName];
        }

        if ($this->remittanceInformation !== null) {
            $data['remittanceInformation'] = $this->remittanceInformation->toArray();
        }

        if ($this->paymentPurpose !== null) {
            $data['paymentPurpose'] = $this->paymentPurpose;
        }

        if ($this->mobileMoneyMno !== null) {
            $data['mobileMoneyMno'] = $this->mobileMoneyMno->toArray();
        }

        if ($this->endToEndIdentification !== null) {
            $data['endToEndIdentification'] = $this->endToEndIdentification;
        }

        return $data;
    }
}
