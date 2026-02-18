<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\ValueObject;

/**
 * EFT transaction details from response.
 *
 * @psalm-immutable
*/
final class EftTransactionDetails
{
    public function __construct(
        public readonly ?string $transactionStatus = null,
        public readonly ?string $dbpUniqueTransactionNumber = null,
        public readonly ?string $debitAmount = null,
        public readonly ?string $debitAmountCurrency = null,
        public readonly ?string $creditAmount = null,
        public readonly ?string $creditAmountCurrency = null,
        public readonly ?string $valueDate = null,
        public readonly ?string $chargesAmount = null,
        public readonly ?string $chargesCurrency = null,
        public readonly ?string $exchangeRate = null,
        public readonly ?string $utn = null,
        public readonly ?string $error = null,
        public readonly ?string $errorDescription = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
    */
    public static function fromArray(array $data): self
    {
        $hasDbpUniqueTransactionNumber = isset($data['DBPUniqueTransactionNumber'])
            || isset($data['dbpUniqueTransactionNumber']);

        return new self(
            transactionStatus: isset($data['TransactionStatus']) || isset($data['transactionStatus'])
                ? (string) ($data['TransactionStatus'] ?? $data['transactionStatus'])
                : null,
            dbpUniqueTransactionNumber: $hasDbpUniqueTransactionNumber
                ? (string) ($data['DBPUniqueTransactionNumber'] ?? $data['dbpUniqueTransactionNumber'])
                : null,
            debitAmount: isset($data['DebitAmount']) || isset($data['debitAmount'])
                ? (string) ($data['DebitAmount'] ?? $data['debitAmount'])
                : null,
            debitAmountCurrency: isset($data['DebitAmountCurrency']) || isset($data['debitAmountCurrency'])
                ? (string) ($data['DebitAmountCurrency'] ?? $data['debitAmountCurrency'])
                : null,
            creditAmount: isset($data['CreditAmount']) || isset($data['creditAmount'])
                ? (string) ($data['CreditAmount'] ?? $data['creditAmount'])
                : null,
            creditAmountCurrency: isset($data['CreditAmountCurrency']) || isset($data['creditAmountCurrency'])
                ? (string) ($data['CreditAmountCurrency'] ?? $data['creditAmountCurrency'])
                : null,
            valueDate: isset($data['ValueDate']) || isset($data['valueDate'])
                ? (string) ($data['ValueDate'] ?? $data['valueDate'])
                : null,
            chargesAmount: isset($data['ChargesAmount']) || isset($data['chargesAmount'])
                ? (string) ($data['ChargesAmount'] ?? $data['chargesAmount'])
                : null,
            chargesCurrency: isset($data['ChargesCurrency']) || isset($data['chargesCurrency'])
                ? (string) ($data['ChargesCurrency'] ?? $data['chargesCurrency'])
                : null,
            exchangeRate: isset($data['ExchangeRate']) || isset($data['exchangeRate'])
                ? (string) ($data['ExchangeRate'] ?? $data['exchangeRate'])
                : null,
            utn: isset($data['UTN']) || isset($data['utn']) ? (string) ($data['UTN'] ?? $data['utn']) : null,
            error: isset($data['Error']) || isset($data['error']) ? (string) ($data['Error'] ?? $data['error']) : null,
            errorDescription: isset($data['ErrorDescription']) || isset($data['errorDescription'])
                ? (string) ($data['ErrorDescription'] ?? $data['errorDescription'])
                : null,
        );
    }

    /**
     * @return array<string, mixed>
    */
    public function toArray(): array
    {
        $data = [];

        if ($this->transactionStatus !== null) {
            $data['TransactionStatus'] = $this->transactionStatus;
        }

        if ($this->dbpUniqueTransactionNumber !== null) {
            $data['DBPUniqueTransactionNumber'] = $this->dbpUniqueTransactionNumber;
        }

        if ($this->debitAmount !== null) {
            $data['DebitAmount'] = $this->debitAmount;
        }

        if ($this->debitAmountCurrency !== null) {
            $data['DebitAmountCurrency'] = $this->debitAmountCurrency;
        }

        if ($this->creditAmount !== null) {
            $data['CreditAmount'] = $this->creditAmount;
        }

        if ($this->creditAmountCurrency !== null) {
            $data['CreditAmountCurrency'] = $this->creditAmountCurrency;
        }

        if ($this->valueDate !== null) {
            $data['ValueDate'] = $this->valueDate;
        }

        if ($this->chargesAmount !== null) {
            $data['ChargesAmount'] = $this->chargesAmount;
        }

        if ($this->chargesCurrency !== null) {
            $data['ChargesCurrency'] = $this->chargesCurrency;
        }

        if ($this->exchangeRate !== null) {
            $data['ExchangeRate'] = $this->exchangeRate;
        }

        if ($this->utn !== null) {
            $data['UTN'] = $this->utn;
        }

        if ($this->error !== null) {
            $data['Error'] = $this->error;
        }

        if ($this->errorDescription !== null) {
            $data['ErrorDescription'] = $this->errorDescription;
        }

        return $data;
    }
}
