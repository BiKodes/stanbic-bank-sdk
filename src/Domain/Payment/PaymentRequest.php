<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Payment;

/**
 * Base payment request.
 *
 * @psalm-immutable
*/
abstract class PaymentRequest
{
    public function __construct(
        public readonly string $dbsReferenceId,
        public readonly string $txnNarrative,
        public readonly string $requestedExecutionDate,
        public readonly ?string $endToEndId = null,
        public readonly ?string $chargeBearer = null,
    ) {
    }

    /**
     * @return array<string, mixed>
    */
    protected function baseArray(): array
    {
        $data = [
            'dbsReferenceId' => $this->dbsReferenceId,
            'txnNarrative' => $this->txnNarrative,
            'requestedExecutionDate' => $this->requestedExecutionDate,
        ];

        if ($this->endToEndId !== null) {
            $data['endToEndId'] = $this->endToEndId;
        }

        if ($this->chargeBearer !== null) {
            $data['chargeBearer'] = $this->chargeBearer;
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
    */
    abstract public function toArray(): array;
}
