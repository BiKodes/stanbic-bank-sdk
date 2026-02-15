<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\Service;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\Service\TransferServiceInterface;
use ReflectionClass;

final class TransferServiceInterfaceTest extends TestCase
{
    public function testInterfaceExists(): void
    {
        $this->assertTrue(interface_exists(TransferServiceInterface::class));
    }

    public function testInterfaceHasRequiredMethods(): void
    {
        $reflection = new ReflectionClass(TransferServiceInterface::class);
        $methods = $reflection->getMethods();
        $methodNames = array_map(static fn($method) => $method->getName(), $methods);

        $expectedMethods = [
            'initiateInterAccountTransfer',
            'initiateEftTransfer',
            'initiateSwiftTransfer',
            'initiateRtgsTransfer',
        ];

        foreach ($expectedMethods as $method) {
            $this->assertContains($method, $methodNames);
        }
    }
}
