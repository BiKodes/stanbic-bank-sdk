<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\ValueObject;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\ValueObject\PostalAddress;

final class PostalAddressTest extends TestCase
{
    public function testCreatePostalAddress(): void
    {
        $address = new PostalAddress(
            addressLine1: 'Some street',
            addressLine2: '99',
            postCode: '1100 ZZ',
            town: 'Amsterdam',
            country: 'NL'
        );

        $this->assertSame('Some street', $address->addressLine1);
        $this->assertSame('99', $address->addressLine2);
        $this->assertSame('1100 ZZ', $address->postCode);
        $this->assertSame('Amsterdam', $address->town);
        $this->assertSame('NL', $address->country);
    }

    public function testFromArray(): void
    {
        $data = [
            'addressLine' => 'UGANDA',
            'postCode' => '1100 ZZ',
            'town' => 'Kampala',
            'country' => 'UG',
        ];

        $address = PostalAddress::fromArray($data);

        $this->assertSame('UGANDA', $address->addressLine);
        $this->assertSame('1100 ZZ', $address->postCode);
        $this->assertSame('Kampala', $address->town);
        $this->assertSame('UG', $address->country);
    }

    public function testToArray(): void
    {
        $address = new PostalAddress(addressLine: 'Kenya', country: 'KE');

        $array = $address->toArray();

        $this->assertSame('Kenya', $array['addressLine']);
        $this->assertSame('KE', $array['country']);
    }

    public function testFromArrayWithSnakeCase(): void
    {
        $data = [
            'address_line1' => 'Line 1',
            'address_line2' => 'Line 2',
            'post_code' => '00100',
            'town' => 'Nairobi',
            'country' => 'KE',
        ];

        $address = PostalAddress::fromArray($data);

        $this->assertSame('Line 1', $address->addressLine1);
        $this->assertSame('Line 2', $address->addressLine2);
        $this->assertSame('00100', $address->postCode);
        $this->assertSame('Nairobi', $address->town);
        $this->assertSame('KE', $address->country);
    }

    public function testToArrayEmptyWhenAllNull(): void
    {
        $address = new PostalAddress();

        $this->assertSame([], $address->toArray());
    }

    public function testToArrayIncludesAddressLine2PostCodeAndTown(): void
    {
        $address = new PostalAddress(
            addressLine1: 'Main Street',
            addressLine2: 'Suite 100',
            postCode: '00200',
            town: 'Mombasa',
            country: 'KE'
        );

        $array = $address->toArray();

        $this->assertArrayHasKey('addressLine2', $array);
        $this->assertSame('Suite 100', $array['addressLine2']);
        $this->assertArrayHasKey('postCode', $array);
        $this->assertSame('00200', $array['postCode']);
        $this->assertArrayHasKey('town', $array);
        $this->assertSame('Mombasa', $array['town']);
    }
}
