<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Payment;

/**
 * Common payment response.
 *
 * @psalm-immutable
*/
final class PaymentResponse
{
    public function __construct(
        public readonly string $bankReferenceId,
        public readonly string $dbsReferenceId,
        public readonly string $status,
        public readonly ?string $message = null,
        public readonly ?string $transactionId = null,
        public readonly ?float $transferFee = null,
        public readonly ?string $timestamp = null,
        public readonly ?string $errorCode = null,
        public readonly ?string $reasonText = null,
        public readonly ?string $nextExecutionDate = null,
        public readonly ?string $originatorConversationId = null,
        public readonly ?string $conversationId = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
    */
    public static function fromArray(array $data): self
    {
        $originatorConversationId = isset($data['OriginatorConversationID'])
            || isset($data['originatorConversationId'])
            ? (string) ($data['OriginatorConversationID'] ?? $data['originatorConversationId'])
            : null;

        return new self(
            bankReferenceId: (string) ($data['bankReferenceId'] ?? $data['bank_reference_id'] ?? ''),
            dbsReferenceId: (string) ($data['dbsReferenceId'] ?? $data['dbs_reference_id'] ?? ''),
            status: (string) ($data['status'] ?? $data['bankStatus'] ?? 'UNKNOWN'),
            message: isset($data['message']) || isset($data['statusDescription'])
                ? (string) ($data['message'] ?? $data['statusDescription'])
                : null,
            transactionId: isset($data['transactionId']) || isset($data['transaction_id'])
                ? (string) ($data['transactionId'] ?? $data['transaction_id'])
                : null,
            transferFee: isset($data['transferFee']) || isset($data['transfer_fee'])
                ? (float) ($data['transferFee'] ?? $data['transfer_fee'])
                : null,
            timestamp: isset($data['timestamp']) || isset($data['statusDate'])
                ? (string) ($data['timestamp'] ?? $data['statusDate'])
                : null,
            errorCode: isset($data['errorCode']) || isset($data['error_code'])
                ? (string) ($data['errorCode'] ?? $data['error_code'])
                : null,
            reasonText: isset($data['reasonText']) || isset($data['reason_text'])
                ? (string) ($data['reasonText'] ?? $data['reason_text'])
                : null,
            nextExecutionDate: isset($data['nextExecutionDate']) || isset($data['next_execution_date'])
                ? (string) ($data['nextExecutionDate'] ?? $data['next_execution_date'])
                : null,
            originatorConversationId: $originatorConversationId,
            conversationId: isset($data['ConversationID']) || isset($data['conversationId'])
                ? (string) ($data['ConversationID'] ?? $data['conversationId'])
                : null,
        );
    }

    /**
     * @return array<string, mixed>
    */
    public function toArray(): array
    {
        $data = [
            'bankReferenceId' => $this->bankReferenceId,
            'dbsReferenceId' => $this->dbsReferenceId,
            'status' => $this->status,
        ];

        if ($this->message !== null) {
            $data['message'] = $this->message;
        }

        if ($this->transactionId !== null) {
            $data['transactionId'] = $this->transactionId;
        }

        if ($this->transferFee !== null) {
            $data['transferFee'] = $this->transferFee;
        }

        if ($this->timestamp !== null) {
            $data['timestamp'] = $this->timestamp;
        }

        if ($this->errorCode !== null) {
            $data['errorCode'] = $this->errorCode;
        }

        if ($this->reasonText !== null) {
            $data['reasonText'] = $this->reasonText;
        }

        if ($this->nextExecutionDate !== null) {
            $data['nextExecutionDate'] = $this->nextExecutionDate;
        }

        if ($this->originatorConversationId !== null) {
            $data['OriginatorConversationID'] = $this->originatorConversationId;
        }

        if ($this->conversationId !== null) {
            $data['ConversationID'] = $this->conversationId;
        }

        return $data;
    }
}
