<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\Payment;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\Payment\Counterparty;
use Stanbic\SDK\Domain\Payment\CounterpartyAccount;
use Stanbic\SDK\Domain\ValueObject\CounterpartyAccountIdentification;
use Stanbic\SDK\Domain\ValueObject\OriginatorAccount;
use Stanbic\SDK\Domain\ValueObject\OriginatorIdentification;
use Stanbic\SDK\Domain\Payment\SwiftTransferRequest;
use Stanbic\SDK\Domain\ValueObject\Schedule;
use Stanbic\SDK\Domain\Payment\TransferTransactionInformation;

final class SwiftTransferRequestTest extends TestCase
{
    public function testCreateSwiftTransferRequest(): void
    {
        $account = new CounterpartyAccount(
            identification: new CounterpartyAccountIdentification(
                identification: '9877665554',
                correspondentBank: 'UGBAUGKAXXX',
                beneficiaryBank: 'SW-CERBUGKA'
            )
        );

        $tti = new TransferTransactionInformation(
            amount: 500.00,
            currency: 'KES',
            counterpartyAccount: $account,
            counterpartyName: 'John Doe'
        );

        $originator = new OriginatorAccount(
            new OriginatorIdentification(identification: '0100001536723', debitCurrency: 'KES')
        );

        $request = new SwiftTransferRequest(
            originatorAccount: $originator,
            transferTransactionInformation: $tti,
            counterparty: null,
            schedule: null,
            callBackUrl: null,
            dbsReferenceId: 'DBS-303',
            txnNarrative: 'SWIFT',
            requestedExecutionDate: '2026-02-16'
        );

        $this->assertSame('DBS-303', $request->dbsReferenceId);
        $this->assertNull($request->counterparty);
    }

    public function testFromArrayWithCounterparty(): void
    {
        $data = [
            'originatorAccount' => [
                'identification' => [
                    'identification' => '0100001536723',
                    'debitCurrency' => 'KES',
                ],
            ],
            'dbsReferenceId' => 'DBS-404',
            'txnNarrative' => 'SWIFT array',
            'requestedExecutionDate' => '2026-02-17',
            'transferTransactionInformation' => [
                'amount' => 100.00,
                'currency' => 'KES',
                'counterpartyAccount' => [
                    'identification' => [
                        'identification' => '9877665554',
                        'correspondentBank' => 'UGBAUGKAXXX',
                        'beneficiaryBank' => 'SW-CERBUGKA',
                    ],
                ],
                'counterpartyName' => 'Jane Doe',
            ],
            'counterparty' => [
                'name' => 'Jane Doe',
                'account' => [
                    'recipientBankAcctNo' => '1234567890',
                    'recipientBankCode' => '01000',
                ],
            ],
            'schedule' => [
                'transferFrequency' => 'MONTHLY',
                'on' => '12',
            ],
        ];

        $request = SwiftTransferRequest::fromArray($data);

        $this->assertSame('DBS-404', $request->dbsReferenceId);
        $this->assertSame('0100001536723', $request->originatorAccount->identification->identification);
        $this->assertSame('Jane Doe', $request->counterparty?->name);
        $this->assertSame('MONTHLY', $request->schedule?->transferFrequency);
    }

    public function testToArrayOmitsCounterparty(): void
    {
        $account = new CounterpartyAccount(
            identification: new CounterpartyAccountIdentification(
                identification: '9877665554',
                correspondentBank: 'UGBAUGKAXXX',
                beneficiaryBank: 'SW-CERBUGKA'
            )
        );

        $tti = new TransferTransactionInformation(
            amount: 300.00,
            currency: 'USD',
            counterpartyAccount: $account,
            counterpartyName: 'Array User'
        );

        $originator = new OriginatorAccount(
            new OriginatorIdentification(identification: '0100001536723', debitCurrency: 'KES')
        );

        $request = new SwiftTransferRequest(
            originatorAccount: $originator,
            transferTransactionInformation: $tti,
            counterparty: null,
            schedule: new Schedule(transferFrequency: 'DAILY'),
            callBackUrl: 'https://clientdomain.com/client/Callback',
            dbsReferenceId: 'DBS-505',
            txnNarrative: 'To array',
            requestedExecutionDate: '2026-02-18'
        );

        /** @var array<string, mixed> $array */
        $array = $request->toArray();

        $this->assertArrayNotHasKey('counterparty', $array);
        $this->assertSame('DBS-505', $array['dbsReferenceId']);
        $this->assertIsArray($array['schedule']);
        /** @var array<string, mixed> $schedule */
        $schedule = $array['schedule'];
        $this->assertSame('DAILY', $schedule['transferFrequency']);
    }

