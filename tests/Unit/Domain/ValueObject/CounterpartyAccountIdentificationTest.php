<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\ValueObject;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\ValueObject\CounterpartyAccountIdentification;

final class CounterpartyAccountIdentificationTest extends TestCase
{
    public function testCreateCounterpartyAccountIdentification(): void
    {
        $identification = new CounterpartyAccountIdentification(
            identification: '0100004614423',
            recipientMobileNo: '25472XXXXXXXX',
            recipientBankAcctNo: '01008747142',
            recipientBankCode: '07000',
            correspondentBank: 'UGBAUGKAXXX',
            beneficiaryBank: 'SW-CERBUGKA',
            beneficiaryChargeType: 'SHA'
        );

        $this->assertSame('0100004614423', $identification->identification);
        $this->assertSame('25472XXXXXXXX', $identification->recipientMobileNo);
        $this->assertSame('01008747142', $identification->recipientBankAcctNo);
        $this->assertSame('07000', $identification->recipientBankCode);
        $this->assertSame('UGBAUGKAXXX', $identification->correspondentBank);
        $this->assertSame('SW-CERBUGKA', $identification->beneficiaryBank);
        $this->assertSame('SHA', $identification->beneficiaryChargeType);
    }

    public function testFromArray(): void
    {
        $data = [
            'identification' => '9877665554',
            'correspondentBank' => 'UGBAUGKAXXX',
            'beneficiaryBank' => 'SW-CERBUGKA',
        ];

        $identification = CounterpartyAccountIdentification::fromArray($data);

        $this->assertSame('9877665554', $identification->identification);
        $this->assertSame('UGBAUGKAXXX', $identification->correspondentBank);
        $this->assertSame('SW-CERBUGKA', $identification->beneficiaryBank);
    }

    public function testToArray(): void
    {
        $identification = new CounterpartyAccountIdentification(recipientBankAcctNo: '1234567890');

        $array = $identification->toArray();

        $this->assertSame('1234567890', $array['recipientBankAcctNo']);
    }
}
