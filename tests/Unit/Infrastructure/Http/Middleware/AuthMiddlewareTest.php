<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Infrastructure\Http\Middleware;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Stanbic\SDK\Infrastructure\Http\AccessToken;
use Stanbic\SDK\Infrastructure\Http\Middleware\AuthMiddleware;

final class AuthMiddlewareTest extends TestCase
{
    public function testAddsAuthorizationHeader(): void
    {
        $token = AccessToken::fromResponse(['access_token' => 'abc123', 'expires_in' => 3600], 1.0);

        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $request->expects($this->once())
            ->method('withHeader')
            ->with('Authorization', 'Bearer abc123')
            ->willReturn($request);

        $middleware = new AuthMiddleware($token);

        $next = function (RequestInterface $req) use ($response): ResponseInterface {
            return $response;
        };

        $result = $middleware($request, $next);

        $this->assertSame($response, $result);
    }
}
