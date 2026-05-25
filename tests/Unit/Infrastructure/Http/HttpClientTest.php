<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Infrastructure\Http;

use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Psr\Log\NullLogger;
use Stanbic\SDK\Infrastructure\Http\HttpClient;
use Stanbic\SDK\Infrastructure\Http\HttpConfig;
use Stanbic\SDK\Infrastructure\Http\TokenProviderInterface;
use Stanbic\SDK\Infrastructure\Http\Middleware\AuthMiddleware;
use Stanbic\SDK\Infrastructure\Http\Middleware\LoggingMiddleware;
use Stanbic\SDK\Infrastructure\Http\Middleware\MiddlewareStackClient;
use Stanbic\SDK\Infrastructure\Http\Middleware\RetryMiddleware;
use Stanbic\SDK\Infrastructure\Http\Middleware\TimeoutMiddleware;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class HttpClientTest extends TestCase
{
    private HttpConfig $config;

    protected function setUp(): void
    {
        $this->config = HttpConfig::create(
            baseUrl: 'https://api.stanbic.com',
            clientId: 'client-id',
            clientSecret: 'client-secret',
            tokenUrl: 'https://auth.stanbic.com/oauth/token',
            timeoutMs: 30000,
            retryAttempts: 3,
            retryBackoffMs: 100,
            logger: new NullLogger(),
        );
    }

    public function testMiddlewareOrderIsLoggingTimeoutRetryAuth(): void
    {
        $baseClient = $this->createMock(ClientInterface::class);
        $tokenProvider = $this->createMock(TokenProviderInterface::class);

        $httpClient = new HttpClient($this->config, $baseClient, $tokenProvider, $this->requestFactory());

        $clientProperty = new \ReflectionProperty($httpClient, 'client');
        $clientProperty->setAccessible(true);

        $wrappedClient = $clientProperty->getValue($httpClient);
        $this->assertInstanceOf(MiddlewareStackClient::class, $wrappedClient);

        $middlewareProperty = new \ReflectionProperty($wrappedClient, 'middleware');
        $middlewareProperty->setAccessible(true);

        /** @var list<object> $middleware */
        $middleware = $middlewareProperty->getValue($wrappedClient);

        $this->assertSame([
            LoggingMiddleware::class,
            TimeoutMiddleware::class,
            RetryMiddleware::class,
            AuthMiddleware::class,
        ], array_map(static fn (object $mw): string => $mw::class, $middleware));
    }

    public function testRequestBuildsUriAndSendsThroughMiddleware(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $uri = $this->createMock(UriInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $appliedHeaders = [];

        $uri->method('__toString')->willReturn('https://api.stanbic.com/accounts?limit=10');

        $request->method('getMethod')->willReturn('GET');
        $request->method('getUri')->willReturn($uri);
        $request->method('getHeaders')->willReturn([]);
        $request->method('withHeader')->willReturnCallback(
            function (string $name, string $value) use (&$appliedHeaders, $request): RequestInterface {
                $appliedHeaders[$name] = $value;
                return $request;
            }
        );

        $response->method('getStatusCode')->willReturn(200);
        $response->method('getHeaders')->willReturn([]);

        $baseClient = $this->createMock(ClientInterface::class);
        $baseClient->expects($this->once())
            ->method('sendRequest')
            ->willReturnCallback(
                function (
                    RequestInterface $sentRequest
                ) use (
                    $request,
                    &$appliedHeaders,
                    $response
                ): ResponseInterface {
                    $this->assertSame($request, $sentRequest);
                    $this->assertSame(
                        '30',
                        $appliedHeaders['X-Timeout-Seconds'] ?? null
                    );
                    $this->assertSame(
                        'Bearer test-token',
                        $appliedHeaders['Authorization'] ?? null
                    );

                    return $response;
                }
            );

        $tokenProvider = $this->createMock(TokenProviderInterface::class);
        $tokenProvider->expects($this->once())
            ->method('getToken')
            ->willReturn('test-token');

        $captured = ['method' => '', 'uri' => '', 'headers' => [], 'body' => ''];

        $requestFactory = function (
            string $method,
            string $uri,
            array $headers,
            string $body
        ) use (
            &$captured,
            $request
        ): RequestInterface {
            $captured['method'] = $method;
            $captured['uri'] = $uri;
            $captured['headers'] = $headers;
            $captured['body'] = $body;

            return $request;
        };

        $httpClient = new HttpClient($this->config, $baseClient, $tokenProvider, $requestFactory(...));
        $result = $httpClient->get('/accounts', ['limit' => 10]);

        $this->assertSame($response, $result);
        $this->assertSame('GET', $captured['method']);
        $this->assertSame('https://api.stanbic.com/accounts?limit=10', $captured['uri']);
        $this->assertSame('', $captured['body']);
    }

    public function testRequestRetriesThroughHttpClientMiddlewareStack(): void
    {
        $config = $this->config->withRetrySettings(3, 0);

        $request = $this->createMock(RequestInterface::class);
        $uri = $this->createMock(UriInterface::class);
        $response500 = $this->createMock(ResponseInterface::class);
        $response200 = $this->createMock(ResponseInterface::class);

        $uri->method('__toString')->willReturn('https://api.stanbic.com/payments');

        $request->method('getMethod')->willReturn('POST');
        $request->method('getUri')->willReturn($uri);
        $request->method('getHeaders')->willReturn([]);
        $request->method('withHeader')->willReturnSelf();

        $response500->method('getStatusCode')->willReturn(500);
        $response500->method('getHeaders')->willReturn([]);

        $response200->method('getStatusCode')->willReturn(200);
        $response200->method('getHeaders')->willReturn([]);

        $baseClient = $this->createMock(ClientInterface::class);
        $baseClient->expects($this->exactly(2))
            ->method('sendRequest')
            ->with($request)
            ->willReturnOnConsecutiveCalls($response500, $response200);

        $tokenProvider = $this->createMock(TokenProviderInterface::class);
        $tokenProvider->expects($this->exactly(2))
            ->method('getToken')
            ->willReturn('retry-token');

        $httpClient = new HttpClient(
            $config,
            $baseClient,
            $tokenProvider,
            static fn (string $method, string $uri, array $headers, string $body): RequestInterface => $request,
        );

        $result = $httpClient->post('/payments', '{"amount":100}');

        $this->assertSame($response200, $result);
    }

    public function testConvenienceMethodsForwardMethodAndBody(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $uri = $this->createMock(UriInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $captured = [];

        $uri->method('__toString')->willReturn('https://api.stanbic.com/resource');

        $request->method('getMethod')->willReturn('GET');
        $request->method('getUri')->willReturn($uri);
        $request->method('getHeaders')->willReturn([]);
        $request->method('withHeader')->willReturnSelf();

        $response->method('getStatusCode')->willReturn(200);
        $response->method('getHeaders')->willReturn([]);

        $baseClient = $this->createMock(ClientInterface::class);
        $baseClient->expects($this->exactly(3))
            ->method('sendRequest')
            ->with($request)
            ->willReturn($response);

        $tokenProvider = $this->createMock(TokenProviderInterface::class);
        $tokenProvider->expects($this->exactly(3))
            ->method('getToken')
            ->willReturn('test-token');

        $requestFactory = function (
            string $method,
            string $uri,
            array $headers,
            string $body
        ) use (
            &$captured,
            $request
        ): RequestInterface {
            $captured[] = [
                'method' => $method,
                'uri' => $uri,
                'headers' => $headers,
                'body' => $body,
            ];

            return $request;
        };

        $httpClient = new HttpClient($this->config, $baseClient, $tokenProvider, $requestFactory);

        $httpClient->put('/resource', 'put-body');
        $httpClient->patch('/resource', 'patch-body');
        $httpClient->delete('/resource');

        $this->assertSame([
            [
                'method' => 'PUT',
                'uri' => 'https://api.stanbic.com/resource',
                'headers' => [],
                'body' => 'put-body',
            ],
            [
                'method' => 'PATCH',
                'uri' => 'https://api.stanbic.com/resource',
                'headers' => [],
                'body' => 'patch-body',
            ],
            [
                'method' => 'DELETE',
                'uri' => 'https://api.stanbic.com/resource',
                'headers' => [],
                'body' => '',
            ],
        ], $captured);
    }

    public function testRequestUsesDefaultFactoryForAbsoluteUriWithQuery(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getHeaders')->willReturn([]);

        $baseClient = $this->createMock(ClientInterface::class);
        $baseClient->expects($this->once())
            ->method('sendRequest')
            ->willReturnCallback(function (RequestInterface $sentRequest) use ($response): ResponseInterface {
                $this->assertSame(
                    'https://api.stanbic.com/accounts?sort=asc&limit=10',
                    (string) $sentRequest->getUri(),
                );
                $this->assertSame('Bearer test-token', $sentRequest->getHeaderLine('Authorization'));
                $this->assertSame('30', $sentRequest->getHeaderLine('X-Timeout-Seconds'));

                return $response;
            });

        $tokenProvider = $this->createMock(TokenProviderInterface::class);
        $tokenProvider->expects($this->once())
            ->method('getToken')
            ->willReturn('test-token');

        $httpClient = new HttpClient($this->config, $baseClient, $tokenProvider);
        $result = $httpClient->get('https://api.stanbic.com/accounts?sort=asc', ['limit' => 10]);

        $this->assertSame($response, $result);
    }

    /**
     * @runInSeparateProcess
     */
    public function testDefaultRequestFactoryThrowsWithoutPsr7Implementation(): void
    {
        class_exists(HttpClient::class);

        $autoloaders = spl_autoload_functions() ?: [];

        foreach ($autoloaders as $autoloader) {
            spl_autoload_unregister($autoloader);
        }

        try {
            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage('No PSR-7 request implementation found.');

            $method = new \ReflectionMethod(HttpClient::class, 'defaultRequestFactory');
            $method->setAccessible(true);
            $method->invoke(null);
        } finally {
            foreach ($autoloaders as $autoloader) {
                spl_autoload_register($autoloader);
            }
        }
    }

    /**
     * @return \Closure(string, string, array<string, string>, string): RequestInterface
     */
    private function requestFactory(): \Closure
    {
        return static function (string $method, string $uri, array $headers, string $body): RequestInterface {
            throw new \RuntimeException('Request factory should not be called in this test');
        };
    }
}
