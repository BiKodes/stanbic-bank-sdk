<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\Payment;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\Payment\CounterpartyAccount;
use Stanbic\SDK\Domain\ValueObject\CounterpartyAccountIdentification;

final class CounterpartyAccountTest extends TestCase
{
    public function testCreateCounterpartyAccount(): void
    {
        $identification = new CounterpartyAccountIdentification(
            recipientBankAcctNo: '01008747142',
            recipientBankCode: '07000',
            recipientMobileNo: '254700000000'
        );

        $account = new CounterpartyAccount(
            identification: $identification,
            recipientAccountName: 'John Doe',
            recipientBankName: 'Stanbic Bank'
        );

        $this->assertSame('01008747142', $account->identification?->recipientBankAcctNo);
        $this->assertSame('07000', $account->identification?->recipientBankCode);
        $this->assertSame('254700000000', $account->identification?->recipientMobileNo);
        $this->assertSame('John Doe', $account->recipientAccountName);
        $this->assertSame('Stanbic Bank', $account->recipientBankName);
    }

    public function testFromArrayWithCamelCase(): void
    {
        $data = [
            'identification' => [
                'recipientBankAcctNo' => '1234567890',
                'recipientBankCode' => '01000',
                'recipientMobileNo' => '254711111111',
            ],
        ];

        $account = CounterpartyAccount::fromArray($data);

        $this->assertSame('1234567890', $account->identification?->recipientBankAcctNo);
        $this->assertSame('01000', $account->identification?->recipientBankCode);
        $this->assertSame('254711111111', $account->identification?->recipientMobileNo);
    }

    public function testFromArrayWithSnakeCase(): void
    {
        $data = [
            'recipient_bank_acct_no' => '5555555555',
            'recipient_bank_code' => '02000',
            'recipient_account_name' => 'Snake Case',
            'recipient_bank_name' => 'Snake Bank',
        ];

        $account = CounterpartyAccount::fromArray($data);

        $this->assertSame('5555555555', $account->recipientBankAcctNo);
        $this->assertSame('02000', $account->recipientBankCode);
        $this->assertSame('Snake Case', $account->recipientAccountName);
        $this->assertSame('Snake Bank', $account->recipientBankName);
    }

    public function testToArray(): void
    {
        $identification = new CounterpartyAccountIdentification(
            recipientBankAcctNo: '8888888888',
            recipientBankCode: '03000'
        );

        $account = new CounterpartyAccount(
            identification: $identification,
            recipientAccountName: 'Array Name',
            recipientBankName: 'Array Bank'
        );

        /** @var array<string, mixed> $array */
        $array = $account->toArray();

        $this->assertIsArray($array['identification']);
        /** @var array<string, mixed> $identification */
        $identification = $array['identification'];
        $this->assertSame('8888888888', $identification['recipientBankAcctNo']);
        $this->assertSame('03000', $identification['recipientBankCode']);
    }

    public function testFromArrayWithIdentificationObject(): void
    {
        $identification = new CounterpartyAccountIdentification(
            recipientBankAcctNo: '1010101010',
            recipientBankCode: '04000'
        );

        $account = CounterpartyAccount::fromArray([
            'identification' => $identification,
        ]);

        $this->assertSame($identification, $account->identification);
    }

    public function testFromArrayWithEmptyIdentification(): void
    {
        $account = CounterpartyAccount::fromArray([
            'identification' => [],
        ]);

        $this->assertNull($account->identification);
    }

    public function testToArrayWithoutIdentificationUsesBankFields(): void
    {
        $account = new CounterpartyAccount(
            identification: null,
            recipientBankAcctNo: '2020202020',
            recipientBankCode: '05000',
            recipientAccountName: 'Account Name',
            recipientBankName: 'Bank Name'
        );

        $array = $account->toArray();

        $this->assertSame('2020202020', $array['recipientBankAcctNo']);
        $this->assertSame('05000', $array['recipientBankCode']);
        $this->assertSame('Account Name', $array['recipientAccountName']);
        $this->assertSame('Bank Name', $array['recipientBankName']);
        $this->assertArrayNotHasKey('identification', $array);
    }
}
