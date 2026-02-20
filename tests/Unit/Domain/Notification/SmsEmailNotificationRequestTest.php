<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\Notification;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\Notification\SmsEmailNotificationRequest;

final class SmsEmailNotificationRequestTest extends TestCase
{
    public function testCreateSmsEmailNotificationRequest(): void
    {
        $request = new SmsEmailNotificationRequest(
            Subject: 'Stanbic notification',
            ReferenceId: '8a23b243-120f-44a4-a3b2-43120fa4a40b',
            Message: 'Your transaction was successful',
            Channel: 'onboarding',
            EmailId: 'user@example.com',
            SendSMS: 'false',
            SendEmail: 'true',
            MobileNumber: '254712345678'
        );

        $this->assertSame('Stanbic notification', $request->Subject);
        $this->assertSame('8a23b243-120f-44a4-a3b2-43120fa4a40b', $request->ReferenceId);
        $this->assertSame('Your transaction was successful', $request->Message);
        $this->assertSame('onboarding', $request->Channel);
        $this->assertSame('user@example.com', $request->EmailId);
        $this->assertSame('false', $request->SendSMS);
        $this->assertSame('true', $request->SendEmail);
        $this->assertSame('254712345678', $request->MobileNumber);
    }

    public function testFromArray(): void
    {
        $data = [
            'Subject' => 'Stanbic notification',
            'ReferenceId' => '8a23b243-120f-44a4-a3b2-43120fa4a40b',
            'EmailId' => 'user@example.com',
            'SendSMS' => 'false',
            'Message' => 'Your transaction was successful',
            'Channel' => 'onboarding',
            'SendEmail' => 'true',
            'MobileNumber' => '254712345678',
        ];

        $request = SmsEmailNotificationRequest::fromArray($data);

        $this->assertSame('Stanbic notification', $request->Subject);
        $this->assertSame('8a23b243-120f-44a4-a3b2-43120fa4a40b', $request->ReferenceId);
        $this->assertSame('user@example.com', $request->EmailId);
        $this->assertSame('false', $request->SendSMS);
        $this->assertSame('Your transaction was successful', $request->Message);
        $this->assertSame('onboarding', $request->Channel);
        $this->assertSame('true', $request->SendEmail);
        $this->assertSame('254712345678', $request->MobileNumber);
    }

    public function testFromArrayWithCamelCase(): void
    {
        $data = [
            'subject' => 'Test notification',
            'referenceId' => '12345-uuid',
            'message' => 'Test message',
            'channel' => 'api',
        ];

        $request = SmsEmailNotificationRequest::fromArray($data);

        $this->assertSame('Test notification', $request->Subject);
        $this->assertSame('12345-uuid', $request->ReferenceId);
        $this->assertSame('Test message', $request->Message);
        $this->assertSame('api', $request->Channel);
        $this->assertNull($request->EmailId);
        $this->assertNull($request->SendSMS);
        $this->assertNull($request->SendEmail);
        $this->assertNull($request->MobileNumber);
    }

    public function testToArray(): void
    {
        $request = new SmsEmailNotificationRequest(
            Subject: 'Stanbic notification',
            ReferenceId: '8a23b243-120f-44a4-a3b2-43120fa4a40b',
            Message: 'Your transaction was successful',
            Channel: 'onboarding',
            EmailId: 'user@example.com',
            SendSMS: 'false',
            SendEmail: 'true',
            MobileNumber: '254712345678'
        );

        $array = $request->toArray();

        $this->assertSame('Stanbic notification', $array['Subject']);
        $this->assertSame('8a23b243-120f-44a4-a3b2-43120fa4a40b', $array['ReferenceId']);
        $this->assertSame('Your transaction was successful', $array['Message']);
        $this->assertSame('onboarding', $array['Channel']);
        $this->assertSame('user@example.com', $array['EmailId']);
        $this->assertSame('false', $array['SendSMS']);
        $this->assertSame('true', $array['SendEmail']);
        $this->assertSame('254712345678', $array['MobileNumber']);
    }

    public function testToArrayOmitsNullFields(): void
    {
        $request = new SmsEmailNotificationRequest(
            Subject: 'Stanbic notification',
            ReferenceId: '8a23b243-120f-44a4-a3b2-43120fa4a40b',
            Message: 'Your transaction was successful',
            Channel: 'onboarding'
        );

        $array = $request->toArray();

        $this->assertArrayHasKey('Subject', $array);
        $this->assertArrayHasKey('ReferenceId', $array);
        $this->assertArrayHasKey('Message', $array);
        $this->assertArrayHasKey('Channel', $array);
        $this->assertArrayNotHasKey('EmailId', $array);
        $this->assertArrayNotHasKey('SendSMS', $array);
        $this->assertArrayNotHasKey('SendEmail', $array);
        $this->assertArrayNotHasKey('MobileNumber', $array);
    }
}
