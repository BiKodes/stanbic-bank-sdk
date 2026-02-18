<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Payment;

/**
 * Inter-account transfer request (sandbox schema).
 *
 * @psalm-immutable
*/
final class InterAccountTransferRequest
{
    public function __construct(
        public readonly string $referenceId,
        public readonly string $channel,
        public readonly string $creditAccount,
        public readonly string $creditCurrency,
        public readonly string $narration,
        public readonly string $debitAmount,
        public readonly string $debitAccount,
        public readonly string $debitCurrency,
        public readonly string $paymentDetails,
    ) {
    }

    /**
     * @param array<string, mixed> $data
    */
    public static function fromArray(array $data): self
    {
        $creditAccount = (string) ($data['CreditAccount']
            ?? $data['creditAccount']
            ?? $data['destinationAccountNumber']
            ?? $data['destination_account_number']
            ?? '');

        $debitAccount = (string) ($data['DebitAccount']
            ?? $data['debitAccount']
            ?? $data['sourceAccountNumber']
            ?? $data['source_account_number']
            ?? '');

        $debitAmount = (string) ($data['DebitAmount']
            ?? $data['debitAmount']
            ?? $data['amount']
            ?? 0.0);

        $debitCurrency = (string) ($data['DebitCurrency']
            ?? $data['debitCurrency']
            ?? $data['currency']
            ?? 'KES');

        return new self(
            referenceId: (string) ($data['ReferenceId'] ?? $data['referenceId'] ?? $data['reference_id'] ?? ''),
            channel: (string) ($data['Channel'] ?? $data['channel'] ?? ''),
            creditAccount: $creditAccount,
            creditCurrency: (string) ($data['CreditCurrency'] ?? $data['creditCurrency'] ?? ''),
            narration: (string) ($data['Narration'] ?? $data['narration'] ?? ''),
            debitAmount: $debitAmount,
            debitAccount: $debitAccount,
            debitCurrency: $debitCurrency,
            paymentDetails: (string) ($data['PaymentDetails'] ?? $data['paymentDetails'] ?? ''),
        );
    }

    public function toArray(): array
    {
        return [
            'ReferenceId' => $this->referenceId,
            'Channel' => $this->channel,
            'CreditAccount' => $this->creditAccount,
            'CreditCurrency' => $this->creditCurrency,
            'Narration' => $this->narration,
            'DebitAmount' => $this->debitAmount,
            'DebitAccount' => $this->debitAccount,
            'DebitCurrency' => $this->debitCurrency,
            'PaymentDetails' => $this->paymentDetails,
        ];
    }
}
