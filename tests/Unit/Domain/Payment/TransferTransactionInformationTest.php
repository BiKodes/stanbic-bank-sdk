<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\Payment;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\Payment\CounterpartyAccount;
use Stanbic\SDK\Domain\Payment\Counterparty;
use Stanbic\SDK\Domain\Payment\RemittanceInformation;
use Stanbic\SDK\Domain\Payment\TransferTransactionInformation;
use Stanbic\SDK\Domain\ValueObject\MobileMoneyMno;

final class TransferTransactionInformationTest extends TestCase
{
    public function testCreateTransferTransactionInformation(): void
    {
        $account = new CounterpartyAccount(
            recipientBankAcctNo: '01008747142',
            recipientBankCode: '07000'
        );

        $remittance = new RemittanceInformation(
            unstructured: 'Invoice 123'
        );

        $counterparty = new Counterparty(
            name: 'John Doe',
            account: null
        );

        $info = new TransferTransactionInformation(
            amount: 500.00,
            currency: 'KES',
            counterpartyAccount: $account,
            counterparty: $counterparty,
            remittanceInformation: $remittance,
            paymentPurpose: 'Goods'
        );

        $this->assertSame(500.00, $info->amount);
        $this->assertSame('KES', $info->currency);
        $this->assertSame('John Doe', $info->counterparty?->name);
        $this->assertSame('Invoice 123', $info->remittanceInformation?->unstructured);
        $this->assertSame('Goods', $info->paymentPurpose);
    }

    public function testFromArrayWithInstructedAmount(): void
    {
        $data = [
            'instructedAmount' => [
                'amount' => '750.50',
                'currencyCode' => 'USD',
            ],
            'counterpartyAccount' => [
                'recipientBankAcctNo' => '1234567890',
                'recipientBankCode' => '01000',
            ],
            'counterparty' => [
                'name' => 'Jane Doe',
            ],
        ];

        $info = TransferTransactionInformation::fromArray($data);

        $this->assertSame(750.50, $info->amount);
        $this->assertSame('USD', $info->currency);
        $this->assertSame('Jane Doe', $info->counterparty?->name);
    }

    public function testFromArrayWithFlatAmount(): void
    {
        $data = [
            'amount' => 1200.00,
            'currency' => 'KES',
            'counterpartyAccount' => [
                'recipientBankAcctNo' => '9999999999',
                'recipientBankCode' => '03000',
            ],
            'counterpartyName' => 'Flat Amount',
        ];

        $info = TransferTransactionInformation::fromArray($data);

        $this->assertSame(1200.00, $info->amount);
        $this->assertSame('KES', $info->currency);
        $this->assertSame('Flat Amount', $info->counterpartyName);
    }

    public function testToArrayUsesInstructedAmount(): void
    {
        $account = new CounterpartyAccount(
            recipientBankAcctNo: '7777777777',
            recipientBankCode: '04000'
        );

        $info = new TransferTransactionInformation(
            amount: 250.00,
            currency: 'EUR',
            counterpartyAccount: $account,
            counterpartyName: 'Array User'
        );

        /** @var array<string, mixed> $array */
        $array = $info->toArray();

        $this->assertSame(
            ['amount' => 250.00, 'currencyCode' => 'EUR'],
            $array['instructedAmount']
        );
        $this->assertIsArray($array['counterparty']);
        /** @var array<string, mixed> $counterparty */
        $counterparty = $array['counterparty'];
        $this->assertSame('Array User', $counterparty['name']);
    }

    public function testRoundTripSerialization(): void
    {
        $account = new CounterpartyAccount(
            recipientBankAcctNo: '3333333333',
            recipientBankCode: '05000'
        );

        $original = new TransferTransactionInformation(
            amount: 100.00,
            currency: 'GBP',
            counterpartyAccount: $account,
            counterpartyName: 'Round Trip'
        );

        $restored = TransferTransactionInformation::fromArray($original->toArray());

        $this->assertSame($original->amount, $restored->amount);
        $this->assertSame($original->currency, $restored->currency);
        $this->assertSame($original->counterpartyName, $restored->counterpartyName);
    }

