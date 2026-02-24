<?php

declare(strict_types=1);

namespace Stanbic\SDK\Infrastructure\Http;

use Psr\Http\Client\ClientInterface;

/**
 * HTTP Client Factory.
 *
 * Factory for creating PSR-18 HTTP clients with appropriate configuration.
 * Supports discovery of available PSR-18 implementations.
 *
 * Uses an injectable class resolver to allow testing all discovery branches
 * without requiring every HTTP client package to be installed.
*/
final class HttpClientFactory
{
    /**
     * Class resolver callable.
     *
     * Returns true if a given fully-qualified class name is available.
     *
     * @var \Closure(string): bool
    */
    private \Closure $classExists;

    /**
     * @param (\Closure(string): bool)|null $classExists Optional class resolver for testing.
     *                                                    Defaults to native class_exists().
    */
    public function __construct(?\Closure $classExists = null)
    {
        $this->classExists = $classExists ?? static fn (string $class): bool => class_exists($class);
    }

    /**
     * Static convenience: create HTTP client from configuration.
     *
     * Attempts to auto-discover available PSR-18 client implementations
     * or uses provided client instance.
     *
     * @param HttpConfig $config HTTP configuration
     * @param ClientInterface|null $client Optional pre-configured PSR-18 client
     * @return ClientInterface Configured HTTP client
     * @throws \RuntimeException If no PSR-18 client is available
    */
    public static function create(HttpConfig $config, ?ClientInterface $client = null): ClientInterface
    {
        if ($client !== null) {
            return $client;
        }

        return (new self())->discover();
    }

    /**
     * Discover available PSR-18 HTTP client.
     *
     * Checks for common PSR-18 implementations in order of preference:
     * 1. Guzzle 7+
     * 2. Symfony HTTP Client
     * 3. HTTPlug (php-http/curl-client)
     * 4. PSR-18 Discovery (psr/http-client-implementation)
     *
     * @return ClientInterface Discovered HTTP client
     * @throws \RuntimeException If no PSR-18 client is available
    */
    public function discover(): ClientInterface
    {
        $exists = $this->classExists;

        if ($exists('GuzzleHttp\Client')) {
            /** @var ClientInterface */
            return new \GuzzleHttp\Client();
        }

        if ($exists('Symfony\Component\HttpClient\Psr18Client')) {
            /** @psalm-suppress UndefinedClass */
            /** @var ClientInterface */
            return new \Symfony\Component\HttpClient\Psr18Client();
        }

        if ($exists('Http\Client\Curl\Client')) {
            /** @psalm-suppress UndefinedClass */
            /** @var ClientInterface */
            return new \Http\Client\Curl\Client();
        }

        if ($exists('Psr\Http\Client\ClientInterface') && $exists('Http\Discovery\Psr18ClientDiscovery')) {
            $client = \Http\Discovery\Psr18ClientDiscovery::find();
            return $client;
        }

        throw new \RuntimeException(
            'No PSR-18 HTTP client found. Please install one of: ' .
            'guzzlehttp/guzzle, symfony/http-client, php-http/curl-client, or psr/http-client-implementation'
        );
    }

    /**
     * Create Guzzle HTTP client.
     *
     * @param HttpConfig $config HTTP configuration
     * @return ClientInterface Guzzle HTTP client
     * @throws \RuntimeException If Guzzle is not available
    */
    public function createGuzzle(HttpConfig $config): ClientInterface
    {
        $exists = $this->classExists;

        if (!$exists('GuzzleHttp\Client')) {
            throw new \RuntimeException(
                'Guzzle HTTP client is not installed. Run: composer require guzzlehttp/guzzle'
            );
        }

        /** @var ClientInterface */
        return new \GuzzleHttp\Client([
            'base_uri' => $config->getBaseUrl(),
            'timeout' => $config->getTimeoutMs() / 1000,
            'connect_timeout' => 10,
            'http_errors' => false,
        ]);
    }

    /**
     * Create Symfony HTTP client.
     *
     * @param HttpConfig $config HTTP configuration
     * @return ClientInterface Symfony HTTP client
     * @throws \RuntimeException If Symfony HTTP client is not available
    */
    public function createSymfony(HttpConfig $config): ClientInterface
    {
        $exists = $this->classExists;

        if (!$exists('Symfony\Component\HttpClient\Psr18Client')) {
            throw new \RuntimeException(
                'Symfony HTTP client is not installed. Run: composer require symfony/http-client'
            );
        }

        /** @psalm-suppress UndefinedClass */
        /** @var ClientInterface */
        return new \Symfony\Component\HttpClient\Psr18Client();
    }

    /**
     * Create HTTPlug Curl client.
     *
     * @param HttpConfig $config HTTP configuration
     * @return ClientInterface HTTPlug Curl client
     * @throws \RuntimeException If HTTPlug Curl client is not available
    */
    public function createCurl(HttpConfig $config): ClientInterface
    {
        $exists = $this->classExists;

        if (!$exists('Http\Client\Curl\Client')) {
            throw new \RuntimeException(
                'HTTPlug Curl client is not installed. Run: composer require php-http/curl-client'
            );
        }

        /** @psalm-suppress UndefinedClass */
        /** @var ClientInterface */
        return new \Http\Client\Curl\Client();
    }
}
