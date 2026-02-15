<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\Account;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\Account\StatementResponse;
use Stanbic\SDK\Domain\Account\Transaction;

final class StatementResponseTest extends TestCase
{
    public function testCreateStatementResponse(): void
    {
        $transactions = [
            new Transaction(
                transactionId: 'TXN001',
                date: new DateTimeImmutable('2026-02-15'),
                amount: 1000.00,
                currency: 'KES',
                type: 'CREDIT'
            ),
            new Transaction(
                transactionId: 'TXN002',
                date: new DateTimeImmutable('2026-02-14'),
                amount: 500.00,
                currency: 'KES',
                type: 'DEBIT'
            ),
        ];

        $response = new StatementResponse(
            totalElements: 15,
            transactions: $transactions,
            page: 0,
            pageSize: 2,
            totalPages: 8,
            accountNumber: '1234567890'
        );

        $this->assertSame(15, $response->totalElements);
        $this->assertCount(2, $response->transactions);
        $this->assertSame(0, $response->page);
        $this->assertSame(2, $response->pageSize);
        $this->assertSame(8, $response->totalPages);
        $this->assertSame('1234567890', $response->accountNumber);
    }

    public function testCount(): void
    {
        $transactions = [
            new Transaction(
                transactionId: 'TXN001',
                date: new DateTimeImmutable(),
                amount: 1000.00,
                currency: 'KES',
                type: 'CREDIT'
            ),
            new Transaction(
                transactionId: 'TXN002',
                date: new DateTimeImmutable(),
                amount: 500.00,
                currency: 'KES',
                type: 'DEBIT'
            ),
        ];

        $response = new StatementResponse(
            totalElements: 2,
            transactions: $transactions
        );

        $this->assertSame(2, $response->count());
    }

    public function testHasNext(): void
    {
        $transactions = [];

        $response = new StatementResponse(
            totalElements: 100,
            transactions: $transactions,
            page: 0,
            pageSize: 10,
            totalPages: 10
        );

        $this->assertTrue($response->hasNext());

        $responseLast = new StatementResponse(
            totalElements: 100,
            transactions: $transactions,
            page: 9,
            pageSize: 10,
            totalPages: 10
        );

        $this->assertFalse($responseLast->hasNext());
    }

    public function testHasPrevious(): void
    {
        $transactions = [];

        $responseFirstPage = new StatementResponse(
            totalElements: 100,
            transactions: $transactions,
            page: 0,
            pageSize: 10,
            totalPages: 10
        );

        $this->assertFalse($responseFirstPage->hasPrevious());

        $responseMiddle = new StatementResponse(
            totalElements: 100,
            transactions: $transactions,
            page: 5,
            pageSize: 10,
            totalPages: 10
        );

        $this->assertTrue($responseMiddle->hasPrevious());
    }

    public function testIsEmpty(): void
    {
        $emptyResponse = new StatementResponse(
            totalElements: 0,
            transactions: []
        );

        $this->assertTrue($emptyResponse->isEmpty());

        $nonEmptyResponse = new StatementResponse(
            totalElements: 1,
            transactions: [
                new Transaction(
                    transactionId: 'TXN001',
                    date: new DateTimeImmutable(),
                    amount: 1000.00,
                    currency: 'KES',
                    type: 'CREDIT'
                ),
            ]
        );

        $this->assertFalse($nonEmptyResponse->isEmpty());
    }

    public function testGetCredits(): void
    {
        $transactions = [
            new Transaction(
                transactionId: 'TXN001',
                date: new DateTimeImmutable(),
                amount: 1000.00,
                currency: 'KES',
                type: 'CREDIT'
            ),
            new Transaction(
                transactionId: 'TXN002',
                date: new DateTimeImmutable(),
                amount: 500.00,
                currency: 'KES',
                type: 'DEBIT'
            ),
            new Transaction(
                transactionId: 'TXN003',
                date: new DateTimeImmutable(),
                amount: 2000.00,
                currency: 'KES',
                type: 'CREDIT'
            ),
        ];

        $response = new StatementResponse(
            totalElements: 3,
            transactions: $transactions
        );

        $credits = $response->getCredits();
        $this->assertCount(2, $credits);
        foreach ($credits as $credit) {
            $this->assertTrue($credit->isCredit());
        }
    }

    public function testGetDebits(): void
    {
        $transactions = [
            new Transaction(
                transactionId: 'TXN001',
                date: new DateTimeImmutable(),
                amount: 1000.00,
                currency: 'KES',
                type: 'CREDIT'
            ),
            new Transaction(
                transactionId: 'TXN002',
                date: new DateTimeImmutable(),
                amount: 500.00,
                currency: 'KES',
                type: 'DEBIT'
            ),
            new Transaction(
                transactionId: 'TXN003',
                date: new DateTimeImmutable(),
                amount: 250.00,
                currency: 'KES',
                type: 'DEBIT'
            ),
        ];

        $response = new StatementResponse(
            totalElements: 3,
            transactions: $transactions
        );

        $debits = $response->getDebits();
        $this->assertCount(2, $debits);
        foreach ($debits as $debit) {
            $this->assertTrue($debit->isDebit());
        }
    }

    public function testGetTotalCredits(): void
    {
        $transactions = [
            new Transaction(
                transactionId: 'TXN001',
                date: new DateTimeImmutable(),
                amount: 1000.00,
                currency: 'KES',
                type: 'CREDIT'
            ),
            new Transaction(
                transactionId: 'TXN002',
                date: new DateTimeImmutable(),
                amount: 500.00,
                currency: 'KES',
                type: 'DEBIT'
            ),
            new Transaction(
                transactionId: 'TXN003',
                date: new DateTimeImmutable(),
                amount: 2000.00,
                currency: 'KES',
                type: 'CREDIT'
            ),
        ];

        $response = new StatementResponse(
            totalElements: 3,
            transactions: $transactions
        );

        $this->assertSame(3000.00, $response->getTotalCredits());
    }

