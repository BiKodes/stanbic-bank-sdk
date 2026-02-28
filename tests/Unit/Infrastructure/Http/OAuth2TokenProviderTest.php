<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Infrastructure\Http;

use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Stanbic\SDK\Infrastructure\Http\AccessToken;
use Stanbic\SDK\Infrastructure\Http\HttpConfig;
use Stanbic\SDK\Infrastructure\Http\OAuth2TokenProvider;

/**
 * @covers \Stanbic\SDK\Infrastructure\Http\OAuth2TokenProvider
 * @psalm-suppress PropertyNotSetInConstructor
*/
final class OAuth2TokenProviderTest extends TestCase
{
    private HttpConfig $config;

    protected function setUp(): void
    {
        $this->config = HttpConfig::create(
            baseUrl: 'https://api.stanbic.com',
            clientId: 'test-client-id',
            clientSecret: 'test-client-secret',
            tokenUrl: 'https://auth.stanbic.com/oauth/token',
        );
    }

    /*---------------------------------------------------------------
     Helpers
    ----------------------------------------------------------------*/

    /**
     * Build a mock HTTP client that returns the given JSON body and status code.
     *
     * @param string $jsonBody
     * @param int $statusCode
     * @return ClientInterface
    */
    private function mockHttpClient(string $jsonBody, int $statusCode = 200): ClientInterface
    {
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('__toString')->willReturn($jsonBody);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn($statusCode);
        $response->method('getBody')->willReturn($stream);

        $client = $this->createMock(ClientInterface::class);
        $client->method('sendRequest')->willReturn($response);

        return $client;
    }

    /**
     * Build a mock HTTP client that throws on sendRequest.
    */
    private function mockHttpClientThatThrows(\Throwable $exception): ClientInterface
    {
        $client = $this->createMock(ClientInterface::class);
        $client->method('sendRequest')->willThrowException($exception);

        return $client;
    }

    /**
     * Build a mock request factory.
     *
     * @return \Closure(string, string, array<string, string>, string): RequestInterface
    */
    private function mockRequestFactory(): \Closure
    {
        $mock = $this->createMock(RequestInterface::class);

        return static fn (
            string $method,
            string $uri,
            array $headers,
            string $body,
        ): RequestInterface => $mock;
    }

    /**
     * Standard successful token JSON response.
    */
    private function tokenJson(int $expiresIn = 3600, string $tokenValue = 'test-access-token'): string
    {
        return json_encode([
            'access_token' => $tokenValue,
            'expires_in' => $expiresIn,
            'token_type' => 'Bearer',
            'scope' => 'read write',
        ], JSON_THROW_ON_ERROR);
    }

    /**
     * Build a fixed-time clock closure.
     *
     * @return \Closure(): float
    */
    private function fixedClock(float $time): \Closure
    {
        return static fn (): float => $time;
    }

    /**
     * Build an advancing clock that returns sequential timestamps.
     *
     * @param list<float> $times
     * @return \Closure(): float
    */
    private function advancingClock(array $times): \Closure
    {
        $index = 0;
        assert($times !== []); // Psalm: ensure non-empty
        return static function () use ($times, &$index): float {
            /** @var int $index */
            $time = $times[$index] ?? $times[array_key_last($times)];
            $index++;
            return $time;
        };
    }

    /*---------------------------------------------------------------
     getToken() – fetches on first call
    ---------------------------------------------------------------*/

    public function testGetTokenFetchesOnFirstCall(): void
    {
        $provider = new OAuth2TokenProvider(
            $this->config,
            $this->mockHttpClient($this->tokenJson()),
            $this->mockRequestFactory(),
            $this->fixedClock(1000.0),
        );

        $token = $provider->getToken();

        self::assertSame('test-access-token', $token);
    }

    public function testGetTokenCachesResult(): void
    {
        $client = $this->createMock(ClientInterface::class);

        $stream = $this->createMock(StreamInterface::class);
        $stream->method('__toString')->willReturn($this->tokenJson());

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getBody')->willReturn($stream);

        /* sendRequest should only be called ONCE */
        $client->expects(self::once())
            ->method('sendRequest')
            ->willReturn($response);

        $provider = new OAuth2TokenProvider(
            $this->config,
            $client,
            $this->mockRequestFactory(),
            $this->fixedClock(1000.0),
        );

        $first = $provider->getToken();
        $second = $provider->getToken();

        self::assertSame($first, $second);
    }

