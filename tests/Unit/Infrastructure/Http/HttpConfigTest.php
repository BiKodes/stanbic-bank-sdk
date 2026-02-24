<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Infrastructure\Http;

use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Stanbic\SDK\Infrastructure\Http\HttpConfig;

/**
 * @covers \Stanbic\SDK\Infrastructure\Http\HttpConfig
*/
final class HttpConfigTest extends TestCase
{
    private const BASE_URL = 'https://api.stanbic.com';
    private const CLIENT_ID = 'test-client-id';
    private const CLIENT_SECRET = 'test-client-secret';
    private const TOKEN_URL = 'https://auth.stanbic.com/oauth/token';

    public function testConstructorWithRequiredParameters(): void
    {
        $config = new HttpConfig(
            baseUrl: self::BASE_URL,
            clientId: self::CLIENT_ID,
            clientSecret: self::CLIENT_SECRET,
            tokenUrl: self::TOKEN_URL,
        );

        self::assertSame(self::BASE_URL, $config->baseUrl);
        self::assertSame(self::CLIENT_ID, $config->clientId);
        self::assertSame(self::CLIENT_SECRET, $config->clientSecret);
        self::assertSame(self::TOKEN_URL, $config->tokenUrl);
        self::assertSame(30000, $config->timeoutMs);
        self::assertSame(3, $config->retryAttempts);
        self::assertSame(1000, $config->retryBackoffMs);
        self::assertNull($config->logger);
    }

    public function testConstructorWithAllParameters(): void
    {
        $logger = new NullLogger();

        $config = new HttpConfig(
            baseUrl: self::BASE_URL,
            clientId: self::CLIENT_ID,
            clientSecret: self::CLIENT_SECRET,
            tokenUrl: self::TOKEN_URL,
            timeoutMs: 60000,
            retryAttempts: 5,
            retryBackoffMs: 2000,
            logger: $logger,
        );

        self::assertSame(self::BASE_URL, $config->baseUrl);
        self::assertSame(self::CLIENT_ID, $config->clientId);
        self::assertSame(self::CLIENT_SECRET, $config->clientSecret);
        self::assertSame(self::TOKEN_URL, $config->tokenUrl);
        self::assertSame(60000, $config->timeoutMs);
        self::assertSame(5, $config->retryAttempts);
        self::assertSame(2000, $config->retryBackoffMs);
        self::assertSame($logger, $config->logger);
    }

    public function testCreateWithDefaults(): void
    {
        $config = HttpConfig::create(
            baseUrl: self::BASE_URL,
            clientId: self::CLIENT_ID,
            clientSecret: self::CLIENT_SECRET,
            tokenUrl: self::TOKEN_URL,
        );

        self::assertSame(self::BASE_URL, $config->baseUrl);
        self::assertSame(self::CLIENT_ID, $config->clientId);
        self::assertSame(self::CLIENT_SECRET, $config->clientSecret);
        self::assertSame(self::TOKEN_URL, $config->tokenUrl);
        self::assertSame(30000, $config->timeoutMs);
        self::assertSame(3, $config->retryAttempts);
        self::assertSame(1000, $config->retryBackoffMs);
        self::assertNull($config->logger);
    }

    public function testCreateWithCustomValues(): void
    {
        $logger = new NullLogger();

        $config = HttpConfig::create(
            baseUrl: self::BASE_URL,
            clientId: self::CLIENT_ID,
            clientSecret: self::CLIENT_SECRET,
            tokenUrl: self::TOKEN_URL,
            timeoutMs: 45000,
            retryAttempts: 4,
            retryBackoffMs: 5000,
            logger: $logger,
        );

        self::assertSame(45000, $config->timeoutMs);
        self::assertSame(4, $config->retryAttempts);
        self::assertSame(5000, $config->retryBackoffMs);
        self::assertSame($logger, $config->logger);
    }

    public function testEmptyBaseUrlThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Base URL cannot be empty');

