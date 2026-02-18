<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\Payment;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Tests\Unit\Domain\Payment\PaymentRequestStub;

final class PaymentRequestTest extends TestCase
{
    public function testBaseArrayIncludesOptionalFields(): void
    {
        $request = new PaymentRequestStub(
            dbsReferenceId: 'DBS-001',
            txnNarrative: 'Test narrative',
            requestedExecutionDate: '2026-02-17',
            endToEndId: 'E2E-123',
            chargeBearer: 'SHA'
        );

        $array = $request->toArray();

        $this->assertSame('DBS-001', $array['dbsReferenceId']);
        $this->assertSame('Test narrative', $array['txnNarrative']);
        $this->assertSame('2026-02-17', $array['requestedExecutionDate']);
        $this->assertSame('E2E-123', $array['endToEndId']);
        $this->assertSame('SHA', $array['chargeBearer']);
    }

    public function testBaseArrayOmitsNullOptionalFields(): void
    {
        $request = new PaymentRequestStub(
            dbsReferenceId: 'DBS-002',
            txnNarrative: 'No optionals',
            requestedExecutionDate: '2026-02-18'
        );

        $array = $request->toArray();

        $this->assertSame('DBS-002', $array['dbsReferenceId']);
        $this->assertArrayNotHasKey('endToEndId', $array);
        $this->assertArrayNotHasKey('chargeBearer', $array);
    }
}
