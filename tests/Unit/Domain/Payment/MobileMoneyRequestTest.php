<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\Payment;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\Payment\Counterparty;
use Stanbic\SDK\Domain\Payment\CounterpartyAccount;
use Stanbic\SDK\Domain\Payment\MobileMoneyRequest;
use Stanbic\SDK\Domain\ValueObject\MobileMoneyMno;
use Stanbic\SDK\Domain\ValueObject\OriginatorAccount;
use Stanbic\SDK\Domain\ValueObject\OriginatorIdentification;
use Stanbic\SDK\Domain\Payment\TransferTransactionInformation;

final class MobileMoneyRequestTest extends TestCase
{
    public function testCreateMobileMoneyRequest(): void
    {
        $originator = new OriginatorAccount(
            new OriginatorIdentification(mobileNumber: '254700000000')
        );

        $counterparty = new Counterparty(
            name: 'John Doe',
            account: null,
            mobileNumber: '254711111111'
        );

        $tti = new TransferTransactionInformation(
            amount: 150.00,
            currency: 'KES',
            counterpartyAccount: new CounterpartyAccount(),
            counterparty: $counterparty,
            mobileMoneyMno: new MobileMoneyMno('MPESA')
        );

        $request = new MobileMoneyRequest(
            originatorAccount: $originator,
            transferTransactionInformation: $tti,
            callBackUrl: 'http://client_domain.com/omnichannel/esbCallback',
            dbsReferenceId: 'DBS-121',
            txnNarrative: 'Mobile payment',
            requestedExecutionDate: '2026-02-16'
        );

        $this->assertSame('254700000000', $request->originatorAccount->identification->mobileNumber);
        $this->assertSame('John Doe', $request->transferTransactionInformation->counterparty?->name);
        $this->assertSame('MPESA', $request->transferTransactionInformation->mobileMoneyMno?->name);
    }

    public function testFromArray(): void
    {
        $data = [
            'originatorAccount' => [
                'identification' => [
                    'mobileNumber' => '254711111111',
                ],
            ],
            'dbsReferenceId' => 'DBS-131',
            'txnNarrative' => 'From array',
            'requestedExecutionDate' => '2026-02-17',
            'callBackUrl' => 'http://client_domain.com/omnichannel/esbCallback',
            'transferTransactionInformation' => [
                'instructedAmount' => [
                    'amount' => 250.00,
                    'currencyCode' => 'USD',
                ],
                'mobileMoneyMno' => [
                    'name' => 'AIRTEL MONEY',
                ],
                'counterparty' => [
                    'name' => 'Jane Doe',
                    'mobileNumber' => '254711111111',
                ],
            ],
        ];

        $request = MobileMoneyRequest::fromArray($data);

        $this->assertSame('254711111111', $request->originatorAccount->identification->mobileNumber);
        $this->assertSame('Jane Doe', $request->transferTransactionInformation->counterparty?->name);
        $this->assertSame('AIRTEL MONEY', $request->transferTransactionInformation->mobileMoneyMno?->name);
    }

    public function testFromArrayWithFallbackFields(): void
    {
        $data = [
            'originator_mobile_number' => '254700123456',
            'recipientName' => 'Fallback User',
            'recipientMobileNumber' => '254711999999',
            'mobileNetwork' => 'MPESA',
            'call_back_url' => 'https://client.example.com/callback',
            'end_to_end_id' => 'E2E-200',
            'charge_bearer' => 'SHA',
            'dbs_reference_id' => 'DBS-200',
            'txn_narrative' => 'Fallback flow',
            'requested_execution_date' => '2026-03-01',
            'transfer_transaction_information' => [
                'amount' => 100.00,
                'currency' => 'KES',
                'counterparty_account' => [
                    'recipient_bank_acct_no' => '1234567890',
                    'recipient_bank_code' => '01000',
                ],
            ],
        ];

        $request = MobileMoneyRequest::fromArray($data);

        $this->assertSame('254700123456', $request->originatorAccount->identification->mobileNumber);
        $this->assertSame('Fallback User', $request->transferTransactionInformation->counterparty?->name);
        $this->assertSame('MPESA', $request->transferTransactionInformation->mobileMoneyMno?->name);
        $this->assertSame('https://client.example.com/callback', $request->callBackUrl);
        $this->assertSame('E2E-200', $request->endToEndId);
        $this->assertSame('SHA', $request->chargeBearer);
    }

