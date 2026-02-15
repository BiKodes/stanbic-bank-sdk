<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\Account;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\Account\Transaction;

final class TransactionTest extends TestCase
{
    public function testCreateTransaction(): void
    {
        $date = new DateTimeImmutable('2026-02-15 10:30:00');
        $transaction = new Transaction(
            transactionId: 'TXN001',
            date: $date,
            amount: 1000.00,
            currency: 'KES',
            type: 'CREDIT',
            counterparty: 'John Doe',
            description: 'Salary Payment',
            reference: 'REF001',
            balance: 50000.00
        );

        $this->assertSame('TXN001', $transaction->transactionId);
        $this->assertSame($date, $transaction->date);
        $this->assertSame(1000.00, $transaction->amount);
        $this->assertSame('KES', $transaction->currency);
        $this->assertSame('CREDIT', $transaction->type);
        $this->assertSame('John Doe', $transaction->counterparty);
        $this->assertSame('Salary Payment', $transaction->description);
        $this->assertSame('REF001', $transaction->reference);
        $this->assertSame(50000.00, $transaction->balance);
    }

    public function testCreateTransactionWithoutOptional(): void
    {
        $date = new DateTimeImmutable('2026-02-15');
        $transaction = new Transaction(
            transactionId: 'TXN002',
            date: $date,
            amount: 500.00,
            currency: 'USD',
            type: 'DEBIT'
        );

        $this->assertSame('TXN002', $transaction->transactionId);
        $this->assertNull($transaction->counterparty);
        $this->assertNull($transaction->description);
        $this->assertNull($transaction->reference);
        $this->assertNull($transaction->balance);
    }

    public function testIsCredit(): void
    {
        $date = new DateTimeImmutable();
        $transaction = new Transaction(
            transactionId: 'TXN003',
            date: $date,
            amount: 100.00,
            currency: 'KES',
            type: 'CREDIT'
        );

        $this->assertTrue($transaction->isCredit());
        $this->assertFalse($transaction->isDebit());
    }

    public function testIsDebit(): void
    {
        $date = new DateTimeImmutable();
        $transaction = new Transaction(
            transactionId: 'TXN004',
            date: $date,
            amount: 100.00,
            currency: 'KES',
            type: 'DEBIT'
        );

        $this->assertTrue($transaction->isDebit());
        $this->assertFalse($transaction->isCredit());
    }

    public function testFromArrayWithCamelCase(): void
    {
        $data = [
            'transactionId' => 'TXN005',
            'date' => '2026-02-15 14:30:00',
            'amount' => 2500.00,
            'currency' => 'EUR',
            'type' => 'CREDIT',
            'counterparty' => 'Jane Smith',
            'description' => 'Refund',
            'reference' => 'REF002',
            'balance' => 75000.50
        ];

        $transaction = Transaction::fromArray($data);

        $this->assertSame('TXN005', $transaction->transactionId);
        $this->assertSame(2500.00, $transaction->amount);
        $this->assertSame('EUR', $transaction->currency);
        $this->assertSame('CREDIT', $transaction->type);
        $this->assertSame('Jane Smith', $transaction->counterparty);
        $this->assertSame('Refund', $transaction->description);
        $this->assertSame('REF002', $transaction->reference);
        $this->assertSame(75000.50, $transaction->balance);
    }

    public function testFromArrayWithSnakeCase(): void
    {
        $data = [
            'transaction_id' => 'TXN006',
            'date' => '2026-02-14',
            'amount' => 3000.00,
            'currency' => 'GBP',
            'transaction_type' => 'DEBIT',
            'counter_party' => 'Supplier Ltd',
            'narrative' => 'Invoice Payment',
            'reference_number' => 'INV123',
            'balance' => 40000.00
        ];

        $transaction = Transaction::fromArray($data);

        $this->assertSame('TXN006', $transaction->transactionId);
        $this->assertSame(3000.00, $transaction->amount);
        $this->assertSame('GBP', $transaction->currency);
        $this->assertSame('DEBIT', $transaction->type);
        $this->assertSame('Supplier Ltd', $transaction->counterparty);
        $this->assertSame('Invoice Payment', $transaction->description);
    }

    public function testFromArrayWithDateTimeImmutable(): void
    {
        $date = new DateTimeImmutable('2026-02-13 09:00:00');
        $data = [
            'transactionId' => 'TXN007',
            'date' => $date,
            'amount' => 500.00,
            'currency' => 'KES',
            'type' => 'CREDIT'
        ];

        $transaction = Transaction::fromArray($data);

        $this->assertSame($date, $transaction->date);
        $this->assertSame(500.00, $transaction->amount);
    }

    public function testFromArrayWithDefaults(): void
    {
        $data = [
            'transactionId' => 'TXN008'
        ];

        $transaction = Transaction::fromArray($data);

        $this->assertSame('TXN008', $transaction->transactionId);
        $this->assertSame(0.0, $transaction->amount);
        $this->assertSame('KES', $transaction->currency);
        $this->assertSame('DEBIT', $transaction->type);
        $this->assertNull($transaction->counterparty);
    }

    public function testToArray(): void
    {
        $date = new DateTimeImmutable('2026-02-12 11:15:00');
        $transaction = new Transaction(
            transactionId: 'TXN009',
            date: $date,
            amount: 1500.75,
            currency: 'ZAR',
            type: 'DEBIT',
            counterparty: 'Mobile Provider',
            description: 'Top-up',
            reference: 'MB001',
            balance: 25000.00
        );

        $array = $transaction->toArray();

        $this->assertArrayHasKey('transactionId', $array);
        $this->assertArrayHasKey('date', $array);
        $this->assertArrayHasKey('amount', $array);
        $this->assertArrayHasKey('currency', $array);
        $this->assertArrayHasKey('type', $array);
        $this->assertSame('TXN009', $array['transactionId']);
        $this->assertSame(1500.75, $array['amount']);
        $this->assertStringContainsString('2026-02-12', (string) $array['date']);
    }

    public function testRoundTripSerialization(): void
    {
        $date = new DateTimeImmutable('2026-02-11');
        $original = new Transaction(
            transactionId: 'TXN010',
            date: $date,
            amount: 5000.00,
            currency: 'USD',
            type: 'CREDIT',
            counterparty: 'International Remittance',
            description: 'Inbound Transfer',
            reference: 'INT001',
            balance: 60000.00
        );

        $restored = Transaction::fromArray($original->toArray());

        $this->assertSame($original->transactionId, $restored->transactionId);
        $this->assertSame($original->amount, $restored->amount);
        $this->assertSame($original->currency, $restored->currency);
        $this->assertSame($original->type, $restored->type);
        $this->assertSame($original->counterparty, $restored->counterparty);
    }
}
