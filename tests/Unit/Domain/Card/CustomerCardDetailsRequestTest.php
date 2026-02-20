<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\Card;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\Card\Card;
use Stanbic\SDK\Domain\Card\CustomerCardDetailsRequest;
use Stanbic\SDK\Domain\Card\Interaction;

final class CustomerCardDetailsRequestTest extends TestCase
{
    public function testCreateCustomerCardDetailsRequest(): void
    {
        $card = new Card(
            pan: '4069051007375838',
            expiryYear: '2030',
            expiryMonth: '12',
            cardholderName: '1694241',
            paymentScheme: 'PULL_VISA',
        );
        $interaction = new Interaction(
            originInteractionID: '97c936d8-aadb-4d68-80ad-4fa84d8a295c',
            type: 'BROWSER_3D_SECURE_2'
        );

        $request = new CustomerCardDetailsRequest(
            messageID: '562bf227-5070-447e-b0c1-f1f30f2a1c4e',
            bankingID: 'StandardBank_BW',
            card: $card,
            interaction: $interaction
        );

        $this->assertSame('562bf227-5070-447e-b0c1-f1f30f2a1c4e', $request->messageID);
        $this->assertSame('StandardBank_BW', $request->bankingID);
        $this->assertSame('4069051007375838', $request->card->pan);
        $this->assertSame('2030', $request->card->expiryYear);
        $this->assertSame('BROWSER_3D_SECURE_2', $request->interaction->type);
    }

    public function testFromArray(): void
    {
        $data = [
            'messageID' => '562bf227-5070-447e-b0c1-f1f30f2a1c4e',
            'bankingID' => 'StandardBank_BW',
            'card' => [
                'pan' => '4069051007375838',
                'expiryYear' => '2030',
                'expiryMonth' => '12',
                'cardholderName' => '1694241',
                'paymentScheme' => 'PULL_VISA',
                'staus' => 'ACTIVE',
            ],
            'interaction' => [
                'originInteractionID' => '97c936d8-aadb-4d68-80ad-4fa84d8a295c',
                'entersektInteractionID' => 'f8b49fd5-2d03-427c-a925-1d62a87d3d2c',
                'interactionDateTime' => '2025-08-08T14:40:55.000Z',
                'type' => 'BROWSER_3D_SECURE_2',
            ],
        ];

        $request = CustomerCardDetailsRequest::fromArray($data);

        $this->assertSame('562bf227-5070-447e-b0c1-f1f30f2a1c4e', $request->messageID);
        $this->assertSame('StandardBank_BW', $request->bankingID);
        $this->assertSame('4069051007375838', $request->card->pan);
        $this->assertSame('2030', $request->card->expiryYear);
        $this->assertSame('12', $request->card->expiryMonth);
        $this->assertSame('97c936d8-aadb-4d68-80ad-4fa84d8a295c', $request->interaction->originInteractionID);
        $this->assertSame('f8b49fd5-2d03-427c-a925-1d62a87d3d2c', $request->interaction->entersektInteractionID);
    }

    public function testToArray(): void
    {
        $card = new Card(
            pan: '4069051007375838',
            expiryYear: '2030',
            expiryMonth: '12'
        );
        $interaction = new Interaction(
            originInteractionID: '97c936d8-aadb-4d68-80ad-4fa84d8a295c',
            type: 'BROWSER_3D_SECURE_2'
        );

        $request = new CustomerCardDetailsRequest(
            messageID: '562bf227-5070-447e-b0c1-f1f30f2a1c4e',
            bankingID: 'StandardBank_BW',
            card: $card,
            interaction: $interaction
        );

        $array = $request->toArray();

        $this->assertSame('562bf227-5070-447e-b0c1-f1f30f2a1c4e', $array['messageID']);
        $this->assertSame('StandardBank_BW', $array['bankingID']);
        $this->assertIsArray($array['card']);
        $this->assertIsArray($array['interaction']);
    }

    public function testFromArrayWithObjectInputs(): void
    {
        $card = new Card(
            pan: '4069051007375838',
            expiryYear: '2030'
        );
        $interaction = new Interaction(originInteractionID: '97c936d8-aadb-4d68-80ad-4fa84d8a295c');

        $request = CustomerCardDetailsRequest::fromArray([
            'messageID' => '562bf227-5070-447e-b0c1-f1f30f2a1c4e',
            'bankingID' => 'StandardBank_BW',
            'card' => $card,
            'interaction' => $interaction,
        ]);

        $this->assertSame('4069051007375838', $request->card->pan);
        $this->assertSame('2030', $request->card->expiryYear);
        $this->assertSame('97c936d8-aadb-4d68-80ad-4fa84d8a295c', $request->interaction->originInteractionID);
    }

    public function testFromArrayWithMissingCardAndInteraction(): void
    {
        $request = CustomerCardDetailsRequest::fromArray([
            'messageID' => '562bf227-5070-447e-b0c1-f1f30f2a1c4e',
            'bankingID' => 'StandardBank_BW',
        ]);

        $this->assertSame('562bf227-5070-447e-b0c1-f1f30f2a1c4e', $request->messageID);
        $this->assertSame('StandardBank_BW', $request->bankingID);
        $this->assertSame([], $request->card->toArray());
        $this->assertSame([], $request->interaction->toArray());
    }
}
