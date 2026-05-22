<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Infrastructure\Http;

use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Stanbic\SDK\Infrastructure\Http\HttpClientFactory;
use Stanbic\SDK\Infrastructure\Http\HttpConfig;
use Stanbic\SDK\Infrastructure\Http\Middleware\MiddlewareInterface;
use Stanbic\SDK\Infrastructure\Http\Middleware\MiddlewareStackClient;

final class HttpClientFactoryCreateWithMiddlewareTest extends TestCase
{
    public function testCreateWithMiddlewareReturnsMiddlewareStackClient(): void
    {
        $config = HttpConfig::create(
            baseUrl: 'https://api.test',
            clientId: 'a',
            clientSecret: 'b',
            tokenUrl: 'https://auth.test/token',
        );

        $mockClient = $this->createMock(ClientInterface::class);

        $middleware = new class implements MiddlewareInterface {
            public function __invoke(RequestInterface $request, callable $next): ResponseInterface
            {
                return $next($request);
            }
        };

        $client = HttpClientFactory::createWithMiddleware($config, [$middleware], $mockClient);

        $this->assertInstanceOf(MiddlewareStackClient::class, $client);
    }
}
