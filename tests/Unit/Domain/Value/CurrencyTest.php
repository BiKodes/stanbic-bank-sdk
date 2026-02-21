<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\Value;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\Value\Currency;

final class CurrencyTest extends TestCase
{
    public function testCanBeCreatedWithCode(): void
    {
        $currency = new Currency(code: 'KES');

        $this->assertSame('KES', $currency->code);
    }

    public function testFromCodeNormalizesToUppercase(): void
    {
        $currency = Currency::fromCode('kes');

        $this->assertSame('KES', $currency->code);
    }

    public function testGetCode(): void
    {
        $currency = new Currency(code: 'USD');

        $this->assertSame('USD', $currency->getCode());
    }

    public function testEquals(): void
    {
        $currency1 = new Currency(code: 'KES');
        $currency2 = new Currency(code: 'KES');
        $currency3 = new Currency(code: 'USD');

        $this->assertTrue($currency1->equals($currency2));
        $this->assertFalse($currency1->equals($currency3));
    }

    public function testToString(): void
    {
        $currency = new Currency(code: 'GBP');

        $this->assertSame('GBP', (string) $currency);
    }
}
