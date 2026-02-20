<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\Notification;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\Notification\RegisterUrlRequest;

final class RegisterUrlRequestTest extends TestCase
{
    public function testCreateRegisterUrlRequest(): void
    {
        $request = new RegisterUrlRequest(
            ReferenceId: 'P33KL312231221',
            ApiKey: 'AwkekerrDE',
            CallBackUrl: 'https://www.testing.dev.example.co.ke',
            NotificationType: 'CREDIT',
            ProfileApprover: 'Your app Channel',
            Channel: 'APGW'
        );

        $this->assertSame('P33KL312231221', $request->ReferenceId);
        $this->assertSame('AwkekerrDE', $request->ApiKey);
        $this->assertSame('https://www.testing.dev.example.co.ke', $request->CallBackUrl);
        $this->assertSame('CREDIT', $request->NotificationType);
        $this->assertSame('Your app Channel', $request->ProfileApprover);
        $this->assertSame('APGW', $request->Channel);
    }

    public function testFromArray(): void
    {
        $data = [
            'ReferenceId' => 'P33KL312231221',
            'ApiKey' => 'AwkekerrDE',
            'CallBackUrl' => 'https://www.testing.dev.example.co.ke',
            'NotificationType' => 'CREDIT',
            'ProfileApprover' => 'Your app Channel',
            'Channel' => 'APGW',
        ];

        $request = RegisterUrlRequest::fromArray($data);

        $this->assertSame('P33KL312231221', $request->ReferenceId);
        $this->assertSame('AwkekerrDE', $request->ApiKey);
        $this->assertSame('https://www.testing.dev.example.co.ke', $request->CallBackUrl);
        $this->assertSame('CREDIT', $request->NotificationType);
        $this->assertSame('Your app Channel', $request->ProfileApprover);
        $this->assertSame('APGW', $request->Channel);
    }

    public function testFromArrayWithCamelCase(): void
    {
        $data = [
            'referenceId' => 'P33KL312231221',
            'apiKey' => 'AwkekerrDE',
            'callBackUrl' => 'https://www.testing.dev.example.co.ke',
            'notificationType' => 'DEBIT',
        ];

        $request = RegisterUrlRequest::fromArray($data);

        $this->assertSame('P33KL312231221', $request->ReferenceId);
        $this->assertSame('AwkekerrDE', $request->ApiKey);
        $this->assertSame('https://www.testing.dev.example.co.ke', $request->CallBackUrl);
        $this->assertSame('DEBIT', $request->NotificationType);
        $this->assertNull($request->ProfileApprover);
        $this->assertNull($request->Channel);
    }

    public function testToArray(): void
    {
        $request = new RegisterUrlRequest(
            ReferenceId: 'P33KL312231221',
            ApiKey: 'AwkekerrDE',
            CallBackUrl: 'https://www.testing.dev.example.co.ke',
            NotificationType: 'CREDIT',
            ProfileApprover: 'Your app Channel',
            Channel: 'APGW'
        );

        $array = $request->toArray();

        $this->assertSame('P33KL312231221', $array['ReferenceId']);
        $this->assertSame('AwkekerrDE', $array['ApiKey']);
        $this->assertSame('https://www.testing.dev.example.co.ke', $array['CallBackUrl']);
        $this->assertSame('CREDIT', $array['NotificationType']);
        $this->assertSame('Your app Channel', $array['ProfileApprover']);
        $this->assertSame('APGW', $array['Channel']);
    }

    public function testToArrayOmitsNullFields(): void
    {
        $request = new RegisterUrlRequest(
            ReferenceId: 'P33KL312231221',
            ApiKey: 'AwkekerrDE',
            CallBackUrl: 'https://www.testing.dev.example.co.ke',
            NotificationType: 'CREDIT'
        );

        $array = $request->toArray();

        $this->assertArrayHasKey('ReferenceId', $array);
        $this->assertArrayHasKey('ApiKey', $array);
        $this->assertArrayHasKey('CallBackUrl', $array);
        $this->assertArrayHasKey('NotificationType', $array);
        $this->assertArrayNotHasKey('ProfileApprover', $array);
        $this->assertArrayNotHasKey('Channel', $array);
    }
}
