<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\ValueObject;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\ValueObject\OriginatorIdentification;

/**
 * @covers \Stanbic\SDK\Domain\ValueObject\OriginatorIdentification
 */
final class OriginatorIdentificationTest extends TestCase
{
    public function testConstructorWithAllParameters(): void
    {
        $identification = new OriginatorIdentification(
            identification: 'ID123456',
            debitCurrency: 'USD',
            mobileNumber: '+254712345678'
        );

        $this->assertSame('ID123456', $identification->identification);
        $this->assertSame('USD', $identification->debitCurrency);
        $this->assertSame('+254712345678', $identification->mobileNumber);
    }

    public function testConstructorWithNullParameters(): void
    {
        $identification = new OriginatorIdentification();

        $this->assertNull($identification->identification);
        $this->assertNull($identification->debitCurrency);
        $this->assertNull($identification->mobileNumber);
    }

    public function testConstructorWithPartialParameters(): void
    {
        $identification = new OriginatorIdentification(
            identification: 'ID123456',
            debitCurrency: null,
            mobileNumber: '+254712345678'
        );

        $this->assertSame('ID123456', $identification->identification);
        $this->assertNull($identification->debitCurrency);
        $this->assertSame('+254712345678', $identification->mobileNumber);
    }

    public function testFromArrayWithCamelCaseKeys(): void
    {
        $data = [
            'identification' => 'ID123456',
            'debitCurrency' => 'USD',
            'mobileNumber' => '+254712345678',
        ];

        $identification = OriginatorIdentification::fromArray($data);

        $this->assertSame('ID123456', $identification->identification);
        $this->assertSame('USD', $identification->debitCurrency);
        $this->assertSame('+254712345678', $identification->mobileNumber);
    }

    public function testFromArrayWithSnakeCaseKeys(): void
    {
        $data = [
            'identification' => 'ID123456',
            'debit_currency' => 'USD',
            'mobile_number' => '+254712345678',
        ];

        $identification = OriginatorIdentification::fromArray($data);

        $this->assertSame('ID123456', $identification->identification);
        $this->assertSame('USD', $identification->debitCurrency);
        $this->assertSame('+254712345678', $identification->mobileNumber);
    }

    public function testFromArrayWithMixedKeys(): void
    {
        $data = [
            'identification' => 'ID123456',
            'debitCurrency' => 'USD',
            'mobile_number' => '+254712345678',
        ];

        $identification = OriginatorIdentification::fromArray($data);

        $this->assertSame('ID123456', $identification->identification);
        $this->assertSame('USD', $identification->debitCurrency);
        $this->assertSame('+254712345678', $identification->mobileNumber);
    }

    public function testFromArrayCamelCaseTakesPrecedenceOverSnakeCase(): void
    {
        $data = [
            'debitCurrency' => 'USD',
            'debit_currency' => 'KES',
            'mobileNumber' => '+254712345678',
            'mobile_number' => '+254700000000',
        ];

        $identification = OriginatorIdentification::fromArray($data);

        $this->assertSame('USD', $identification->debitCurrency);
        $this->assertSame('+254712345678', $identification->mobileNumber);
    }

    public function testFromArrayWithEmptyArray(): void
    {
        $identification = OriginatorIdentification::fromArray([]);

        $this->assertNull($identification->identification);
        $this->assertNull($identification->debitCurrency);
        $this->assertNull($identification->mobileNumber);
    }

    public function testFromArrayWithMissingFields(): void
    {
        $data = [
            'identification' => 'ID123456',
        ];

        $identification = OriginatorIdentification::fromArray($data);

        $this->assertSame('ID123456', $identification->identification);
        $this->assertNull($identification->debitCurrency);
        $this->assertNull($identification->mobileNumber);
    }

    public function testFromArrayCastsValuesToString(): void
    {
        $data = [
            'identification' => 12345,
            'debitCurrency' => 840,
            'mobileNumber' => 254712345678,
        ];

        $identification = OriginatorIdentification::fromArray($data);

        $this->assertSame('12345', $identification->identification);
        $this->assertSame('840', $identification->debitCurrency);
        $this->assertSame('254712345678', $identification->mobileNumber);
    }

    public function testToArrayWithAllFields(): void
    {
        $identification = new OriginatorIdentification(
            identification: 'ID123456',
            debitCurrency: 'USD',
            mobileNumber: '+254712345678'
        );

        $expected = [
            'identification' => 'ID123456',
            'debitCurrency' => 'USD',
            'mobileNumber' => '+254712345678',
        ];

        $this->assertSame($expected, $identification->toArray());
    }

    public function testToArrayWithNullFields(): void
    {
        $identification = new OriginatorIdentification();

        $this->assertSame([], $identification->toArray());
    }

    public function testToArrayWithPartialFields(): void
    {
        $identification = new OriginatorIdentification(
            identification: 'ID123456',
            debitCurrency: null,
            mobileNumber: '+254712345678'
        );

        $expected = [
            'identification' => 'ID123456',
            'mobileNumber' => '+254712345678',
        ];

        $this->assertSame($expected, $identification->toArray());
    }

    public function testToArrayOnlyIncludesIdentification(): void
    {
        $identification = new OriginatorIdentification(
            identification: 'ID123456'
        );

        $expected = [
            'identification' => 'ID123456',
        ];

        $this->assertSame($expected, $identification->toArray());
    }

    public function testToArrayOnlyIncludesDebitCurrency(): void
    {
        $identification = new OriginatorIdentification(
            debitCurrency: 'USD'
        );

        $expected = [
            'debitCurrency' => 'USD',
        ];

        $this->assertSame($expected, $identification->toArray());
    }

    public function testToArrayOnlyIncludesMobileNumber(): void
    {
        $identification = new OriginatorIdentification(
            mobileNumber: '+254712345678'
        );

        $expected = [
            'mobileNumber' => '+254712345678',
        ];

        $this->assertSame($expected, $identification->toArray());
    }

    public function testRoundTripSerializationWithCamelCase(): void
    {
        $original = [
            'identification' => 'ID123456',
            'debitCurrency' => 'USD',
            'mobileNumber' => '+254712345678',
        ];

        $identification = OriginatorIdentification::fromArray($original);
        $result = $identification->toArray();

        $this->assertSame($original, $result);
    }

    public function testRoundTripSerializationWithSnakeCase(): void
    {
        $original = [
            'identification' => 'ID123456',
            'debit_currency' => 'USD',
            'mobile_number' => '+254712345678',
        ];

        $identification = OriginatorIdentification::fromArray($original);
        $result = $identification->toArray();

        // toArray always returns camelCase
        $expected = [
            'identification' => 'ID123456',
            'debitCurrency' => 'USD',
            'mobileNumber' => '+254712345678',
        ];

        $this->assertSame($expected, $result);
    }

    public function testRoundTripSerializationWithEmptyData(): void
    {
        $identification = OriginatorIdentification::fromArray([]);
        $result = $identification->toArray();

        $this->assertSame([], $result);
    }
}
