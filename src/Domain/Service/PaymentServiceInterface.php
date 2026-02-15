<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Service;

interface PaymentServiceInterface
{
    /**
     * Initiate inter-bank transfer via Pesalink.
     *
     * @param mixed $request PesalinkPaymentRequest
     * @return mixed PaymentResponse
    */
    public function initiatePesalinkPayment(mixed $request): mixed;

    /**
     * Initiate transfer within Stanbic accounts.
     *
     * @param mixed $request StanbicPaymentRequest
     * @return mixed PaymentResponse
    */
    public function initiateStanbicPayment(mixed $request): mixed;

    /**
     * Disburse to M-PESA, Airtel Money, T-Kash.
     *
     * @param mixed $request MobileMoneyRequest
     * @return mixed PaymentResponse
    */
    public function sendToMobileWallet(mixed $request): mixed;
}
