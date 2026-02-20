<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\Card;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\Card\Card;
use Stanbic\SDK\Domain\Card\CardDetailsRequest;
use Stanbic\SDK\Domain\Card\Interaction;

final class CardDetailsRequestTest extends TestCase
{
    public function testCreateCardDetailsRequest(): void
    {
        $card = new Card(pan: '4069051007375838');
        $interaction = new Interaction(
            originInteractionID: '97c936d8-aadb-4d68-80ad-4fa84d8a295c',
            type: 'BROWSER_3D_SECURE_2'
        );

        $request = new CardDetailsRequest(
            messageID: '562bf227-5070-447e-b0c1-f1f30f2a1c4e',
            bankingID: 'StandardBank_BW',
            card: $card,
            interaction: $interaction
        );

        $this->assertSame('562bf227-5070-447e-b0c1-f1f30f2a1c4e', $request->messageID);
        $this->assertSame('StandardBank_BW', $request->bankingID);
        $this->assertSame('4069051007375838', $request->card->pan);
        $this->assertSame('BROWSER_3D_SECURE_2', $request->interaction->type);
    }

    public function testFromArray(): void
    {
        $data = [
            'messageID' => '562bf227-5070-447e-b0c1-f1f30f2a1c4e',
            'bankingID' => 'StandardBank_BW',
            'card' => [
                'pan' => '4069051007375838',
            ],
            'interaction' => [
                'originInteractionID' => '97c936d8-aadb-4d68-80ad-4fa84d8a295c',
                'GetInteractionID' => 'f8b49fd5-2d03-427c-a925-1d62a87d3d2c',
                'interactionDateTime' => '2025-08-08T14:40:55.000Z',
                'type' => 'BROWSER_3D_SECURE_2',
            ],
        ];

        $request = CardDetailsRequest::fromArray($data);

        $this->assertSame('562bf227-5070-447e-b0c1-f1f30f2a1c4e', $request->messageID);
        $this->assertSame('StandardBank_BW', $request->bankingID);
        $this->assertSame('4069051007375838', $request->card->pan);
        $this->assertSame('97c936d8-aadb-4d68-80ad-4fa84d8a295c', $request->interaction->originInteractionID);
    }

    public function testToArray(): void
    {
        $card = new Card(pan: '4069051007375838', status: '200');
        $interaction = new Interaction(
            originInteractionID: '97c936d8-aadb-4d68-80ad-4fa84d8a295c',
            type: 'BROWSER_3D_SECURE_2'
        );

        $request = new CardDetailsRequest(
            messageID: '562bf227-5070-447e-b0c1-f1f30f2a1c4e',
            bankingID: 'StandardBank_BW',
            card: $card,
            interaction: $interaction
        );

        $array = $request->toArray();

        $this->assertSame('562bf227-5070-447e-b0c1-f1f30f2a1c4e', $array['messageID']);
        $this->assertSame('StandardBank_BW', $array['bankingID']);
        $this->assertIsArray($array['card']);
        /** @var array<string, mixed> $cardArray */
        $cardArray = $array['card'];
        $this->assertSame('4069051007375838', $cardArray['pan']);
        $this->assertIsArray($array['interaction']);
        /** @var array<string, mixed> $interactionArray */
        $interactionArray = $array['interaction'];
        $this->assertSame('97c936d8-aadb-4d68-80ad-4fa84d8a295c', $interactionArray['originInteractionID']);
    }

    public function testFromArrayWithObjectInputs(): void
    {
        $card = new Card(pan: '4069051007375838');
        $interaction = new Interaction(type: 'BROWSER_3D_SECURE_2');

        $request = CardDetailsRequest::fromArray([
            'messageID' => '562bf227-5070-447e-b0c1-f1f30f2a1c4e',
            'bankingID' => 'StandardBank_BW',
            'card' => $card,
            'interaction' => $interaction,
        ]);

        $this->assertSame('4069051007375838', $request->card->pan);
        $this->assertSame('BROWSER_3D_SECURE_2', $request->interaction->type);
    }

    public function testFromArrayWithMissingFields(): void
    {
        $data = [
            'messageID' => '562bf227-5070-447e-b0c1-f1f30f2a1c4e',
            'bankingID' => 'StandardBank_BW',
        ];

        $request = CardDetailsRequest::fromArray($data);

        $this->assertSame('562bf227-5070-447e-b0c1-f1f30f2a1c4e', $request->messageID);
        $this->assertSame('StandardBank_BW', $request->bankingID);
        $this->assertNull($request->card->pan);
        $this->assertNull($request->interaction->type);
    }
}
