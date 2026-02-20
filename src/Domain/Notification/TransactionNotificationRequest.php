<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Notification;

/**
 * Transaction Notification Request (Letter object).
 *
 * @psalm-immutable
*/
final class TransactionNotificationRequest
{
    public function __construct(
        public readonly string $ApiKey,
        public readonly ?string $AlertType = null,
        public readonly ?string $AlertName = null,
        public readonly ?string $SendTo = null,
        public readonly ?string $AccountNo = null,
        public readonly ?string $CustomerNo = null,
        public readonly ?string $Subject = null,
        public readonly ?string $CustomerName = null,
        public readonly ?string $Date = null,
        public readonly ?string $ValueDate = null,
        public readonly ?string $ActionType = null,
        public readonly ?string $TxnDescr = null,
        public readonly ?string $TxnAmount = null,
        public readonly ?string $OurTxnReference = null,
        public readonly ?string $ThirdPartyRef = null,
        public readonly ?string $TransactionOriginationBranch = null,
        public readonly ?string $Narrative = null,
        public readonly ?string $CurrentBalance = null,
        public readonly ?string $AvailableBalance = null,
        public readonly ?string $ClearedBalance = null,
        public readonly ?string $AccountOfficer = null,
        public readonly ?string $ClientFormat = null,
        public readonly ?string $MSISDN = null,
        public readonly ?string $PayerRef = null,
        public readonly ?string $CallbackUrl = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
    */
    public static function fromArray(array $data): self
    {
        /** @var array<string, mixed> $letter */
        $letter = $data['Letter'] ?? $data;

        return new self(
            ApiKey: (string) ($letter['ApiKey'] ?? $letter['apiKey'] ?? ''),
            AlertType: isset($letter['AlertType']) || isset($letter['alertType'])
                ? (string) ($letter['AlertType'] ?? $letter['alertType'])
                : null,
            AlertName: isset($letter['AlertName']) || isset($letter['alertName'])
                ? (string) ($letter['AlertName'] ?? $letter['alertName'])
                : null,
            SendTo: isset($letter['SendTo']) || isset($letter['sendTo'])
                ? (string) ($letter['SendTo'] ?? $letter['sendTo'])
                : null,
            AccountNo: isset($letter['AccountNo']) || isset($letter['accountNo'])
                ? (string) ($letter['AccountNo'] ?? $letter['accountNo'])
                : null,
            CustomerNo: isset($letter['CustomerNo']) || isset($letter['customerNo'])
                ? (string) ($letter['CustomerNo'] ?? $letter['customerNo'])
                : null,
            Subject: isset($letter['Subject']) || isset($letter['subject'])
                ? (string) ($letter['Subject'] ?? $letter['subject'])
                : null,
            CustomerName: isset($letter['CustomerName']) || isset($letter['customerName'])
                ? (string) ($letter['CustomerName'] ?? $letter['customerName'])
                : null,
            Date: isset($letter['Date']) || isset($letter['date'])
                ? (string) ($letter['Date'] ?? $letter['date'])
                : null,
            ValueDate: isset($letter['ValueDate']) || isset($letter['valueDate'])
                ? (string) ($letter['ValueDate'] ?? $letter['valueDate'])
                : null,
            ActionType: isset($letter['ActionType']) || isset($letter['actionType'])
                ? (string) ($letter['ActionType'] ?? $letter['actionType'])
                : null,
            TxnDescr: isset($letter['TxnDescr']) || isset($letter['txnDescr'])
                ? (string) ($letter['TxnDescr'] ?? $letter['txnDescr'])
                : null,
            TxnAmount: isset($letter['TxnAmount']) || isset($letter['txnAmount'])
                ? (string) ($letter['TxnAmount'] ?? $letter['txnAmount'])
                : null,
            OurTxnReference: isset($letter['OurTxnReference']) || isset($letter['ourTxnReference'])
                ? (string) ($letter['OurTxnReference'] ?? $letter['ourTxnReference'])
                : null,
            ThirdPartyRef: isset($letter['ThirdPartyRef']) || isset($letter['thirdPartyRef'])
                ? (string) ($letter['ThirdPartyRef'] ?? $letter['thirdPartyRef'])
                : null,
            TransactionOriginationBranch: (
                isset($letter['TransactionOriginationBranch']) ||
                isset($letter['transactionOriginationBranch'])
            )
                ? (string) (
                    $letter['TransactionOriginationBranch'] ??
                    $letter['transactionOriginationBranch']
                )
                : null,
            Narrative: isset($letter['Narrative']) || isset($letter['narrative'])
                ? (string) ($letter['Narrative'] ?? $letter['narrative'])
                : null,
            CurrentBalance: isset($letter['CurrentBalance']) || isset($letter['currentBalance'])
                ? (string) ($letter['CurrentBalance'] ?? $letter['currentBalance'])
                : null,
            AvailableBalance: isset($letter['AvailableBalance']) || isset($letter['availableBalance'])
                ? (string) ($letter['AvailableBalance'] ?? $letter['availableBalance'])
                : null,
            ClearedBalance: isset($letter['ClearedBalance']) || isset($letter['clearedBalance'])
                ? (string) ($letter['ClearedBalance'] ?? $letter['clearedBalance'])
                : null,
            AccountOfficer: isset($letter['AccountOfficer']) || isset($letter['accountOfficer'])
                ? (string) ($letter['AccountOfficer'] ?? $letter['accountOfficer'])
                : null,
            ClientFormat: isset($letter['ClientFormat']) || isset($letter['clientFormat'])
                ? (string) ($letter['ClientFormat'] ?? $letter['clientFormat'])
                : null,
            MSISDN: isset($letter['MSISDN']) || isset($letter['msisdn'])
                ? (string) ($letter['MSISDN'] ?? $letter['msisdn'])
                : null,
            PayerRef: isset($letter['PAYER.REF']) || isset($letter['PayerRef']) || isset($letter['payerRef'])
                ? (string) ($letter['PAYER.REF'] ?? $letter['PayerRef'] ?? $letter['payerRef'])
                : null,
            CallbackUrl: isset($letter['CallbackUrl']) || isset($letter['callbackUrl'])
                ? (string) ($letter['CallbackUrl'] ?? $letter['callbackUrl'])
                : null,
        );
    }

    /**
     * @return array<string, mixed>
    */
    public function toArray(): array
    {
        $data = [
            'ApiKey' => $this->ApiKey,
        ];

        if ($this->AlertType !== null) {
            $data['AlertType'] = $this->AlertType;
        }

        if ($this->AlertName !== null) {
            $data['AlertName'] = $this->AlertName;
        }

        if ($this->SendTo !== null) {
            $data['SendTo'] = $this->SendTo;
        }

        if ($this->AccountNo !== null) {
            $data['AccountNo'] = $this->AccountNo;
        }

        if ($this->CustomerNo !== null) {
            $data['CustomerNo'] = $this->CustomerNo;
        }

        if ($this->Subject !== null) {
            $data['Subject'] = $this->Subject;
        }

        if ($this->CustomerName !== null) {
            $data['CustomerName'] = $this->CustomerName;
        }

        if ($this->Date !== null) {
            $data['Date'] = $this->Date;
        }

        if ($this->ValueDate !== null) {
            $data['ValueDate'] = $this->ValueDate;
        }

        if ($this->ActionType !== null) {
            $data['ActionType'] = $this->ActionType;
        }

        if ($this->TxnDescr !== null) {
            $data['TxnDescr'] = $this->TxnDescr;
        }

        if ($this->TxnAmount !== null) {
            $data['TxnAmount'] = $this->TxnAmount;
        }

        if ($this->OurTxnReference !== null) {
            $data['OurTxnReference'] = $this->OurTxnReference;
        }

        if ($this->ThirdPartyRef !== null) {
            $data['ThirdPartyRef'] = $this->ThirdPartyRef;
        }

        if ($this->TransactionOriginationBranch !== null) {
            $data['TransactionOriginationBranch'] = $this->TransactionOriginationBranch;
        }

        if ($this->Narrative !== null) {
            $data['Narrative'] = $this->Narrative;
        }

        if ($this->CurrentBalance !== null) {
            $data['CurrentBalance'] = $this->CurrentBalance;
        }

        if ($this->AvailableBalance !== null) {
            $data['AvailableBalance'] = $this->AvailableBalance;
        }

        if ($this->ClearedBalance !== null) {
            $data['ClearedBalance'] = $this->ClearedBalance;
        }

        if ($this->AccountOfficer !== null) {
            $data['AccountOfficer'] = $this->AccountOfficer;
        }

        if ($this->ClientFormat !== null) {
            $data['ClientFormat'] = $this->ClientFormat;
        }

        if ($this->MSISDN !== null) {
            $data['MSISDN'] = $this->MSISDN;
        }

        if ($this->PayerRef !== null) {
            $data['PAYER.REF'] = $this->PayerRef;
        }

        if ($this->CallbackUrl !== null) {
            $data['CallbackUrl'] = $this->CallbackUrl;
        }

        return ['Letter' => $data];
    }
}
