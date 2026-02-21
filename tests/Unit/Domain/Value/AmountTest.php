<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\Value;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\Value\Amount;
use Stanbic\SDK\Domain\Value\Currency;

final class AmountTest extends TestCase
{
    public function testCanBeCreatedWithValueAndCurrency(): void
    {
        $currency = new Currency(code: 'KES');
        $amount = new Amount(value: 1000.50, currency: $currency);

        $this->assertSame(1000.50, $amount->value);
        $this->assertTrue($amount->currency->equals($currency));
    }

    public function testOfFactoryMethod(): void
    {
        $amount = Amount::of(5000, 'USD');

        $this->assertSame('5000', $amount->getValue());
        $this->assertSame('USD', $amount->getCurrencyCode());
    }

    public function testGetValue(): void
    {
        $amount = new Amount(value: 1500.75, currency: new Currency(code: 'KES'));

        $this->assertSame('1500.75', $amount->getValue());
    }

    public function testToFloat(): void
    {
        $amount = new Amount(value: 1000, currency: new Currency(code: 'KES'));

        $this->assertSame(1000.0, $amount->toFloat());
    }

    public function testToInt(): void
    {
        $amount = new Amount(value: 1500.99, currency: new Currency(code: 'KES'));

        $this->assertSame(1500, $amount->toInt());
    }

    public function testGetCurrencyCode(): void
    {
        $amount = Amount::of(1000, 'GBP');

        $this->assertSame('GBP', $amount->getCurrencyCode());
    }

    public function testEquals(): void
    {
        $amount1 = Amount::of(1000, 'KES');
        $amount2 = Amount::of(1000, 'KES');
        $amount3 = Amount::of(2000, 'KES');
        $amount4 = Amount::of(1000, 'USD');

        $this->assertTrue($amount1->equals($amount2));
        $this->assertFalse($amount1->equals($amount3));
        $this->assertFalse($amount1->equals($amount4));
    }

    public function testIsGreaterThan(): void
    {
        $amount1 = Amount::of(2000, 'KES');
        $amount2 = Amount::of(1000, 'KES');

        $this->assertTrue($amount1->isGreaterThan($amount2));
        $this->assertFalse($amount2->isGreaterThan($amount1));
    }

    public function testIsGreaterThanWithDifferentCurrencies(): void
    {
        $amount1 = Amount::of(1000, 'KES');
        $amount2 = Amount::of(1000, 'USD');

        $this->expectException(\InvalidArgumentException::class);
        $amount1->isGreaterThan($amount2);
    }

    public function testIsLessThan(): void
    {
        $amount1 = Amount::of(1000, 'KES');
        $amount2 = Amount::of(2000, 'KES');

        $this->assertTrue($amount1->isLessThan($amount2));
        $this->assertFalse($amount2->isLessThan($amount1));
    }

    public function testIsLessThanWithDifferentCurrencies(): void
    {
        $amount1 = Amount::of(1000, 'KES');
        $amount2 = Amount::of(1000, 'USD');

        $this->expectException(\InvalidArgumentException::class);
        $amount1->isLessThan($amount2);
    }

    public function testToString(): void
    {
        $amount = Amount::of(1500.50, 'KES');

        $this->assertSame('1500.5 KES', (string) $amount);
    }
}
