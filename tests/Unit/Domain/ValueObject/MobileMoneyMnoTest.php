<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\ValueObject;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\ValueObject\MobileMoneyMno;

final class MobileMoneyMnoTest extends TestCase
{
    public function testCreateMobileMoneyMno(): void
    {
        $mno = new MobileMoneyMno('MPESA');

        $this->assertSame('MPESA', $mno->name);
    }

    public function testFromArray(): void
    {
        $mno = MobileMoneyMno::fromArray(['name' => 'AIRTEL MONEY']);

        $this->assertSame('AIRTEL MONEY', $mno->name);
    }

    public function testToArray(): void
    {
        $mno = new MobileMoneyMno('T-KASH');

        $array = $mno->toArray();

        $this->assertSame('T-KASH', $array['name']);
    }
}
