<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\Payment;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\ValueObject\EftAttachment;
use Stanbic\SDK\Domain\Payment\EftTransferRequest;

final class EftTransferRequestTest extends TestCase
{
    public function testCreateEftTransferRequest(): void
    {
        $attachment = new EftAttachment(
            attachmentFileId: 'doc.id.12345',
            attachmentFileName: 'Sand-Man.pdf'
        );

        $request = new EftTransferRequest(
            sourceChannel: 'BCBC',
            sourceMsgId: '44463******22560',
            createdTime: '04:17',
            debitAccount: '49032******76599',
            dbpUniqueTransactionNumber: '77172******05216',
            beneficiaryAcctNo: '3653******1981',
            beneficiaryName: 'Peter Parker',
            beneficiaryBankCode: '17000',
            beneficiaryAddr: 'nairobi',
            exchangeRate: '',
            creditAmount: '345.50',
            creditCurrency: 'KES',
            paymentDetails: 'transfer narrative',
            fxDealId: '5235623692599296',
            execute: '1',
            paymentType: 'SBK.BCB.EFT.CREDIT',
            executionDate: 'dd/mm/yyyy',
            attachments: [$attachment]
        );

        $this->assertSame('BCBC', $request->sourceChannel);
        $this->assertSame('17000', $request->beneficiaryBankCode);
        $this->assertCount(1, $request->attachments);
    }

    public function testFromArrayWithCounterparty(): void
    {
        $data = [
            'SourceChannel' => 'BCBC',
            'SourceMsgId' => '5099172699045888',
            'CreatedTime' => '04:17',
            'DebitAccount' => '49032******76599',
            'DBPUniqueTransactionNumber' => '77172******05216',
            'BeneficiaryAcctNo' => '3653******1981',
            'BeneficiaryName' => 'Peter Parker',
            'BeneficiaryBankCode' => '17000',
            'CreditAmount' => '345.50',
            'CreditCurrency' => 'KES',
            'PaymentDetails' => 'transfer narrative',
            'Attachments' => [
                [
                    'AttachmentFileId' => 'doc.id.12345',
                    'AttachmentFileName' => 'Sand-Man.pdf',
                ],
            ],
        ];

        $request = EftTransferRequest::fromArray($data);

        $this->assertSame('BCBC', $request->sourceChannel);
        $this->assertSame('17000', $request->beneficiaryBankCode);
        $this->assertCount(1, $request->attachments);
    }

    public function testFromArrayWithLowercaseAttachments(): void
    {
        $attachment = new EftAttachment(
            attachmentFileId: 'doc.id.999',
            attachmentFileName: 'Plan.pdf'
        );

        $data = [
            'sourceChannel' => 'BCBC',
            'sourceMsgId' => '5099172699045888',
            'createdTime' => '04:17',
            'debitAccount' => '49032******76599',
            'dbpUniqueTransactionNumber' => '77172******05216',
            'beneficiaryAcctNo' => '3653******1981',
            'beneficiaryName' => 'Peter Parker',
            'beneficiaryBankCode' => '17000',
            'creditAmount' => '345.50',
            'creditCurrency' => 'KES',
            'paymentDetails' => 'transfer narrative',
            'attachments' => [$attachment],
        ];

        $request = EftTransferRequest::fromArray($data);

        $this->assertSame('BCBC', $request->sourceChannel);
        $this->assertCount(1, $request->attachments);
    }

    public function testToArrayOmitsCounterparty(): void
    {
        $request = new EftTransferRequest(
            sourceChannel: 'BCBC',
            sourceMsgId: '5099172699045888',
            createdTime: '04:17',
            debitAccount: '49032******76599',
            dbpUniqueTransactionNumber: '77172******05216',
            beneficiaryAcctNo: '3653******1981',
            beneficiaryName: 'Peter Parker',
            beneficiaryBankCode: '17000',
            beneficiaryAddr: null,
            exchangeRate: null,
            creditAmount: '345.50',
            creditCurrency: 'KES',
            paymentDetails: 'transfer narrative',
            fxDealId: null,
            execute: null,
            paymentType: null,
            executionDate: null
        );

        $array = $request->toArray();

        $this->assertArrayHasKey('SourceChannel', $array);
        $this->assertArrayNotHasKey('Attachments', $array);
    }

