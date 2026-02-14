<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\Service;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\Service\AccountServiceInterface;
use ReflectionClass;

final class AccountServiceInterfaceTest extends TestCase
{
    public function testInterfaceExists(): void
    {
        $this->assertTrue(interface_exists(AccountServiceInterface::class));
    }

    public function testInterfaceHasRequiredMethods(): void
    {
        $reflection = new ReflectionClass(AccountServiceInterface::class);
        $methods = $reflection->getMethods();
        $methodNames = array_map(static fn($method) => $method->getName(), $methods);

        $expectedMethods = [
            'getBalance',
            'fetchStatements',
            'getTransactionStatus',
        ];

        foreach ($expectedMethods as $method) {
            $this->assertContains($method, $methodNames);
        }
    }
}
