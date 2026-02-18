<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\ValueObject;

/**
 * Counterparty account identification details.
 *
 * @psalm-immutable
*/
final class CounterpartyAccountIdentification
{
    public function __construct(
        public readonly ?string $identification = null,
        public readonly ?string $recipientMobileNo = null,
        public readonly ?string $recipientBankAcctNo = null,
        public readonly ?string $recipientBankCode = null,
        public readonly ?string $correspondentBank = null,
        public readonly ?string $beneficiaryBank = null,
        public readonly ?string $beneficiaryChargeType = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
    */
    public static function fromArray(array $data): self
    {
        return new self(
            identification: isset($data['identification']) ? (string) $data['identification'] : null,
            recipientMobileNo: isset($data['recipientMobileNo']) || isset($data['recipient_mobile_no'])
                ? (string) ($data['recipientMobileNo'] ?? $data['recipient_mobile_no'])
                : null,
            recipientBankAcctNo: isset($data['recipientBankAcctNo']) || isset($data['recipient_bank_acct_no'])
                ? (string) ($data['recipientBankAcctNo'] ?? $data['recipient_bank_acct_no'])
                : null,
            recipientBankCode: isset($data['recipientBankCode']) || isset($data['recipient_bank_code'])
                ? (string) ($data['recipientBankCode'] ?? $data['recipient_bank_code'])
                : null,
            correspondentBank: isset($data['correspondentBank']) || isset($data['correspondent_bank'])
                ? (string) ($data['correspondentBank'] ?? $data['correspondent_bank'])
                : null,
            beneficiaryBank: isset($data['beneficiaryBank']) || isset($data['beneficiary_bank'])
                ? (string) ($data['beneficiaryBank'] ?? $data['beneficiary_bank'])
                : null,
            beneficiaryChargeType: isset($data['beneficiaryChargeType']) || isset($data['beneficiary_charge_type'])
                ? (string) ($data['beneficiaryChargeType'] ?? $data['beneficiary_charge_type'])
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

        if ($this->recipientMobileNo !== null) {
            $data['recipientMobileNo'] = $this->recipientMobileNo;
        }

        if ($this->recipientBankAcctNo !== null) {
            $data['recipientBankAcctNo'] = $this->recipientBankAcctNo;
        }

        if ($this->recipientBankCode !== null) {
            $data['recipientBankCode'] = $this->recipientBankCode;
        }

        if ($this->correspondentBank !== null) {
            $data['correspondentBank'] = $this->correspondentBank;
        }

        if ($this->beneficiaryBank !== null) {
            $data['beneficiaryBank'] = $this->beneficiaryBank;
        }

        if ($this->beneficiaryChargeType !== null) {
            $data['beneficiaryChargeType'] = $this->beneficiaryChargeType;
        }

        return $data;
    }
}