    public function testFromArrayWithInstructedAmountValueKey(): void
    {
        $data = [
            'instructedAmount' => [
                'value' => 999.99,
                'currencyCode' => 'USD',
            ],
            'counterpartyAccount' => [
                'recipientBankAcctNo' => '1111111111',
                'recipientBankCode' => '01000',
            ],
        ];

        $info = TransferTransactionInformation::fromArray($data);

        $this->assertSame(999.99, $info->amount);
        $this->assertSame('USD', $info->currency);
    }

    public function testFromArrayWithInstructedAmountCreditCurrency(): void
    {
        $data = [
            'instructedAmount' => [
                'amount' => 500.00,
                'creditCurrency' => 'EUR',
            ],
            'counterpartyAccount' => [
                'recipientBankAcctNo' => '2222222222',
                'recipientBankCode' => '02000',
            ],
        ];

        $info = TransferTransactionInformation::fromArray($data);

        $this->assertSame(500.00, $info->amount);
        $this->assertSame('EUR', $info->currency);
        $this->assertSame('creditCurrency', $info->instructedCurrencyKey);
    }

    public function testFromArrayWithSnakeCaseKeys(): void
    {
        $data = [
            'instructed_amount' => [
                'amount' => 300.00,
                'currency' => 'KES',
            ],
            'counterparty_account' => [
                'recipientBankAcctNo' => '3333333333',
                'recipientBankCode' => '03000',
            ],
            'counterparty_name' => 'Snake Case User',
            'payment_purpose' => 'Services',
            'end_to_end_identification' => 'E2E-123',
        ];

        $info = TransferTransactionInformation::fromArray($data);

        $this->assertSame(300.00, $info->amount);
        $this->assertSame('KES', $info->currency);
        $this->assertSame('Snake Case User', $info->counterpartyName);
        $this->assertSame('Services', $info->paymentPurpose);
        $this->assertSame('E2E-123', $info->endToEndIdentification);
    }

    public function testFromArrayWithCounterpartyAccountObject(): void
    {
        $account = new CounterpartyAccount(
            recipientBankAcctNo: '4444444444',
            recipientBankCode: '04000'
        );

        $data = [
            'amount' => 150.00,
            'currency' => 'USD',
            'counterpartyAccount' => $account,
        ];

        $info = TransferTransactionInformation::fromArray($data);

        $this->assertSame($account, $info->counterpartyAccount);
    }

    public function testFromArrayWithCounterpartyObject(): void
    {
        $counterparty = new Counterparty(
            name: 'Object Counterparty'
        );

        $data = [
            'amount' => 200.00,
            'currency' => 'GBP',
            'counterpartyAccount' => [
                'recipientBankAcctNo' => '5555555555',
                'recipientBankCode' => '05000',
            ],
            'counterparty' => $counterparty,
        ];

        $info = TransferTransactionInformation::fromArray($data);

        $this->assertSame($counterparty, $info->counterparty);
        $this->assertSame('Object Counterparty', $info->counterpartyName);
    }

    public function testFromArrayWithRemittanceInformationObject(): void
    {
        $remittance = new RemittanceInformation(
            unstructured: 'Object Remittance'
        );

        $data = [
            'amount' => 350.00,
            'currency' => 'EUR',
            'counterpartyAccount' => [
                'recipientBankAcctNo' => '6666666666',
                'recipientBankCode' => '06000',
            ],
            'remittanceInformation' => $remittance,
        ];

        $info = TransferTransactionInformation::fromArray($data);

        $this->assertSame($remittance, $info->remittanceInformation);
    }

    public function testFromArrayWithRemittanceInformationSnakeCase(): void
    {
        $data = [
            'amount' => 400.00,
            'currency' => 'KES',
            'counterpartyAccount' => [
                'recipientBankAcctNo' => '7777777777',
                'recipientBankCode' => '07000',
            ],
            'remittance_information' => [
                'unstructured' => 'Snake case remittance',
            ],
        ];

        $info = TransferTransactionInformation::fromArray($data);

        $this->assertNotNull($info->remittanceInformation);
        $this->assertSame('Snake case remittance', $info->remittanceInformation->unstructured);
    }

