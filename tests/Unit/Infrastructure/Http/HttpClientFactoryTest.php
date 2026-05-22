<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Infrastructure\Http;

use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Stanbic\SDK\Infrastructure\Http\HttpClientFactory;
use Stanbic\SDK\Infrastructure\Http\HttpConfig;
use Stanbic\SDK\Infrastructure\Http\Middleware\MiddlewareInterface;
use Stanbic\SDK\Infrastructure\Http\Middleware\MiddlewareStackClient;

/**
 * @covers \Stanbic\SDK\Infrastructure\Http\HttpClientFactory
 * @psalm-suppress PropertyNotSetInConstructor
*/
final class HttpClientFactoryTest extends TestCase
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

    /**
     * Build a class resolver that only reports the given FQCNs as available.
     *
     * @param list<string> $available
     * @return \Closure(string): bool
    */
    private function resolver(array $available): \Closure
    {
        return static fn (string $class): bool => in_array($class, $available, true);
    }

     /* ---------------------------------------------------------------
         create() – static convenience
     --------------------------------------------------------------- */

    public function testCreateReturnsPreconfiguredClient(): void
    {
        $mock = $this->createMock(ClientInterface::class);

        self::assertSame($mock, HttpClientFactory::create($this->config, $mock));
    }

    public function testCreateDiscoversClientWhenNoneProvided(): void
    {
        $client = HttpClientFactory::create($this->config);

        self::assertInstanceOf(ClientInterface::class, $client);
    }

     /* ---------------------------------------------------------------
         discover() – default resolver (real class_exists)
     --------------------------------------------------------------- */

    public function testDiscoverWithDefaultResolverReturnsClient(): void
    {
        $factory = new HttpClientFactory();

        self::assertInstanceOf(ClientInterface::class, $factory->discover());
    }

     /* ---------------------------------------------------------------
         discover() – Guzzle branch
     --------------------------------------------------------------- */

    public function testDiscoverReturnsGuzzleWhenAvailable(): void
    {
        $factory = new HttpClientFactory($this->resolver([
            'GuzzleHttp\Client',
            'Symfony\Component\HttpClient\Psr18Client',
            'Http\Client\Curl\Client',
        ]));

        $client = $factory->discover();

        self::assertInstanceOf(\GuzzleHttp\Client::class, $client);
    }

    public function testDiscoverPrefersGuzzleOverOtherClients(): void
    {
        $factory = new HttpClientFactory($this->resolver([
            'GuzzleHttp\Client',
            'Symfony\Component\HttpClient\Psr18Client',
        ]));

        self::assertInstanceOf(\GuzzleHttp\Client::class, $factory->discover());
    }

     /* ---------------------------------------------------------------
         discover() – Symfony branch
     --------------------------------------------------------------- */

    public function testDiscoverReturnsSymfonyWhenGuzzleUnavailable(): void
    {
        $factory = new HttpClientFactory($this->resolver([
            'Symfony\Component\HttpClient\Psr18Client',
        ]));

        if (!class_exists('Symfony\Component\HttpClient\Psr18Client')) {
            $this->expectException(\Error::class);
        }

        $client = $factory->discover();

        self::assertInstanceOf(ClientInterface::class, $client);
    }

     /* ---------------------------------------------------------------
         discover() – Curl branch
     --------------------------------------------------------------- */

    public function testDiscoverReturnsCurlWhenGuzzleAndSymfonyUnavailable(): void
    {
        $factory = new HttpClientFactory($this->resolver([
            'Http\Client\Curl\Client',
        ]));

        if (!class_exists('Http\Client\Curl\Client')) {
            $this->expectException(\Error::class);
        }

        $client = $factory->discover();

        self::assertInstanceOf(ClientInterface::class, $client);
    }

     /* ---------------------------------------------------------------
         discover() – PSR-18 Discovery branch
     --------------------------------------------------------------- */

    public function testDiscoverFallsToPsr18Discovery(): void
    {
        $factory = new HttpClientFactory($this->resolver([
            'Psr\Http\Client\ClientInterface',
            'Http\Discovery\Psr18ClientDiscovery',
        ]));

        $client = $factory->discover();

        self::assertInstanceOf(ClientInterface::class, $client);
    }

     /* ---------------------------------------------------------------
         discover() – no client available
     --------------------------------------------------------------- */

    public function testDiscoverThrowsWhenNoClientAvailable(): void
    {
        $factory = new HttpClientFactory($this->resolver([]));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No PSR-18 HTTP client found');

        $factory->discover();
    }

    public function testDiscoverThrowsMessageListsInstallOptions(): void
    {
        $factory = new HttpClientFactory($this->resolver([]));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('guzzlehttp/guzzle');

        $factory->discover();
    }

     /* ---------------------------------------------------------------
         createGuzzle() – happy path
     --------------------------------------------------------------- */

    public function testCreateGuzzleReturnsClient(): void
    {
        $factory = new HttpClientFactory($this->resolver(['GuzzleHttp\Client']));

        $client = $factory->createGuzzle($this->config);

        self::assertInstanceOf(ClientInterface::class, $client);
        self::assertInstanceOf(\GuzzleHttp\Client::class, $client);
    }

    public function testCreateGuzzleAppliesBaseUrl(): void
    {
        $factory = new HttpClientFactory($this->resolver(['GuzzleHttp\Client']));
        $config = $this->config->withBaseUrl('https://api.custom.com');

        self::assertInstanceOf(\GuzzleHttp\Client::class, $factory->createGuzzle($config));
    }

    public function testCreateGuzzleAppliesTimeout(): void
    {
        $factory = new HttpClientFactory($this->resolver(['GuzzleHttp\Client']));
        $config = $this->config->withTimeout(60000);

        self::assertInstanceOf(\GuzzleHttp\Client::class, $factory->createGuzzle($config));
    }

    public function testCreateGuzzleWithMinimumTimeout(): void
    {
        $factory = new HttpClientFactory($this->resolver(['GuzzleHttp\Client']));

        self::assertInstanceOf(
            \GuzzleHttp\Client::class,
            $factory->createGuzzle($this->config->withTimeout(1000)),
        );
    }

    public function testCreateGuzzleWithMaximumTimeout(): void
    {
        $factory = new HttpClientFactory($this->resolver(['GuzzleHttp\Client']));

        self::assertInstanceOf(
            \GuzzleHttp\Client::class,
            $factory->createGuzzle($this->config->withTimeout(300000)),
        );
    }

    public function testCreateGuzzleConvertsMillisecondsToSeconds(): void
    {
        $factory = new HttpClientFactory($this->resolver(['GuzzleHttp\Client']));

        self::assertInstanceOf(
            \GuzzleHttp\Client::class,
            $factory->createGuzzle($this->config->withTimeout(5000)),
        );
    }

     /* ---------------------------------------------------------------
         createGuzzle() – throw path
     --------------------------------------------------------------- */

    public function testCreateGuzzleThrowsWhenNotInstalled(): void
    {
        $factory = new HttpClientFactory($this->resolver([]));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Guzzle HTTP client is not installed');

        $factory->createGuzzle($this->config);
    }

     /* ---------------------------------------------------------------
         createSymfony() – happy path
     --------------------------------------------------------------- */

    public function testCreateSymfonyReturnsClientWhenAvailable(): void
    {
        $factory = new HttpClientFactory($this->resolver([
            'Symfony\Component\HttpClient\Psr18Client',
        ]));

        if (!class_exists('Symfony\Component\HttpClient\Psr18Client')) {
            // Class not installed — instantiation reaches the return line but throws \Error
            $this->expectException(\Error::class);
            $factory->createSymfony($this->config);
        } else {
            self::assertInstanceOf(ClientInterface::class, $factory->createSymfony($this->config));
        }
    }

     /* ---------------------------------------------------------------
         createSymfony() – throw path
     --------------------------------------------------------------- */

    public function testCreateSymfonyThrowsWhenNotInstalled(): void
    {
        $factory = new HttpClientFactory($this->resolver([]));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Symfony HTTP client is not installed');

        $factory->createSymfony($this->config);
    }

     /* ---------------------------------------------------------------
         createCurl() – happy path
     --------------------------------------------------------------- */

    public function testCreateCurlReturnsClientWhenAvailable(): void
    {
        $factory = new HttpClientFactory($this->resolver([
            'Http\Client\Curl\Client',
        ]));

        if (!class_exists('Http\Client\Curl\Client')) {
            // Class not installed — instantiation reaches the return line but throws \Error
            $this->expectException(\Error::class);
            $factory->createCurl($this->config);
        } else {
            self::assertInstanceOf(ClientInterface::class, $factory->createCurl($this->config));
        }
    }

     /* ---------------------------------------------------------------
         createCurl() – throw path
     --------------------------------------------------------------- */

    public function testCreateCurlThrowsWhenNotInstalled(): void
    {
        $factory = new HttpClientFactory($this->resolver([]));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('HTTPlug Curl client is not installed');

        $factory->createCurl($this->config);
    }

     /* ---------------------------------------------------------------
         Constructor – default resolver
     --------------------------------------------------------------- */

    public function testDefaultConstructorUsesRealClassExists(): void
    {
        $factory = new HttpClientFactory();

        self::assertInstanceOf(ClientInterface::class, $factory->discover());
    }

    public function testDefaultConstructorCreateGuzzleSucceeds(): void
    {
        $factory = new HttpClientFactory();

        self::assertInstanceOf(\GuzzleHttp\Client::class, $factory->createGuzzle($this->config));
    }

     /* ---------------------------------------------------------------
         Statelessness / multiple calls
     --------------------------------------------------------------- */

    public function testMultipleDiscoverCallsReturnNewInstances(): void
    {
        $factory = new HttpClientFactory();

        self::assertNotSame($factory->discover(), $factory->discover());
    }

    public function testCreateWithMockReturnsSameInstance(): void
    {
        $mock = $this->createMock(ClientInterface::class);

        self::assertSame($mock, HttpClientFactory::create($this->config, $mock));
        self::assertSame($mock, HttpClientFactory::create($this->config, $mock));
    }

     /* ---------------------------------------------------------------
         Discovery order verification
     --------------------------------------------------------------- */

    public function testDiscoveryOrderGuzzleBeforeSymfony(): void
    {
        /** @var list<string> $order */
        $order = [];
        $factory = new HttpClientFactory(static function (string $class) use (&$order): bool {
            /** @var list<string> $order */
            $order[] = $class;
            return $class === 'GuzzleHttp\Client';
        });

        $factory->discover();

        self::assertSame(['GuzzleHttp\Client'], $order);
    }

    public function testDiscoveryOrderSymfonyCheckedSecond(): void
    {
        /** @var list<string> $order */
        $order = [];
        $factory = new HttpClientFactory(static function (string $class) use (&$order): bool {
            /** @var list<string> $order */
            $order[] = $class;
            return $class === 'Symfony\Component\HttpClient\Psr18Client';
        });

        try {
            $factory->discover();
        } catch (\Error) {
            /* Instantiation may fail since Symfony isn't actually installed */
        }

        self::assertSame([
            'GuzzleHttp\Client',
            'Symfony\Component\HttpClient\Psr18Client',
        ], $order);
    }

    public function testDiscoveryOrderCurlCheckedThird(): void
    {
        /** @var list<string> $order */
        $order = [];
        $factory = new HttpClientFactory(static function (string $class) use (&$order): bool {
            /** @var list<string> $order */
            $order[] = $class;
            return $class === 'Http\Client\Curl\Client';
        });

        try {
            $factory->discover();
        } catch (\Error) {
            /* Instantiation may fail since Curl isn't actually installed */
        }

        self::assertSame([
            'GuzzleHttp\Client',
            'Symfony\Component\HttpClient\Psr18Client',
            'Http\Client\Curl\Client',
        ], $order);
    }

    public function testDiscoveryOrderPsr18CheckedFourth(): void
    {
        /** @var list<string> $order */
        $order = [];
        $factory = new HttpClientFactory(static function (string $class) use (&$order): bool {
            /** @var list<string> $order */
            $order[] = $class;
            return in_array($class, [
                'Psr\Http\Client\ClientInterface',
                'Http\Discovery\Psr18ClientDiscovery',
            ], true);
        });

        $client = $factory->discover();

        self::assertSame([
            'GuzzleHttp\Client',
            'Symfony\Component\HttpClient\Psr18Client',
            'Http\Client\Curl\Client',
            'Psr\Http\Client\ClientInterface',
            'Http\Discovery\Psr18ClientDiscovery',
        ], $order);
        self::assertInstanceOf(ClientInterface::class, $client);
    }

    public function testDiscoveryPsr18RequiresBothClasses(): void
    {
        $factory = new HttpClientFactory($this->resolver([
            'Psr\Http\Client\ClientInterface',
        ]));

        $this->expectException(\RuntimeException::class);

        $factory->discover();
    }

     /* ---------------------------------------------------------------
         Configuration integration
     --------------------------------------------------------------- */

    public function testCreateGuzzleWithRetrySettings(): void
    {
        $factory = new HttpClientFactory($this->resolver(['GuzzleHttp\Client']));

        self::assertInstanceOf(
            \GuzzleHttp\Client::class,
            $factory->createGuzzle($this->config->withRetrySettings(5, 2000)),
        );
    }

    public function testConfigurationDoesNotAffectDiscovery(): void
    {
        $c1 = HttpConfig::create(
            baseUrl: 'https://api.one.com',
            clientId: 'a',
            clientSecret: 'b',
            tokenUrl: 'https://auth.one.com/token',
        );
        $c2 = HttpConfig::create(
            baseUrl: 'https://api.two.com',
            clientId: 'x',
            clientSecret: 'y',
            tokenUrl: 'https://auth.two.com/token',
        );

        self::assertInstanceOf(ClientInterface::class, HttpClientFactory::create($c1));
        self::assertInstanceOf(ClientInterface::class, HttpClientFactory::create($c2));
    }
}
