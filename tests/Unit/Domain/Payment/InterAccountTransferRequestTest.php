<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\Payment;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\Payment\InterAccountTransferRequest;

final class InterAccountTransferRequestTest extends TestCase
{
    public function testCreateInterAccountTransferRequest(): void
    {
        $request = new InterAccountTransferRequest(
            referenceId: 'gdhet-dh7ba-dfhbf',
            channel: 'NRA',
            creditAccount: '0200000195129',
            creditCurrency: 'USD',
            narration: 'Tax Payment',
            debitAmount: '100',
            debitAccount: '0200000172935',
            debitCurrency: 'USD',
            paymentDetails: 'Payment Details'
        );

        $this->assertSame('gdhet-dh7ba-dfhbf', $request->referenceId);
        $this->assertSame('NRA', $request->channel);
        $this->assertSame('0200000195129', $request->creditAccount);
        $this->assertSame('USD', $request->creditCurrency);
    }

    public function testFromArray(): void
    {
        $data = [
            'ReferenceId' => 'ref-001',
            'Channel' => 'NRA',
            'CreditAccount' => '4444444444',
            'CreditCurrency' => 'USD',
            'Narration' => 'From array',
            'DebitAmount' => '2500.00',
            'DebitAccount' => '3333333333',
            'DebitCurrency' => 'USD',
            'PaymentDetails' => 'Payment Details',
        ];

        $request = InterAccountTransferRequest::fromArray($data);

        $this->assertSame('ref-001', $request->referenceId);
        $this->assertSame('3333333333', $request->debitAccount);
        $this->assertSame('4444444444', $request->creditAccount);
        $this->assertSame('2500.00', $request->debitAmount);
        $this->assertSame('USD', $request->debitCurrency);
    }

    public function testToArray(): void
    {
        $request = new InterAccountTransferRequest(
            referenceId: 'ref-002',
            channel: 'NRA',
            creditAccount: '6666666666',
            creditCurrency: 'EUR',
            narration: 'To array',
            debitAmount: '3500.00',
            debitAccount: '5555555555',
            debitCurrency: 'EUR',
            paymentDetails: 'Payment Details'
        );

        $array = $request->toArray();

        $this->assertSame('ref-002', $array['ReferenceId']);
        $this->assertSame('5555555555', $array['DebitAccount']);
        $this->assertSame('6666666666', $array['CreditAccount']);
        $this->assertSame('3500.00', $array['DebitAmount']);
        $this->assertSame('EUR', $array['DebitCurrency']);
    }
}
