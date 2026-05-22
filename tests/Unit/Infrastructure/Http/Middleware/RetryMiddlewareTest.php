<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Infrastructure\Http\Middleware;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Stanbic\SDK\Infrastructure\Http\Middleware\RetryMiddleware;

final class RetryMiddlewareTest extends TestCase
{
    public function testReturnsImmediatelyOnSuccess(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $middleware = new RetryMiddleware(3, 1);

        $next = function (RequestInterface $req) use ($response): ResponseInterface {
            return $response;
        };

        $this->assertSame($response, $middleware($request, $next));
    }

    public function testRetriesOnServerErrorThenSucceeds(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $resp500 = $this->createMock(ResponseInterface::class);
        $resp500->method('getStatusCode')->willReturn(500);
        $resp200 = $this->createMock(ResponseInterface::class);
        $resp200->method('getStatusCode')->willReturn(200);

        $calls = 0;
        $next = function (RequestInterface $req) use (&$calls, $resp500, $resp200): ResponseInterface {
            $calls++;
            return $calls === 1 ? $resp500 : $resp200;
        };

        $middleware = new RetryMiddleware(3, 1);

        $this->assertSame($resp200, $middleware($request, $next));
    }

    public function testRetriesOnClientExceptionThenSucceeds(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $resp200 = $this->createMock(ResponseInterface::class);
        $resp200->method('getStatusCode')->willReturn(200);

        $exception = $this->createMock(ClientExceptionInterface::class);

        $calls = 0;
        $next = function (RequestInterface $req) use (&$calls, $exception, $resp200): ResponseInterface {
            $calls++;
            if ($calls === 1) {
                throw $exception;
            }
            return $resp200;
        };

        $middleware = new RetryMiddleware(3, 1);

        $this->assertSame($resp200, $middleware($request, $next));
    }

    public function testThrowsLastClientExceptionAfterMaxAttempts(): void
    {
        $this->expectException(ClientExceptionInterface::class);

        $request = $this->createMock(RequestInterface::class);

        $next = function (RequestInterface $req): never {
            throw new class extends \Exception implements ClientExceptionInterface
            {
            };
        };

        $middleware = new RetryMiddleware(2, 1);

        $middleware($request, $next);
    }

    public function testThrowsAfterMaxAttempts(): void
    {
        $this->expectException(\RuntimeException::class);

        $request = $this->createMock(RequestInterface::class);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(500);

        $calls = 0;
        $next = function (RequestInterface $req) use (&$calls, $response): ResponseInterface {
            $calls++;

            return $response;
        };

        $middleware = new RetryMiddleware(2, 1);

        $middleware($request, $next);
    }
}
