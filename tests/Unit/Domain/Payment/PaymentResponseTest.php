<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\Payment;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\Payment\PaymentResponse;

final class PaymentResponseTest extends TestCase
{
    public function testCreatePaymentResponse(): void
    {
        $response = new PaymentResponse(
            bankReferenceId: 'BANK-123',
            dbsReferenceId: 'DBS-456',
            status: 'SUCCESS',
            message: 'Processed',
            transactionId: 'TXN-789',
            transferFee: 12.50,
            timestamp: '2026-02-16T10:00:00Z',
            errorCode: null,
            reasonText: 'Processed by bank',
            nextExecutionDate: '2026-02-20',
            originatorConversationId: 'ORIG-123',
            conversationId: 'CONV-456'
        );

        $this->assertSame('BANK-123', $response->bankReferenceId);
        $this->assertSame('DBS-456', $response->dbsReferenceId);
        $this->assertSame('SUCCESS', $response->status);
        $this->assertSame('Processed', $response->message);
        $this->assertSame('TXN-789', $response->transactionId);
        $this->assertSame(12.50, $response->transferFee);
        $this->assertSame('2026-02-16T10:00:00Z', $response->timestamp);
        $this->assertNull($response->errorCode);
        $this->assertSame('Processed by bank', $response->reasonText);
        $this->assertSame('2026-02-20', $response->nextExecutionDate);
        $this->assertSame('ORIG-123', $response->originatorConversationId);
        $this->assertSame('CONV-456', $response->conversationId);
    }

    public function testFromArrayWithCamelCase(): void
    {
        $data = [
            'bankReferenceId' => 'BANK-ABC',
            'dbsReferenceId' => 'DBS-DEF',
            'status' => 'PENDING',
            'message' => 'Awaiting',
            'transactionId' => 'TXN-001',
            'transferFee' => 5.00,
            'timestamp' => '2026-02-16T12:00:00Z',
            'errorCode' => 'E001',
            'reasonText' => 'Accepted by bank',
            'nextExecutionDate' => '2026-02-21',
        ];

        $response = PaymentResponse::fromArray($data);

        $this->assertSame('BANK-ABC', $response->bankReferenceId);
        $this->assertSame('DBS-DEF', $response->dbsReferenceId);
        $this->assertSame('PENDING', $response->status);
        $this->assertSame('Awaiting', $response->message);
        $this->assertSame('TXN-001', $response->transactionId);
        $this->assertSame(5.00, $response->transferFee);
        $this->assertSame('2026-02-16T12:00:00Z', $response->timestamp);
        $this->assertSame('E001', $response->errorCode);
        $this->assertSame('Accepted by bank', $response->reasonText);
        $this->assertSame('2026-02-21', $response->nextExecutionDate);
    }

    public function testFromArrayWithSnakeCase(): void
    {
        $data = [
            'bank_reference_id' => 'BANK-XYZ',
            'dbs_reference_id' => 'DBS-XYZ',
            'bankStatus' => 'FAILED',
            'statusDescription' => 'Declined',
            'transaction_id' => 'TXN-999',
            'transfer_fee' => 7.50,
            'statusDate' => '2026-02-16T15:00:00Z',
            'error_code' => 'E999',
            'reasonText' => 'Declined',
            'OriginatorConversationID' => 'ORIG-789',
            'ConversationID' => 'CONV-999',
        ];

        $response = PaymentResponse::fromArray($data);

        $this->assertSame('BANK-XYZ', $response->bankReferenceId);
        $this->assertSame('DBS-XYZ', $response->dbsReferenceId);
        $this->assertSame('FAILED', $response->status);
        $this->assertSame('Declined', $response->message);
        $this->assertSame('TXN-999', $response->transactionId);
        $this->assertSame(7.50, $response->transferFee);
        $this->assertSame('2026-02-16T15:00:00Z', $response->timestamp);
        $this->assertSame('E999', $response->errorCode);
        $this->assertSame('Declined', $response->reasonText);
        $this->assertSame('ORIG-789', $response->originatorConversationId);
        $this->assertSame('CONV-999', $response->conversationId);
    }

    public function testFromArrayWithDefaults(): void
    {
        $response = PaymentResponse::fromArray([]);

        $this->assertSame('', $response->bankReferenceId);
        $this->assertSame('', $response->dbsReferenceId);
        $this->assertSame('UNKNOWN', $response->status);
        $this->assertNull($response->message);
    }

