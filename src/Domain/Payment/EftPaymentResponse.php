<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Payment;

use Stanbic\SDK\Domain\ValueObject\EftTransactionDetails;

/**
 * EFT payment response.
 *
 * @psalm-immutable
*/
final class EftPaymentResponse
{
    public function __construct(
        public readonly string $sourceMsgId,
        public readonly string $responseCode,
        public readonly string $responseMessage,
        public readonly string $responseTime,
        public readonly ?EftTransactionDetails $transactionDetails = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
    */
    public static function fromArray(array $data): self
    {
        /** @var array<string, mixed>|EftTransactionDetails|null $detailsData */
        $detailsData = $data['TransactionDetails'] ?? $data['transactionDetails'] ?? null;
        $details = null;
        if ($detailsData instanceof EftTransactionDetails) {
            $details = $detailsData;
        } elseif (is_array($detailsData)) {
            /** @var array<string, mixed> $detailsArray */
            $detailsArray = $detailsData;
            $details = EftTransactionDetails::fromArray($detailsArray);
        }

        return new self(
            sourceMsgId: (string) ($data['SourceMsgId'] ?? $data['sourceMsgId'] ?? ''),
            responseCode: (string) ($data['ResponseCode'] ?? $data['responseCode'] ?? ''),
            responseMessage: (string) ($data['ResponseMessage'] ?? $data['responseMessage'] ?? ''),
            responseTime: (string) ($data['ResponseTime'] ?? $data['responseTime'] ?? ''),
            transactionDetails: $details,
        );
    }

    /**
     * @return array<string, mixed>
    */
    public function toArray(): array
    {
        $data = [
            'SourceMsgId' => $this->sourceMsgId,
            'ResponseCode' => $this->responseCode,
            'ResponseMessage' => $this->responseMessage,
            'ResponseTime' => $this->responseTime,
        ];

        if ($this->transactionDetails !== null) {
            $data['TransactionDetails'] = $this->transactionDetails->toArray();
        }

        return $data;
    }
}
