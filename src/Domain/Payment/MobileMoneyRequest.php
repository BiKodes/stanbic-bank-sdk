<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Payment;

use Stanbic\SDK\Domain\ValueObject\MobileMoneyMno;
use Stanbic\SDK\Domain\ValueObject\OriginatorAccount;
use Stanbic\SDK\Domain\ValueObject\OriginatorIdentification;

/**
 * Mobile money payment request.
 *
 * @psalm-immutable
*/
final class MobileMoneyRequest extends PaymentRequest
{
    public function __construct(
        public readonly OriginatorAccount $originatorAccount,
        public readonly TransferTransactionInformation $transferTransactionInformation,
        public readonly ?string $callBackUrl = null,
        string $dbsReferenceId,
        string $txnNarrative,
        string $requestedExecutionDate,
        ?string $endToEndId = null,
        ?string $chargeBearer = null,
    ) {
        parent::__construct(
            dbsReferenceId: $dbsReferenceId,
            txnNarrative: $txnNarrative,
            requestedExecutionDate: $requestedExecutionDate,
            endToEndId: $endToEndId,
            chargeBearer: $chargeBearer,
        );
    }

    /**
     * @param array<string, mixed> $data
    */
    public static function fromArray(array $data): self
    {
        /** @var array<string, mixed>|OriginatorAccount|null $originatorData */
        $originatorData = $data['originatorAccount'] ?? $data['originator_account'] ?? null;
        if ($originatorData instanceof OriginatorAccount) {
            $originatorAccount = $originatorData;
        } elseif (is_array($originatorData)) {
            /** @var array<string, mixed> $originatorArray */
            $originatorArray = $originatorData;
            $originatorAccount = OriginatorAccount::fromArray($originatorArray);
        } else {
            $originatorAccount = OriginatorAccount::fromArray([]);
        }

        if (empty($originatorAccount->identification->toArray())) {
            $mobile = isset($data['originatorMobileNumber']) || isset($data['originator_mobile_number'])
                ? (string) ($data['originatorMobileNumber'] ?? $data['originator_mobile_number'])
                : null;
            $originatorAccount = new OriginatorAccount(
                new OriginatorIdentification(mobileNumber: $mobile)
            );
        }

        /** @var array<string, mixed>|TransferTransactionInformation|null $ttiData */
        $ttiData = $data['transferTransactionInformation'] ?? $data['transfer_transaction_information'] ?? null;
        if ($ttiData instanceof TransferTransactionInformation) {
            $tti = $ttiData;
        } elseif (is_array($ttiData)) {
            /** @var array<string, mixed> $ttiArray */
            $ttiArray = $ttiData;
            $tti = TransferTransactionInformation::fromArray($ttiArray);
        } else {
            $tti = TransferTransactionInformation::fromArray([]);
        }

        if ($tti->counterparty === null) {
            $recipientName = isset($data['recipientName']) || isset($data['recipient_name'])
                ? (string) ($data['recipientName'] ?? $data['recipient_name'])
                : null;

            $recipientMobile = isset($data['recipientMobileNumber']) || isset($data['recipient_mobile_number'])
                ? (string) ($data['recipientMobileNumber'] ?? $data['recipient_mobile_number'])
                : null;
            if ($recipientName !== null || $recipientMobile !== null) {
                $tti = new TransferTransactionInformation(
                    amount: $tti->amount,
                    currency: $tti->currency,
                    counterpartyAccount: $tti->counterpartyAccount,
                    counterparty: new Counterparty(
                        name: $recipientName ?? '',
                        account: null,
                        mobileNumber: $recipientMobile
                    ),
                    counterpartyName: $tti->counterpartyName,
                    remittanceInformation: $tti->remittanceInformation,
                    paymentPurpose: $tti->paymentPurpose,
                    mobileMoneyMno: $tti->mobileMoneyMno,
                    endToEndIdentification: $tti->endToEndIdentification,
                    instructedCurrencyKey: $tti->instructedCurrencyKey
                );
            }
        }

        $mobileNetwork = isset($data['mobileNetwork']) || isset($data['mobile_network'])
            ? (string) ($data['mobileNetwork'] ?? $data['mobile_network'])
            : null;
        if ($mobileNetwork !== null && $tti->mobileMoneyMno === null) {
            $tti = new TransferTransactionInformation(
                amount: $tti->amount,
                currency: $tti->currency,
                counterpartyAccount: $tti->counterpartyAccount,
                counterparty: $tti->counterparty,
                counterpartyName: $tti->counterpartyName,
                remittanceInformation: $tti->remittanceInformation,
                paymentPurpose: $tti->paymentPurpose,
                mobileMoneyMno: new MobileMoneyMno($mobileNetwork),
                endToEndIdentification: $tti->endToEndIdentification,
                instructedCurrencyKey: $tti->instructedCurrencyKey
            );
        }

        $requestedExecutionDate = (string) ($data['requestedExecutionDate'] ?? $data['requested_execution_date'] ?? '');

        return new self(
            originatorAccount: $originatorAccount,
            transferTransactionInformation: $tti,
            callBackUrl: isset($data['callBackUrl']) || isset($data['call_back_url'])
                ? (string) ($data['callBackUrl'] ?? $data['call_back_url'])
                : null,
            dbsReferenceId: (string) ($data['dbsReferenceId'] ?? $data['dbs_reference_id'] ?? ''),
            txnNarrative: (string) ($data['txnNarrative'] ?? $data['txn_narrative'] ?? ''),
            requestedExecutionDate: $requestedExecutionDate,
            endToEndId: isset($data['endToEndId']) || isset($data['end_to_end_id'])
                ? (string) ($data['endToEndId'] ?? $data['end_to_end_id'])
                : null,
            chargeBearer: isset($data['chargeBearer']) || isset($data['charge_bearer'])
                ? (string) ($data['chargeBearer'] ?? $data['charge_bearer'])
                : null,
        );
    }

    public function toArray(): array
    {
        $data = array_merge(
            $this->baseArray(),
            [
                'originatorAccount' => $this->originatorAccount->toArray(),
                'transferTransactionInformation' => $this->transferTransactionInformation->toArray(),
            ]
        );

        if ($this->callBackUrl !== null) {
            $data['callBackUrl'] = $this->callBackUrl;
        }

        return $data;
    }
}