    public function testToArray(): void
    {
        $response = new PaymentResponse(
            bankReferenceId: 'BANK-321',
            dbsReferenceId: 'DBS-654',
            status: 'SUCCESS',
            message: 'OK'
        );

        $array = $response->toArray();

        $this->assertSame('BANK-321', $array['bankReferenceId']);
        $this->assertSame('DBS-654', $array['dbsReferenceId']);
        $this->assertSame('SUCCESS', $array['status']);
        $this->assertSame('OK', $array['message']);
        $this->assertArrayNotHasKey('transferFee', $array);
    }

    public function testToArrayIncludesConversationIds(): void
    {
        $response = new PaymentResponse(
            bankReferenceId: 'BANK-900',
            dbsReferenceId: 'DBS-900',
            status: 'SUCCESS',
            originatorConversationId: 'ORIG-900',
            conversationId: 'CONV-900'
        );

        $array = $response->toArray();

        $this->assertSame('ORIG-900', $array['OriginatorConversationID']);
        $this->assertSame('CONV-900', $array['ConversationID']);
    }

    public function testFromArrayWithAlternateKeys(): void
    {
        $response = PaymentResponse::fromArray([
            'bank_reference_id' => 'BANK-ALT',
            'dbs_reference_id' => 'DBS-ALT',
            'bankStatus' => 'PENDING',
            'statusDescription' => 'Awaiting approval',
            'transaction_id' => 'TXN-ALT',
            'transfer_fee' => 9.99,
            'statusDate' => '2026-02-17T10:00:00Z',
            'error_code' => 'E123',
            'reason_text' => 'Awaiting approval',
            'next_execution_date' => '2026-02-22',
            'originatorConversationId' => 'ORIG-ALT',
            'conversationId' => 'CONV-ALT',
        ]);

        $this->assertSame('BANK-ALT', $response->bankReferenceId);
        $this->assertSame('DBS-ALT', $response->dbsReferenceId);
        $this->assertSame('PENDING', $response->status);
        $this->assertSame('Awaiting approval', $response->message);
        $this->assertSame('TXN-ALT', $response->transactionId);
        $this->assertSame(9.99, $response->transferFee);
        $this->assertSame('2026-02-17T10:00:00Z', $response->timestamp);
        $this->assertSame('E123', $response->errorCode);
        $this->assertSame('Awaiting approval', $response->reasonText);
        $this->assertSame('2026-02-22', $response->nextExecutionDate);
        $this->assertSame('ORIG-ALT', $response->originatorConversationId);
        $this->assertSame('CONV-ALT', $response->conversationId);
    }

    public function testToArrayIncludesOptionalFields(): void
    {
        $response = new PaymentResponse(
            bankReferenceId: 'BANK-OPT',
            dbsReferenceId: 'DBS-OPT',
            status: 'SUCCESS',
            message: 'All good',
            transactionId: 'TXN-OPT',
            transferFee: 1.23,
            timestamp: '2026-02-17T11:00:00Z',
            errorCode: 'E000',
            reasonText: 'Optional',
            nextExecutionDate: '2026-02-23',
            originatorConversationId: 'ORIG-OPT',
            conversationId: 'CONV-OPT'
        );

        $array = $response->toArray();

        $this->assertSame('All good', $array['message']);
        $this->assertSame('TXN-OPT', $array['transactionId']);
        $this->assertSame(1.23, $array['transferFee']);
        $this->assertSame('2026-02-17T11:00:00Z', $array['timestamp']);
        $this->assertSame('E000', $array['errorCode']);
        $this->assertSame('Optional', $array['reasonText']);
        $this->assertSame('2026-02-23', $array['nextExecutionDate']);
        $this->assertSame('ORIG-OPT', $array['OriginatorConversationID']);
        $this->assertSame('CONV-OPT', $array['ConversationID']);
    }

    public function testRoundTripSerialization(): void
    {
        $original = new PaymentResponse(
            bankReferenceId: 'BANK-777',
            dbsReferenceId: 'DBS-888',
            status: 'SUCCESS',
            transactionId: 'TXN-777'
        );

        $restored = PaymentResponse::fromArray($original->toArray());

        $this->assertSame($original->bankReferenceId, $restored->bankReferenceId);
        $this->assertSame($original->dbsReferenceId, $restored->dbsReferenceId);
        $this->assertSame($original->status, $restored->status);
        $this->assertSame($original->transactionId, $restored->transactionId);
    }
}
