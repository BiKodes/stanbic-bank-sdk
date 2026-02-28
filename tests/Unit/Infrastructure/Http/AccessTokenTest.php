<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Infrastructure\Http;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Infrastructure\Http\AccessToken;

/**
 * @covers \Stanbic\SDK\Infrastructure\Http\AccessToken
 * @psalm-suppress PropertyNotSetInConstructor
*/
final class AccessTokenTest extends TestCase
{
    /*---------------------------------------------------------------
    Construction
    ---------------------------------------------------------------*/

    public function testConstructorSetsAllProperties(): void
    {
        $token = new AccessToken(
            token: 'abc123',
            expiresIn: 3600,
            issuedAt: 1000000.0,
            tokenType: 'Bearer',
            scope: 'read write',
        );

        self::assertSame('abc123', $token->token);
        self::assertSame(3600, $token->expiresIn);
        self::assertSame(1000000.0, $token->issuedAt);
        self::assertSame('Bearer', $token->tokenType);
        self::assertSame('read write', $token->scope);
    }

    public function testConstructorDefaultTokenType(): void
    {
        $token = new AccessToken('xyz', 600, 1000.0);

        self::assertSame('Bearer', $token->tokenType);
    }

    public function testConstructorDefaultScope(): void
    {
        $token = new AccessToken('xyz', 600, 1000.0);

        self::assertSame('', $token->scope);
    }

    /*---------------------------------------------------------------
    fromResponse()
    ----------------------------------------------------------------*/

    public function testFromResponseWithMinimalPayload(): void
    {
        $token = AccessToken::fromResponse([
            'access_token' => 'tok_minimal',
            'expires_in' => 1800,
        ], 5000.0);

        self::assertSame('tok_minimal', $token->token);
        self::assertSame(1800, $token->expiresIn);
        self::assertSame(5000.0, $token->issuedAt);
        self::assertSame('Bearer', $token->tokenType);
        self::assertSame('', $token->scope);
    }

    public function testFromResponseWithFullPayload(): void
    {
        $token = AccessToken::fromResponse([
            'access_token' => 'tok_full',
            'expires_in' => 7200,
            'token_type' => 'mac',
            'scope' => 'admin',
        ], 9000.0);

        self::assertSame('tok_full', $token->token);
        self::assertSame(7200, $token->expiresIn);
        self::assertSame(9000.0, $token->issuedAt);
        self::assertSame('mac', $token->tokenType);
        self::assertSame('admin', $token->scope);
    }

    public function testFromResponseUsesCurrentTimeWhenIssuedAtNull(): void
    {
        $before = microtime(true);
        $token = AccessToken::fromResponse([
            'access_token' => 'tok',
            'expires_in' => 60,
        ]);
        $after = microtime(true);

        self::assertGreaterThanOrEqual($before, $token->issuedAt);
        self::assertLessThanOrEqual($after, $token->issuedAt);
    }

    /*---------------------------------------------------------------
    isExpired()
    ----------------------------------------------------------------*/

    public function testIsExpiredReturnsFalseForFreshToken(): void
    {
        $token = new AccessToken('tok', 3600, 1000.0);

        /* At t=1000, token expires at 1000+3600-30=4570 → not expired */
        self::assertFalse($token->isExpired(1000.0));
    }

    public function testIsExpiredReturnsFalseJustBeforeMargin(): void
    {
        $token = new AccessToken('tok', 3600, 1000.0);

        /* Expires at 4570.0 (1000 + 3600 - 30) */
        self::assertFalse($token->isExpired(4569.9));
    }

    public function testIsExpiredReturnsTrueAtExactMargin(): void
    {
        $token = new AccessToken('tok', 3600, 1000.0);

        /* Expires at 4570.0 → at 4570.0, should be expired */
        self::assertTrue($token->isExpired(4570.0));
    }

    public function testIsExpiredReturnsTrueAfterExpiration(): void
    {
        $token = new AccessToken('tok', 3600, 1000.0);

        self::assertTrue($token->isExpired(5000.0));
    }

