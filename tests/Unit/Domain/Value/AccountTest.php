<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\Value;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\Value\Account;

final class AccountTest extends TestCase
{
    public function testCanBeCreatedWithNumberAndBankCode(): void
    {
        $account = new Account(
            number: '123456789',
            bankCode: '01',
        );

        $this->assertSame('123456789', $account->number);
        $this->assertSame('01', $account->bankCode);
    }

    public function testCreateFactoryMethod(): void
    {
        $account = Account::create('  123456789  ', '  01  ');

        $this->assertSame('123456789', $account->getNumber());
        $this->assertSame('01', $account->getBankCode());
    }

    public function testGetNumber(): void
    {
        $account = new Account(number: '987654321', bankCode: '02');

        $this->assertSame('987654321', $account->getNumber());
    }

    public function testGetBankCode(): void
    {
        $account = new Account(number: '123456789', bankCode: '03');

        $this->assertSame('03', $account->getBankCode());
    }

    public function testEquals(): void
    {
        $account1 = new Account(number: '123456789', bankCode: '01');
        $account2 = new Account(number: '123456789', bankCode: '01');
        $account3 = new Account(number: '987654321', bankCode: '01');
        $account4 = new Account(number: '123456789', bankCode: '02');

        $this->assertTrue($account1->equals($account2));
        $this->assertFalse($account1->equals($account3));
        $this->assertFalse($account1->equals($account4));
    }

    public function testToString(): void
    {
        $account = new Account(number: '123456789', bankCode: '01');

        $this->assertSame('01:123456789', (string) $account);
    }
}