    public function testFromArrayWithMobileMoneyMnoObject(): void
    {
        $mno = new MobileMoneyMno(
            name: 'MPESA'
        );

        $data = [
            'amount' => 450.00,
            'currency' => 'KES',
            'counterpartyAccount' => [
                'recipientBankAcctNo' => '8888888888',
                'recipientBankCode' => '08000',
            ],
            'mobileMoneyMno' => $mno,
        ];

        $info = TransferTransactionInformation::fromArray($data);

        $this->assertSame($mno, $info->mobileMoneyMno);
    }

    public function testFromArrayWithMobileMoneyMnoArray(): void
    {
        $data = [
            'amount' => 500.00,
            'currency' => 'KES',
            'counterpartyAccount' => [
                'recipientBankAcctNo' => '9999999999',
                'recipientBankCode' => '09000',
            ],
            'mobileMoneyMno' => [
                'name' => 'AIRTEL',
            ],
        ];

        $info = TransferTransactionInformation::fromArray($data);

        $this->assertNotNull($info->mobileMoneyMno);
        $this->assertSame('AIRTEL', $info->mobileMoneyMno->name);
    }

    public function testFromArrayWithMobileMoneyMnoSnakeCase(): void
    {
        $data = [
            'amount' => 550.00,
            'currency' => 'KES',
            'counterpartyAccount' => [
                'recipientBankAcctNo' => '1010101010',
                'recipientBankCode' => '10000',
            ],
            'mobile_money_mno' => [
                'name' => 'TKASH',
            ],
        ];

        $info = TransferTransactionInformation::fromArray($data);

        $this->assertNotNull($info->mobileMoneyMno);
        $this->assertSame('TKASH', $info->mobileMoneyMno->name);
    }

    public function testFromArrayWithCounterpartyNameDerivesFromCounterparty(): void
    {
        $data = [
            'amount' => 600.00,
            'currency' => 'USD',
            'counterpartyAccount' => [
                'recipientBankAcctNo' => '1212121212',
                'recipientBankCode' => '11000',
            ],
            'counterparty' => [
                'name' => 'Derived Name',
            ],
        ];

        $info = TransferTransactionInformation::fromArray($data);

        $this->assertSame('Derived Name', $info->counterpartyName);
    }

    public function testFromArrayWithInstructedCurrencyKeyDefault(): void
    {
        $data = [
            'instructedAmount' => [
                'amount' => 700.00,
                'currencyCode' => 'GBP',
            ],
            'counterpartyAccount' => [
                'recipientBankAcctNo' => '1313131313',
                'recipientBankCode' => '12000',
            ],
        ];

        $info = TransferTransactionInformation::fromArray($data);

        $this->assertSame('currencyCode', $info->instructedCurrencyKey);
    }

    public function testFromArrayWithDefaultCurrencyWhenMissing(): void
    {
        $data = [
            'instructedAmount' => [
                'amount' => 800.00,
            ],
            'counterpartyAccount' => [
                'recipientBankAcctNo' => '1414141414',
                'recipientBankCode' => '13000',
            ],
        ];

        $info = TransferTransactionInformation::fromArray($data);

        $this->assertSame('KES', $info->currency);
    }

    public function testFromArrayWithInstructedCurrency(): void
    {
        $data = [
            'instructedCurrency' => 'CHF',
            'counterpartyAccount' => [
                'recipientBankAcctNo' => '1515151515',
                'recipientBankCode' => '14000',
            ],
        ];

        $info = TransferTransactionInformation::fromArray($data);

        $this->assertSame('CHF', $info->currency);
    }

