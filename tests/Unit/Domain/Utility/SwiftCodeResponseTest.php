<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\Utility;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\Utility\SwiftCodeResponse;

final class SwiftCodeResponseTest extends TestCase
{
    public function testCanBeCreatedWithRequiredFields(): void
    {
        $response = new SwiftCodeResponse(
            swiftCode: 'SBICKENXXXX',
            bankName: 'Stanbic Bank Kenya',
        );

        $this->assertSame('SBICKENXXXX', $response->swiftCode);
        $this->assertSame('Stanbic Bank Kenya', $response->bankName);
    }

    public function testFromArray(): void
    {
        $data = [
            'swiftCode' => 'BKIDINBBBKC',
            'bankName' => 'Bank of India',
        ];

        $response = SwiftCodeResponse::fromArray($data);

        $this->assertSame('BKIDINBBBKC', $response->swiftCode);
        $this->assertSame('Bank of India', $response->bankName);
    }

    public function testToArray(): void
    {
        $response = new SwiftCodeResponse(
            swiftCode: 'KCBLKENXXXX',
            bankName: 'KCB Bank',
        );

        $array = $response->toArray();

        $this->assertSame('KCBLKENXXXX', $array['swiftCode']);
        $this->assertSame('KCB Bank', $array['bankName']);
    }
}
