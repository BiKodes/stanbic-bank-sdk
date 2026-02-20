<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\Notification;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\Notification\SmsEmailNotificationResponse;

final class SmsEmailNotificationResponseTest extends TestCase
{
    public function testCreateSmsEmailNotificationResponse(): void
    {
        $response = new SmsEmailNotificationResponse(
            ResponseCode: '00',
            ResponseMessage: 'Success',
            ReferenceId: '8a23b243-120f-44a4-a3b2-43120fa4a40b'
        );

        $this->assertSame('00', $response->ResponseCode);
        $this->assertSame('Success', $response->ResponseMessage);
        $this->assertSame('8a23b243-120f-44a4-a3b2-43120fa4a40b', $response->ReferenceId);
    }

    public function testFromArray(): void
    {
        $data = [
            'ResponseCode' => '00',
            'ResponseMessage' => 'Success',
            'ReferenceId' => '8a23b243-120f-44a4-a3b2-43120fa4a40b',
        ];

        $response = SmsEmailNotificationResponse::fromArray($data);

        $this->assertSame('00', $response->ResponseCode);
        $this->assertSame('Success', $response->ResponseMessage);
        $this->assertSame('8a23b243-120f-44a4-a3b2-43120fa4a40b', $response->ReferenceId);
    }

    public function testFromArrayWithCamelCase(): void
    {
        $data = [
            'responseCode' => '01',
            'responseMessage' => 'Failed',
            'referenceId' => 'failed-ref-123',
        ];

        $response = SmsEmailNotificationResponse::fromArray($data);

        $this->assertSame('01', $response->ResponseCode);
        $this->assertSame('Failed', $response->ResponseMessage);
        $this->assertSame('failed-ref-123', $response->ReferenceId);
    }

    public function testToArray(): void
    {
        $response = new SmsEmailNotificationResponse(
            ResponseCode: '00',
            ResponseMessage: 'Success',
            ReferenceId: '8a23b243-120f-44a4-a3b2-43120fa4a40b'
        );

        $array = $response->toArray();

        $this->assertSame('00', $array['ResponseCode']);
        $this->assertSame('Success', $array['ResponseMessage']);
        $this->assertSame('8a23b243-120f-44a4-a3b2-43120fa4a40b', $array['ReferenceId']);
    }
}
