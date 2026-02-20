<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\Card;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\Card\Interaction;

final class InteractionTest extends TestCase
{
    public function testCreateInteraction(): void
    {
        $interaction = new Interaction(
            originInteractionID: '97c936d8-aadb-4d68-80ad-4fa84d8a295c',
            GetInteractionID: 'f8b49fd5-2d03-427c-a925-1d62a87d3d2c',
            interactionDateTime: '2025-08-08T14:40:55.000Z',
            type: 'BROWSER_3D_SECURE_2'
        );

        $this->assertSame('97c936d8-aadb-4d68-80ad-4fa84d8a295c', $interaction->originInteractionID);
        $this->assertSame('f8b49fd5-2d03-427c-a925-1d62a87d3d2c', $interaction->GetInteractionID);
        $this->assertSame('2025-08-08T14:40:55.000Z', $interaction->interactionDateTime);
        $this->assertSame('BROWSER_3D_SECURE_2', $interaction->type);
    }

    public function testFromArray(): void
    {
        $data = [
            'originInteractionID' => '97c936d8-aadb-4d68-80ad-4fa84d8a295c',
            'GetInteractionID' => 'f8b49fd5-2d03-427c-a925-1d62a87d3d2c',
            'interactionDateTime' => '2025-08-08T14:40:55.000Z',
            'type' => 'BROWSER_3D_SECURE_2',
        ];

        $interaction = Interaction::fromArray($data);

        $this->assertSame('97c936d8-aadb-4d68-80ad-4fa84d8a295c', $interaction->originInteractionID);
        $this->assertSame('f8b49fd5-2d03-427c-a925-1d62a87d3d2c', $interaction->GetInteractionID);
        $this->assertSame('2025-08-08T14:40:55.000Z', $interaction->interactionDateTime);
        $this->assertSame('BROWSER_3D_SECURE_2', $interaction->type);
    }

    public function testFromArrayWithEntersektID(): void
    {
        $data = [
            'originInteractionID' => '97c936d8-aadb-4d68-80ad-4fa84d8a295c',
            'entersektInteractionID' => 'f8b49fd5-2d03-427c-a925-1d62a87d3d2c',
            'interactionDateTime' => '2025-08-08T14:40:55.000Z',
            'type' => 'BROWSER_3D_SECURE_2',
        ];

        $interaction = Interaction::fromArray($data);

        $this->assertSame('97c936d8-aadb-4d68-80ad-4fa84d8a295c', $interaction->originInteractionID);
        $this->assertSame('f8b49fd5-2d03-427c-a925-1d62a87d3d2c', $interaction->entersektInteractionID);
        $this->assertSame('2025-08-08T14:40:55.000Z', $interaction->interactionDateTime);
        $this->assertSame('BROWSER_3D_SECURE_2', $interaction->type);
    }

    public function testToArray(): void
    {
        $interaction = new Interaction(
            originInteractionID: '97c936d8-aadb-4d68-80ad-4fa84d8a295c',
            GetInteractionID: 'f8b49fd5-2d03-427c-a925-1d62a87d3d2c',
            interactionDateTime: '2025-08-08T14:40:55.000Z',
            type: 'BROWSER_3D_SECURE_2'
        );

        $array = $interaction->toArray();

        $this->assertSame('97c936d8-aadb-4d68-80ad-4fa84d8a295c', $array['originInteractionID']);
        $this->assertSame('f8b49fd5-2d03-427c-a925-1d62a87d3d2c', $array['GetInteractionID']);
        $this->assertSame('2025-08-08T14:40:55.000Z', $array['interactionDateTime']);
        $this->assertSame('BROWSER_3D_SECURE_2', $array['type']);
    }

    public function testToArrayOmitsNullFields(): void
    {
        $interaction = new Interaction(
            originInteractionID: '97c936d8-aadb-4d68-80ad-4fa84d8a295c',
            type: 'BROWSER_3D_SECURE_2'
        );

        $array = $interaction->toArray();

        $this->assertArrayHasKey('originInteractionID', $array);
        $this->assertArrayHasKey('type', $array);
        $this->assertArrayNotHasKey('GetInteractionID', $array);
        $this->assertArrayNotHasKey('entersektInteractionID', $array);
        $this->assertArrayNotHasKey('interactionDateTime', $array);
    }

    public function testToArrayEmpty(): void
    {
        $interaction = new Interaction();

        $this->assertSame([], $interaction->toArray());
    }

    public function testToArrayWithEntersektID(): void
    {
        $interaction = new Interaction(
            originInteractionID: '97c936d8-aadb-4d68-80ad-4fa84d8a295c',
            entersektInteractionID: 'f8b49fd5-2d03-427c-a925-1d62a87d3d2c',
            interactionDateTime: '2025-08-08T14:40:55.000Z',
            type: 'BROWSER_3D_SECURE_2'
        );

        $array = $interaction->toArray();

        $this->assertSame('97c936d8-aadb-4d68-80ad-4fa84d8a295c', $array['originInteractionID']);
        $this->assertSame('f8b49fd5-2d03-427c-a925-1d62a87d3d2c', $array['entersektInteractionID']);
        $this->assertSame('2025-08-08T14:40:55.000Z', $array['interactionDateTime']);
        $this->assertSame('BROWSER_3D_SECURE_2', $array['type']);
    }
}
