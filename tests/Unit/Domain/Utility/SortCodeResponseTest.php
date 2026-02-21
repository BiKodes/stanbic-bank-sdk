<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\Utility;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\Utility\SortCodeResponse;

final class SortCodeResponseTest extends TestCase
{
    public function testCanBeCreatedWithRequiredFields(): void
    {
        $response = new SortCodeResponse(
            bankName: 'BARCLAYS HEAD OFFICE',
            sortCodeId: '03000',
        );

        $this->assertSame('BARCLAYS HEAD OFFICE', $response->bankName);
        $this->assertSame('03000', $response->sortCodeId);
    }

    public function testFromArray(): void
    {
        $data = [
            'bankName' => 'STANBIC BANK KENYA',
            'sortCodeId' => '01010',
        ];

        $response = SortCodeResponse::fromArray($data);

        $this->assertSame('STANBIC BANK KENYA', $response->bankName);
        $this->assertSame('01010', $response->sortCodeId);
    }

    public function testToArray(): void
    {
        $response = new SortCodeResponse(
            bankName: 'KCB BANK',
            sortCodeId: '02020',
        );

        $array = $response->toArray();

        $this->assertSame('KCB BANK', $array['bankName']);
        $this->assertSame('02020', $array['sortCodeId']);
    }
}
