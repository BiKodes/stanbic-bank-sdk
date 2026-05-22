<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Infrastructure\Http\Middleware;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Stanbic\SDK\Infrastructure\Http\Middleware\TimeoutMiddleware;

final class TimeoutMiddlewareTest extends TestCase
{
    public function testAddsTimeoutHeader(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $request->expects($this->once())
            ->method('withHeader')
            ->with('X-Timeout-Seconds', '10')
            ->willReturn($request);

        $middleware = new TimeoutMiddleware(10);

        $next = function (RequestInterface $req) use ($response): ResponseInterface {
            return $response;
        };

        $result = $middleware($request, $next);

        $this->assertSame($response, $result);
    }
}
