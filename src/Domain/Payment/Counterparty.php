<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Payment;

use Stanbic\SDK\Domain\ValueObject\PostalAddress;

/**
 * Counterparty details for a payment.
 *
 * @psalm-immutable
*/
final class Counterparty
{
    public function __construct(
        public readonly string $name,
        public readonly ?CounterpartyAccount $account = null,
        public readonly ?PostalAddress $postalAddress = null,
        public readonly ?string $address = null,
        public readonly ?string $countryCode = null,
        public readonly ?string $phoneNumber = null,
        public readonly ?string $mobileNumber = null,
        public readonly ?string $email = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
    */
    public static function fromArray(array $data): self
    {
        /** @var array<string, mixed>|CounterpartyAccount|null $accountData */
        $accountData = $data['account'] ?? $data['counterpartyAccount'] ?? $data['counterparty_account'] ?? null;
        $account = null;
        if ($accountData instanceof CounterpartyAccount) {
            $account = $accountData;
        } elseif (is_array($accountData) && $accountData !== []) {
            /** @var array<string, mixed> $accountArray */
            $accountArray = $accountData;
            $account = CounterpartyAccount::fromArray($accountArray);
        }

        /** @var array<string, mixed>|PostalAddress|null $postalData */
        $postalData = $data['postalAddress'] ?? $data['postal_address'] ?? null;
        $postalAddress = null;
        if ($postalData instanceof PostalAddress) {
            $postalAddress = $postalData;
        } elseif (is_array($postalData)) {
            /** @var array<string, mixed> $postalArray */
            $postalArray = $postalData;
            $postalAddress = PostalAddress::fromArray($postalArray);
        }

        return new self(
            name: (string) ($data['name'] ?? $data['counterpartyName'] ?? ''),
            account: $account,
            postalAddress: $postalAddress,
            address: isset($data['address']) ? (string) $data['address'] : null,
            countryCode: isset($data['countryCode']) || isset($data['country_code'])
                ? (string) ($data['countryCode'] ?? $data['country_code'])
                : null,
            phoneNumber: isset($data['phoneNumber']) || isset($data['phone_number'])
                ? (string) ($data['phoneNumber'] ?? $data['phone_number'])
                : null,
            mobileNumber: isset($data['mobileNumber']) || isset($data['mobile_number'])
                ? (string) ($data['mobileNumber'] ?? $data['mobile_number'])
                : null,
            email: isset($data['email']) ? (string) $data['email'] : null,
        );
    }

    /**
     * @return array<string, mixed>
    */
    public function toArray(): array
    {
        $data = [
            'name' => $this->name,
        ];

        if ($this->account !== null) {
            $data['account'] = $this->account->toArray();
        }

        if ($this->postalAddress !== null) {
            $data['postalAddress'] = $this->postalAddress->toArray();
        }

        if ($this->address !== null) {
            $data['address'] = $this->address;
        }

        if ($this->countryCode !== null) {
            $data['countryCode'] = $this->countryCode;
        }

        if ($this->phoneNumber !== null) {
            $data['phoneNumber'] = $this->phoneNumber;
        }

        if ($this->mobileNumber !== null) {
            $data['mobileNumber'] = $this->mobileNumber;
        }

        if ($this->email !== null) {
            $data['email'] = $this->email;
        }

        return $data;
    }
}
