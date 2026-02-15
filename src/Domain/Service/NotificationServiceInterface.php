<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Service;

interface NotificationServiceInterface
{
    /**
     * Register webhook for payment alerts.
     *
     * @param mixed $request RegisterUrlRequest
     * @return mixed RegisterUrlResponse
    */
    public function registerPaymentResultUrl(mixed $request): mixed;

    /**
     * Enable transaction notifications.
     *
     * @param mixed $request TransactionNotificationRequest
     * @return mixed TransactionNotificationResponse
    */
    public function registerTransactionNotification(mixed $request): mixed;

    /**
     * Send SMS/email alerts.
     *
     * @param mixed $request SmsEmailNotificationRequest
     * @return mixed SmsEmailNotificationResponse
    */
    public function sendSmsEmailNotification(mixed $request): mixed;
}
