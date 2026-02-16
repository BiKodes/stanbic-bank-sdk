<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\Payment;

use Stanbic\SDK\Domain\Payment\PaymentRequest;

/**
 * @psalm-immutable
*/
final class PaymentRequestStub extends PaymentRequest
{
    public function __construct(
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
     * @psalm-external-mutation-free
     * @return array<string, mixed>
    */
    public function toArray(): array
    {
        return $this->baseArray();
    }
}
