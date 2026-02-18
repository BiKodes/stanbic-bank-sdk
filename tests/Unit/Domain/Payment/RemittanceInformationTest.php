<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\Payment;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\Payment\RemittanceInformation;

final class RemittanceInformationTest extends TestCase
{
    public function testCreateRemittanceInformation(): void
    {
        $remittance = new RemittanceInformation(
            type: 'UNSTRUCTURED',
            content: 'Invoice 123',
            unstructured: 'Payment for services',
            reference: 'INV-123'
        );

        $this->assertSame('UNSTRUCTURED', $remittance->type);
        $this->assertSame('Invoice 123', $remittance->content);
        $this->assertSame('Payment for services', $remittance->unstructured);
        $this->assertSame('INV-123', $remittance->reference);
    }

    public function testFromArray(): void
    {
        $data = [
            'type' => 'UNSTRUCTURED',
            'content' => 'Payment for services',
            'unstructured' => 'Unstructured details',
            'reference' => 'REF-001',
        ];

        $remittance = RemittanceInformation::fromArray($data);

        $this->assertSame('UNSTRUCTURED', $remittance->type);
        $this->assertSame('Payment for services', $remittance->content);
        $this->assertSame('Unstructured details', $remittance->unstructured);
        $this->assertSame('REF-001', $remittance->reference);
    }

    public function testToArrayOmitsNullFields(): void
    {
        $remittance = new RemittanceInformation(type: 'SCOR');

        $array = $remittance->toArray();

        $this->assertSame('SCOR', $array['type']);
        $this->assertArrayNotHasKey('content', $array);
        $this->assertArrayNotHasKey('unstructured', $array);
        $this->assertArrayNotHasKey('reference', $array);
    }

    public function testToArrayOmitsNulls(): void
    {
        $remittance = new RemittanceInformation();

        $array = $remittance->toArray();

        $this->assertSame([], $array);
    }

    public function testFromArrayWithEmptyArray(): void
    {
        $remittance = RemittanceInformation::fromArray([]);

        $this->assertNull($remittance->type);
        $this->assertNull($remittance->content);
        $this->assertNull($remittance->unstructured);
        $this->assertNull($remittance->reference);
    }

    public function testConstructorWithDefaultParameters(): void
    {
        $remittance = new RemittanceInformation();

        $this->assertNull($remittance->type);
        $this->assertNull($remittance->content);
        $this->assertNull($remittance->unstructured);
        $this->assertNull($remittance->reference);
    }

    public function testToArrayWithOnlyContent(): void
    {
        $remittance = new RemittanceInformation(content: 'Payment content');

        $array = $remittance->toArray();

        $this->assertSame(['content' => 'Payment content'], $array);
    }

    public function testToArrayWithOnlyUnstructured(): void
    {
        $remittance = new RemittanceInformation(unstructured: 'Unstructured data');

        $array = $remittance->toArray();

        $this->assertSame(['unstructured' => 'Unstructured data'], $array);
    }

    public function testToArrayWithOnlyReference(): void
    {
        $remittance = new RemittanceInformation(reference: 'REF-123');

        $array = $remittance->toArray();

        $this->assertSame(['reference' => 'REF-123'], $array);
    }

    public function testFromArrayWithPartialData(): void
    {
        $data = [
            'type' => 'STRUCTURED',
            'reference' => 'REF-456',
        ];

        $remittance = RemittanceInformation::fromArray($data);

        $this->assertSame('STRUCTURED', $remittance->type);
        $this->assertNull($remittance->content);
        $this->assertNull($remittance->unstructured);
        $this->assertSame('REF-456', $remittance->reference);
    }

    public function testFromArrayCastsValuesToString(): void
    {
        $data = [
            'type' => 12345,
            'content' => 67890,
            'unstructured' => 11111,
            'reference' => 22222,
        ];

        $remittance = RemittanceInformation::fromArray($data);

        $this->assertSame('12345', $remittance->type);
        $this->assertSame('67890', $remittance->content);
        $this->assertSame('11111', $remittance->unstructured);
        $this->assertSame('22222', $remittance->reference);
    }

    public function testRoundTripSerialization(): void
    {
        $original = [
            'type' => 'UNSTRUCTURED',
            'content' => 'Test content',
            'unstructured' => 'Test unstructured',
            'reference' => 'TEST-REF',
        ];

        $remittance = RemittanceInformation::fromArray($original);
        $result = $remittance->toArray();

        $this->assertSame($original, $result);
    }

    public function testToArrayWithAllFields(): void
    {
        $remittance = new RemittanceInformation(
            type: 'STRUCTURED',
            content: 'Full content',
            unstructured: 'Full unstructured',
            reference: 'FULL-REF'
        );

        $array = $remittance->toArray();

        $this->assertSame('STRUCTURED', $array['type']);
        $this->assertSame('Full content', $array['content']);
        $this->assertSame('Full unstructured', $array['unstructured']);
        $this->assertSame('FULL-REF', $array['reference']);
        $this->assertCount(4, $array);
    }
}
