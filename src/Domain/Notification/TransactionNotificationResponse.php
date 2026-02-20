<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Notification;

/**
 * Transaction Notification Response.
 *
 * @psalm-immutable
*/
final class TransactionNotificationResponse
{
    public function __construct(
        public readonly string $ResultCode,
        public readonly string $ResultDesc,
    ) {
    }

    /**
     * @param array<string, mixed> $data
    */
    public static function fromArray(array $data): self
    {
        /** @var array<string, mixed> $result */
        $result = $data['Result'] ?? $data;

        return new self(
            ResultCode: (string) ($result['ResultCode'] ?? $result['resultCode'] ?? ''),
            ResultDesc: (string) ($result['ResultDesc'] ?? $result['resultDesc'] ?? ''),
        );
    }

    /**
     * @return array<string, mixed>
    */
    public function toArray(): array
    {
        return [
            'Result' => [
                'ResultCode' => $this->ResultCode,
                'ResultDesc' => $this->ResultDesc,
            ],
        ];
    }
}
