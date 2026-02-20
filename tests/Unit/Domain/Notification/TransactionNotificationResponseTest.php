<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\Notification;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\Notification\TransactionNotificationResponse;

final class TransactionNotificationResponseTest extends TestCase
{
    public function testCreateTransactionNotificationResponse(): void
    {
        $response = new TransactionNotificationResponse(
            ResultCode: '0',
            ResultDesc: 'Accepted'
        );

        $this->assertSame('0', $response->ResultCode);
        $this->assertSame('Accepted', $response->ResultDesc);
    }

    public function testFromArrayWithResultStructure(): void
    {
        $data = [
            'Result' => [
                'ResultCode' => '0',
                'ResultDesc' => 'Accepted',
            ],
        ];

        $response = TransactionNotificationResponse::fromArray($data);

        $this->assertSame('0', $response->ResultCode);
        $this->assertSame('Accepted', $response->ResultDesc);
    }

    public function testFromArrayWithoutResultWrapper(): void
    {
        $data = [
            'ResultCode' => '1',
            'ResultDesc' => 'Rejected',
        ];

        $response = TransactionNotificationResponse::fromArray($data);

        $this->assertSame('1', $response->ResultCode);
        $this->assertSame('Rejected', $response->ResultDesc);
    }

    public function testFromArrayWithCamelCase(): void
    {
        $data = [
            'resultCode' => '0',
            'resultDesc' => 'Accepted',
        ];

        $response = TransactionNotificationResponse::fromArray($data);

        $this->assertSame('0', $response->ResultCode);
        $this->assertSame('Accepted', $response->ResultDesc);
    }

    public function testToArray(): void
    {
        $response = new TransactionNotificationResponse(
            ResultCode: '0',
            ResultDesc: 'Accepted'
        );

        $array = $response->toArray();

        $this->assertArrayHasKey('Result', $array);
        $this->assertIsArray($array['Result']);
        $this->assertSame('0', $array['Result']['ResultCode']);
        $this->assertSame('Accepted', $array['Result']['ResultDesc']);
    }
}
