<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Notification;

/**
 * Register URL Request.
 *
 * @psalm-immutable
*/
final class RegisterUrlRequest
{
    public function __construct(
        public readonly string $ReferenceId,
        public readonly string $ApiKey,
        public readonly string $CallBackUrl,
        public readonly string $NotificationType,
        public readonly ?string $ProfileApprover = null,
        public readonly ?string $Channel = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
    */
    public static function fromArray(array $data): self
    {
        return new self(
            ReferenceId: (string) ($data['ReferenceId'] ?? $data['referenceId'] ?? ''),
            ApiKey: (string) ($data['ApiKey'] ?? $data['apiKey'] ?? ''),
            CallBackUrl: (string) ($data['CallBackUrl'] ?? $data['callBackUrl'] ?? ''),
            NotificationType: (string) ($data['NotificationType'] ?? $data['notificationType'] ?? ''),
            ProfileApprover: isset($data['ProfileApprover']) || isset($data['profileApprover'])
                ? (string) ($data['ProfileApprover'] ?? $data['profileApprover'])
                : null,
            Channel: isset($data['Channel']) || isset($data['channel'])
                ? (string) ($data['Channel'] ?? $data['channel'])
                : null,
        );
    }

    /**
     * @return array<string, mixed>
    */
    public function toArray(): array
    {
        $data = [
            'ReferenceId' => $this->ReferenceId,
            'ApiKey' => $this->ApiKey,
            'CallBackUrl' => $this->CallBackUrl,
            'NotificationType' => $this->NotificationType,
        ];

        if ($this->ProfileApprover !== null) {
            $data['ProfileApprover'] = $this->ProfileApprover;
        }

        if ($this->Channel !== null) {
            $data['Channel'] = $this->Channel;
        }

        return $data;
    }
}