    public function testToArray(): void
    {
        $originator = new OriginatorAccount(
            new OriginatorIdentification(mobileNumber: '254722222222')
        );

        $counterparty = new Counterparty(
            name: 'Array User',
            account: null,
            mobileNumber: '254733333333'
        );

        $tti = new TransferTransactionInformation(
            amount: 350.00,
            currency: 'EUR',
            counterpartyAccount: new CounterpartyAccount(),
            counterparty: $counterparty
        );

        $request = new MobileMoneyRequest(
            originatorAccount: $originator,
            transferTransactionInformation: $tti,
            callBackUrl: null,
            dbsReferenceId: 'DBS-141',
            txnNarrative: 'To array',
            requestedExecutionDate: '2026-02-18'
        );

        /** @var array<string, mixed> $array */
        $array = $request->toArray();

        $this->assertIsArray($array['originatorAccount']);
        /** @var array<string, mixed> $originatorAccount */
        $originatorAccount = $array['originatorAccount'];
        $this->assertIsArray($originatorAccount['identification']);
        /** @var array<string, mixed> $identification */
        $identification = $originatorAccount['identification'];

        $this->assertIsArray($array['transferTransactionInformation']);
        /** @var array<string, mixed> $tti */
        $tti = $array['transferTransactionInformation'];
        $this->assertIsArray($tti['counterparty']);
        /** @var array<string, mixed> $counterparty */
        $counterparty = $tti['counterparty'];

        $this->assertSame('254722222222', $identification['mobileNumber']);
        $this->assertSame('Array User', $counterparty['name']);
        $this->assertSame('DBS-141', $array['dbsReferenceId']);
        $this->assertArrayNotHasKey('mobileNetwork', $array);
    }

    public function testFromArrayWithObjectInputs(): void
    {
        $originator = new OriginatorAccount(
            new OriginatorIdentification(mobileNumber: '254700111111')
        );

        $counterparty = new Counterparty(
            name: 'Object User',
            account: null,
            mobileNumber: '254711222222'
        );

        $tti = new TransferTransactionInformation(
            amount: 500.00,
            currency: 'KES',
            counterpartyAccount: new CounterpartyAccount(),
            counterparty: $counterparty
        );

        $request = MobileMoneyRequest::fromArray([
            'originatorAccount' => $originator,
            'transferTransactionInformation' => $tti,
            'dbsReferenceId' => 'DBS-OBJ',
            'txnNarrative' => 'Object input',
            'requestedExecutionDate' => '2026-02-28',
        ]);

        $this->assertSame('254700111111', $request->originatorAccount->identification->mobileNumber);
        $this->assertSame('Object User', $request->transferTransactionInformation->counterparty?->name);
    }

    public function testFromArrayAddsMobileNetworkWhenMissingMno(): void
    {
        $data = [
            'originator_mobile_number' => '254700333333',
            'mobileNetwork' => 'AIRTEL',
            'dbs_reference_id' => 'DBS-MNO',
            'txn_narrative' => 'MNO fallback',
            'requested_execution_date' => '2026-03-02',
            'transfer_transaction_information' => [
                'amount' => 80.00,
                'currency' => 'KES',
                'counterparty_account' => [
                    'recipient_bank_acct_no' => '1234567890',
                    'recipient_bank_code' => '01000',
                ],
            ],
        ];

        $request = MobileMoneyRequest::fromArray($data);

        $this->assertSame('AIRTEL', $request->transferTransactionInformation->mobileMoneyMno?->name);
    }

