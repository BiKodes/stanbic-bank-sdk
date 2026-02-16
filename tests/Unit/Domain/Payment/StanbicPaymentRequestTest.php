<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\Payment;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\Payment\CounterpartyAccount;
use Stanbic\SDK\Domain\ValueObject\CounterpartyAccountIdentification;
use Stanbic\SDK\Domain\ValueObject\OriginatorAccount;
use Stanbic\SDK\Domain\ValueObject\OriginatorIdentification;
use Stanbic\SDK\Domain\Payment\StanbicPaymentRequest;
use Stanbic\SDK\Domain\Payment\TransferTransactionInformation;

final class StanbicPaymentRequestTest extends TestCase
{
    public function testCreateStanbicPaymentRequest(): void
    {
        $account = new CounterpartyAccount(
            identification: new CounterpartyAccountIdentification(identification: '0100004614423')
        );

        $tti = new TransferTransactionInformation(
            amount: 750.00,
            currency: 'KES',
            counterpartyAccount: $account,
            counterpartyName: 'John Doe'
        );

        $originator = new OriginatorAccount(
            new OriginatorIdentification(identification: '1234567890')
        );

        $request = new StanbicPaymentRequest(
            originatorAccount: $originator,
            transferTransactionInformation: $tti,
            callBackUrl: 'https://clientdomain.com/client/Callback',
            dbsReferenceId: 'DBS-222',
            txnNarrative: 'Stanbic transfer',
            requestedExecutionDate: '2026-02-16'
        );

        $this->assertSame('1234567890', $request->originatorAccount->identification->identification);
        $this->assertSame('https://clientdomain.com/client/Callback', $request->callBackUrl);
        $this->assertSame('DBS-222', $request->dbsReferenceId);
        $this->assertSame('Stanbic transfer', $request->txnNarrative);
    }

    public function testFromArray(): void
    {
        $data = [
            'originatorAccount' => [
                'identification' => [
                    'identification' => '2222222222',
                ],
            ],
            'dbsReferenceId' => 'DBS-333',
            'txnNarrative' => 'From array',
            'requestedExecutionDate' => '2026-02-17',
            'transferTransactionInformation' => [
                'instructedAmount' => [
                    'amount' => 50.00,
                    'currencyCode' => 'KES',
                ],
                'counterpartyAccount' => [
                    'identification' => [
                        'identification' => '0100004614423',
                    ],
                ],
                'counterparty' => [
                    'name' => 'Array User',
                ],
            ],
        ];

        $request = StanbicPaymentRequest::fromArray($data);

        $this->assertSame('2222222222', $request->originatorAccount->identification->identification);
        $this->assertSame('DBS-333', $request->dbsReferenceId);
        $this->assertSame('From array', $request->txnNarrative);
        $this->assertSame('Array User', $request->transferTransactionInformation->counterparty?->name);
    }

    public function testFromArrayWithOriginatorAccountNumberFallback(): void
    {
        $data = [
            'originator_account_number' => '4444444444',
            'dbs_reference_id' => 'DBS-555',
            'txn_narrative' => 'Fallback originator',
            'requested_execution_date' => '2026-02-21',
            'transfer_transaction_information' => [
                'amount' => 75.00,
                'currency' => 'KES',
                'counterparty_account' => [
                    'identification' => [
                        'identification' => '0100004614423',
                    ],
                ],
                'counterparty_name' => 'Fallback User',
            ],
        ];

        $request = StanbicPaymentRequest::fromArray($data);

        $this->assertSame('4444444444', $request->originatorAccount->identification->identification);
        $this->assertSame('Fallback User', $request->transferTransactionInformation->counterpartyName);
    }