    public function testToArrayIncludesCounterparty(): void
    {
        $account = new CounterpartyAccount(
            identification: new CounterpartyAccountIdentification(
                identification: '9877665554',
                correspondentBank: 'UGBAUGKAXXX',
                beneficiaryBank: 'SW-CERBUGKA'
            )
        );

        $tti = new TransferTransactionInformation(
            amount: 400.00,
            currency: 'USD',
            counterpartyAccount: $account,
            counterpartyName: 'Array User'
        );

        $counterparty = new Counterparty(
            name: 'Array User',
            account: $account
        );

        $originator = new OriginatorAccount(
            new OriginatorIdentification(identification: '0100001536723')
        );

        $request = new SwiftTransferRequest(
            originatorAccount: $originator,
            transferTransactionInformation: $tti,
            counterparty: $counterparty,
            schedule: null,
            callBackUrl: null,
            dbsReferenceId: 'DBS-606',
            txnNarrative: 'To array',
            requestedExecutionDate: '2026-02-19'
        );

        /** @var array<string, mixed> $array */
        $array = $request->toArray();

        $this->assertIsArray($array['counterparty']);
        /** @var array<string, mixed> $counterpartyArray */
        $counterpartyArray = $array['counterparty'];
        $this->assertSame('Array User', $counterpartyArray['name']);
    }

    public function testFromArrayWithObjectInputs(): void
    {
        $originator = new OriginatorAccount(
            new OriginatorIdentification(identification: 'ORIG-901')
        );

        $account = new CounterpartyAccount(
            identification: new CounterpartyAccountIdentification(
                identification: '9877665554'
            )
        );

        $tti = new TransferTransactionInformation(
            amount: 275.00,
            currency: 'KES',
            counterpartyAccount: $account,
            counterpartyName: 'Object User'
        );

        $schedule = new Schedule(transferFrequency: 'MONTHLY');

        $request = SwiftTransferRequest::fromArray([
            'originatorAccount' => $originator,
            'transferTransactionInformation' => $tti,
            'counterparty' => new Counterparty(name: 'Object User'),
            'schedule' => $schedule,
            'callBackUrl' => 'https://clientdomain.com/swift',
            'dbsReferenceId' => 'DBS-OBJ',
            'txnNarrative' => 'Obj',
            'requestedExecutionDate' => '2026-02-24',
            'endToEndId' => 'E2E-SWIFT',
            'chargeBearer' => 'BEN',
        ]);

        $this->assertSame('ORIG-901', $request->originatorAccount->identification->identification);
        $this->assertSame('Object User', $request->counterparty?->name);
        $this->assertSame('MONTHLY', $request->schedule?->transferFrequency);
        $this->assertSame('https://clientdomain.com/swift', $request->callBackUrl);
        $this->assertSame('E2E-SWIFT', $request->endToEndId);
        $this->assertSame('BEN', $request->chargeBearer);
    }

    public function testToArrayIncludesScheduleAndCallback(): void
    {
        $account = new CounterpartyAccount(
            identification: new CounterpartyAccountIdentification(
                identification: '9877665554'
            )
        );

        $tti = new TransferTransactionInformation(
            amount: 375.00,
            currency: 'USD',
            counterpartyAccount: $account,
            counterpartyName: 'Callback User'
        );

        $originator = new OriginatorAccount(
            new OriginatorIdentification(identification: 'ORIG-902')
        );

        $request = new SwiftTransferRequest(
            originatorAccount: $originator,
            transferTransactionInformation: $tti,
            counterparty: null,
            schedule: new Schedule(transferFrequency: 'DAILY'),
            callBackUrl: 'https://clientdomain.com/swift-cb',
            dbsReferenceId: 'DBS-SWIFT',
            txnNarrative: 'Callback',
            requestedExecutionDate: '2026-02-25'
        );

        $array = $request->toArray();

        $this->assertIsArray($array['schedule']);
        $this->assertSame('https://clientdomain.com/swift-cb', $array['callBackUrl']);
    }

    public function testFromArrayWithMissingOriginatorAndTti(): void
    {
        $data = [
            'dbs_reference_id' => 'DBS-NO-ORIG-TTI-SWIFT',
            'txn_narrative' => 'Missing originator and tti Swift',
            'requested_execution_date' => '2026-03-11',
        ];

        $request = SwiftTransferRequest::fromArray($data);

        $this->assertSame(0.0, $request->transferTransactionInformation->amount);
        $this->assertSame('DBS-NO-ORIG-TTI-SWIFT', $request->dbsReferenceId);
    }
}
