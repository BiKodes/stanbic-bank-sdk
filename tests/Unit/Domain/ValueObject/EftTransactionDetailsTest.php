<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\ValueObject;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\ValueObject\EftTransactionDetails;

final class EftTransactionDetailsTest extends TestCase
{
    public function testCreateEftTransactionDetails(): void
    {
        $details = new EftTransactionDetails(
            transactionStatus: '001',
            dbpUniqueTransactionNumber: '1926223162769408',
            debitAmount: '238.00',
            debitAmountCurrency: 'KES',
            creditAmount: '100.00',
            creditAmountCurrency: 'KES',
            error: 'ERR00210'
        );

        $this->assertSame('001', $details->transactionStatus);
        $this->assertSame('1926223162769408', $details->dbpUniqueTransactionNumber);
        $this->assertSame('238.00', $details->debitAmount);
        $this->assertSame('KES', $details->debitAmountCurrency);
        $this->assertSame('100.00', $details->creditAmount);
        $this->assertSame('KES', $details->creditAmountCurrency);
        $this->assertSame('ERR00210', $details->error);
    }

    public function testFromArray(): void
    {
        $data = [
            'TransactionStatus' => '001',
            'DBPUniqueTransactionNumber' => '1926223162769408',
            'DebitAmount' => '238.00',
            'DebitAmountCurrency' => 'KES',
            'CreditAmount' => '100.00',
            'CreditAmountCurrency' => 'KES',
            'Error' => 'ERR00210',
        ];

        $details = EftTransactionDetails::fromArray($data);

        $this->assertSame('001', $details->transactionStatus);
        $this->assertSame('1926223162769408', $details->dbpUniqueTransactionNumber);
        $this->assertSame('238.00', $details->debitAmount);
        $this->assertSame('KES', $details->debitAmountCurrency);
        $this->assertSame('100.00', $details->creditAmount);
        $this->assertSame('KES', $details->creditAmountCurrency);
        $this->assertSame('ERR00210', $details->error);
    }

    public function testFromArrayWithLowercaseKeys(): void
    {
        $data = [
            'transactionStatus' => '002',
            'dbpUniqueTransactionNumber' => '111222333444',
            'debitAmount' => '10.00',
            'debitAmountCurrency' => 'USD',
            'creditAmount' => '9.50',
            'creditAmountCurrency' => 'USD',
            'valueDate' => '2026-02-16',
            'chargesAmount' => '0.50',
            'chargesCurrency' => 'USD',
            'exchangeRate' => '1.00',
            'utn' => 'UTN-001',
            'errorDescription' => 'Minor issue',
        ];

        $details = EftTransactionDetails::fromArray($data);

        $this->assertSame('002', $details->transactionStatus);
        $this->assertSame('111222333444', $details->dbpUniqueTransactionNumber);
        $this->assertSame('10.00', $details->debitAmount);
        $this->assertSame('USD', $details->debitAmountCurrency);
        $this->assertSame('9.50', $details->creditAmount);
        $this->assertSame('USD', $details->creditAmountCurrency);
        $this->assertSame('2026-02-16', $details->valueDate);
        $this->assertSame('0.50', $details->chargesAmount);
        $this->assertSame('USD', $details->chargesCurrency);
        $this->assertSame('1.00', $details->exchangeRate);
        $this->assertSame('UTN-001', $details->utn);
        $this->assertSame('Minor issue', $details->errorDescription);
    }

    public function testToArray(): void
    {
        $details = new EftTransactionDetails(
            transactionStatus: '001',
            dbpUniqueTransactionNumber: '1926223162769408'
        );

        $array = $details->toArray();

        $this->assertSame('001', $array['TransactionStatus']);
        $this->assertSame('1926223162769408', $array['DBPUniqueTransactionNumber']);
    }

    public function testFromArrayWithMissingTransactionNumber(): void
    {
        $details = EftTransactionDetails::fromArray([
            'TransactionStatus' => '004',
        ]);

        $this->assertSame('004', $details->transactionStatus);
        $this->assertNull($details->dbpUniqueTransactionNumber);
    }

    public function testFromArrayWithUppercaseErrorFields(): void
    {
        $details = EftTransactionDetails::fromArray([
            'Error' => 'E100',
            'ErrorDescription' => 'Bad request',
            'UTN' => 'UTN-999',
        ]);

        $this->assertSame('E100', $details->error);
        $this->assertSame('Bad request', $details->errorDescription);
        $this->assertSame('UTN-999', $details->utn);
    }

    public function testToArrayWithAllFields(): void
    {
        $details = new EftTransactionDetails(
            transactionStatus: '005',
            dbpUniqueTransactionNumber: 'DBP-555',
            debitAmount: '100.00',
            debitAmountCurrency: 'USD',
            creditAmount: '99.50',
            creditAmountCurrency: 'USD',
            valueDate: '2026-02-17',
            chargesAmount: '0.50',
            chargesCurrency: 'USD',
            exchangeRate: '1.00',
            utn: 'UTN-123',
            error: 'E200',
            errorDescription: 'Charge error'
        );

        $array = $details->toArray();

        $this->assertSame('005', $array['TransactionStatus']);
        $this->assertSame('DBP-555', $array['DBPUniqueTransactionNumber']);
        $this->assertSame('100.00', $array['DebitAmount']);
        $this->assertSame('USD', $array['DebitAmountCurrency']);
        $this->assertSame('99.50', $array['CreditAmount']);
        $this->assertSame('USD', $array['CreditAmountCurrency']);
        $this->assertSame('2026-02-17', $array['ValueDate']);
        $this->assertSame('0.50', $array['ChargesAmount']);
        $this->assertSame('USD', $array['ChargesCurrency']);
        $this->assertSame('1.00', $array['ExchangeRate']);
        $this->assertSame('UTN-123', $array['UTN']);
        $this->assertSame('E200', $array['Error']);
        $this->assertSame('Charge error', $array['ErrorDescription']);
    }
}
