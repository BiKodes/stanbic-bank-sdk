<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\Service;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\Service\NotificationServiceInterface;
use ReflectionClass;

final class NotificationServiceInterfaceTest extends TestCase
{
    public function testInterfaceExists(): void
    {
        $this->assertTrue(interface_exists(NotificationServiceInterface::class));
    }

    public function testInterfaceHasRequiredMethods(): void
    {
        $reflection = new ReflectionClass(NotificationServiceInterface::class);
        $methods = $reflection->getMethods();
        $methodNames = array_map(static fn($method) => $method->getName(), $methods);

        $expectedMethods = [
            'registerPaymentResultUrl',
            'registerTransactionNotification',
            'sendSmsEmailNotification',
        ];

        foreach ($expectedMethods as $method) {
            $this->assertContains($method, $methodNames);
        }
    }
}
