<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\Notification;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\Notification\RegisterUrlResponse;

final class RegisterUrlResponseTest extends TestCase
{
    public function testCreateRegisterUrlResponse(): void
    {
        $response = new RegisterUrlResponse(
            ResponseCode: '00',
            ResponseMessage: 'Success',
            ReferenceId: 'P33KL312231221'
        );

        $this->assertSame('00', $response->ResponseCode);
        $this->assertSame('Success', $response->ResponseMessage);
        $this->assertSame('P33KL312231221', $response->ReferenceId);
    }

    public function testFromArray(): void
    {
        $data = [
            'ResponseCode' => '00',
            'ResponseMessage' => 'Success',
            'ReferenceId' => 'P33KL312231221',
        ];

        $response = RegisterUrlResponse::fromArray($data);

        $this->assertSame('00', $response->ResponseCode);
        $this->assertSame('Success', $response->ResponseMessage);
        $this->assertSame('P33KL312231221', $response->ReferenceId);
    }

    public function testFromArrayWithCamelCase(): void
    {
        $data = [
            'responseCode' => '01',
            'responseMessage' => 'Failed',
            'referenceId' => 'XVD12233333111',
        ];

        $response = RegisterUrlResponse::fromArray($data);

        $this->assertSame('01', $response->ResponseCode);
        $this->assertSame('Failed', $response->ResponseMessage);
        $this->assertSame('XVD12233333111', $response->ReferenceId);
    }

    public function testToArray(): void
    {
        $response = new RegisterUrlResponse(
            ResponseCode: '00',
            ResponseMessage: 'Success',
            ReferenceId: 'P33KL312231221'
        );

        $array = $response->toArray();

        $this->assertSame('00', $array['ResponseCode']);
        $this->assertSame('Success', $array['ResponseMessage']);
        $this->assertSame('P33KL312231221', $array['ReferenceId']);
    }
}
