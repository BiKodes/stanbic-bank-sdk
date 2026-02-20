<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\Card;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\Card\Card;
use Stanbic\SDK\Domain\Card\CardDetailsResponse;
use Stanbic\SDK\Domain\Card\Interaction;

final class CardDetailsResponseTest extends TestCase
{
    public function testCreateCardDetailsResponse(): void
    {
        $card = new Card(pan: '0100011231394', status: '200');
        $interaction = new Interaction(
            originInteractionID: '97c936d8-aadb-4d68-80ad-4fa84d8a295c',
            type: 'BROWSER_3D_SECURE_2'
        );

        $response = new CardDetailsResponse(
            messageID: '200',
            interaction: $interaction,
            card: $card
        );

        $this->assertSame('200', $response->messageID);
        $this->assertSame('0100011231394', $response->card->pan);
        $this->assertSame('97c936d8-aadb-4d68-80ad-4fa84d8a295c', $response->interaction->originInteractionID);
    }

    public function testFromArray(): void
    {
        $data = [
            'messageID' => '200',
            'interaction' => [
                'originInteractionID' => '97c936d8-aadb-4d68-80ad-4fa84d8a295c',
                'GetInteractionID' => 'f8b49fd5-2d03-427c-a925-1d62a87d3d2c',
                'interactionDateTime' => '2025-08-08T14:40:55.000Z',
                'type' => 'BROWSER_3D_SECURE_2',
            ],
            'result' => [
                'action' => 'APPROVED',
                'reasons' => [],
            ],
            'card' => [
                'status' => '200',
                'pan' => '0100011231394',
                'cardholdername' => 'MR',
                'paymentScheme' => 'SURNAME',
            ],
        ];

        $response = CardDetailsResponse::fromArray($data);

        $this->assertSame('200', $response->messageID);
        $this->assertSame('0100011231394', $response->card->pan);
        $this->assertIsArray($response->result);
        $this->assertSame('APPROVED', $response->result['action']);
    }

    public function testToArray(): void
    {
        $card = new Card(status: '200', pan: '0100011231394');
        $interaction = new Interaction(originInteractionID: '97c936d8-aadb-4d68-80ad-4fa84d8a295c');

        $response = new CardDetailsResponse(
            messageID: '200',
            interaction: $interaction,
            result: ['action' => 'APPROVED'],
            card: $card
        );

        $array = $response->toArray();

        $this->assertSame('200', $array['messageID']);
        $this->assertIsArray($array['interaction']);
        $this->assertIsArray($array['result']);
        $this->assertSame('APPROVED', $array['result']['action']);
        $this->assertIsArray($array['card']);
    }

    public function testToArrayOmitsNullResult(): void
    {
        $card = new Card(pan: '0100011231394');
        $interaction = new Interaction(originInteractionID: '97c936d8-aadb-4d68-80ad-4fa84d8a295c');

        $response = new CardDetailsResponse(
            messageID: '200',
            interaction: $interaction,
            card: $card
        );

        $array = $response->toArray();

        $this->assertArrayHasKey('messageID', $array);
        $this->assertArrayHasKey('interaction', $array);
        $this->assertArrayHasKey('card', $array);
        $this->assertArrayNotHasKey('result', $array);
    }

    public function testFromArrayWithObjectInputs(): void
    {
        $card = new Card(pan: '0100011231394');
        $interaction = new Interaction(type: 'BROWSER_3D_SECURE_2');

        $response = CardDetailsResponse::fromArray([
            'messageID' => '200',
            'interaction' => $interaction,
            'card' => $card,
        ]);

        $this->assertSame('0100011231394', $response->card->pan);
        $this->assertSame('BROWSER_3D_SECURE_2', $response->interaction->type);
    }

    public function testFromArrayWithMissingFields(): void
    {
        $data = [
            'messageID' => '200',
        ];

        $response = CardDetailsResponse::fromArray($data);

        $this->assertSame('200', $response->messageID);
        $this->assertNull($response->result);
        $this->assertNull($response->card->pan);
    }
}
