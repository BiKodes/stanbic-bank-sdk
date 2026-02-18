<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\ValueObject;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\ValueObject\OriginatorAccount;
use Stanbic\SDK\Domain\ValueObject\OriginatorIdentification;

final class OriginatorAccountTest extends TestCase
{
    public function testCreateOriginatorAccount(): void
    {
        $identification = new OriginatorIdentification(
            identification: '0100001536723',
            debitCurrency: 'KES',
            mobileNumber: '254735084266'
        );

        $account = new OriginatorAccount($identification);

        $this->assertSame('0100001536723', $account->identification->identification);
        $this->assertSame('KES', $account->identification->debitCurrency);
        $this->assertSame('254735084266', $account->identification->mobileNumber);
    }

    public function testFromArray(): void
    {
        $data = [
            'identification' => [
                'identification' => '0100001536723',
                'debitCurrency' => 'KES',
                'mobileNumber' => '254735084266',
            ],
        ];

        $account = OriginatorAccount::fromArray($data);

        $this->assertSame('0100001536723', $account->identification->identification);
        $this->assertSame('KES', $account->identification->debitCurrency);
        $this->assertSame('254735084266', $account->identification->mobileNumber);
    }

    public function testToArray(): void
    {
        $account = new OriginatorAccount(
            new OriginatorIdentification(mobileNumber: '2547200000000')
        );

        /** @var array<string, mixed> $array */
        $array = $account->toArray();

        $this->assertIsArray($array['identification']);
        /** @var array<string, mixed> $identification */
        $identification = $array['identification'];
        $this->assertSame('2547200000000', $identification['mobileNumber']);
    }

    public function testFromArrayWithIdentificationObject(): void
    {
        $identification = new OriginatorIdentification(
            identification: 'ORIG-777'
        );

        $account = OriginatorAccount::fromArray([
            'identification' => $identification,
        ]);

        $this->assertSame($identification, $account->identification);
    }

    public function testFromArrayWithEmptyIdentification(): void
    {
        $account = OriginatorAccount::fromArray([]);

        $this->assertSame([], $account->identification->toArray());
    }
}
