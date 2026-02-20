<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\Card;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\Card\CustomerCardDetailsResponse;
use Stanbic\SDK\Domain\Card\Interaction;

final class CustomerCardDetailsResponseTest extends TestCase
{
    public function testCreateCustomerCardDetailsResponse(): void
    {
        $interaction = new Interaction(
            originInteractionID: '97c936d8-aadb-4d68-80ad-4fa84d8a295c',
            entersektInteractionID: 'f8b49fd5-2d03-427c-a925-1d62a87d3d2c',
            type: 'BROWSER_3D_SECURE_2'
        );

        $response = new CustomerCardDetailsResponse(
            messageID: '200',
            interaction: $interaction,
            result: ['action' => 'APPROVED'],
            customer: ['bankingCustomerID' => '1694241']
        );

        $this->assertSame('200', $response->messageID);
        $this->assertSame('97c936d8-aadb-4d68-80ad-4fa84d8a295c', $response->interaction->originInteractionID);
        $this->assertIsArray($response->result);
        $this->assertSame('APPROVED', $response->result['action']);
        $this->assertIsArray($response->customer);
        $this->assertSame('1694241', $response->customer['bankingCustomerID']);
    }

    public function testFromArray(): void
    {
        $data = [
            'messageID' => '200',
            'interaction' => [
                'originInteractionID' => '97c936d8-aadb-4d68-80ad-4fa84d8a295c',
                'entersektInteractionID' => 'f8b49fd5-2d03-427c-a925-1d62a87d3d2c',
                'interactionDateTime' => '2025-08-08T14:40:55.000Z',
                'type' => 'BROWSER_3D_SECURE_2',
            ],
            'result' => [
                'action' => 'APPROVED',
                'reasons' => [],
            ],
            'customer' => [
                'customerDigital' => [
                    'authenticationCommsOptions' => 'SMS',
                ],
                'customerBanking' => [
                    'bankingCustomerID' => '1694241',
                ],
            ],
        ];

        $response = CustomerCardDetailsResponse::fromArray($data);

        $this->assertSame('200', $response->messageID);
        $this->assertSame('97c936d8-aadb-4d68-80ad-4fa84d8a295c', $response->interaction->originInteractionID);
        $this->assertIsArray($response->result);
        $this->assertSame('APPROVED', $response->result['action']);
        $this->assertIsArray($response->customer);
        $this->assertIsArray($response->customer['customerDigital']);
        $this->assertSame('SMS', $response->customer['customerDigital']['authenticationCommsOptions']);
    }

    public function testToArray(): void
    {
        $interaction = new Interaction(
            originInteractionID: '97c936d8-aadb-4d68-80ad-4fa84d8a295c',
            type: 'BROWSER_3D_SECURE_2'
        );

        $response = new CustomerCardDetailsResponse(
            messageID: '200',
            interaction: $interaction,
            result: ['action' => 'APPROVED'],
            customer: ['bankingCustomerID' => '1694241']
        );

        $array = $response->toArray();

        $this->assertSame('200', $array['messageID']);
        $this->assertIsArray($array['interaction']);
        $this->assertIsArray($array['result']);
        $this->assertSame('APPROVED', $array['result']['action']);
        $this->assertIsArray($array['customer']);
        $this->assertSame('1694241', $array['customer']['bankingCustomerID']);
    }

    public function testToArrayOmitsNullFields(): void
    {
        $interaction = new Interaction(originInteractionID: '97c936d8-aadb-4d68-80ad-4fa84d8a295c');

        $response = new CustomerCardDetailsResponse(
            messageID: '200',
            interaction: $interaction
        );

        $array = $response->toArray();

        $this->assertArrayHasKey('messageID', $array);
        $this->assertArrayHasKey('interaction', $array);
        $this->assertArrayNotHasKey('result', $array);
        $this->assertArrayNotHasKey('customer', $array);
    }

    public function testFromArrayWithObjectInput(): void
    {
        $interaction = new Interaction(
            originInteractionID: '97c936d8-aadb-4d68-80ad-4fa84d8a295c',
            type: 'BROWSER_3D_SECURE_2'
        );

        $response = CustomerCardDetailsResponse::fromArray([
            'messageID' => '200',
            'interaction' => $interaction,
        ]);

        $this->assertSame('97c936d8-aadb-4d68-80ad-4fa84d8a295c', $response->interaction->originInteractionID);
        $this->assertNull($response->result);
        $this->assertNull($response->customer);
    }

    public function testFromArrayWithMissingFields(): void
    {
        $data = [
            'messageID' => '200',
        ];

        $response = CustomerCardDetailsResponse::fromArray($data);

        $this->assertSame('200', $response->messageID);
        $this->assertNull($response->result);
        $this->assertNull($response->customer);
    }
}
