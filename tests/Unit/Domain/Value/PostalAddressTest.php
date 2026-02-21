<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\Value;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\Value\PostalAddress;

final class PostalAddressTest extends TestCase
{
    public function testCanBeCreatedWithRequiredFields(): void
    {
        $address = new PostalAddress(
            street: 'Stanbic Bank Centre',
            city: 'Nairobi',
            country: 'Kenya',
        );

        $this->assertSame('Stanbic Bank Centre', $address->street);
        $this->assertSame('Nairobi', $address->city);
        $this->assertSame('Kenya', $address->country);
        $this->assertNull($address->postalCode);
        $this->assertNull($address->state);
        $this->assertNull($address->poBox);
    }

    public function testCanBeCreatedWithAllFields(): void
    {
        $address = new PostalAddress(
            street: '4th Floor, Upper Hill Office Park',
            city: 'Nairobi',
            country: 'Kenya',
            postalCode: '00100',
            state: 'Nairobi',
            poBox: 'PO Box 12345',
        );

        $this->assertSame('4th Floor, Upper Hill Office Park', $address->street);
        $this->assertSame('Nairobi', $address->city);
        $this->assertSame('Kenya', $address->country);
        $this->assertSame('00100', $address->postalCode);
        $this->assertSame('Nairobi', $address->state);
        $this->assertSame('PO Box 12345', $address->poBox);
    }

    public function testCreateFactoryMethod(): void
    {
        $address = PostalAddress::create(
            '  123 Main Street  ',
            '  New York  ',
            '  USA  ',
            '10001',
            'NY',
        );

        $this->assertSame('123 Main Street', $address->street);
        $this->assertSame('New York', $address->city);
        $this->assertSame('USA', $address->country);
        $this->assertSame('10001', $address->postalCode);
        $this->assertSame('NY', $address->state);
    }

    public function testFormatWithMinimalFields(): void
    {
        $address = new PostalAddress(
            street: 'Main Street',
            city: 'Kampala',
            country: 'Uganda',
        );

        $this->assertSame('Main Street, Kampala, Uganda', $address->format());
    }

    public function testFormatWithAllFields(): void
    {
        $address = new PostalAddress(
            street: '123 Business Avenue',
            city: 'Lagos',
            country: 'Nigeria',
            postalCode: '100001',
            state: 'Lagos State',
            poBox: 'PO Box 5000',
        );

        $this->assertSame('123 Business Avenue, Lagos, Lagos State, 100001, Nigeria', $address->format());
    }

    public function testEquals(): void
    {
        $address1 = new PostalAddress(
            street: 'Main Street',
            city: 'Nairobi',
            country: 'Kenya',
        );

        $address2 = new PostalAddress(
            street: 'Main Street',
            city: 'Nairobi',
            country: 'Kenya',
        );

        $address3 = new PostalAddress(
            street: 'Other Street',
            city: 'Nairobi',
            country: 'Kenya',
        );

        $this->assertTrue($address1->equals($address2));
        $this->assertFalse($address1->equals($address3));
    }

    public function testToString(): void
    {
        $address = new PostalAddress(
            street: 'Bank Street',
            city: 'London',
            country: 'UK',
            postalCode: 'EC2R 8AH',
        );

        $this->assertSame('Bank Street, London, EC2R 8AH, UK', (string) $address);
    }
}