        new HttpConfig(
            baseUrl: '',
            clientId: self::CLIENT_ID,
            clientSecret: self::CLIENT_SECRET,
            tokenUrl: self::TOKEN_URL,
        );
    }

    public function testEmptyClientIdThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Client ID cannot be empty');

        new HttpConfig(
            baseUrl: self::BASE_URL,
            clientId: '',
            clientSecret: self::CLIENT_SECRET,
            tokenUrl: self::TOKEN_URL,
        );
    }

    public function testEmptyClientSecretThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Client secret cannot be empty');

        new HttpConfig(
            baseUrl: self::BASE_URL,
            clientId: self::CLIENT_ID,
            clientSecret: '',
            tokenUrl: self::TOKEN_URL,
        );
    }

    public function testEmptyTokenUrlThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Token URL cannot be empty');

        new HttpConfig(
            baseUrl: self::BASE_URL,
            clientId: self::CLIENT_ID,
            clientSecret: self::CLIENT_SECRET,
            tokenUrl: '',
        );
    }

    public function testTimeoutBelowMinimumThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Timeout must be at least 1000ms');

        new HttpConfig(
            baseUrl: self::BASE_URL,
            clientId: self::CLIENT_ID,
            clientSecret: self::CLIENT_SECRET,
            tokenUrl: self::TOKEN_URL,
            timeoutMs: 999,
        );
    }

    public function testTimeoutAboveMaximumThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Timeout must not exceed 300000ms (5 minutes)');

        new HttpConfig(
            baseUrl: self::BASE_URL,
            clientId: self::CLIENT_ID,
            clientSecret: self::CLIENT_SECRET,
            tokenUrl: self::TOKEN_URL,
            timeoutMs: 300001,
        );
    }

    public function testTimeoutAtMinimumBoundary(): void
    {
        $config = new HttpConfig(
            baseUrl: self::BASE_URL,
            clientId: self::CLIENT_ID,
            clientSecret: self::CLIENT_SECRET,
            tokenUrl: self::TOKEN_URL,
            timeoutMs: 1000,
        );

        self::assertSame(1000, $config->timeoutMs);
    }

    public function testTimeoutAtMaximumBoundary(): void
    {
        $config = new HttpConfig(
            baseUrl: self::BASE_URL,
            clientId: self::CLIENT_ID,
            clientSecret: self::CLIENT_SECRET,
            tokenUrl: self::TOKEN_URL,
            timeoutMs: 300000,
        );

        self::assertSame(300000, $config->timeoutMs);
    }

    public function testNegativeRetryAttemptsThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Retry attempts must be >= 0');

        new HttpConfig(
            baseUrl: self::BASE_URL,
            clientId: self::CLIENT_ID,
            clientSecret: self::CLIENT_SECRET,
            tokenUrl: self::TOKEN_URL,
            retryAttempts: -1,
        );
    }

    public function testRetryAttemptsAboveMaximumThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Retry attempts must not exceed 10');

        new HttpConfig(
            baseUrl: self::BASE_URL,
            clientId: self::CLIENT_ID,
            clientSecret: self::CLIENT_SECRET,
            tokenUrl: self::TOKEN_URL,
            retryAttempts: 11,
        );
    }

    public function testRetryAttemptsAtMinimumBoundary(): void
    {
        $config = new HttpConfig(
            baseUrl: self::BASE_URL,
            clientId: self::CLIENT_ID,
            clientSecret: self::CLIENT_SECRET,
            tokenUrl: self::TOKEN_URL,
            retryAttempts: 0,
        );

        self::assertSame(0, $config->retryAttempts);
    }

    public function testRetryAttemptsAtMaximumBoundary(): void
    {
        $config = new HttpConfig(
            baseUrl: self::BASE_URL,
            clientId: self::CLIENT_ID,
            clientSecret: self::CLIENT_SECRET,
            tokenUrl: self::TOKEN_URL,
            retryAttempts: 10,
        );

        self::assertSame(10, $config->retryAttempts);
    }

    public function testNegativeBackoffThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Retry backoff must be >= 0');

        new HttpConfig(
            baseUrl: self::BASE_URL,
            clientId: self::CLIENT_ID,
            clientSecret: self::CLIENT_SECRET,
            tokenUrl: self::TOKEN_URL,
            retryBackoffMs: -1,
        );
    }

    public function testBackoffAboveMaximumThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Retry backoff must not exceed 60000ms (1 minute)');

        new HttpConfig(
            baseUrl: self::BASE_URL,
            clientId: self::CLIENT_ID,
            clientSecret: self::CLIENT_SECRET,
            tokenUrl: self::TOKEN_URL,
            retryBackoffMs: 60001,
        );
    }

    public function testBackoffAtMinimumBoundary(): void
    {
        $config = new HttpConfig(
            baseUrl: self::BASE_URL,
            clientId: self::CLIENT_ID,
            clientSecret: self::CLIENT_SECRET,
            tokenUrl: self::TOKEN_URL,
            retryBackoffMs: 0,
        );

        self::assertSame(0, $config->retryBackoffMs);
    }

    public function testBackoffAtMaximumBoundary(): void
    {
        $config = new HttpConfig(
            baseUrl: self::BASE_URL,
            clientId: self::CLIENT_ID,
            clientSecret: self::CLIENT_SECRET,
            tokenUrl: self::TOKEN_URL,
            retryBackoffMs: 60000,
        );

        self::assertSame(60000, $config->retryBackoffMs);
    }

    public function testWithBaseUrlReturnsNewInstanceWithUpdatedValue(): void
    {
        $original = new HttpConfig(
            baseUrl: self::BASE_URL,
            clientId: self::CLIENT_ID,
            clientSecret: self::CLIENT_SECRET,
            tokenUrl: self::TOKEN_URL,
        );

        $newUrl = 'https://api.new.stanbic.com';
        $updated = $original->withBaseUrl($newUrl);

        self::assertSame(self::BASE_URL, $original->baseUrl);
        self::assertSame($newUrl, $updated->baseUrl);
        self::assertSame(self::CLIENT_ID, $updated->clientId);
        self::assertNotSame($original, $updated);
    }

    public function testWithTimeoutReturnsNewInstanceWithUpdatedValue(): void
    {
        $original = new HttpConfig(
            baseUrl: self::BASE_URL,
            clientId: self::CLIENT_ID,
            clientSecret: self::CLIENT_SECRET,
            tokenUrl: self::TOKEN_URL,
        );

        $updated = $original->withTimeout(60000);

        self::assertSame(30000, $original->timeoutMs);
        self::assertSame(60000, $updated->timeoutMs);
        self::assertNotSame($original, $updated);
    }

    public function testWithTimeoutValidatesNewValue(): void
    {
        $config = new HttpConfig(
            baseUrl: self::BASE_URL,
            clientId: self::CLIENT_ID,
            clientSecret: self::CLIENT_SECRET,
            tokenUrl: self::TOKEN_URL,
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Timeout must be at least 1000ms');

        $config->withTimeout(500);
    }

    public function testWithRetrySettingsReturnsNewInstanceWithUpdatedValues(): void
    {
        $original = new HttpConfig(
            baseUrl: self::BASE_URL,
            clientId: self::CLIENT_ID,
            clientSecret: self::CLIENT_SECRET,
            tokenUrl: self::TOKEN_URL,
        );

        $updated = $original->withRetrySettings(5, 2000);

        self::assertSame(3, $original->retryAttempts);
        self::assertSame(1000, $original->retryBackoffMs);
        self::assertSame(5, $updated->retryAttempts);
        self::assertSame(2000, $updated->retryBackoffMs);
        self::assertNotSame($original, $updated);
    }

    public function testWithRetrySettingsValidatesAttempts(): void
    {
        $config = new HttpConfig(
            baseUrl: self::BASE_URL,
            clientId: self::CLIENT_ID,
            clientSecret: self::CLIENT_SECRET,
            tokenUrl: self::TOKEN_URL,
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Retry attempts must be >= 0');

        $config->withRetrySettings(-1, 1000);
    }

    public function testWithRetrySettingsValidatesBackoff(): void
    {
        $config = new HttpConfig(
            baseUrl: self::BASE_URL,
            clientId: self::CLIENT_ID,
            clientSecret: self::CLIENT_SECRET,
            tokenUrl: self::TOKEN_URL,
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Retry backoff must not exceed 60000ms (1 minute)');

        $config->withRetrySettings(3, 61000);
    }

    public function testWithLoggerReturnsNewInstanceWithUpdatedLogger(): void
    {
        $original = new HttpConfig(
            baseUrl: self::BASE_URL,
            clientId: self::CLIENT_ID,
            clientSecret: self::CLIENT_SECRET,
            tokenUrl: self::TOKEN_URL,
        );

        $logger = new NullLogger();
        $updated = $original->withLogger($logger);

        self::assertNull($original->logger);
        self::assertSame($logger, $updated->logger);
        self::assertNotSame($original, $updated);
    }

    public function testWithLoggerCanRemoveLoggerByPassingNull(): void
    {
        $logger = new NullLogger();
        $original = new HttpConfig(
            baseUrl: self::BASE_URL,
            clientId: self::CLIENT_ID,
            clientSecret: self::CLIENT_SECRET,
            tokenUrl: self::TOKEN_URL,
            logger: $logger,
        );

        $updated = $original->withLogger(null);

        self::assertSame($logger, $original->logger);
        self::assertNull($updated->logger);
        self::assertNotSame($original, $updated);
    }

    public function testImmutabilityChainMultipleUpdates(): void
    {
        $config = HttpConfig::create(
            baseUrl: self::BASE_URL,
            clientId: self::CLIENT_ID,
            clientSecret: self::CLIENT_SECRET,
            tokenUrl: self::TOKEN_URL,
        );

        $updated = $config
            ->withBaseUrl('https://api.new.stanbic.com')
            ->withTimeout(60000)
            ->withRetrySettings(5, 2000);

        self::assertSame(self::BASE_URL, $config->baseUrl);
        self::assertSame(30000, $config->timeoutMs);
        self::assertSame(3, $config->retryAttempts);

        self::assertSame('https://api.new.stanbic.com', $updated->baseUrl);
        self::assertSame(60000, $updated->timeoutMs);
        self::assertSame(5, $updated->retryAttempts);
    }

    public function testGettersReturnCorrectValues(): void
    {
        $logger = new NullLogger();

        $config = new HttpConfig(
            baseUrl: self::BASE_URL,
            clientId: self::CLIENT_ID,
            clientSecret: self::CLIENT_SECRET,
            tokenUrl: self::TOKEN_URL,
            timeoutMs: 45000,
            retryAttempts: 4,
            retryBackoffMs: 2500,
            logger: $logger,
        );

        self::assertSame(self::BASE_URL, $config->getBaseUrl());
        self::assertSame(self::CLIENT_ID, $config->getClientId());
        self::assertSame(self::CLIENT_SECRET, $config->getClientSecret());
        self::assertSame(self::TOKEN_URL, $config->getTokenUrl());
        self::assertSame(45000, $config->getTimeoutMs());
        self::assertSame(4, $config->getRetryAttempts());
        self::assertSame(2500, $config->getRetryBackoffMs());
        self::assertSame($logger, $config->getLogger());
    }

    public function testHasLoggerReturnsFalseWhenNoLoggerConfigured(): void
    {
        $config = new HttpConfig(
            baseUrl: self::BASE_URL,
            clientId: self::CLIENT_ID,
            clientSecret: self::CLIENT_SECRET,
            tokenUrl: self::TOKEN_URL,
        );

        self::assertFalse($config->hasLogger());
    }

    public function testHasLoggerReturnsTrueWhenLoggerConfigured(): void
    {
        $logger = new NullLogger();

        $config = new HttpConfig(
            baseUrl: self::BASE_URL,
            clientId: self::CLIENT_ID,
            clientSecret: self::CLIENT_SECRET,
            tokenUrl: self::TOKEN_URL,
            logger: $logger,
        );

        self::assertTrue($config->hasLogger());
    }

    public function testHasLoggerReturnsTrueAfterSettingLogger(): void
    {
        $config = new HttpConfig(
            baseUrl: self::BASE_URL,
            clientId: self::CLIENT_ID,
            clientSecret: self::CLIENT_SECRET,
            tokenUrl: self::TOKEN_URL,
        );

        $logger = new NullLogger();
        $updatedConfig = $config->withLogger($logger);

        self::assertTrue($updatedConfig->hasLogger());
    }

    public function testHasLoggerReturnsFalseAfterRemovingLogger(): void
    {
        $logger = new NullLogger();

        $config = new HttpConfig(
            baseUrl: self::BASE_URL,
            clientId: self::CLIENT_ID,
            clientSecret: self::CLIENT_SECRET,
            tokenUrl: self::TOKEN_URL,
            logger: $logger,
        );

        $updatedConfig = $config->withLogger(null);

        self::assertFalse($updatedConfig->hasLogger());
    }
}