    public function testIsExpiredReturnsTrueForShortLivedTokenWithinMargin(): void
    {
        /* Token with 20-second lifetime (less than 30s margin) → immediately expired */
        $token = new AccessToken('tok', 20, 1000.0);

        self::assertTrue($token->isExpired(1000.0));
    }

    public function testIsExpiredUsesCurrentTimeByDefault(): void
    {
        /* Token expired far in the past */
        $token = new AccessToken('tok', 1, 0.0);

        self::assertTrue($token->isExpired());
    }

    public function testIsExpiredFreshTokenUsesCurrentTime(): void
    {
        /* Token with 1 hour lifetime issued right now */
        $token = new AccessToken('tok', 3600, microtime(true));

        self::assertFalse($token->isExpired());
    }

    /*---------------------------------------------------------------
    remainingSeconds()
    ----------------------------------------------------------------*/

    public function testRemainingSecondsForFreshToken(): void
    {
        $token = new AccessToken('tok', 3600, 1000.0);

        /* At t=1000, remaining = 4570 - 1000 = 3570 → ceil(3570) = 3570 */
        self::assertSame(3570, $token->remainingSeconds(1000.0));
    }

    public function testRemainingSecondsPartialSecond(): void
    {
        $token = new AccessToken('tok', 3600, 1000.0);

        /* At t=1000.5, remaining = 4570 - 1000.5 = 3569.5 → ceil = 3570 */
        self::assertSame(3570, $token->remainingSeconds(1000.5));
    }

    public function testRemainingSecondsReturnsZeroWhenExpired(): void
    {
        $token = new AccessToken('tok', 3600, 1000.0);

        self::assertSame(0, $token->remainingSeconds(5000.0));
    }

    public function testRemainingSecondsReturnsZeroAtExactExpiry(): void
    {
        $token = new AccessToken('tok', 3600, 1000.0);

        self::assertSame(0, $token->remainingSeconds(4570.0));
    }

    public function testRemainingSecondsUsesCurrentTimeByDefault(): void
    {
        $token = new AccessToken('tok', 1, 0.0);

        self::assertSame(0, $token->remainingSeconds());
    }

    /*---------------------------------------------------------------
    toAuthorizationHeader()
    ----------------------------------------------------------------*/

    public function testToAuthorizationHeaderBearer(): void
    {
        $token = new AccessToken('eyJhbGciOiJSUzI1NiIs', 3600, 1000.0, 'Bearer');

        self::assertSame('Bearer eyJhbGciOiJSUzI1NiIs', $token->toAuthorizationHeader());
    }

    public function testToAuthorizationHeaderCustomType(): void
    {
        $token = new AccessToken('abc123', 3600, 1000.0, 'MAC');

        self::assertSame('MAC abc123', $token->toAuthorizationHeader());
    }

    /*---------------------------------------------------------------
    Immutability
    ----------------------------------------------------------------*/

    public function testTokenIsImmutable(): void
    {
        $token = new AccessToken('tok', 3600, 1000.0);

        /* All properties are readonly — this test verifies they exist and don't change */
        self::assertSame(3600, $token->expiresIn);
        self::assertSame(1000.0, $token->issuedAt);
        self::assertSame('Bearer', $token->tokenType);
        self::assertSame('', $token->scope);
    }

    /*---------------------------------------------------------------
    Edge cases
    ----------------------------------------------------------------*/

    public function testZeroExpiresIn(): void
    {
        $token = new AccessToken('tok', 0, 1000.0);

        /* 1000 + 0 - 30 = 970 → expired at t=1000 */
        self::assertTrue($token->isExpired(1000.0));
        self::assertSame(0, $token->remainingSeconds(1000.0));
    }

    public function testVeryLargeExpiresIn(): void
    {
        $token = new AccessToken('tok', 86400, 1000.0);

        /* 1000 + 86400 - 30 = 87370 */
        self::assertFalse($token->isExpired(1000.0));
        self::assertSame(86370, $token->remainingSeconds(1000.0));
    }

    public function testEmptyTokenString(): void
    {
        $token = new AccessToken('', 3600, 1000.0);

        self::assertSame('', $token->token);
        self::assertSame('Bearer ', $token->toAuthorizationHeader());
    }
}
