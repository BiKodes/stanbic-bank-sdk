<?php

declare(strict_types=1);

namespace Stanbic\SDK\Infrastructure\Http;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * OAuth2 Token Provider.
 *
 * Implements the client_credentials grant type with:
 * - In-memory token caching (TTL-based)
 * - Automatic token refresh on expiration
 * - Safety margin to avoid using nearly-expired tokens
 * - PSR-3 logging for token lifecycle events
 *
 * This is the primary TokenProviderInterface implementation for the SDK.
 *
 * @see TokenProviderInterface
*/
final class OAuth2TokenProvider implements TokenProviderInterface
{
    private ?AccessToken $cachedToken = null;
    private readonly LoggerInterface $logger;

    /**
     * PSR-7 request factory callable.
     *
     * @var \Closure(string, string, array<string, string>, string): RequestInterface
    */
    private readonly \Closure $requestFactory;

    /**
     * Clock callable for testability.
     *
     * @var \Closure(): float
    */
    private readonly \Closure $clock;

    /**
     * @param HttpConfig $config SDK HTTP configuration (provides tokenUrl, clientId, clientSecret)
     * @param ClientInterface $httpClient PSR-18 HTTP client for token requests
     * @param (\Closure(string, string, array<string, string>, string): RequestInterface)|null $requestFactory
     *        Optional PSR-7 request factory. Defaults to GuzzleHttp\Psr7\Request.
     * @param (\Closure(): float)|null $clock Optional clock for testability. Defaults to microtime(true).
    */
    public function __construct(
        private readonly HttpConfig $config,
        private readonly ClientInterface $httpClient,
        ?\Closure $requestFactory = null,
        ?\Closure $clock = null,
    ) {
        $this->logger = $config->getLogger() ?? new NullLogger();
        $this->requestFactory = $requestFactory ?? self::defaultRequestFactory();
        $this->clock = $clock ?? static fn (): float => microtime(true);
    }

    /**
     * {@inheritDoc}
    */
    public function getToken(): string
    {
        if ($this->cachedToken !== null && !$this->cachedToken->isExpired(($this->clock)())) {
            $this->logger->debug('Using cached OAuth2 token', [
                'remaining_seconds' => $this->cachedToken->remainingSeconds(($this->clock)()),
            ]);

            return $this->cachedToken->token;
        }

        $this->logger->info('OAuth2 token expired or missing, fetching new token');

        return $this->fetchAndCacheToken();
    }

    /**
     * {@inheritDoc}
    */
    public function refreshToken(): string
    {
        $this->logger->info('Forcing OAuth2 token refresh');

        return $this->fetchAndCacheToken();
    }

    /**
     * {@inheritDoc}
    */
    public function invalidate(): void
    {
        $this->cachedToken = null;
        $this->logger->info('OAuth2 token cache invalidated');
    }

    /**
     * Get the currently cached token (if any).
     *
     * Exposed for testing and diagnostics only.
     *
     * @return AccessToken|null
    */
    public function getCachedToken(): ?AccessToken
    {
        return $this->cachedToken;
    }

    /**
     * Fetch a new token from the OAuth2 server and cache it.
     *
     * @return string The new bearer token value
     * @throws \RuntimeException If the token request fails
    */
    private function fetchAndCacheToken(): string
    {
        $token = $this->requestToken();
        $this->cachedToken = $token;

        $this->logger->info('OAuth2 token acquired', [
            'expires_in' => $token->expiresIn,
            'token_type' => $token->tokenType,
            'scope' => $token->scope,
        ]);

        return $token->token;
    }

    /**
     * Perform the HTTP request to the OAuth2 token endpoint.
     *
     * @return AccessToken Parsed token from the response
     * @throws \RuntimeException If the request fails or response is invalid
    */
    private function requestToken(): AccessToken
    {
        $body = http_build_query([
            'grant_type' => 'client_credentials',
            'client_id' => $this->config->getClientId(),
            'client_secret' => $this->config->getClientSecret(),
        ]);

        $request = ($this->requestFactory)(
            'POST',
            $this->config->getTokenUrl(),
            [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Accept' => 'application/json',
            ],
            $body,
        );

        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (\Throwable $e) {
            $this->logger->error('OAuth2 token request failed', [
                'url' => $this->config->getTokenUrl(),
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException(
                'Failed to request OAuth2 token: ' . $e->getMessage(),
                0,
                $e,
            );
        }

        $statusCode = $response->getStatusCode();

        if ($statusCode < 200 || $statusCode >= 300) {
            $responseBody = (string) $response->getBody();

            $this->logger->error('OAuth2 token request returned error', [
                'status_code' => $statusCode,
                'response' => $responseBody,
            ]);

            throw new \RuntimeException(
                sprintf(
                    'OAuth2 token request failed with status %d: %s',
                    $statusCode,
                    $responseBody,
                ),
            );
        }

        return $this->parseTokenResponse((string) $response->getBody());
    }

    /**
     * Parse the JSON token response into an AccessToken.
     *
     * @param string $body Raw JSON response body
     * @return AccessToken
     * @throws \RuntimeException If the response cannot be parsed
    */
    private function parseTokenResponse(string $body): AccessToken
    {
        /** @var mixed $data */
        $data = json_decode($body, true);

        if (!is_array($data)) {
            throw new \RuntimeException(
                'Invalid OAuth2 token response: could not decode JSON',
            );
        }

        if (!isset($data['access_token']) || !is_string($data['access_token'])) {
            throw new \RuntimeException(
                'Invalid OAuth2 token response: missing or invalid "access_token"',
            );
        }

        if (!isset($data['expires_in']) || !is_int($data['expires_in'])) {
            // Some servers return expires_in as a numeric string
            if (isset($data['expires_in']) && is_numeric($data['expires_in'])) {
                $data['expires_in'] = (int) $data['expires_in'];
            } else {
                throw new \RuntimeException(
                    'Invalid OAuth2 token response: missing or invalid "expires_in"',
                );
            }
        }

        /** @var array{access_token: string, expires_in: int, token_type?: string, scope?: string} $data */
        return AccessToken::fromResponse($data, ($this->clock)());
    }

    /**
     * Build the default PSR-7 request factory using Guzzle's PSR-7 implementation.
     *
     * @return \Closure(string, string, array<string, string>, string): RequestInterface
     *
     * @codeCoverageIgnore — only reachable when guzzlehttp/psr7 is not installed
    */
    private static function defaultRequestFactory(): \Closure
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
