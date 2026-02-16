<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\Payment;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\Payment\Counterparty;
use Stanbic\SDK\Domain\Payment\CounterpartyAccount;
use Stanbic\SDK\Domain\ValueObject\PostalAddress;

final class CounterpartyTest extends TestCase
{
    public function testCreateCounterparty(): void
    {
        $account = new CounterpartyAccount(
            recipientBankAcctNo: '01008747142',
            recipientBankCode: '07000'
        );

        $postalAddress = new PostalAddress(
            addressLine1: 'Kifuli Refu street',
            town: 'Nairobi',
            country: 'KE'
        );

        $counterparty = new Counterparty(
            name: 'John Doe',
            account: $account,
            postalAddress: $postalAddress,
            phoneNumber: '254700000000',
            email: 'john@kifulirefu.com'
        );

        $this->assertSame('John Doe', $counterparty->name);
        $this->assertSame('Nairobi', $counterparty->postalAddress?->town);
        $this->assertSame('KE', $counterparty->postalAddress?->country);
        $this->assertSame('254700000000', $counterparty->phoneNumber);
        $this->assertSame('john@kifulirefu.com', $counterparty->email);
        $this->assertNotNull($counterparty->account);
        $this->assertSame('01008747142', $counterparty->account->recipientBankAcctNo);
    }

    public function testFromArray(): void
    {
        $data = [
            'name' => 'Jane Doe',
            'account' => [
                'recipientBankAcctNo' => '1234567890',
                'recipientBankCode' => '01000',
            ],
            'postalAddress' => [
                'addressLine' => 'Mombasa',
                'country' => 'KE',
            ],
            'phoneNumber' => '254711111111',
            'email' => 'jane@kifulirefu.com',
        ];

        $counterparty = Counterparty::fromArray($data);

        $this->assertSame('Jane Doe', $counterparty->name);
        $this->assertSame('Mombasa', $counterparty->postalAddress?->addressLine);
        $this->assertSame('KE', $counterparty->postalAddress?->country);
        $this->assertSame('254711111111', $counterparty->phoneNumber);
        $this->assertSame('jane@kifulirefu.com', $counterparty->email);
        $this->assertNotNull($counterparty->account);
        $this->assertSame('1234567890', $counterparty->account->recipientBankAcctNo);
    }

    public function testToArray(): void
    {
        $account = new CounterpartyAccount(
            recipientBankAcctNo: '8888888888',
            recipientBankCode: '03000',
            recipientAccountName: 'Array Name'
        );

        $counterparty = new Counterparty(
            name: 'Array User',
            account: $account,
            phoneNumber: '254722222222'
        );

        /** @var array<string, mixed> $array */
        $array = $counterparty->toArray();

        $this->assertSame('Array User', $array['name']);
        $this->assertSame('254722222222', $array['phoneNumber']);
        $this->assertIsArray($array['account']);
        /** @var array<string, mixed> $account */
        $account = $array['account'];
        $this->assertSame('8888888888', $account['recipientBankAcctNo']);
        $this->assertSame('03000', $account['recipientBankCode']);
        $this->assertSame('Array Name', $account['recipientAccountName']);
        $this->assertArrayNotHasKey('address', $array);
    }

    public function testFromArrayWithCounterpartyAccountKey(): void
    {
        $data = [
            'name' => 'Test User',
            'counterpartyAccount' => [
                'recipientBankAcctNo' => '1234567890',
                'recipientBankCode' => '01000',
            ],
        ];

        $counterparty = Counterparty::fromArray($data);

        $this->assertSame('Test User', $counterparty->name);
        $this->assertNotNull($counterparty->account);
        $this->assertSame('1234567890', $counterparty->account->recipientBankAcctNo);
    }

    public function testFromArrayWithCounterpartyAccountSnakeCaseKey(): void
    {
        $data = [
            'name' => 'Test User',
            'counterparty_account' => [
                'recipientBankAcctNo' => '1234567890',
                'recipientBankCode' => '01000',
            ],
        ];

        $counterparty = Counterparty::fromArray($data);

        $this->assertSame('Test User', $counterparty->name);
        $this->assertNotNull($counterparty->account);
        $this->assertSame('1234567890', $counterparty->account->recipientBankAcctNo);
    }

    public function testFromArrayWithCounterpartyNameKey(): void
    {
        $data = [
            'counterpartyName' => 'Counterparty Name',
        ];

        $counterparty = Counterparty::fromArray($data);

        $this->assertSame('Counterparty Name', $counterparty->name);
    }

