<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\Utility;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\Utility\SwiftCodeRequest;

final class SwiftCodeRequestTest extends TestCase
{
    public function testCanBeCreatedWithCountryCode(): void
    {
        $request = new SwiftCodeRequest(
            countryCode: 'KE',
        );

        $this->assertSame('KE', $request->countryCode);
    }

    public function testFromArray(): void
    {
        $data = [
            'countryCode' => 'US',
        ];

        $request = SwiftCodeRequest::fromArray($data);

        $this->assertSame('US', $request->countryCode);
    }

    public function testToArray(): void
    {
        $request = new SwiftCodeRequest(
            countryCode: 'GB',
        );

        $array = $request->toArray();

        $this->assertSame('GB', $array['countryCode']);
    }
}