    public function testToArray(): void
    {
        $account = new CounterpartyAccount(
            identification: new CounterpartyAccountIdentification(identification: '0100004614423')
        );

        $tti = new TransferTransactionInformation(
            amount: 400.00,
            currency: 'USD',
            counterpartyAccount: $account,
            counterpartyName: 'Array User'
        );

        $originator = new OriginatorAccount(
            new OriginatorIdentification(identification: '3333333333')
        );

        $request = new StanbicPaymentRequest(
            originatorAccount: $originator,
            transferTransactionInformation: $tti,
            callBackUrl: null,
            dbsReferenceId: 'DBS-444',
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

        $this->assertSame('3333333333', $identification['identification']);
        $this->assertSame('DBS-444', $array['dbsReferenceId']);
        $this->assertSame('To array', $array['txnNarrative']);
        $this->assertIsArray($array['transferTransactionInformation']);
        /** @var array<string, mixed> $tti */
        $tti = $array['transferTransactionInformation'];
        $this->assertIsArray($tti['counterparty']);
        /** @var array<string, mixed> $counterparty */
        $counterparty = $tti['counterparty'];
        $this->assertSame('Array User', $counterparty['name']);
    }

    public function testFromArrayWithObjectInputs(): void
    {
        $originator = new OriginatorAccount(
            new OriginatorIdentification(identification: 'ORIG-123')
        );

        $account = new CounterpartyAccount(
            identification: new CounterpartyAccountIdentification(identification: '0100004614423')
        );

        $tti = new TransferTransactionInformation(
            amount: 125.00,
            currency: 'KES',
            counterpartyAccount: $account,
            counterpartyName: 'Object User'
        );

        $request = StanbicPaymentRequest::fromArray([
            'originatorAccount' => $originator,
            'transferTransactionInformation' => $tti,
            'callBackUrl' => 'https://clientdomain.com/stanbic',
            'dbsReferenceId' => 'DBS-OBJ',
            'txnNarrative' => 'Object input',
            'requestedExecutionDate' => '2026-02-26',
            'endToEndId' => 'E2E-STAN',
            'chargeBearer' => 'OUR',
        ]);

        $this->assertSame('ORIG-123', $request->originatorAccount->identification->identification);
        $this->assertSame('Object User', $request->transferTransactionInformation->counterpartyName);
        $this->assertSame('https://clientdomain.com/stanbic', $request->callBackUrl);
        $this->assertSame('E2E-STAN', $request->endToEndId);
        $this->assertSame('OUR', $request->chargeBearer);
    }

    public function testToArrayIncludesCallbackAndOptionals(): void
    {
        $account = new CounterpartyAccount(
            identification: new CounterpartyAccountIdentification(identification: '0100004614423')
        );

        $tti = new TransferTransactionInformation(
            amount: 225.00,
            currency: 'USD',
            counterpartyAccount: $account,
            counterpartyName: 'Callback User'
        );

        $originator = new OriginatorAccount(
            new OriginatorIdentification(identification: 'ORIG-456')
        );

        $request = new StanbicPaymentRequest(
            originatorAccount: $originator,
            transferTransactionInformation: $tti,
            callBackUrl: 'https://clientdomain.com/callback',
            dbsReferenceId: 'DBS-CB',
            txnNarrative: 'Callback',
            requestedExecutionDate: '2026-02-27',
            endToEndId: 'E2E-CB',
            chargeBearer: 'SHA'
        );

        $array = $request->toArray();

        $this->assertSame('https://clientdomain.com/callback', $array['callBackUrl']);
        $this->assertSame('E2E-CB', $array['endToEndId']);
        $this->assertSame('SHA', $array['chargeBearer']);
    }
    public function testFromArrayWithMissingTti(): void
    {
        $data = [
            'originatorAccount' => [
                'identification' => [
                    'identification' => '5555555555',
                ],
            ],
            'dbs_reference_id' => 'DBS-NO-TTI-STAN',
            'txn_narrative' => 'Missing TTI Stanbic',
            'requested_execution_date' => '2026-03-09',
        ];

        $request = StanbicPaymentRequest::fromArray($data);

        $this->assertSame('5555555555', $request->originatorAccount->identification->identification);
        $this->assertSame(0.0, $request->transferTransactionInformation->amount);
    }

    public function testFromArrayWithEmptyOriginatorAndAccountNumber(): void
    {
        $data = [
            'originatorAccount' => [],
            'originator_account_number' => '6666666666',
            'dbs_reference_id' => 'DBS-EMPTY-ORIG-STAN',
            'txn_narrative' => 'Empty originator Stanbic',
            'requested_execution_date' => '2026-03-10',
            'transferTransactionInformation' => [
                'amount' => 95.00,
                'currency' => 'KES',
            ],
        ];

        $request = StanbicPaymentRequest::fromArray($data);

        $this->assertSame('6666666666', $request->originatorAccount->identification->identification);
    }

    public function testFromArrayWithCamelCaseOriginatorAccountNumber(): void
    {
        $data = [
            'originatorAccount' => [],
            'originatorAccountNumber' => '7777777777',
            'dbs_reference_id' => 'DBS-CAMEL-STAN',
            'txn_narrative' => 'CamelCase account',
            'requested_execution_date' => '2026-03-14',
            'transferTransactionInformation' => [
                'amount' => 85.00,
                'currency' => 'KES',
            ],
        ];

        $request = StanbicPaymentRequest::fromArray($data);

        $this->assertSame('7777777777', $request->originatorAccount->identification->identification);
    }
}
