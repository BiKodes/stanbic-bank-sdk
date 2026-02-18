<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Payment;

use Stanbic\SDK\Domain\ValueObject\OriginatorAccount;
use Stanbic\SDK\Domain\ValueObject\Schedule;

/**
 * RTGS transfer request.
 *
 * @psalm-immutable
*/
final class RtgsTransferRequest extends PaymentRequest
{
    public function __construct(
        public readonly OriginatorAccount $originatorAccount,
        public readonly TransferTransactionInformation $transferTransactionInformation,
        public readonly ?Counterparty $counterparty = null,
        public readonly ?Schedule $schedule = null,
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

        /** @var array<string, mixed>|Schedule|null $scheduleData */
        $scheduleData = $data['schedule'] ?? null;
        $schedule = null;
        if ($scheduleData instanceof Schedule) {
            $schedule = $scheduleData;
        } elseif (is_array($scheduleData)) {
            /** @var array<string, mixed> $scheduleArray */
            $scheduleArray = $scheduleData;
            $schedule = Schedule::fromArray($scheduleArray);
        }

        $requestedExecutionDate = (string) ($data['requestedExecutionDate'] ?? $data['requested_execution_date'] ?? '');

        return new self(
            originatorAccount: $originatorAccount,
            transferTransactionInformation: $tti,
            counterparty: $counterparty,
            schedule: $schedule,
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

        if ($this->counterparty !== null) {
            $data['counterparty'] = $this->counterparty->toArray();
        }

        if ($this->schedule !== null) {
            $data['schedule'] = $this->schedule->toArray();
        }

        if ($this->callBackUrl !== null) {
            $data['callBackUrl'] = $this->callBackUrl;
        }

        return $data;
    }
}