    public function testGetTokenRefetchesWhenExpired(): void
    {
        $client = $this->createMock(ClientInterface::class);

        /* First call: return token with 60s lifetime */
        $stream1 = $this->createMock(StreamInterface::class);
        $stream1->method('__toString')->willReturn($this->tokenJson(60, 'token-1'));
        $response1 = $this->createMock(ResponseInterface::class);
        $response1->method('getStatusCode')->willReturn(200);
        $response1->method('getBody')->willReturn($stream1);

        /* Second call: return different token */
        $stream2 = $this->createMock(StreamInterface::class);
        $stream2->method('__toString')->willReturn($this->tokenJson(3600, 'token-2'));
        $response2 = $this->createMock(ResponseInterface::class);
        $response2->method('getStatusCode')->willReturn(200);
        $response2->method('getBody')->willReturn($stream2);

        $client->expects(self::exactly(2))
            ->method('sendRequest')
            ->willReturnOnConsecutiveCalls($response1, $response2);

        /*Clock calls:
            1. parseTokenResponse issuedAt for token-1 → 1000.0
            2. getToken isExpired check (expiresAt = 1000+60-30 = 1030, 1040 >= 1030 → expired) → 1040.0
            3. parseTokenResponse issuedAt for token-2 → 1040.0
        */
        $provider = new OAuth2TokenProvider(
            $this->config,
            $client,
            $this->mockRequestFactory(),
            $this->advancingClock([1000.0, 1040.0, 1040.0]),
        );

        $first = $provider->getToken();
        self::assertSame('token-1', $first);

        $second = $provider->getToken();
        self::assertSame('token-2', $second);
    }

    /*---------------------------------------------------------------
    getToken() – uses cache for non-expired token
    -----------------------------------------------------------------*/

    public function testGetTokenReturnsCachedWhenNotExpired(): void
    {
        $provider = new OAuth2TokenProvider(
            $this->config,
            $this->mockHttpClient($this->tokenJson(3600, 'cached-tok')),
            $this->mockRequestFactory(),
            $this->fixedClock(1000.0),
        );

        /* Fetch and cache */
        $provider->getToken();

        /* Second call — should use cache */
        self::assertSame('cached-tok', $provider->getToken());
    }

    /*---------------------------------------------------------------
    refreshToken()
    ----------------------------------------------------------------*/

    public function testRefreshTokenAlwaysFetches(): void
    {
        $client = $this->createMock(ClientInterface::class);

        $stream1 = $this->createMock(StreamInterface::class);
        $stream1->method('__toString')->willReturn($this->tokenJson(3600, 'original'));
        $response1 = $this->createMock(ResponseInterface::class);
        $response1->method('getStatusCode')->willReturn(200);
        $response1->method('getBody')->willReturn($stream1);

        $stream2 = $this->createMock(StreamInterface::class);
        $stream2->method('__toString')->willReturn($this->tokenJson(3600, 'refreshed'));
        $response2 = $this->createMock(ResponseInterface::class);
        $response2->method('getStatusCode')->willReturn(200);
        $response2->method('getBody')->willReturn($stream2);

        $client->expects(self::exactly(2))
            ->method('sendRequest')
            ->willReturnOnConsecutiveCalls($response1, $response2);

        $provider = new OAuth2TokenProvider(
            $this->config,
            $client,
            $this->mockRequestFactory(),
            $this->fixedClock(1000.0),
        );

        $original = $provider->getToken();
        self::assertSame('original', $original);

        $refreshed = $provider->refreshToken();
        self::assertSame('refreshed', $refreshed);
    }

