<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\Service;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\Service\UtilityServiceInterface;
use ReflectionClass;

final class UtilityServiceInterfaceTest extends TestCase
{
    public function testInterfaceExists(): void
    {
        $this->assertTrue(interface_exists(UtilityServiceInterface::class));
    }

    public function testInterfaceHasRequiredMethods(): void
    {
        $reflection = new ReflectionClass(UtilityServiceInterface::class);
        $methods = $reflection->getMethods();
        $methodNames = array_map(static fn($method) => $method->getName(), $methods);

        $expectedMethods = [
            'fetchSortCodes',
            'getSwiftCode',
            'getSwiftBranch',
        ];

        foreach ($expectedMethods as $method) {
            $this->assertContains($method, $methodNames);
        }
    }
}
