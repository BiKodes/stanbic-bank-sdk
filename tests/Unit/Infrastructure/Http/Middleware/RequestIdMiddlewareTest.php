<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Infrastructure\Http\Middleware;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Stanbic\SDK\Infrastructure\Http\Middleware\RequestIdMiddleware;

final class RequestIdMiddlewareTest extends TestCase
{
    public function testAddsXRequestIdHeader(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $request->expects($this->once())
            ->method('withHeader')
            ->with(
                'X-Request-ID',
                $this->callback(function ($id) {
                    return is_string($id) && strlen($id) === 32 && ctype_xdigit($id);
                })
            )
            ->willReturn($request);

        $middleware = new RequestIdMiddleware();

        $next = function (RequestInterface $req) use ($response): ResponseInterface {
            return $response;
        };

        $result = $middleware($request, $next);

        $this->assertSame($response, $result);
    }
}