    public function testGetTotalDebits(): void
    {
        $transactions = [
            new Transaction(
                transactionId: 'TXN001',
                date: new DateTimeImmutable(),
                amount: 1000.00,
                currency: 'KES',
                type: 'CREDIT'
            ),
            new Transaction(
                transactionId: 'TXN002',
                date: new DateTimeImmutable(),
                amount: 500.00,
                currency: 'KES',
                type: 'DEBIT'
            ),
            new Transaction(
                transactionId: 'TXN003',
                date: new DateTimeImmutable(),
                amount: 250.00,
                currency: 'KES',
                type: 'DEBIT'
            ),
        ];

        $response = new StatementResponse(
            totalElements: 3,
            transactions: $transactions
        );

        $this->assertSame(750.00, $response->getTotalDebits());
    }

    public function testFromArrayWithObjects(): void
    {
        $transactions = [
            new Transaction(
                transactionId: 'TXN001',
                date: new DateTimeImmutable(),
                amount: 1500.00,
                currency: 'KES',
                type: 'CREDIT'
            ),
        ];

        $data = [
            'totalElements' => 50,
            'transactions' => $transactions,
            'page' => 0,
            'pageSize' => 10,
            'totalPages' => 5,
            'accountNumber' => 'ACC001'
        ];

        $response = StatementResponse::fromArray($data);

        $this->assertSame(50, $response->totalElements);
        $this->assertCount(1, $response->transactions);
        $this->assertSame('ACC001', $response->accountNumber);
    }

    public function testFromArrayWithArrays(): void
    {
        $data = [
            'totalElements' => 25,
            'items' => [
                [
                    'transactionId' => 'TXN001',
                    'date' => '2026-02-15',
                    'amount' => 1000.00,
                    'currency' => 'KES',
                    'type' => 'DEBIT'
                ],
                [
                    'transactionId' => 'TXN002',
                    'date' => '2026-02-14',
                    'amount' => 2000.00,
                    'currency' => 'KES',
                    'type' => 'CREDIT'
                ],
            ],
            'page' => 1,
            'size' => 2,
            'total_pages' => 13,
            'account_number' => 'ACC002'
        ];

        $response = StatementResponse::fromArray($data);

        $this->assertSame(25, $response->totalElements);
        $this->assertCount(2, $response->transactions);
        $this->assertSame(1, $response->page);
        $this->assertSame(2, $response->pageSize);
        $this->assertSame(13, $response->totalPages);
        $this->assertSame('ACC002', $response->accountNumber);
    }

    public function testFromArrayWithDefaults(): void
    {
        $data = [
            'items' => [
                [
                    'transactionId' => 'TXN001',
                    'date' => '2026-02-15',
                    'amount' => 500.00,
                    'currency' => 'KES',
                    'type' => 'CREDIT'
                ],
            ]
        ];

        $response = StatementResponse::fromArray($data);

        $this->assertSame(1, $response->totalElements);
        $this->assertCount(1, $response->transactions);
        $this->assertSame(0, $response->page);
        $this->assertSame(1, $response->pageSize);
        $this->assertSame(1, $response->totalPages);
    }

    public function testToArray(): void
    {
        $transactions = [
            new Transaction(
                transactionId: 'TXN001',
                date: new DateTimeImmutable('2026-02-15'),
                amount: 1000.00,
                currency: 'KES',
                type: 'CREDIT'
            ),
        ];

        $response = new StatementResponse(
            totalElements: 50,
            transactions: $transactions,
            page: 2,
            pageSize: 25,
            totalPages: 2,
            accountNumber: 'ACC003'
        );

        $array = $response->toArray();

        $this->assertArrayHasKey('totalElements', $array);
        $this->assertArrayHasKey('transactions', $array);
        $this->assertArrayHasKey('page', $array);
        $this->assertArrayHasKey('pageSize', $array);
        $this->assertArrayHasKey('totalPages', $array);
        $this->assertArrayHasKey('accountNumber', $array);
        $this->assertSame(50, $array['totalElements']);
        $this->assertIsArray($array['transactions']);
    }

    public function testRoundTripSerialization(): void
    {
        $transactions = [
            new Transaction(
                transactionId: 'TXN001',
                date: new DateTimeImmutable('2026-02-15'),
                amount: 3000.00,
                currency: 'KES',
                type: 'CREDIT'
            ),
            new Transaction(
                transactionId: 'TXN002',
                date: new DateTimeImmutable('2026-02-14'),
                amount: 1000.00,
                currency: 'KES',
                type: 'DEBIT'
            ),
        ];

        $original = new StatementResponse(
            totalElements: 100,
            transactions: $transactions,
            page: 3,
            pageSize: 25,
            totalPages: 4,
            accountNumber: 'ACC004'
        );

        $restored = StatementResponse::fromArray($original->toArray());

        $this->assertSame($original->totalElements, $restored->totalElements);
        $this->assertCount(count($original->transactions), $restored->transactions);
        $this->assertSame($original->page, $restored->page);
        $this->assertSame($original->pageSize, $restored->pageSize);
        $this->assertSame($original->totalPages, $restored->totalPages);
        $this->assertSame($original->accountNumber, $restored->accountNumber);
    }
}