    public function testFromArrayWithPostalAddressSnakeCase(): void
    {
        $data = [
            'name' => 'Test User',
            'postal_address' => [
                'addressLine' => 'Kisumu',
                'country' => 'KE',
            ],
        ];

        $counterparty = Counterparty::fromArray($data);

        $this->assertSame('Test User', $counterparty->name);
        $this->assertNotNull($counterparty->postalAddress);
        $this->assertSame('Kisumu', $counterparty->postalAddress->addressLine);
    }

    public function testFromArrayWithSnakeCaseKeys(): void
    {
        $data = [
            'name' => 'Snake Case User',
            'country_code' => 'KE',
            'phone_number' => '254700000000',
            'mobile_number' => '254712345678',
        ];

        $counterparty = Counterparty::fromArray($data);

        $this->assertSame('Snake Case User', $counterparty->name);
        $this->assertSame('KE', $counterparty->countryCode);
        $this->assertSame('254700000000', $counterparty->phoneNumber);
        $this->assertSame('254712345678', $counterparty->mobileNumber);
    }

    public function testFromArrayWithCounterpartyAccountObject(): void
    {
        $account = new CounterpartyAccount(
            recipientBankAcctNo: '9999999999',
            recipientBankCode: '05000'
        );

        $data = [
            'name' => 'Object User',
            'account' => $account,
        ];

        $counterparty = Counterparty::fromArray($data);

        $this->assertSame('Object User', $counterparty->name);
        $this->assertSame($account, $counterparty->account);
    }

    public function testFromArrayWithPostalAddressObject(): void
    {
        $postalAddress = new PostalAddress(
            addressLine1: 'Nakuru',
            country: 'KE'
        );

        $data = [
            'name' => 'Postal User',
            'postalAddress' => $postalAddress,
        ];

        $counterparty = Counterparty::fromArray($data);

        $this->assertSame('Postal User', $counterparty->name);
        $this->assertSame($postalAddress, $counterparty->postalAddress);
    }

    public function testFromArrayWithEmptyAccountArray(): void
    {
        $data = [
            'name' => 'Empty Account User',
            'account' => [],
        ];

        $counterparty = Counterparty::fromArray($data);

        $this->assertSame('Empty Account User', $counterparty->name);
        $this->assertNull($counterparty->account);
    }

    public function testFromArrayWithAllNullableFields(): void
    {
        $data = [
            'name' => 'Complete User',
            'address' => '123 Main St',
            'countryCode' => 'KE',
            'phoneNumber' => '254700000000',
            'mobileNumber' => '254712345678',
            'email' => 'complete@example.com',
        ];

        $counterparty = Counterparty::fromArray($data);

        $this->assertSame('Complete User', $counterparty->name);
        $this->assertSame('123 Main St', $counterparty->address);
        $this->assertSame('KE', $counterparty->countryCode);
        $this->assertSame('254700000000', $counterparty->phoneNumber);
        $this->assertSame('254712345678', $counterparty->mobileNumber);
        $this->assertSame('complete@example.com', $counterparty->email);
    }

    public function testToArrayWithAllFields(): void
    {
        $account = new CounterpartyAccount(
            recipientBankAcctNo: '1111111111',
            recipientBankCode: '02000'
        );

        $postalAddress = new PostalAddress(
            addressLine1: 'Eldoret',
            country: 'KE'
        );

        $counterparty = new Counterparty(
            name: 'Full User',
            account: $account,
            postalAddress: $postalAddress,
            address: '456 Side St',
            countryCode: 'KE',
            phoneNumber: '254733333333',
            mobileNumber: '254744444444',
            email: 'full@example.com'
        );

        $array = $counterparty->toArray();

        $this->assertSame('Full User', $array['name']);
        $this->assertSame('456 Side St', $array['address']);
        $this->assertSame('KE', $array['countryCode']);
        $this->assertSame('254733333333', $array['phoneNumber']);
        $this->assertSame('254744444444', $array['mobileNumber']);
        $this->assertSame('full@example.com', $array['email']);
        $this->assertIsArray($array['account']);
        $this->assertIsArray($array['postalAddress']);
    }

    public function testToArrayOmitsNullFields(): void
    {
        $counterparty = new Counterparty(
            name: 'Minimal User'
        );

        $array = $counterparty->toArray();

        $this->assertSame('Minimal User', $array['name']);
        $this->assertArrayNotHasKey('account', $array);
        $this->assertArrayNotHasKey('postalAddress', $array);
        $this->assertArrayNotHasKey('address', $array);
        $this->assertArrayNotHasKey('countryCode', $array);
        $this->assertArrayNotHasKey('phoneNumber', $array);
        $this->assertArrayNotHasKey('mobileNumber', $array);
        $this->assertArrayNotHasKey('email', $array);
    }
}
