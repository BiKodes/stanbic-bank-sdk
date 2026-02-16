<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Payment;

use Stanbic\SDK\Domain\ValueObject\CounterpartyAccountIdentification;

/**
 * Counterparty account details.
 *
 * @psalm-immutable
*/
final class CounterpartyAccount
{
    public function __construct(
        public readonly ?CounterpartyAccountIdentification $identification = null,
        public readonly ?string $recipientBankAcctNo = null,
        public readonly ?string $recipientBankCode = null,
        public readonly ?string $recipientAccountName = null,
        public readonly ?string $recipientBankName = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
    */
    public static function fromArray(array $data): self
    {
        /** @var array<string, mixed>|CounterpartyAccountIdentification $idData */
        $idData = $data['identification'] ?? [];
        $idArray = [];
        if (is_array($idData)) {
            /** @var array<string, mixed> $idArray */
            $idArray = $idData;
        }

        $identification = $idData instanceof CounterpartyAccountIdentification
            ? $idData
            : CounterpartyAccountIdentification::fromArray($idArray);

        $hasIdentification = !empty($identification->toArray());

        return new self(
            identification: $hasIdentification ? $identification : null,
            recipientBankAcctNo: isset($data['recipientBankAcctNo']) || isset($data['recipient_bank_acct_no'])
                ? (string) ($data['recipientBankAcctNo'] ?? $data['recipient_bank_acct_no'])
                : null,
            recipientBankCode: isset($data['recipientBankCode']) || isset($data['recipient_bank_code'])
                ? (string) ($data['recipientBankCode'] ?? $data['recipient_bank_code'])
                : null,
            recipientAccountName: isset($data['recipientAccountName']) || isset($data['recipient_account_name'])
                ? (string) ($data['recipientAccountName'] ?? $data['recipient_account_name'])
                : null,
            recipientBankName: isset($data['recipientBankName']) || isset($data['recipient_bank_name'])
                ? (string) ($data['recipientBankName'] ?? $data['recipient_bank_name'])
                : null,
        );
    }

    /**
     * @return array<string, mixed>
    */
    public function toArray(): array
    {
        if ($this->identification !== null) {
            return [
                'identification' => $this->identification->toArray(),
            ];
        }

        $data = [];

        if ($this->recipientBankAcctNo !== null) {
            $data['recipientBankAcctNo'] = $this->recipientBankAcctNo;
        }

        if ($this->recipientBankCode !== null) {
            $data['recipientBankCode'] = $this->recipientBankCode;
        }

        if ($this->recipientAccountName !== null) {
            $data['recipientAccountName'] = $this->recipientAccountName;
        }

        if ($this->recipientBankName !== null) {
            $data['recipientBankName'] = $this->recipientBankName;
        }

        return $data;
    }
}
