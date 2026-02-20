<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Notification;

/**
 * SMS Email Notification Response.
 *
 * @psalm-immutable
*/
final class SmsEmailNotificationResponse
{
    public function __construct(
        public readonly string $ResponseCode,
        public readonly string $ResponseMessage,
        public readonly string $ReferenceId,
    ) {
    }

    /**
     * @param array<string, mixed> $data
    */
    public static function fromArray(array $data): self
    {
        return new self(
            ResponseCode: (string) ($data['ResponseCode'] ?? $data['responseCode'] ?? ''),
            ResponseMessage: (string) ($data['ResponseMessage'] ?? $data['responseMessage'] ?? ''),
            ReferenceId: (string) ($data['ReferenceId'] ?? $data['referenceId'] ?? ''),
        );
    }

    /**
     * @return array<string, mixed>
    */
    public function toArray(): array
    {
        return [
            'ResponseCode' => $this->ResponseCode,
            'ResponseMessage' => $this->ResponseMessage,
            'ReferenceId' => $this->ReferenceId,
        ];
    }
}
