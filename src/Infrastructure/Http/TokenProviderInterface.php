<?php

declare(strict_types=1);

namespace Stanbic\SDK\Infrastructure\Http;

/**
 * Token Provider Interface (Strategy pattern).
 *
 * Abstracts the mechanism for obtaining access tokens.
 * Implementations may use OAuth2, API keys, static tokens, etc.
*/
interface TokenProviderInterface
{
    /**
     * Get a valid access token.
     *
     * Implementations should handle caching, refresh, and expiration internally.
     * Callers always receive a ready-to-use token string.
     *
     * @return string Bearer token value
     * @throws \RuntimeException If a token cannot be obtained
    */
    public function getToken(): string;

    /**
     * Force a fresh token fetch, bypassing any cache.
     *
     * @return string New bearer token value
     * @throws \RuntimeException If a token cannot be obtained
    */
    public function refreshToken(): string;

    /**
     * Invalidate any cached token.
     *
     * Next call to getToken() will fetch a new token.
    */
    public function invalidate(): void;
}
