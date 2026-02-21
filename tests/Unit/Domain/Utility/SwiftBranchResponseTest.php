<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\Utility;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\Utility\SwiftBranchResponse;

final class SwiftBranchResponseTest extends TestCase
{
    public function testCanBeCreatedWithRequiredFields(): void
    {
        $response = new SwiftBranchResponse(
            branchCode: 'SBCAUS6L',
            branchName: 'San Jose',
            sortCode: '10630340',
        );

        $this->assertSame('SBCAUS6L', $response->branchCode);
        $this->assertSame('San Jose', $response->branchName);
        $this->assertSame('10630340', $response->sortCode);
    }

    public function testFromArray(): void
    {
        $data = [
            'branchCode' => 'NAI001',
            'branchName' => 'Nairobi CBD Branch',
            'sortCode' => '01010',
        ];

        $response = SwiftBranchResponse::fromArray($data);

        $this->assertSame('NAI001', $response->branchCode);
        $this->assertSame('Nairobi CBD Branch', $response->branchName);
        $this->assertSame('01010', $response->sortCode);
    }

    public function testToArray(): void
    {
        $response = new SwiftBranchResponse(
            branchCode: 'MBA001',
            branchName: 'Mombasa Branch',
            sortCode: '02020',
        );

        $array = $response->toArray();

        $this->assertSame('MBA001', $array['branchCode']);
        $this->assertSame('Mombasa Branch', $array['branchName']);
        $this->assertSame('02020', $array['sortCode']);
    }
}
