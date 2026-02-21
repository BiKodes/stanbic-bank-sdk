<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\Utility;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\Utility\SortCodeRequest;

final class SortCodeRequestTest extends TestCase
{
    public function testCanBeCreatedWithTransactionType(): void
    {
        $request = new SortCodeRequest(
            transactionType: 'PESALINK',
        );

        $this->assertSame('PESALINK', $request->transactionType);
    }

    public function testFromArray(): void
    {
        $data = [
            'transactionType' => 'SWIFT',
        ];

        $request = SortCodeRequest::fromArray($data);

        $this->assertSame('SWIFT', $request->transactionType);
    }

    public function testToArray(): void
    {
        $request = new SortCodeRequest(
            transactionType: 'RTGS',
        );

        $array = $request->toArray();

        $this->assertSame('RTGS', $array['transactionType']);
    }
}
