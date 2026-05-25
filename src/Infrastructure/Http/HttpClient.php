<?php

declare(strict_types=1);

namespace Stanbic\SDK\Infrastructure\Http;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\NullLogger;
use Stanbic\SDK\Infrastructure\Http\Middleware\AuthMiddleware;
use Stanbic\SDK\Infrastructure\Http\Middleware\LoggingMiddleware;
use Stanbic\SDK\Infrastructure\Http\Middleware\RetryMiddleware;
use Stanbic\SDK\Infrastructure\Http\Middleware\TimeoutMiddleware;

/**
 * SDK HTTP client request builder.
 *
 * Builds PSR-7 requests and sends them through a PSR-18 client wrapped
 * with middleware in the required order:
 * logging -> timeout -> retry -> auth
 */
final class HttpClient
{
    private ClientInterface $client;
    /**
     * @var callable(string, string, array<string, string>, string): RequestInterface
     */
    private $requestFactory;

    public function __construct(
        private readonly HttpConfig $config,
        ?ClientInterface $client = null,
        ?TokenProviderInterface $tokenProvider = null,
        ?callable $requestFactory = null,
    ) {
        $baseClient = HttpClientFactory::create($config, $client);
        $provider = $tokenProvider ?? new OAuth2TokenProvider($config, $baseClient);

        $logger = $config->getLogger() ?? new NullLogger();
        $timeoutSeconds = max(1, (int) ceil($config->getTimeoutMs() / 1000));

        $this->client = HttpClientFactory::createWithMiddleware(
            $config,
            [
                new LoggingMiddleware($logger),
                new TimeoutMiddleware($timeoutSeconds),
                new RetryMiddleware($config->getRetryAttempts(), $config->getRetryBackoffMs()),
                new AuthMiddleware($provider),
            ],
            $baseClient,
        );

        /**
         * @var callable(string, string, array<string, string>, string): RequestInterface|null $factory
         */
        $factory = $requestFactory;

        if ($factory === null) {
            $factory = self::defaultRequestFactory();
        }

        $this->requestFactory = $factory;
    }

    /**
     * Send a request using method/path/body/query helpers.
     *
     * @param string $method HTTP method
     * @param string $path Relative path (e.g. "/accounts") or absolute URL
     * @param array<string, string> $headers Request headers
     * @param string $body Raw request body
     * @param array<string, scalar|array<scalar>> $query Query parameters
     */
    public function request(
        string $method,
        string $path,
        array $headers = [],
        string $body = '',
        array $query = [],
    ): ResponseInterface {
        $uri = $this->buildUri($path, $query);
        $request = ($this->requestFactory)($method, $uri, $headers, $body);

        return $this->client->sendRequest($request);
    }

    /**
     * @param array<string, scalar|array<scalar>> $query
     * @param array<string, string> $headers
     */
    public function get(string $path, array $query = [], array $headers = []): ResponseInterface
    {
        return $this->request('GET', $path, $headers, '', $query);
    }

    /**
     * @param array<string, string> $headers
     * @param array<string, scalar|array<scalar>> $query
     */
    public function post(string $path, string $body = '', array $headers = [], array $query = []): ResponseInterface
    {
        return $this->request('POST', $path, $headers, $body, $query);
    }

    /**
     * @param array<string, string> $headers
     * @param array<string, scalar|array<scalar>> $query
     */
    public function put(string $path, string $body = '', array $headers = [], array $query = []): ResponseInterface
    {
        return $this->request('PUT', $path, $headers, $body, $query);
    }

    /**
     * @param array<string, string> $headers
     * @param array<string, scalar|array<scalar>> $query
     */
    public function patch(string $path, string $body = '', array $headers = [], array $query = []): ResponseInterface
    {
        return $this->request('PATCH', $path, $headers, $body, $query);
    }

    /**
     * @param array<string, string> $headers
     * @param array<string, scalar|array<scalar>> $query
     */
    public function delete(string $path, array $headers = [], array $query = []): ResponseInterface
    {
        return $this->request('DELETE', $path, $headers, '', $query);
    }

    /**
     * @param array<string, scalar|array<scalar>> $query
     */
    private function buildUri(string $path, array $query): string
    {
        $isAbsolute = str_starts_with($path, 'http://') || str_starts_with($path, 'https://');
        $uri = $isAbsolute
            ? $path
            : rtrim($this->config->getBaseUrl(), '/') . '/' . ltrim($path, '/');

        if (empty($query)) {
            return $uri;
        }

        $separator = str_contains($uri, '?') ? '&' : '?';

        return $uri . $separator . http_build_query($query);
    }

    /**
     * @return callable(string, string, array<string, string>, string): RequestInterface
     */
    private static function defaultRequestFactory(): callable
    {
        if (!class_exists(\GuzzleHttp\Psr7\Request::class)) {
            throw new \RuntimeException(
                'No PSR-7 request implementation found. '
                . 'Install guzzlehttp/psr7 or provide a custom request factory.',
            );
        }

        return static function (
            string $method,
            string $uri,
            array $headers,
            string $body,
        ): RequestInterface {
            $request = new \GuzzleHttp\Psr7\Request($method, $uri, $headers, $body);

            /** @var RequestInterface */
            return $request;
        };
    }
}
