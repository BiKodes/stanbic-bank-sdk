<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Service;

interface TransferServiceInterface
{
    /**
     * Initiate own account transfer.
     *
     * @param mixed $request InterAccountTransferRequest
     * @return mixed PaymentResponse
    */
    public function initiateInterAccountTransfer(mixed $request): mixed;

    /**
     * Initiate Electronic Funds Transfer.
     *
     * @param mixed $request EftTransferRequest
     * @return mixed PaymentResponse
    */
    public function initiateEftTransfer(mixed $request): mixed;

    /**
     * Initiate international SWIFT payment.
     *
     * @param mixed $request SwiftTransferRequest
     * @return mixed PaymentResponse
    */
    public function initiateSwiftTransfer(mixed $request): mixed;

    /**
     * Initiate Real-time Gross Settlement transfer.
     *
     * @param mixed $request RtgsTransferRequest
     * @return mixed PaymentResponse
    */
    public function initiateRtgsTransfer(mixed $request): mixed;
}