    public function testFromArrayWithEmptyOriginatorAndMobileNumber(): void
    {
        $data = [
            'originatorAccount' => [],
            'originator_mobile_number' => '254700444444',
            'recipientName' => 'Empty Originator User',
            'recipientMobileNumber' => '254711888888',
            'mobileNetwork' => 'MPESA',
            'dbs_reference_id' => 'DBS-EMPTY-ORIG',
            'txn_narrative' => 'Empty originator',
            'requested_execution_date' => '2026-03-03',
            'transfer_transaction_information' => [
                'amount' => 60.00,
                'currency' => 'KES',
            ],
        ];

        $request = MobileMoneyRequest::fromArray($data);

        $this->assertSame('254700444444', $request->originatorAccount->identification->mobileNumber);
        $this->assertSame('Empty Originator User', $request->transferTransactionInformation->counterparty?->name);
        $this->assertSame('254711888888', $request->transferTransactionInformation->counterparty?->mobileNumber);
    }

    public function testFromArrayWithCamelCaseOriginatorMobileNumber(): void
    {
        $data = [
            'originatorAccount' => [],
            'originatorMobileNumber' => '254700999999',
            'dbs_reference_id' => 'DBS-CAMEL',
            'txn_narrative' => 'CamelCase mobile',
            'requested_execution_date' => '2026-03-12',
            'transfer_transaction_information' => [
                'amount' => 70.00,
                'currency' => 'KES',
            ],
        ];

        $request = MobileMoneyRequest::fromArray($data);

        $this->assertSame('254700999999', $request->originatorAccount->identification->mobileNumber);
    }

    public function testFromArrayWithMissingTti(): void
    {
        $data = [
            'originator_mobile_number' => '254700111222',
            'dbs_reference_id' => 'DBS-NO-TTI-MOB',
            'txn_narrative' => 'No TTI',
            'requested_execution_date' => '2026-03-13',
        ];

        $request = MobileMoneyRequest::fromArray($data);

        $this->assertSame('254700111222', $request->originatorAccount->identification->mobileNumber);
        $this->assertSame(0.0, $request->transferTransactionInformation->amount);
        $this->assertSame('KES', $request->transferTransactionInformation->currency);
    }

    public function testFromArrayWithTtiAsArray(): void
    {
        $data = [
            'originator_mobile_number' => '254700555555',
            'dbs_reference_id' => 'DBS-TTI-ARR',
            'txn_narrative' => 'TTI array',
            'requested_execution_date' => '2026-03-04',
            'transferTransactionInformation' => [
                'instructedAmount' => [
                    'amount' => 120.00,
                    'currencyCode' => 'KES',
                ],
                'counterparty' => [
                    'name' => 'TTI Array User',
                    'mobileNumber' => '254711777777',
                ],
            ],
        ];

        $request = MobileMoneyRequest::fromArray($data);

        $this->assertSame('TTI Array User', $request->transferTransactionInformation->counterparty?->name);
        $this->assertSame(120.00, $request->transferTransactionInformation->amount);
        $this->assertSame('KES', $request->transferTransactionInformation->currency);
    }

    public function testToArrayIncludesCallBackUrl(): void
    {
        $originator = new OriginatorAccount(
            new OriginatorIdentification(mobileNumber: '254700666666')
        );

        $counterparty = new Counterparty(
            name: 'Callback User',
            account: null,
            mobileNumber: '254711666666'
        );

        $tti = new TransferTransactionInformation(
            amount: 180.00,
            currency: 'KES',
            counterpartyAccount: new CounterpartyAccount(),
            counterparty: $counterparty
        );

        $request = new MobileMoneyRequest(
            originatorAccount: $originator,
            transferTransactionInformation: $tti,
            callBackUrl: 'https://pesahalisi.com/callback',
            dbsReferenceId: 'DBS-CALLBACK',
            txnNarrative: 'With callback',
            requestedExecutionDate: '2026-03-05'
        );

        /** @var array<string, mixed> $array */
        $array = $request->toArray();

        $this->assertArrayHasKey('callBackUrl', $array);
        $this->assertSame('https://pesahalisi.com/callback', $array['callBackUrl']);
    }
}
