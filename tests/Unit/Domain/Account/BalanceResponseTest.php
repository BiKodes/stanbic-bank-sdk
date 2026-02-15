<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\Account;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\Account\BalanceResponse;

final class BalanceResponseTest extends TestCase
{
    public function testCreateBalanceResponse(): void
    {
        $balance = new BalanceResponse(
            accountNumber: '1234567890',
            currency: 'KES',
            availableBalance: 50000.00,
            currentBalance: 75000.00,
            accountName: 'My Current Account',
            accountType: 'CURRENT'
        );

        $this->assertSame('1234567890', $balance->accountNumber);
        $this->assertSame('KES', $balance->currency);
        $this->assertSame(50000.00, $balance->availableBalance);
        $this->assertSame(75000.00, $balance->currentBalance);
        $this->assertSame('My Current Account', $balance->accountName);
        $this->assertSame('CURRENT', $balance->accountType);
    }

    public function testCreateBalanceResponseWithoutOptional(): void
    {
        $balance = new BalanceResponse(
            accountNumber: '1234567890',
            currency: 'KES',
            availableBalance: 50000.00,
            currentBalance: 75000.00
        );

        $this->assertSame('1234567890', $balance->accountNumber);
        $this->assertNull($balance->accountName);
        $this->assertNull($balance->accountType);
    }

    public function testFromArrayWithCamelCase(): void
    {
        $data = [
            'accountNumber' => '1234567890',
            'currency' => 'USD',
            'availableBalance' => 25000.50,
            'currentBalance' => 30000.75,
            'accountName' => 'Test Account',
            'accountType' => 'SAVINGS'
        ];

        $balance = BalanceResponse::fromArray($data);

        $this->assertSame('1234567890', $balance->accountNumber);
        $this->assertSame('USD', $balance->currency);
        $this->assertSame(25000.50, $balance->availableBalance);
        $this->assertSame(30000.75, $balance->currentBalance);
        $this->assertSame('Test Account', $balance->accountName);
        $this->assertSame('SAVINGS', $balance->accountType);
    }

    public function testFromArrayWithSnakeCase(): void
    {
        $data = [
            'account_number' => '9876543210',
            'currency' => 'GBP',
            'available_balance' => 15000.00,
            'current_balance' => 20000.00,
            'account_name' => 'Snake Case Account',
            'account_type' => 'OVERDRAFT'
        ];

        $balance = BalanceResponse::fromArray($data);

        $this->assertSame('9876543210', $balance->accountNumber);
        $this->assertSame('GBP', $balance->currency);
        $this->assertSame(15000.00, $balance->availableBalance);
        $this->assertSame(20000.00, $balance->currentBalance);
        $this->assertSame('Snake Case Account', $balance->accountName);
        $this->assertSame('OVERDRAFT', $balance->accountType);
    }

    public function testFromArrayWithDefaults(): void
    {
        $data = [
            'accountNumber' => '1111111111'
        ];

        $balance = BalanceResponse::fromArray($data);

        $this->assertSame('1111111111', $balance->accountNumber);
        $this->assertSame('KES', $balance->currency);
        $this->assertSame(0.0, $balance->availableBalance);
        $this->assertSame(0.0, $balance->currentBalance);
        $this->assertNull($balance->accountName);
        $this->assertNull($balance->accountType);
    }

    public function testToArray(): void
    {
        $balance = new BalanceResponse(
            accountNumber: '5555555555',
            currency: 'EUR',
            availableBalance: 10000.00,
            currentBalance: 12000.00,
            accountName: 'European Account',
            accountType: 'BUSINESS'
        );

        $array = $balance->toArray();

        $this->assertArrayHasKey('accountNumber', $array);
        $this->assertArrayHasKey('currency', $array);
        $this->assertArrayHasKey('availableBalance', $array);
        $this->assertArrayHasKey('currentBalance', $array);
        $this->assertArrayHasKey('accountName', $array);
        $this->assertArrayHasKey('accountType', $array);
        $this->assertSame('5555555555', $array['accountNumber']);
        $this->assertSame('EUR', $array['currency']);
    }

    public function testRoundTripSerialization(): void
    {
        $original = new BalanceResponse(
            accountNumber: '7777777777',
            currency: 'ZAR',
            availableBalance: 100000.00,
            currentBalance: 120000.00,
            accountName: 'Rand Account',
            accountType: 'INVESTMENT'
        );

        $restored = BalanceResponse::fromArray($original->toArray());

        $this->assertSame($original->accountNumber, $restored->accountNumber);
        $this->assertSame($original->currency, $restored->currency);
        $this->assertSame($original->availableBalance, $restored->availableBalance);
        $this->assertSame($original->currentBalance, $restored->currentBalance);
        $this->assertSame($original->accountName, $restored->accountName);
        $this->assertSame($original->accountType, $restored->accountType);
    }
}