    public function testToArrayWithCounterparty(): void
    {
        $account = new CounterpartyAccount(
            recipientBankAcctNo: '1616161616',
            recipientBankCode: '15000'
        );

        $counterparty = new Counterparty(
            name: 'To Array Counterparty'
        );

        $info = new TransferTransactionInformation(
            amount: 900.00,
            currency: 'EUR',
            counterpartyAccount: $account,
            counterparty: $counterparty
        );

        $array = $info->toArray();

        $this->assertIsArray($array['counterparty']);
        $this->assertArrayNotHasKey('counterpartyName', $array);
    }

    public function testToArrayWithCounterpartyName(): void
    {
        $account = new CounterpartyAccount(
            recipientBankAcctNo: '1717171717',
            recipientBankCode: '16000'
        );

        $info = new TransferTransactionInformation(
            amount: 1000.00,
            currency: 'USD',
            counterpartyAccount: $account,
            counterpartyName: 'Name Only'
        );

        $array = $info->toArray();

        $this->assertIsArray($array['counterparty']);
        /** @var array<string, mixed> $counterparty */
        $counterparty = $array['counterparty'];
        $this->assertSame('Name Only', $counterparty['name']);
    }

    public function testToArrayWithCreditCurrency(): void
    {
        $account = new CounterpartyAccount(
            recipientBankAcctNo: '1818181818',
            recipientBankCode: '17000'
        );

        $info = new TransferTransactionInformation(
            amount: 1100.00,
            currency: 'JPY',
            counterpartyAccount: $account,
            instructedCurrencyKey: 'creditCurrency'
        );

        $array = $info->toArray();

        $this->assertIsArray($array['instructedAmount']);
        /** @var array<string, mixed> $instructedAmount */
        $instructedAmount = $array['instructedAmount'];
        $this->assertSame(1100.00, $instructedAmount['amount']);
        $this->assertSame('JPY', $instructedAmount['creditCurrency']);
        $this->assertArrayNotHasKey('currencyCode', $instructedAmount);
    }

    public function testToArrayWithAllOptionalFields(): void
    {
        $account = new CounterpartyAccount(
            recipientBankAcctNo: '1919191919',
            recipientBankCode: '18000'
        );

        $remittance = new RemittanceInformation(
            unstructured: 'All fields'
        );

        $mno = new MobileMoneyMno(
            name: 'MPESA'
        );

        $info = new TransferTransactionInformation(
            amount: 1200.00,
            currency: 'KES',
            counterpartyAccount: $account,
            counterpartyName: 'Complete',
            remittanceInformation: $remittance,
            paymentPurpose: 'Complete payment',
            mobileMoneyMno: $mno,
            endToEndIdentification: 'E2E-COMPLETE'
        );

        $array = $info->toArray();

        $this->assertArrayHasKey('remittanceInformation', $array);
        $this->assertArrayHasKey('paymentPurpose', $array);
        $this->assertArrayHasKey('mobileMoneyMno', $array);
        $this->assertArrayHasKey('endToEndIdentification', $array);
        $this->assertSame('Complete payment', $array['paymentPurpose']);
        $this->assertSame('E2E-COMPLETE', $array['endToEndIdentification']);
    }

    public function testToArrayOmitsNullOptionalFields(): void
    {
        $account = new CounterpartyAccount(
            recipientBankAcctNo: '2020202020',
            recipientBankCode: '19000'
        );

        $info = new TransferTransactionInformation(
            amount: 1300.00,
            currency: 'EUR',
            counterpartyAccount: $account
        );

        $array = $info->toArray();

        $this->assertArrayNotHasKey('counterparty', $array);
        $this->assertArrayNotHasKey('remittanceInformation', $array);
        $this->assertArrayNotHasKey('paymentPurpose', $array);
        $this->assertArrayNotHasKey('mobileMoneyMno', $array);
        $this->assertArrayNotHasKey('endToEndIdentification', $array);
    }

    public function testFromArrayWithEmptyCounterpartyAccount(): void
    {
        $data = [
            'amount' => 1400.00,
            'currency' => 'USD',
        ];

        $info = TransferTransactionInformation::fromArray($data);

        $this->assertInstanceOf(CounterpartyAccount::class, $info->counterpartyAccount);
    }
}
