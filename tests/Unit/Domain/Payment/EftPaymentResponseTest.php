<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\Payment;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\Payment\EftPaymentResponse;
use Stanbic\SDK\Domain\ValueObject\EftTransactionDetails;

final class EftPaymentResponseTest extends TestCase
{
    public function testCreateEftPaymentResponse(): void
    {
        $details = new EftTransactionDetails(
            transactionStatus: '001',
            dbpUniqueTransactionNumber: '1926223162769408',
            debitAmount: '238.00',
            debitAmountCurrency: 'KES',
            creditAmount: '100.00',
            creditAmountCurrency: 'KES'
        );

        $response = new EftPaymentResponse(
            sourceMsgId: '5099172699045888',
            responseCode: '00',
            responseMessage: 'success',
            responseTime: '2025-11-21 15:15:53.341749',
            transactionDetails: $details
        );

        $this->assertSame('5099172699045888', $response->sourceMsgId);
        $this->assertSame('00', $response->responseCode);
        $this->assertSame('success', $response->responseMessage);
        $this->assertSame('001', $response->transactionDetails?->transactionStatus);
    }

    public function testFromArray(): void
    {
        $data = [
            'SourceMsgId' => '5099172699045888',
            'ResponseCode' => '00',
            'ResponseMessage' => 'success',
            'ResponseTime' => '2025-11-21 15:15:53.341749',
            'TransactionDetails' => [
                'TransactionStatus' => '001',
                'DBPUniqueTransactionNumber' => '1926223162769408',
            ],
        ];

        $response = EftPaymentResponse::fromArray($data);

        $this->assertSame('5099172699045888', $response->sourceMsgId);
        $this->assertSame('00', $response->responseCode);
        $this->assertSame('001', $response->transactionDetails?->transactionStatus);
    }

    public function testFromArrayWithDetailsObject(): void
    {
        $details = new EftTransactionDetails(
            transactionStatus: '003',
            dbpUniqueTransactionNumber: '9988776655'
        );

        $response = EftPaymentResponse::fromArray([
            'SourceMsgId' => '123',
            'ResponseCode' => '00',
            'ResponseMessage' => 'ok',
            'ResponseTime' => '2026-02-16 10:00:00',
            'TransactionDetails' => $details,
        ]);

        $this->assertSame('003', $response->transactionDetails?->transactionStatus);
    }

    public function testToArray(): void
    {
        $response = new EftPaymentResponse(
            sourceMsgId: '5099172699045888',
            responseCode: '00',
            responseMessage: 'success',
            responseTime: '2025-11-21 15:15:53.341749'
        );

        $array = $response->toArray();

        $this->assertSame('5099172699045888', $array['SourceMsgId']);
        $this->assertSame('00', $array['ResponseCode']);
    }

    public function testFromArrayWithLowercaseDetailsKey(): void
    {
        $response = EftPaymentResponse::fromArray([
            'sourceMsgId' => 'ABC-123',
            'responseCode' => '01',
            'responseMessage' => 'failed',
            'responseTime' => '2026-02-17 12:00:00',
            'transactionDetails' => [
                'TransactionStatus' => '099',
            ],
        ]);

        $this->assertSame('ABC-123', $response->sourceMsgId);
        $this->assertSame('099', $response->transactionDetails?->transactionStatus);
    }

    public function testToArrayIncludesTransactionDetails(): void
    {
        $details = new EftTransactionDetails(
            transactionStatus: '005',
            dbpUniqueTransactionNumber: 'DBP-555'
        );

        $response = new EftPaymentResponse(
            sourceMsgId: 'SRC-555',
            responseCode: '00',
            responseMessage: 'ok',
            responseTime: '2026-02-17 12:30:00',
            transactionDetails: $details
        );

        $array = $response->toArray();

        $this->assertIsArray($array['TransactionDetails']);
        /** @var array<string, mixed> $detailsArray */
        $detailsArray = $array['TransactionDetails'];
        $this->assertSame('005', $detailsArray['TransactionStatus']);
        $this->assertSame('DBP-555', $detailsArray['DBPUniqueTransactionNumber']);
    }
}
