<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\Payment;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\Payment\CounterpartyAccount;
use Stanbic\SDK\Domain\ValueObject\CounterpartyAccountIdentification;
use Stanbic\SDK\Domain\ValueObject\OriginatorAccount;
use Stanbic\SDK\Domain\ValueObject\OriginatorIdentification;
use Stanbic\SDK\Domain\Payment\PesalinkPaymentRequest;
use Stanbic\SDK\Domain\Payment\TransferTransactionInformation;

final class PesalinkPaymentRequestTest extends TestCase
{
    public function testCreatePesalinkPaymentRequest(): void
    {
        $counterpartyId = new CounterpartyAccountIdentification(
            recipientBankAcctNo: '01008747142',
            recipientBankCode: '07000'
        );

        $account = new CounterpartyAccount(
            identification: $counterpartyId
        );

        $tti = new TransferTransactionInformation(
            amount: 500.00,
            currency: 'KES',
            counterpartyAccount: $account,
            counterpartyName: 'John Doe'
        );

        $originator = new OriginatorAccount(
            new OriginatorIdentification(mobileNumber: '254700000000')
        );

        $request = new PesalinkPaymentRequest(
            originatorAccount: $originator,
            transferTransactionInformation: $tti,
            sendMoneyTo: 'ACCOUNT.NUMBER',
            callBackUrl: 'https://clientdomain.com/client/Callback',
            dbsReferenceId: 'DBS-123',
            txnNarrative: 'Payment',
            requestedExecutionDate: '2026-02-16'
        );

        $this->assertSame('254700000000', $request->originatorAccount->identification->mobileNumber);
        $this->assertSame('ACCOUNT.NUMBER', $request->sendMoneyTo);
        $this->assertSame('DBS-123', $request->dbsReferenceId);
        $this->assertSame('Payment', $request->txnNarrative);
        $this->assertSame('2026-02-16', $request->requestedExecutionDate);
    }

    public function testFromArrayWithCamelCase(): void
    {
        $data = [
            'originatorAccount' => [
                'identification' => [
                    'mobileNumber' => '254711111111',
                ],
            ],
            'dbsReferenceId' => 'DBS-456',
            'txnNarrative' => 'Invoice payment',
            'requestedExecutionDate' => '2026-02-16',
            'sendMoneyTo' => 'ACCOUNT.NUMBER',
            'callBackUrl' => 'https://clientdomain.com/client/Callback',
            'transferTransactionInformation' => [
                'instructedAmount' => [
                    'amount' => 100.00,
                    'currencyCode' => 'KES',
                ],
                'counterpartyAccount' => [
                    'identification' => [
                        'recipientBankAcctNo' => '1234567890',
                        'recipientBankCode' => '01000',
                    ],
                ],
                'counterparty' => [
                    'name' => 'Jane Doe',
                ],
            ],
        ];

        $request = PesalinkPaymentRequest::fromArray($data);

        $this->assertSame('254711111111', $request->originatorAccount->identification->mobileNumber);
        $this->assertSame('DBS-456', $request->dbsReferenceId);
        $this->assertSame('Invoice payment', $request->txnNarrative);
        $this->assertSame('2026-02-16', $request->requestedExecutionDate);
        $this->assertSame('Jane Doe', $request->transferTransactionInformation->counterparty?->name);
    }

    public function testFromArrayWithSnakeCase(): void
    {
        $data = [
            'originator_mobile_number' => '254722222222',
            'dbs_reference_id' => 'DBS-789',
            'txn_narrative' => 'Snake payment',
            'requested_execution_date' => '2026-02-17',
            'transfer_transaction_information' => [
                'instructed_amount' => [
                    'amount' => 200.00,
                    'currencyCode' => 'KES',
                ],
                'counterparty_account' => [
                    'identification' => [
                        'recipient_bank_acct_no' => '9999999999',
                        'recipient_bank_code' => '02000',
                    ],
                ],
                'counterparty_name' => 'Snake User',
            ],
        ];

        $request = PesalinkPaymentRequest::fromArray($data);

        $this->assertSame('254722222222', $request->originatorAccount->identification->mobileNumber);
        $this->assertSame('DBS-789', $request->dbsReferenceId);
        $this->assertSame('Snake payment', $request->txnNarrative);
        $this->assertSame('2026-02-17', $request->requestedExecutionDate);
        $this->assertSame('Snake User', $request->transferTransactionInformation->counterpartyName);
    }

    public function testFromArrayWithSnakeCaseCallbacks(): void
    {
        $data = [
            'originator_mobile_number' => '254799000000',
            'send_money_to' => 'ALIAS.001',
            'call_back_url' => 'https://clientdomain.com/callback',
            'dbs_reference_id' => 'DBS-900',
            'txn_narrative' => 'Callback test',
            'requested_execution_date' => '2026-02-20',
            'transfer_transaction_information' => [
                'instructed_amount' => [
                    'amount' => 45.00,
                    'currencyCode' => 'KES',
                ],
                'counterparty_account' => [
                    'recipient_bank_acct_no' => '1111111111',
                    'recipient_bank_code' => '03000',
                ],
            ],
        ];

        $request = PesalinkPaymentRequest::fromArray($data);

        $this->assertSame('254799000000', $request->originatorAccount->identification->mobileNumber);
        $this->assertSame('ALIAS.001', $request->sendMoneyTo);
        $this->assertSame('https://clientdomain.com/callback', $request->callBackUrl);
    }