    public function testRefreshTokenUpdatesCachedToken(): void
    {
        $client = $this->createMock(ClientInterface::class);

        $stream1 = $this->createMock(StreamInterface::class);
        $stream1->method('__toString')->willReturn($this->tokenJson(3600, 'v1'));
        $response1 = $this->createMock(ResponseInterface::class);
        $response1->method('getStatusCode')->willReturn(200);
        $response1->method('getBody')->willReturn($stream1);

        $stream2 = $this->createMock(StreamInterface::class);
        $stream2->method('__toString')->willReturn($this->tokenJson(3600, 'v2'));
        $response2 = $this->createMock(ResponseInterface::class);
        $response2->method('getStatusCode')->willReturn(200);
        $response2->method('getBody')->willReturn($stream2);

        $client->method('sendRequest')
            ->willReturnOnConsecutiveCalls($response1, $response2);

        $provider = new OAuth2TokenProvider(
            $this->config,
            $client,
            $this->mockRequestFactory(),
            $this->fixedClock(1000.0),
        );

        $provider->getToken();
        $provider->refreshToken();

        /* After refresh, getToken should return the refreshed token (cached) */
        self::assertSame('v2', $provider->getToken());
    }

    /*---------------------------------------------------------------
    invalidate()
    ----------------------------------------------------------------*/

    public function testInvalidateClearsCachedToken(): void
    {
        $provider = new OAuth2TokenProvider(
            $this->config,
            $this->mockHttpClient($this->tokenJson()),
            $this->mockRequestFactory(),
            $this->fixedClock(1000.0),
        );

        $provider->getToken();
        self::assertNotNull($provider->getCachedToken());

        $provider->invalidate();
        /** @var AccessToken|null $cachedAfter */
        $cachedAfter = $provider->getCachedToken();
        self::assertNull($cachedAfter);
    }

    public function testInvalidateForcesNewFetchOnNextGetToken(): void
    {
        $client = $this->createMock(ClientInterface::class);

        $stream1 = $this->createMock(StreamInterface::class);
        $stream1->method('__toString')->willReturn($this->tokenJson(3600, 'before'));
        $response1 = $this->createMock(ResponseInterface::class);
        $response1->method('getStatusCode')->willReturn(200);
        $response1->method('getBody')->willReturn($stream1);

        $stream2 = $this->createMock(StreamInterface::class);
        $stream2->method('__toString')->willReturn($this->tokenJson(3600, 'after'));
        $response2 = $this->createMock(ResponseInterface::class);
        $response2->method('getStatusCode')->willReturn(200);
        $response2->method('getBody')->willReturn($stream2);

        $client->expects(self::exactly(2))
            ->method('sendRequest')
            ->willReturnOnConsecutiveCalls($response1, $response2);

        $provider = new OAuth2TokenProvider(
            $this->config,
            $client,
            $this->mockRequestFactory(),
            $this->fixedClock(1000.0),
        );

        self::assertSame('before', $provider->getToken());

        $provider->invalidate();

        self::assertSame('after', $provider->getToken());
    }

    public function testInvalidateWhenAlreadyEmptyIsNoOp(): void
    {
        $provider = new OAuth2TokenProvider(
            $this->config,
            $this->mockHttpClient($this->tokenJson()),
            $this->mockRequestFactory(),
            $this->fixedClock(1000.0),
        );

        /* Should not throw */
        $provider->invalidate();
        self::assertNull($provider->getCachedToken());
    }

    /* ---------------------------------------------------------------
    getCachedToken()
    -----------------------------------------------------------------*/

    public function testGetCachedTokenReturnsNullBeforeFirstFetch(): void
    {
        $provider = new OAuth2TokenProvider(
            $this->config,
            $this->mockHttpClient($this->tokenJson()),
            $this->mockRequestFactory(),
            $this->fixedClock(1000.0),
        );

        self::assertNull($provider->getCachedToken());
    }

    public function testGetCachedTokenReturnsAccessTokenAfterFetch(): void
    {
        $provider = new OAuth2TokenProvider(
            $this->config,
            $this->mockHttpClient($this->tokenJson(3600, 'my-token')),
            $this->mockRequestFactory(),
            $this->fixedClock(1000.0),
        );

        $provider->getToken();

        $cached = $provider->getCachedToken();
        self::assertInstanceOf(AccessToken::class, $cached); // Psalm: ensure not null
        self::assertSame('my-token', $cached->token);
        self::assertSame(3600, $cached->expiresIn);
    }

    /*---------------------------------------------------------------
    HTTP error handling
    ----------------------------------------------------------------*/