    public function testToArrayIncludesAttachments(): void
    {
        $attachment = new EftAttachment(
            attachmentFileId: 'doc.id.12345',
            attachmentFileName: 'Sand-Man.pdf'
        );

        $request = new EftTransferRequest(
            sourceChannel: 'BCBC',
            sourceMsgId: '5099172699045888',
            createdTime: '04:17',
            debitAccount: '49032******76599',
            dbpUniqueTransactionNumber: '77172******05216',
            beneficiaryAcctNo: '3653******1981',
            beneficiaryName: 'Peter Parker',
            beneficiaryBankCode: '17000',
            beneficiaryAddr: null,
            exchangeRate: null,
            creditAmount: '345.50',
            creditCurrency: 'KES',
            paymentDetails: 'transfer narrative',
            fxDealId: null,
            execute: null,
            paymentType: null,
            executionDate: null,
            attachments: [$attachment]
        );

        /** @var array<string, mixed> $array */
        $array = $request->toArray();

        $this->assertIsArray($array['Attachments']);
        /** @var array<array-key, mixed> $attachments */
        $attachments = $array['Attachments'];
        $this->assertCount(1, $attachments);
    }

    public function testFromArrayIgnoresInvalidAttachments(): void
    {
        $data = [
            'SourceChannel' => 'BCBC',
            'SourceMsgId' => '5099172699045888',
            'CreatedTime' => '04:17',
            'DebitAccount' => '49032******76599',
            'DBPUniqueTransactionNumber' => '77172******05216',
            'BeneficiaryAcctNo' => '3653******1981',
            'BeneficiaryName' => 'Peter Parker',
            'BeneficiaryBankCode' => '17000',
            'CreditAmount' => '345.50',
            'CreditCurrency' => 'KES',
            'PaymentDetails' => 'transfer narrative',
            'Attachments' => [
                'invalid',
                123,
                [
                    'AttachmentFileId' => 'doc.id.777',
                    'AttachmentFileName' => 'Valid.pdf',
                ],
            ],
        ];

        $request = EftTransferRequest::fromArray($data);

        $this->assertCount(1, $request->attachments);
        $this->assertSame('doc.id.777', $request->attachments[0]->attachmentFileId);
    }

    public function testFromArrayWithOptionalFieldsLowercase(): void
    {
        $data = [
            'sourceChannel' => 'BCBC',
            'sourceMsgId' => '5099172699045888',
            'createdTime' => '04:17',
            'debitAccount' => '49032******76599',
            'dbpUniqueTransactionNumber' => '77172******05216',
            'beneficiaryAcctNo' => '3653******1981',
            'beneficiaryName' => 'Peter Parker',
            'beneficiaryBankCode' => '17000',
            'beneficiaryAddr' => 'Nairobi',
            'exchangeRate' => '1.05',
            'creditAmount' => '345.50',
            'creditCurrency' => 'KES',
            'paymentDetails' => 'transfer narrative',
            'fxDealId' => 'FX123',
            'execute' => '1',
            'paymentType' => 'SBK.BCB.EFT.CREDIT',
            'executionDate' => '2026-02-18',
        ];

        $request = EftTransferRequest::fromArray($data);

        $this->assertSame('Nairobi', $request->beneficiaryAddr);
        $this->assertSame('1.05', $request->exchangeRate);
        $this->assertSame('FX123', $request->fxDealId);
        $this->assertSame('1', $request->execute);
        $this->assertSame('SBK.BCB.EFT.CREDIT', $request->paymentType);
        $this->assertSame('2026-02-18', $request->executionDate);
    }

    public function testToArrayIncludesOptionalFields(): void
    {
        $request = new EftTransferRequest(
            sourceChannel: 'BCBC',
            sourceMsgId: '5099172699045888',
            createdTime: '04:17',
            debitAccount: '49032******76599',
            dbpUniqueTransactionNumber: '77172******05216',
            beneficiaryAcctNo: '3653******1981',
            beneficiaryName: 'Peter Parker',
            beneficiaryBankCode: '17000',
            beneficiaryAddr: 'Nairobi',
            exchangeRate: '1.05',
            creditAmount: '345.50',
            creditCurrency: 'KES',
            paymentDetails: 'transfer narrative',
            fxDealId: 'FX123',
            execute: '1',
            paymentType: 'SBK.BCB.EFT.CREDIT',
            executionDate: '2026-02-18'
        );

        $array = $request->toArray();

        $this->assertSame('Nairobi', $array['BeneficiaryAddr']);
        $this->assertSame('1.05', $array['ExchangeRate']);
        $this->assertSame('FX123', $array['FxDealId']);
        $this->assertSame('1', $array['Execute']);
        $this->assertSame('SBK.BCB.EFT.CREDIT', $array['PaymentType']);
        $this->assertSame('2026-02-18', $array['ExecutionDate']);
    }
}
