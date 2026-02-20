<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Notification;

/**
 * SMS Email Notification Request.
 *
 * @psalm-immutable
*/
final class SmsEmailNotificationRequest
{
    public function __construct(
        public readonly string $Subject,
        public readonly string $ReferenceId,
        public readonly string $Message,
        public readonly string $Channel,
        public readonly ?string $EmailId = null,
        public readonly ?string $SendSMS = null,
        public readonly ?string $SendEmail = null,
        public readonly ?string $MobileNumber = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
    */
    public static function fromArray(array $data): self
    {
        return new self(
            Subject: (string) ($data['Subject'] ?? $data['subject'] ?? ''),
            ReferenceId: (string) ($data['ReferenceId'] ?? $data['referenceId'] ?? ''),
            Message: (string) ($data['Message'] ?? $data['message'] ?? ''),
            Channel: (string) ($data['Channel'] ?? $data['channel'] ?? ''),
            EmailId: isset($data['EmailId']) || isset($data['emailId'])
                ? (string) ($data['EmailId'] ?? $data['emailId'])
                : null,
            SendSMS: isset($data['SendSMS']) || isset($data['sendSMS'])
                ? (string) ($data['SendSMS'] ?? $data['sendSMS'])
                : null,
            SendEmail: isset($data['SendEmail']) || isset($data['sendEmail'])
                ? (string) ($data['SendEmail'] ?? $data['sendEmail'])
                : null,
            MobileNumber: isset($data['MobileNumber']) || isset($data['mobileNumber'])
                ? (string) ($data['MobileNumber'] ?? $data['mobileNumber'])
                : null,
        );
    }

    /**
     * @return array<string, mixed>
    */
    public function toArray(): array
    {
        $data = [
            'Subject' => $this->Subject,
            'ReferenceId' => $this->ReferenceId,
            'Message' => $this->Message,
            'Channel' => $this->Channel,
        ];

        if ($this->EmailId !== null) {
            $data['EmailId'] = $this->EmailId;
        }

        if ($this->SendSMS !== null) {
            $data['SendSMS'] = $this->SendSMS;
        }

        if ($this->SendEmail !== null) {
            $data['SendEmail'] = $this->SendEmail;
        }

        if ($this->MobileNumber !== null) {
            $data['MobileNumber'] = $this->MobileNumber;
        }

        return $data;
    }
}
