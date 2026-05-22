<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Infrastructure\Http\Middleware;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Stanbic\SDK\Infrastructure\Http\Middleware\LoggingMiddleware;

final class LoggingMiddlewareTest extends TestCase
{
    public function testLogsRequestAndResponse(): void
    {
        $logger = $this->createMock(LoggerInterface::class);

        $call = 0;
        $logger->expects($this->exactly(2))
            ->method('info')
            ->willReturnCallback(function (string $message, array $context) use (&$call): void {
                $call++;

                if ($call === 1) {
                    self::assertSame('HTTP Request', $message);
                    self::assertArrayHasKey('method', $context);
                    self::assertArrayHasKey('uri', $context);
                    self::assertArrayHasKey('headers', $context);

                    return;
                }

                self::assertSame('HTTP Response', $message);
                self::assertArrayHasKey('status', $context);
                self::assertArrayHasKey('headers', $context);
            });

        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $request->method('getMethod')->willReturn('GET');
        $request->method('getUri')->willReturn('https://api.test/');
        $request->method('getHeaders')->willReturn(['h' => ['v']]);

        $response->method('getStatusCode')->willReturn(200);
        $response->method('getHeaders')->willReturn(['h' => ['v']]);

        $middleware = new LoggingMiddleware($logger);

        $next = function (RequestInterface $req) use ($response): ResponseInterface {
            return $response;
        };

        $result = $middleware($request, $next);

        $this->assertSame($response, $result);
    }
}
