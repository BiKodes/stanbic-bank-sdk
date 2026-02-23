<?php

declare(strict_types=1);

namespace Stanbic\SDK\Infrastructure\Http;

use Psr\Log\LoggerInterface;

/**
 * HTTP Configuration.
 *
 * Immutable configuration object for HTTP client settings.
 *
 * @psalm-immutable
*/
final class HttpConfig
{
    /**
     * @param string $baseUrl Base API URL
     * @param string $clientId OAuth2 client ID
     * @param string $clientSecret OAuth2 client secret
     * @param string $tokenUrl OAuth2 token endpoint URL
     * @param int $timeoutMs Request timeout in milliseconds
     * @param int $retryAttempts Maximum number of retry attempts
     * @param int $retryBackoffMs Initial backoff delay in milliseconds
     * @param LoggerInterface|null $logger PSR-3 logger for HTTP operations
    */
    public function __construct(
        public readonly string $baseUrl,
        public readonly string $clientId,
        public readonly string $clientSecret,
        public readonly string $tokenUrl,
        public readonly int $timeoutMs = 30000,
        public readonly int $retryAttempts = 3,
        public readonly int $retryBackoffMs = 1000,
        public readonly ?LoggerInterface $logger = null,
    ) {
        $this->validateConfig();
    }

    /**
     * Create HttpConfig with required parameters.
     *
     * @param string $baseUrl Base API URL
     * @param string $clientId OAuth2 client ID
     * @param string $clientSecret OAuth2 client secret
     * @param string $tokenUrl OAuth2 token endpoint URL
     * @param int $timeoutMs Request timeout in milliseconds (default: 30000)
     * @param int $retryAttempts Maximum retry attempts (default: 3)
     * @param int $retryBackoffMs Initial backoff delay (default: 1000ms)
     * @param LoggerInterface|null $logger Optional PSR-3 logger
    */
    public static function create(
        string $baseUrl,
        string $clientId,
        string $clientSecret,
        string $tokenUrl,
        int $timeoutMs = 30000,
        int $retryAttempts = 3,
        int $retryBackoffMs = 1000,
        ?LoggerInterface $logger = null,
    ): self {
        return new self(
            baseUrl: $baseUrl,
            clientId: $clientId,
            clientSecret: $clientSecret,
            tokenUrl: $tokenUrl,
            timeoutMs: $timeoutMs,
            retryAttempts: $retryAttempts,
            retryBackoffMs: $retryBackoffMs,
            logger: $logger,
        );
    }

    /**
     * Get base URL.
    */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Get client ID.
    */
    public function getClientId(): string
    {
        return $this->clientId;
    }

    /**
     * Get client secret.
    */
    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    /**
     * Get token URL.
    */
    public function getTokenUrl(): string
    {
        return $this->tokenUrl;
    }

    /**
     * Get timeout in milliseconds.
    */
    public function getTimeoutMs(): int
    {
        return $this->timeoutMs;
    }

    /**
     * Get retry attempts.
    */
    public function getRetryAttempts(): int
    {
        return $this->retryAttempts;
    }

    /**
     * Get retry backoff in milliseconds.
    */
    public function getRetryBackoffMs(): int
    {
        return $this->retryBackoffMs;
    }

    /**
     * Get logger instance.
    */
    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }

    /**
     * Check if logger is configured.
    */
    public function hasLogger(): bool
    {
        return $this->logger !== null;
    }

    /**
     * Create a new instance with different base URL.
    */
    public function withBaseUrl(string $baseUrl): self
    {
        return new self(
            baseUrl: $baseUrl,
            clientId: $this->clientId,
            clientSecret: $this->clientSecret,
            tokenUrl: $this->tokenUrl,
            timeoutMs: $this->timeoutMs,
            retryAttempts: $this->retryAttempts,
            retryBackoffMs: $this->retryBackoffMs,
            logger: $this->logger,
        );
    }

    /**
     * Create a new instance with different timeout.
    */
    public function withTimeout(int $timeoutMs): self
    {
        return new self(
            baseUrl: $this->baseUrl,
            clientId: $this->clientId,
            clientSecret: $this->clientSecret,
            tokenUrl: $this->tokenUrl,
            timeoutMs: $timeoutMs,
            retryAttempts: $this->retryAttempts,
            retryBackoffMs: $this->retryBackoffMs,
            logger: $this->logger,
        );
    }

    /**
     * Create a new instance with different retry settings.
    */
    public function withRetrySettings(int $retryAttempts, int $retryBackoffMs): self
    {
        return new self(
            baseUrl: $this->baseUrl,
            clientId: $this->clientId,
            clientSecret: $this->clientSecret,
            tokenUrl: $this->tokenUrl,
            timeoutMs: $this->timeoutMs,
            retryAttempts: $retryAttempts,
            retryBackoffMs: $retryBackoffMs,
            logger: $this->logger,
        );
    }

    /**
     * Create a new instance with logger.
    */
    public function withLogger(?LoggerInterface $logger): self
    {
        return new self(
            baseUrl: $this->baseUrl,
            clientId: $this->clientId,
            clientSecret: $this->clientSecret,
            tokenUrl: $this->tokenUrl,
            timeoutMs: $this->timeoutMs,
            retryAttempts: $this->retryAttempts,
            retryBackoffMs: $this->retryBackoffMs,
            logger: $logger,
        );
    }

    /**
     * Validate configuration values.
     *
     * @throws \InvalidArgumentException If configuration is invalid
    */
    private function validateConfig(): void
    {
        if (empty($this->baseUrl)) {
            throw new \InvalidArgumentException('Base URL cannot be empty');
        }

        if (empty($this->clientId)) {
            throw new \InvalidArgumentException('Client ID cannot be empty');
        }

        if (empty($this->clientSecret)) {
            throw new \InvalidArgumentException('Client secret cannot be empty');
        }

        if (empty($this->tokenUrl)) {
            throw new \InvalidArgumentException('Token URL cannot be empty');
        }

        if ($this->timeoutMs < 1000) {
            throw new \InvalidArgumentException('Timeout must be at least 1000ms');
        }

        if ($this->timeoutMs > 300000) {
            throw new \InvalidArgumentException('Timeout must not exceed 300000ms (5 minutes)');
        }

        if ($this->retryAttempts < 0) {
            throw new \InvalidArgumentException('Retry attempts must be >= 0');
        }

        if ($this->retryAttempts > 10) {
            throw new \InvalidArgumentException('Retry attempts must not exceed 10');
        }

        if ($this->retryBackoffMs < 0) {
            throw new \InvalidArgumentException('Retry backoff must be >= 0');
        }

        if ($this->retryBackoffMs > 60000) {
            throw new \InvalidArgumentException('Retry backoff must not exceed 60000ms (1 minute)');
        }
    }
}
