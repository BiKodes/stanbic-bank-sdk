<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\Payment;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\Payment\Counterparty;
use Stanbic\SDK\Domain\Payment\CounterpartyAccount;
use Stanbic\SDK\Domain\ValueObject\CounterpartyAccountIdentification;
use Stanbic\SDK\Domain\ValueObject\OriginatorAccount;
use Stanbic\SDK\Domain\ValueObject\OriginatorIdentification;
use Stanbic\SDK\Domain\Payment\RtgsTransferRequest;
use Stanbic\SDK\Domain\ValueObject\Schedule;
use Stanbic\SDK\Domain\Payment\TransferTransactionInformation;

final class RtgsTransferRequestTest extends TestCase
{
    public function testCreateRtgsTransferRequest(): void
    {
        $account = new CounterpartyAccount(
            identification: new CounterpartyAccountIdentification(
                identification: '9877665554',
                correspondentBank: 'UGBAUGKAXXX',
                beneficiaryBank: 'SW-CERBUGKA',
                beneficiaryChargeType: 'SHA'
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

        $request = new RtgsTransferRequest(
            originatorAccount: $originator,
            transferTransactionInformation: $tti,
            counterparty: null,
            schedule: null,
            callBackUrl: null,
            dbsReferenceId: 'DBS-707',
            txnNarrative: 'RTGS',
            requestedExecutionDate: '2026-02-16'
        );

        $this->assertSame('DBS-707', $request->dbsReferenceId);
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
            'dbsReferenceId' => 'DBS-808',
            'txnNarrative' => 'RTGS array',
            'requestedExecutionDate' => '2026-02-17',
            'transferTransactionInformation' => [
                'amount' => 100.00,
                'currency' => 'KES',
                'counterpartyAccount' => [
                    'identification' => [
                        'identification' => '9877665554',
                        'correspondentBank' => 'UGBAUGKAXXX',
                        'beneficiaryBank' => 'SW-CERBUGKA',
                        'beneficiaryChargeType' => 'SHA',
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

        $request = RtgsTransferRequest::fromArray($data);

        $this->assertSame('DBS-808', $request->dbsReferenceId);
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
                beneficiaryBank: 'SW-CERBUGKA',
                beneficiaryChargeType: 'SHA'
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

        $request = new RtgsTransferRequest(
            originatorAccount: $originator,
            transferTransactionInformation: $tti,
            counterparty: null,
            schedule: new Schedule(transferFrequency: 'DAILY'),
            callBackUrl: 'https://clientdomain.com/client/Callback',
            dbsReferenceId: 'DBS-909',
            txnNarrative: 'To array',
            requestedExecutionDate: '2026-02-18'
        );

        /** @var array<string, mixed> $array */
        $array = $request->toArray();

        $this->assertArrayNotHasKey('counterparty', $array);
        $this->assertSame('DBS-909', $array['dbsReferenceId']);
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
                beneficiaryBank: 'SW-CERBUGKA',
                beneficiaryChargeType: 'SHA'
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

        $request = new RtgsTransferRequest(
            originatorAccount: $originator,
            transferTransactionInformation: $tti,
            counterparty: $counterparty,
            schedule: null,
            callBackUrl: null,
            dbsReferenceId: 'DBS-010',
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
            new OriginatorIdentification(identification: 'ORIG-001')
        );

        $account = new CounterpartyAccount(
            identification: new CounterpartyAccountIdentification(
                identification: '9877665554'
            )
        );

        $tti = new TransferTransactionInformation(
            amount: 250.00,
            currency: 'KES',
            counterpartyAccount: $account,
            counterpartyName: 'Object User'
        );

        $schedule = new Schedule(transferFrequency: 'WEEKLY');

        $request = RtgsTransferRequest::fromArray([
            'originatorAccount' => $originator,
            'transferTransactionInformation' => $tti,
            'counterparty' => new Counterparty(name: 'Object User'),
            'schedule' => $schedule,
            'callBackUrl' => 'https://clientdomain.com/callback',
            'dbsReferenceId' => 'DBS-OBJ',
            'txnNarrative' => 'Obj',
            'requestedExecutionDate' => '2026-02-24',
            'endToEndId' => 'E2E-RTGS',
            'chargeBearer' => 'SHA',
        ]);

        $this->assertSame('ORIG-001', $request->originatorAccount->identification->identification);
        $this->assertSame('Object User', $request->counterparty?->name);
        $this->assertSame('WEEKLY', $request->schedule?->transferFrequency);
        $this->assertSame('https://clientdomain.com/callback', $request->callBackUrl);
        $this->assertSame('E2E-RTGS', $request->endToEndId);
        $this->assertSame('SHA', $request->chargeBearer);
    }

    public function testToArrayIncludesScheduleAndCallback(): void
    {
        $account = new CounterpartyAccount(
            identification: new CounterpartyAccountIdentification(
                identification: '9877665554'
            )
        );

        $tti = new TransferTransactionInformation(
            amount: 350.00,
            currency: 'USD',
            counterpartyAccount: $account,
            counterpartyName: 'Callback User'
        );

        $originator = new OriginatorAccount(
            new OriginatorIdentification(identification: 'ORIG-002')
        );

        $request = new RtgsTransferRequest(
            originatorAccount: $originator,
            transferTransactionInformation: $tti,
            counterparty: null,
            schedule: new Schedule(transferFrequency: 'DAILY'),
            callBackUrl: 'https://clientdomain.com/rtgs',
            dbsReferenceId: 'DBS-RTGS',
            txnNarrative: 'Schedule',
            requestedExecutionDate: '2026-02-25'
        );

        $array = $request->toArray();

        $this->assertIsArray($array['schedule']);
        $this->assertSame('https://clientdomain.com/rtgs', $array['callBackUrl']);
    }

    public function testFromArrayWithMissingOriginatorAndTti(): void
    {
        $data = [
            'dbs_reference_id' => 'DBS-NO-ORIG-TTI',
            'txn_narrative' => 'Missing originator and tti',
            'requested_execution_date' => '2026-03-08',
        ];

        $request = RtgsTransferRequest::fromArray($data);

        $this->assertSame(0.0, $request->transferTransactionInformation->amount);
        $this->assertSame('DBS-NO-ORIG-TTI', $request->dbsReferenceId);
    }
}
