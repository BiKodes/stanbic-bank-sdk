<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\Account;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\Account\TransactionStatusResponse;

final class TransactionStatusResponseTest extends TestCase
{
    public function testCreateTransactionStatusResponse(): void
    {
        $status = new TransactionStatusResponse(
            bankStatus: 'SUCCESS',
            bankReferenceId: 'BANK001',
            transferFee: 50.00,
            transactionId: 'TXN001',
            statusDescription: 'Transaction completed successfully',
            timestamp: '2026-02-15T10:30:00Z'
        );

        $this->assertSame('SUCCESS', $status->bankStatus);
        $this->assertSame('BANK001', $status->bankReferenceId);
        $this->assertSame(50.00, $status->transferFee);
        $this->assertSame('TXN001', $status->transactionId);
        $this->assertSame('Transaction completed successfully', $status->statusDescription);
        $this->assertSame('2026-02-15T10:30:00Z', $status->timestamp);
    }

    public function testCreateTransactionStatusResponseWithoutOptional(): void
    {
        $status = new TransactionStatusResponse(
            bankStatus: 'PENDING',
            bankReferenceId: 'BANK002'
        );

        $this->assertSame('PENDING', $status->bankStatus);
        $this->assertSame('BANK002', $status->bankReferenceId);
        $this->assertNull($status->transferFee);
        $this->assertNull($status->transactionId);
        $this->assertNull($status->statusDescription);
        $this->assertNull($status->timestamp);
    }

    public function testIsSuccess(): void
    {
        $statusSuccess = new TransactionStatusResponse(
            bankStatus: 'SUCCESS',
            bankReferenceId: 'BANK003'
        );

        $this->assertTrue($statusSuccess->isSuccess());
        $this->assertFalse($statusSuccess->isPending());
        $this->assertFalse($statusSuccess->isFailed());
    }

    public function testIsPending(): void
    {
        $statusPending = new TransactionStatusResponse(
            bankStatus: 'PENDING',
            bankReferenceId: 'BANK004'
        );

        $this->assertTrue($statusPending->isPending());
        $this->assertFalse($statusPending->isSuccess());
        $this->assertFalse($statusPending->isFailed());
    }

    public function testIsPendingWithProcessing(): void
    {
        $statusProcessing = new TransactionStatusResponse(
            bankStatus: 'PROCESSING',
            bankReferenceId: 'BANK005'
        );

        $this->assertTrue($statusProcessing->isPending());
    }

    public function testIsPendingWithInProgress(): void
    {
        $statusInProgress = new TransactionStatusResponse(
            bankStatus: 'IN_PROGRESS',
            bankReferenceId: 'BANK006'
        );

        $this->assertTrue($statusInProgress->isPending());
    }

    public function testIsFailed(): void
    {
        $statusFailed = new TransactionStatusResponse(
            bankStatus: 'FAILED',
            bankReferenceId: 'BANK007'
        );

        $this->assertTrue($statusFailed->isFailed());
        $this->assertFalse($statusFailed->isSuccess());
        $this->assertFalse($statusFailed->isPending());
    }

    public function testIsFailedWithRejected(): void
    {
        $statusRejected = new TransactionStatusResponse(
            bankStatus: 'REJECTED',
            bankReferenceId: 'BANK008'
        );

        $this->assertTrue($statusRejected->isFailed());
    }

    public function testIsFailedWithError(): void
    {
        $statusError = new TransactionStatusResponse(
            bankStatus: 'ERROR',
            bankReferenceId: 'BANK009'
        );

        $this->assertTrue($statusError->isFailed());
    }

    public function testFromArrayWithCamelCase(): void
    {
        $data = [
            'bankStatus' => 'SUCCESS',
            'bankReferenceId' => 'BANK010',
            'transferFee' => 75.50,
            'transactionId' => 'TXN002',
            'statusDescription' => 'Funds sent successfully',
            'timestamp' => '2026-02-14T15:45:00Z'
        ];

        $status = TransactionStatusResponse::fromArray($data);

        $this->assertSame('SUCCESS', $status->bankStatus);
        $this->assertSame('BANK010', $status->bankReferenceId);
        $this->assertSame(75.50, $status->transferFee);
        $this->assertSame('TXN002', $status->transactionId);
        $this->assertSame('Funds sent successfully', $status->statusDescription);
        $this->assertSame('2026-02-14T15:45:00Z', $status->timestamp);
    }

    public function testFromArrayWithSnakeCase(): void
    {
        $data = [
            'bank_status' => 'FAILED',
            'bank_reference_id' => 'BANK011',
            'transfer_fee' => 100.00,
            'transaction_id' => 'TXN003',
            'status_description' => 'Insufficient funds',
            'status_date' => '2026-02-13T09:20:00Z'
        ];

        $status = TransactionStatusResponse::fromArray($data);

        $this->assertSame('FAILED', $status->bankStatus);
        $this->assertSame('BANK011', $status->bankReferenceId);
        $this->assertSame(100.00, $status->transferFee);
        $this->assertTrue($status->isFailed());
    }

    public function testFromArrayWithDefaults(): void
    {
        $data = [
            'bankStatus' => 'PENDING'
        ];

        $status = TransactionStatusResponse::fromArray($data);

        $this->assertSame('PENDING', $status->bankStatus);
        $this->assertSame('', $status->bankReferenceId);
        $this->assertNull($status->transferFee);
        $this->assertTrue($status->isPending());
    }

    public function testFromArrayWithStatusField(): void
    {
        $data = [
            'status' => 'SUCCESS',
            'referenceId' => 'BANK012'
        ];

        $status = TransactionStatusResponse::fromArray($data);

        $this->assertSame('SUCCESS', $status->bankStatus);
        $this->assertSame('BANK012', $status->bankReferenceId);
    }

    public function testToArray(): void
    {
        $status = new TransactionStatusResponse(
            bankStatus: 'SUCCESS',
            bankReferenceId: 'BANK013',
            transferFee: 200.00,
            transactionId: 'TXN004',
            statusDescription: 'Cleared',
            timestamp: '2026-02-12T12:00:00Z'
        );

        $array = $status->toArray();

        $this->assertArrayHasKey('bankStatus', $array);
        $this->assertArrayHasKey('bankReferenceId', $array);
        $this->assertArrayHasKey('transferFee', $array);
        $this->assertArrayHasKey('transactionId', $array);
        $this->assertArrayHasKey('statusDescription', $array);
        $this->assertArrayHasKey('timestamp', $array);
        $this->assertSame('SUCCESS', $array['bankStatus']);
        $this->assertSame('BANK013', $array['bankReferenceId']);
        $this->assertSame(200.00, $array['transferFee']);
    }

    public function testRoundTripSerialization(): void
    {
        $original = new TransactionStatusResponse(
            bankStatus: 'SUCCESS',
            bankReferenceId: 'BANK014',
            transferFee: 250.00,
            transactionId: 'TXN005',
            statusDescription: 'Transaction complete',
            timestamp: '2026-02-11T08:30:00Z'
        );

        $restored = TransactionStatusResponse::fromArray($original->toArray());

        $this->assertSame($original->bankStatus, $restored->bankStatus);
        $this->assertSame($original->bankReferenceId, $restored->bankReferenceId);
        $this->assertSame($original->transferFee, $restored->transferFee);
        $this->assertSame($original->transactionId, $restored->transactionId);
        $this->assertTrue($restored->isSuccess());
    }
}