    public function testGetTokenThrowsOnHttpError(): void
    {
        $provider = new OAuth2TokenProvider(
            $this->config,
            $this->mockHttpClient('{"error":"unauthorized"}', 401),
            $this->mockRequestFactory(),
            $this->fixedClock(1000.0),
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('OAuth2 token request failed with status 401');

        $provider->getToken();
    }

    public function testGetTokenThrowsOn500Error(): void
    {
        $provider = new OAuth2TokenProvider(
            $this->config,
            $this->mockHttpClient('Internal Server Error', 500),
            $this->mockRequestFactory(),
            $this->fixedClock(1000.0),
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('OAuth2 token request failed with status 500');

        $provider->getToken();
    }

    public function testGetTokenThrowsOnNetworkException(): void
    {
        $provider = new OAuth2TokenProvider(
            $this->config,
            $this->mockHttpClientThatThrows(new \RuntimeException('Connection refused')),
            $this->mockRequestFactory(),
            $this->fixedClock(1000.0),
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to request OAuth2 token: Connection refused');

        $provider->getToken();
    }

    public function testGetTokenThrowsOnNetworkExceptionWrapsOriginal(): void
    {
        $original = new \RuntimeException('DNS failure');

        $provider = new OAuth2TokenProvider(
            $this->config,
            $this->mockHttpClientThatThrows($original),
            $this->mockRequestFactory(),
            $this->fixedClock(1000.0),
        );

        try {
            $provider->getToken();
            self::fail('Expected RuntimeException');
        } catch (\RuntimeException $e) {
            self::assertSame($original, $e->getPrevious());
        }
    }

    /*---------------------------------------------------------------
    Response parsing errors
    ----------------------------------------------------------------*/

    public function testGetTokenThrowsOnInvalidJson(): void
    {
        $provider = new OAuth2TokenProvider(
            $this->config,
            $this->mockHttpClient('not valid json'),
            $this->mockRequestFactory(),
            $this->fixedClock(1000.0),
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('could not decode JSON');

        $provider->getToken();
    }

    public function testGetTokenThrowsOnMissingAccessToken(): void
    {
        $provider = new OAuth2TokenProvider(
            $this->config,
            $this->mockHttpClient('{"expires_in": 3600}'),
            $this->mockRequestFactory(),
            $this->fixedClock(1000.0),
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('missing or invalid "access_token"');

        $provider->getToken();
    }

    public function testGetTokenThrowsOnNonStringAccessToken(): void
    {
        $provider = new OAuth2TokenProvider(
            $this->config,
            $this->mockHttpClient('{"access_token": 12345, "expires_in": 3600}'),
            $this->mockRequestFactory(),
            $this->fixedClock(1000.0),
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('missing or invalid "access_token"');

        $provider->getToken();
    }

    public function testGetTokenThrowsOnMissingExpiresIn(): void
    {
        $provider = new OAuth2TokenProvider(
            $this->config,
            $this->mockHttpClient('{"access_token": "tok"}'),
            $this->mockRequestFactory(),
            $this->fixedClock(1000.0),
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('missing or invalid "expires_in"');

        $provider->getToken();
    }

    public function testGetTokenAcceptsNumericStringExpiresIn(): void
    {
        $json = '{"access_token": "tok", "expires_in": "3600"}';

        $provider = new OAuth2TokenProvider(
            $this->config,
            $this->mockHttpClient($json),
            $this->mockRequestFactory(),
            $this->fixedClock(1000.0),
        );

        $token = $provider->getToken();
        self::assertSame('tok', $token);

        $cached = $provider->getCachedToken();
        self::assertNotNull($cached);
        self::assertSame(3600, $cached->expiresIn);
    }

    public function testGetTokenThrowsOnNonNumericExpiresIn(): void
    {
        $json = '{"access_token": "tok", "expires_in": "never"}';

        $provider = new OAuth2TokenProvider(
            $this->config,
            $this->mockHttpClient($json),
            $this->mockRequestFactory(),
            $this->fixedClock(1000.0),
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('missing or invalid "expires_in"');

        $provider->getToken();
    }

    /*---------------------------------------------------------------
    Request construction verification
    ----------------------------------------------------------------*/

    public function testRequestUsesPostMethod(): void
    {
        /** @var string $capturedMethod */
        $capturedMethod = '';

        $requestFactory = function (
            string $method,
            string $uri,
            array $headers,
            string $body,
        ) use (&$capturedMethod): RequestInterface {
            /** @var string $capturedMethod */
            $capturedMethod = $method;
            return $this->createMock(RequestInterface::class);
        };

        $provider = new OAuth2TokenProvider(
            $this->config,
            $this->mockHttpClient($this->tokenJson()),
            \Closure::fromCallable($requestFactory),
            $this->fixedClock(1000.0),
        );

        $provider->getToken();

        self::assertSame('POST', $capturedMethod);
    }

    public function testRequestUsesTokenUrl(): void
    {
        /** @var string $capturedUri */
        $capturedUri = '';

        $requestFactory = function (
            string $method,
            string $uri,
            array $headers,
            string $body,
        ) use (&$capturedUri): RequestInterface {
            /** @var string $capturedUri */
            $capturedUri = $uri;
            return $this->createMock(RequestInterface::class);
        };

        $provider = new OAuth2TokenProvider(
            $this->config,
            $this->mockHttpClient($this->tokenJson()),
            \Closure::fromCallable($requestFactory),
            $this->fixedClock(1000.0),
        );

        $provider->getToken();

        self::assertSame('https://auth.stanbic.com/oauth/token', $capturedUri);
    }

    public function testRequestBodyContainsClientCredentials(): void
    {
        /** @var string $capturedBody */
        $capturedBody = '';

        $requestFactory = function (
            string $method,
            string $uri,
            array $headers,
            string $body,
        ) use (&$capturedBody): RequestInterface {
            /** @var string $capturedBody */
            $capturedBody = $body;
            return $this->createMock(RequestInterface::class);
        };

        $provider = new OAuth2TokenProvider(
            $this->config,
            $this->mockHttpClient($this->tokenJson()),
            \Closure::fromCallable($requestFactory),
            $this->fixedClock(1000.0),
        );

        $provider->getToken();

        /** @var string $capturedBody */
        parse_str($capturedBody, $params);

        self::assertSame('client_credentials', $params['grant_type']);
        self::assertSame('test-client-id', $params['client_id']);
        self::assertSame('test-client-secret', $params['client_secret']);
    }

    public function testRequestHeadersContainContentType(): void
    {
        /** @var array<string, string> $capturedHeaders */
        $capturedHeaders = [];

        $requestFactory = function (
            string $method,
            string $uri,
            array $headers,
            string $body,
        ) use (&$capturedHeaders): RequestInterface {
            /** @var array<string, string> $capturedHeaders */
            $capturedHeaders = $headers;
            return $this->createMock(RequestInterface::class);
        };

        $provider = new OAuth2TokenProvider(
            $this->config,
            $this->mockHttpClient($this->tokenJson()),
            \Closure::fromCallable($requestFactory),
            $this->fixedClock(1000.0),
        );

        $provider->getToken();

        self::assertSame('application/x-www-form-urlencoded', $capturedHeaders['Content-Type']);
        self::assertSame('application/json', $capturedHeaders['Accept']);
    }

    /*---------------------------------------------------------------
    PSR-3 Logging
    ----------------------------------------------------------------*/

    public function testLogsTokenAcquisition(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::atLeastOnce())
            ->method('info')
            ->with(self::stringContains('OAuth2 token'));

        $config = $this->config->withLogger($logger);

        $provider = new OAuth2TokenProvider(
            $config,
            $this->mockHttpClient($this->tokenJson()),
            $this->mockRequestFactory(),
            $this->fixedClock(1000.0),
        );

        $provider->getToken();
    }

    public function testLogsCacheHit(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::atLeastOnce())
            ->method('debug')
            ->with(self::stringContains('cached'));

        $config = $this->config->withLogger($logger);

        $provider = new OAuth2TokenProvider(
            $config,
            $this->mockHttpClient($this->tokenJson()),
            $this->mockRequestFactory(),
            $this->fixedClock(1000.0),
        );

        /* Fetch */
        $provider->getToken();
        /* Cache hit */
        $provider->getToken();
    }

    public function testLogsInvalidation(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::atLeastOnce())
            ->method('info')
            ->with(self::stringContains('invalidated'));

        $config = $this->config->withLogger($logger);

        $provider = new OAuth2TokenProvider(
            $config,
            $this->mockHttpClient($this->tokenJson()),
            $this->mockRequestFactory(),
            $this->fixedClock(1000.0),
        );

        $provider->invalidate();
    }

    public function testLogsHttpError(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::atLeastOnce())
            ->method('error');

        $config = $this->config->withLogger($logger);

        $provider = new OAuth2TokenProvider(
            $config,
            $this->mockHttpClientThatThrows(new \RuntimeException('fail')),
            $this->mockRequestFactory(),
            $this->fixedClock(1000.0),
        );

        try {
            $provider->getToken();
        } catch (\RuntimeException) {
            /* expected */
        }
    }

    /*---------------------------------------------------------------
    Default request factory (uses Guzzle PSR-7)
    ----------------------------------------------------------------*/

    public function testDefaultRequestFactoryCreatesValidRequest(): void
    {
        $provider = new OAuth2TokenProvider(
            $this->config,
            $this->mockHttpClient($this->tokenJson()),
            null,
            $this->fixedClock(1000.0),
        );

        $token = $provider->getToken();

        self::assertSame('test-access-token', $token);
    }

    /*---------------------------------------------------------------
    Default clock
    ---------------------------------------------------------------*/

    public function testDefaultClockUsesRealTime(): void
    {
        $provider = new OAuth2TokenProvider(
            $this->config,
            $this->mockHttpClient($this->tokenJson()),
            $this->mockRequestFactory(),
            /* use default clock */
            null,
        );

        $token = $provider->getToken();
        self::assertSame('test-access-token', $token);

        /* Token should not be expired (3600s lifetime) */
        $cached = $provider->getCachedToken();
        self::assertNotNull($cached);
        self::assertFalse($cached->isExpired());
    }

    /*---------------------------------------------------------------
    No logger configured
    ----------------------------------------------------------------*/

    public function testWorksWithoutLogger(): void
    {
        $config = HttpConfig::create(
            baseUrl: 'https://api.stanbic.com',
            clientId: 'id',
            clientSecret: 'secret',
            tokenUrl: 'https://auth.stanbic.com/token',
        );

        $provider = new OAuth2TokenProvider(
            $config,
            $this->mockHttpClient($this->tokenJson()),
            $this->mockRequestFactory(),
            $this->fixedClock(1000.0),
        );

        self::assertSame('test-access-token', $provider->getToken());
    }

    /*---------------------------------------------------------------
    Default request factory (uses Guzzle PSR-7)
    ----------------------------------------------------------------*/

    public function testImplementsTokenProviderInterface(): void
    {
        $provider = new OAuth2TokenProvider(
            $this->config,
            $this->mockHttpClient($this->tokenJson()),
            $this->mockRequestFactory(),
            $this->fixedClock(1000.0),
        );

        self::assertInstanceOf(
            \Stanbic\SDK\Infrastructure\Http\TokenProviderInterface::class,
            $provider,
        );
    }

    /*---------------------------------------------------------------
    Token scope and type preserved
    ----------------------------------------------------------------*/

    public function testTokenTypeAndScopePreserved(): void
    {
        $json = json_encode([
            'access_token' => 'tok',
            'expires_in' => 3600,
            'token_type' => 'mac',
            'scope' => 'admin payments',
        ], JSON_THROW_ON_ERROR);

        $provider = new OAuth2TokenProvider(
            $this->config,
            $this->mockHttpClient($json),
            $this->mockRequestFactory(),
            $this->fixedClock(1000.0),
        );

        $provider->getToken();

        $cached = $provider->getCachedToken();
        self::assertNotNull($cached);
        self::assertSame('mac', $cached->tokenType);
        self::assertSame('admin payments', $cached->scope);
    }

    /*---------------------------------------------------------------
    Empty body response
    ----------------------------------------------------------------*/

    public function testGetTokenThrowsOnEmptyResponse(): void
    {
        $provider = new OAuth2TokenProvider(
            $this->config,
            $this->mockHttpClient(''),
            $this->mockRequestFactory(),
            $this->fixedClock(1000.0),
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('could not decode JSON');

        $provider->getToken();
    }

    /*---------------------------------------------------------------
    3xx status codes
    ----------------------------------------------------------------*/

    public function testGetTokenThrowsOn3xxStatus(): void
    {
        $provider = new OAuth2TokenProvider(
            $this->config,
            $this->mockHttpClient('', 302),
            $this->mockRequestFactory(),
            $this->fixedClock(1000.0),
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('OAuth2 token request failed with status 302');

        $provider->getToken();
    }
}
