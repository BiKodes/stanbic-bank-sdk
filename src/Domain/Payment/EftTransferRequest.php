<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Payment;

use Stanbic\SDK\Domain\ValueObject\EftAttachment;

/**
 * EFT payment request (sandbox schema).
 *
 * @psalm-immutable
*/
final class EftTransferRequest
{
    /**
     * @param EftAttachment[] $attachments
    */
    public function __construct(
        public readonly string $sourceChannel,
        public readonly string $sourceMsgId,
        public readonly string $createdTime,
        public readonly string $debitAccount,
        public readonly string $dbpUniqueTransactionNumber,
        public readonly string $beneficiaryAcctNo,
        public readonly string $beneficiaryName,
        public readonly string $beneficiaryBankCode,
        public readonly ?string $beneficiaryAddr,
        public readonly ?string $exchangeRate,
        public readonly string $creditAmount,
        public readonly string $creditCurrency,
        public readonly string $paymentDetails,
        public readonly ?string $fxDealId,
        public readonly ?string $execute,
        public readonly ?string $paymentType,
        public readonly ?string $executionDate,
        public readonly array $attachments = [],
    ) {
    }

    /**
     * @param array<string, mixed> $data
    */
    public static function fromArray(array $data): self
    {
        $attachmentsData = [];
        if (isset($data['Attachments']) && is_array($data['Attachments'])) {
            $attachmentsData = $data['Attachments'];
        } elseif (isset($data['attachments']) && is_array($data['attachments'])) {
            $attachmentsData = $data['attachments'];
        }

        /** @var array<array-key, mixed> $attachmentsData */
        $attachments = [];
        foreach ($attachmentsData as $attachmentData) {
            if ($attachmentData instanceof EftAttachment) {
                $attachments[] = $attachmentData;
                continue;
            }
            if (!is_array($attachmentData)) {
                continue;
            }
            /** @var array<string, mixed> $attachmentArray */
            $attachmentArray = $attachmentData;
            $attachments[] = EftAttachment::fromArray($attachmentArray);
        }

        return new self(
            sourceChannel: (string) ($data['SourceChannel'] ?? $data['sourceChannel'] ?? ''),
            sourceMsgId: (string) ($data['SourceMsgId'] ?? $data['sourceMsgId'] ?? ''),
            createdTime: (string) ($data['CreatedTime'] ?? $data['createdTime'] ?? ''),
            debitAccount: (string) ($data['DebitAccount'] ?? $data['debitAccount'] ?? ''),
            dbpUniqueTransactionNumber: (string) (
                $data['DBPUniqueTransactionNumber'] ?? $data['dbpUniqueTransactionNumber'] ?? ''
            ),
            beneficiaryAcctNo: (string) ($data['BeneficiaryAcctNo'] ?? $data['beneficiaryAcctNo'] ?? ''),
            beneficiaryName: (string) ($data['BeneficiaryName'] ?? $data['beneficiaryName'] ?? ''),
            beneficiaryBankCode: (string) (
                $data['BeneficiaryBankCode'] ?? $data['beneficiaryBankCode'] ?? ''
            ),
            beneficiaryAddr: isset($data['BeneficiaryAddr']) || isset($data['beneficiaryAddr'])
                ? (string) ($data['BeneficiaryAddr'] ?? $data['beneficiaryAddr'])
                : null,
            exchangeRate: isset($data['ExchangeRate']) || isset($data['exchangeRate'])
                ? (string) ($data['ExchangeRate'] ?? $data['exchangeRate'])
                : null,
            creditAmount: (string) ($data['CreditAmount'] ?? $data['creditAmount'] ?? ''),
            creditCurrency: (string) ($data['CreditCurrency'] ?? $data['creditCurrency'] ?? ''),
            paymentDetails: (string) ($data['PaymentDetails'] ?? $data['paymentDetails'] ?? ''),
            fxDealId: isset($data['FxDealId']) || isset($data['fxDealId'])
                ? (string) ($data['FxDealId'] ?? $data['fxDealId'])
                : null,
            execute: isset($data['Execute']) || isset($data['execute'])
                ? (string) ($data['Execute'] ?? $data['execute'])
                : null,
            paymentType: isset($data['PaymentType']) || isset($data['paymentType'])
                ? (string) ($data['PaymentType'] ?? $data['paymentType'])
                : null,
            executionDate: isset($data['ExecutionDate']) || isset($data['executionDate'])
                ? (string) ($data['ExecutionDate'] ?? $data['executionDate'])
                : null,
            attachments: $attachments,
        );
    }

    public function toArray(): array
    {
        $data = [
            'SourceChannel' => $this->sourceChannel,
            'SourceMsgId' => $this->sourceMsgId,
            'CreatedTime' => $this->createdTime,
            'DebitAccount' => $this->debitAccount,
            'DBPUniqueTransactionNumber' => $this->dbpUniqueTransactionNumber,
            'BeneficiaryAcctNo' => $this->beneficiaryAcctNo,
            'BeneficiaryName' => $this->beneficiaryName,
            'BeneficiaryBankCode' => $this->beneficiaryBankCode,
            'CreditAmount' => $this->creditAmount,
            'CreditCurrency' => $this->creditCurrency,
            'PaymentDetails' => $this->paymentDetails,
        ];

        if ($this->beneficiaryAddr !== null) {
            $data['BeneficiaryAddr'] = $this->beneficiaryAddr;
        }

        if ($this->exchangeRate !== null) {
            $data['ExchangeRate'] = $this->exchangeRate;
        }

        if ($this->fxDealId !== null) {
            $data['FxDealId'] = $this->fxDealId;
        }

        if ($this->execute !== null) {
            $data['Execute'] = $this->execute;
        }

        if ($this->paymentType !== null) {
            $data['PaymentType'] = $this->paymentType;
        }

        if ($this->executionDate !== null) {
            $data['ExecutionDate'] = $this->executionDate;
        }

        if ($this->attachments !== []) {
            $data['Attachments'] = array_map(
                static fn (EftAttachment $attachment): array => $attachment->toArray(),
                $this->attachments
            );
        }

        return $data;
    }
}
