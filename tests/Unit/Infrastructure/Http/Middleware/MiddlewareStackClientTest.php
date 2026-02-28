<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Infrastructure\Http\Middleware;

use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Stanbic\SDK\Infrastructure\Http\Middleware\MiddlewareInterface;
use Stanbic\SDK\Infrastructure\Http\Middleware\MiddlewareStackClient;

final class MiddlewareStackClientTest extends TestCase
{
    public function testMiddlewareIsAppliedInOrder(): void
    {
        $calls = [];
        $middleware1 = new class ($calls) implements MiddlewareInterface
        {
            private array $calls;

            public function __construct(array &$calls)
            {
                $this->calls = &$calls;
            }

            public function __invoke(RequestInterface $request, callable $next): ResponseInterface
            {
                $this->calls[] = 'mw1';
                return $next($request);
            }
        };
        $middleware2 = new class ($calls) implements MiddlewareInterface
        {
            private array $calls;

            public function __construct(array &$calls)
            {
                $this->calls = &$calls;
            }

            public function __invoke(RequestInterface $request, callable $next): ResponseInterface
            {
                $this->calls[] = 'mw2';
                return $next($request);
            }
        };
        $mockRequest = $this->createMock(RequestInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockClient = $this->createMock(ClientInterface::class);
        $mockClient->expects($this->once())
            ->method('sendRequest')
            ->with($mockRequest)
            ->willReturn($mockResponse);
        $client = new MiddlewareStackClient($mockClient, [$middleware1, $middleware2]);
        $result = $client->sendRequest($mockRequest);
        $this->assertSame($mockResponse, $result);
        $this->assertSame(['mw1', 'mw2'], $calls);
    }

    public function testNoMiddlewareCallsClientDirectly(): void
    {
        $mockRequest = $this->createMock(RequestInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockClient = $this->createMock(ClientInterface::class);
        $mockClient->expects($this->once())
            ->method('sendRequest')
            ->with($mockRequest)
            ->willReturn($mockResponse);
        $client = new MiddlewareStackClient($mockClient, []);
        $result = $client->sendRequest($mockRequest);
        $this->assertSame($mockResponse, $result);
    }
}
