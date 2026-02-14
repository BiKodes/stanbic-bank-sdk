<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\Exception;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\Exception\ApiErrorMapping;
use Stanbic\SDK\Domain\Exception\ConflictException;
use Stanbic\SDK\Domain\Exception\DuplicateTransactionException;
use Stanbic\SDK\Domain\Exception\ForbiddenException;
use Stanbic\SDK\Domain\Exception\InsufficientFundsException;
use Stanbic\SDK\Domain\Exception\InvalidAccountException;
use Stanbic\SDK\Domain\Exception\InvalidRequestException;
use Stanbic\SDK\Domain\Exception\NotFoundException;
use Stanbic\SDK\Domain\Exception\ServerErrorException;
use Stanbic\SDK\Domain\Exception\StanbicException;
use Stanbic\SDK\Domain\Exception\TimeoutException;
use Stanbic\SDK\Domain\Exception\UnauthorizedException;

final class ApiErrorMappingTest extends TestCase
{
    public function testMapsErrorCodeToException(): void
    {
        $exception = ApiErrorMapping::fromError('INSUFFICIENT_FUNDS', 'no funds', 400);
        $this->assertInstanceOf(InsufficientFundsException::class, $exception);
        $this->assertSame('no funds', $exception->getMessage());
    }

    public function testMapsStatusCodeToException(): void
    {
        $this->assertInstanceOf(InvalidRequestException::class, ApiErrorMapping::fromStatusCode(400));
        $this->assertInstanceOf(UnauthorizedException::class, ApiErrorMapping::fromStatusCode(401));
        $this->assertInstanceOf(ForbiddenException::class, ApiErrorMapping::fromStatusCode(403));
        $this->assertInstanceOf(NotFoundException::class, ApiErrorMapping::fromStatusCode(404));
        $this->assertInstanceOf(TimeoutException::class, ApiErrorMapping::fromStatusCode(408));
        $exception = ApiErrorMapping::fromStatusCode(409, 'conflict');
        $this->assertInstanceOf(ConflictException::class, $exception);
    }

    public function testMapsServerErrorForFiveHundred(): void
    {
        $exception = ApiErrorMapping::fromStatusCode(503, 'down');
        $this->assertInstanceOf(ServerErrorException::class, $exception);
    }

    public function testFallsBackToBaseException(): void
    {
        $exception = ApiErrorMapping::fromError('UNKNOWN', 'oops', 418);
        $this->assertInstanceOf(StanbicException::class, $exception);
    }

    public function testErrorCodeOverridesStatusCode(): void
    {
        $exception = ApiErrorMapping::fromError('DUPLICATE_TRANSACTION', 'dup', 400);
        $this->assertInstanceOf(DuplicateTransactionException::class, $exception);
    }

    public function testMapsInvalidAccountErrorCode(): void
    {
        $exception = ApiErrorMapping::fromError('INVALID_ACCOUNT', 'invalid', 400);
        $this->assertInstanceOf(InvalidAccountException::class, $exception);
    }

    public function testNullStatusCodeFallsBackToBase(): void
    {
        $exception = ApiErrorMapping::fromStatusCode(null, 'unknown');
        $this->assertInstanceOf(StanbicException::class, $exception);
    }
}