    public function testToArray(): void
    {
        $counterpartyId = new CounterpartyAccountIdentification(
            recipientBankAcctNo: '8888888888',
            recipientBankCode: '03000'
        );

        $account = new CounterpartyAccount(
            identification: $counterpartyId
        );

        $tti = new TransferTransactionInformation(
            amount: 300.00,
            currency: 'USD',
            counterpartyAccount: $account,
            counterpartyName: 'Array User'
        );

        $originator = new OriginatorAccount(
            new OriginatorIdentification(mobileNumber: '254733333333')
        );

        $request = new PesalinkPaymentRequest(
            originatorAccount: $originator,
            transferTransactionInformation: $tti,
            sendMoneyTo: null,
            callBackUrl: null,
            dbsReferenceId: 'DBS-111',
            txnNarrative: 'Array payment',
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

        $this->assertSame('254733333333', $identification['mobileNumber']);
        $this->assertSame('DBS-111', $array['dbsReferenceId']);
        $this->assertSame('Array payment', $array['txnNarrative']);
        $this->assertSame('2026-02-18', $array['requestedExecutionDate']);
        $this->assertIsArray($array['transferTransactionInformation']);
    }

    public function testFromArrayWithMissingTti(): void
    {
        $data = [
            'originatorAccount' => [
                'identification' => [
                    'mobileNumber' => '254700777777',
                ],
            ],
            'dbs_reference_id' => 'DBS-NO-TTI',
            'txn_narrative' => 'Missing TTI',
            'requested_execution_date' => '2026-03-06',
        ];

        $request = PesalinkPaymentRequest::fromArray($data);

        $this->assertSame('254700777777', $request->originatorAccount->identification->mobileNumber);
        $this->assertSame(0.0, $request->transferTransactionInformation->amount);
    }

    public function testFromArrayWithEmptyOriginatorAndMobileNumber(): void
    {
        $data = [
            'originatorAccount' => [],
            'originator_mobile_number' => '254700888888',
            'dbs_reference_id' => 'DBS-EMPTY-ORIG-PESA',
            'txn_narrative' => 'Empty originator Pesalink',
            'requested_execution_date' => '2026-03-07',
            'transferTransactionInformation' => [
                'amount' => 90.00,
                'currency' => 'KES',
            ],
        ];

        $request = PesalinkPaymentRequest::fromArray($data);

        $this->assertSame('254700888888', $request->originatorAccount->identification->mobileNumber);
    }

    public function testFromArrayWithObjectInputs(): void
    {
        $originator = new OriginatorAccount(
            new OriginatorIdentification(mobileNumber: '254700123123')
        );

        $account = new CounterpartyAccount(
            identification: new CounterpartyAccountIdentification(
                recipientBankAcctNo: '1234567890',
                recipientBankCode: '01000'
            )
        );

        $tti = new TransferTransactionInformation(
            amount: 150.00,
            currency: 'KES',
            counterpartyAccount: $account,
            counterpartyName: 'Object User'
        );

        $data = [
            'originatorAccount' => $originator,
            'transferTransactionInformation' => $tti,
            'dbsReferenceId' => 'DBS-OBJ',
            'txnNarrative' => 'Object input',
            'requestedExecutionDate' => '2026-02-22',
            'endToEndId' => 'E2E-OBJ',
            'chargeBearer' => 'SHA',
        ];

        $request = PesalinkPaymentRequest::fromArray($data);

        $this->assertSame('254700123123', $request->originatorAccount->identification->mobileNumber);
        $this->assertSame('Object User', $request->transferTransactionInformation->counterpartyName);
        $this->assertSame('E2E-OBJ', $request->endToEndId);
        $this->assertSame('SHA', $request->chargeBearer);
    }

    public function testToArrayIncludesOptionalFields(): void
    {
        $account = new CounterpartyAccount(
            identification: new CounterpartyAccountIdentification(
                recipientBankAcctNo: '7777777777',
                recipientBankCode: '04000'
            )
        );

        $tti = new TransferTransactionInformation(
            amount: 200.00,
            currency: 'USD',
            counterpartyAccount: $account,
            counterpartyName: 'Optional User'
        );

        $originator = new OriginatorAccount(
            new OriginatorIdentification(mobileNumber: '254700999999')
        );

        $request = new PesalinkPaymentRequest(
            originatorAccount: $originator,
            transferTransactionInformation: $tti,
            sendMoneyTo: 'PHONE',
            callBackUrl: 'https://clientdomain.com/callback',
            dbsReferenceId: 'DBS-OPT',
            txnNarrative: 'Optional data',
            requestedExecutionDate: '2026-02-23',
            endToEndId: 'E2E-OPT',
            chargeBearer: 'BEN'
        );

        $array = $request->toArray();

        $this->assertSame('PHONE', $array['sendMoneyTo']);
        $this->assertSame('https://clientdomain.com/callback', $array['callBackUrl']);
        $this->assertSame('E2E-OPT', $array['endToEndId']);
        $this->assertSame('BEN', $array['chargeBearer']);
    }
}
