<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\Service;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\Service\CardServiceInterface;
use ReflectionClass;

final class CardServiceInterfaceTest extends TestCase
{
    public function testInterfaceExists(): void
    {
        $this->assertTrue(interface_exists(CardServiceInterface::class));
    }

    public function testInterfaceHasRequiredMethods(): void
    {
        $reflection = new ReflectionClass(CardServiceInterface::class);
        $methods = $reflection->getMethods();
        $methodNames = array_map(static fn($method) => $method->getName(), $methods);

        $expectedMethods = [
            'getCardDetails',
            'getCustomerCardDetails',
        ];

        foreach ($expectedMethods as $method) {
            $this->assertContains($method, $methodNames);
        }
    }
}
