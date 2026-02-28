<?php

declare(strict_types=1);

namespace Stanbic\SDK\Infrastructure\Http;

/**
 * Access Token value object.
 *
 * Immutable representation of an OAuth2 access token with expiration metadata.
 *
 * @psalm-immutable
*/
final class AccessToken
{
    /**
     * Safety margin in seconds subtracted from expiration to avoid using nearly-expired tokens.
    */
    private const EXPIRY_MARGIN_SECONDS = 30;

    /**
     * @param string $token The bearer token value
     * @param int $expiresIn Token lifetime in seconds (as returned by the OAuth2 server)
     * @param float $issuedAt Unix timestamp (with microseconds) when the token was issued
     * @param string $tokenType Token type (typically "Bearer")
     * @param string $scope Granted scopes (space-separated)
    */
    public function __construct(
        public readonly string $token,
        public readonly int $expiresIn,
        public readonly float $issuedAt,
        public readonly string $tokenType = 'Bearer',
        public readonly string $scope = '',
    ) {
    }

    /**
     * Create from an OAuth2 token response array.
     *
     * @param array{access_token: string, expires_in: int, token_type?: string, scope?: string} $response
     * @param float|null $issuedAt Override issued timestamp (for testing); defaults to now
     * @return self
    */
    public static function fromResponse(array $response, ?float $issuedAt = null): self
    {
        return new self(
            token: $response['access_token'],
            expiresIn: $response['expires_in'],
            issuedAt: $issuedAt ?? microtime(true),
            tokenType: $response['token_type'] ?? 'Bearer',
            scope: $response['scope'] ?? '',
        );
    }

    /**
     * Check whether the token has expired (with safety margin).
     *
     * @param float|null $now Current timestamp; defaults to microtime(true)
     * @return bool True if the token is expired or about to expire
    */
    public function isExpired(?float $now = null): bool
    {
        /** @psalm-suppress ImpureFunctionCall microtime used as default only */
        $now ??= microtime(true);
        $expiresAt = $this->issuedAt + (float) $this->expiresIn - (float) self::EXPIRY_MARGIN_SECONDS;

        return $now >= $expiresAt;
    }

    /**
     * Get the number of seconds remaining before expiration (excluding margin).
     *
     * @param float|null $now Current timestamp; defaults to microtime(true)
     * @return int Seconds remaining (0 if already expired)
    */
    public function remainingSeconds(?float $now = null): int
    {
        /** @psalm-suppress ImpureFunctionCall microtime used as default only */
        $now ??= microtime(true);
        $expiresAt = $this->issuedAt + (float) $this->expiresIn - (float) self::EXPIRY_MARGIN_SECONDS;
        $remaining = $expiresAt - $now;

        return $remaining > 0 ? (int) ceil($remaining) : 0;
    }

    /**
     * Get the bearer token string ready for an Authorization header.
     *
     * @return string e.g. "Bearer eyJhbGciOiJSUzI1NiIs..."
    */
    public function toAuthorizationHeader(): string
    {
        return $this->tokenType . ' ' . $this->token;
    }
}
