<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\Exception;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\Exception\ConflictException;
use Stanbic\SDK\Domain\Exception\DuplicateTransactionException;
use Stanbic\SDK\Domain\Exception\ForbiddenException;
use Stanbic\SDK\Domain\Exception\InsufficientFundsException;
use Stanbic\SDK\Domain\Exception\InvalidAccountException;
use Stanbic\SDK\Domain\Exception\InvalidRequestException;
use Stanbic\SDK\Domain\Exception\NetworkException;
use Stanbic\SDK\Domain\Exception\NotFoundException;
use Stanbic\SDK\Domain\Exception\ServerErrorException;
use Stanbic\SDK\Domain\Exception\StanbicException;
use Stanbic\SDK\Domain\Exception\TimeoutException;
use Stanbic\SDK\Domain\Exception\UnauthorizedException;

final class StanbicExceptionTest extends TestCase
{
    public function testBaseExceptionDefaultStatusCodeIsNull(): void
    {
        $exception = new StanbicException('base');
        $this->assertNull($exception->getStatusCode());
    }

    public function testTypedExceptionsExposeDefaultStatusCodes(): void
    {
        $this->assertSame(400, (new InvalidRequestException('bad'))->getStatusCode());
        $this->assertSame(401, (new UnauthorizedException('nope'))->getStatusCode());
        $this->assertSame(403, (new ForbiddenException('forbidden'))->getStatusCode());
        $this->assertSame(404, (new NotFoundException('missing'))->getStatusCode());
        $this->assertSame(408, (new TimeoutException('timeout'))->getStatusCode());
        $this->assertSame(409, (new ConflictException('conflict'))->getStatusCode());
        $this->assertSame(400, (new InvalidAccountException('invalid'))->getStatusCode());
        $this->assertSame(402, (new InsufficientFundsException('funds'))->getStatusCode());
        $this->assertSame(409, (new DuplicateTransactionException('duplicate'))->getStatusCode());
        $this->assertSame(500, (new ServerErrorException('server'))->getStatusCode());
    }

    public function testNetworkExceptionHasNoDefaultStatus(): void
    {
        $exception = new NetworkException('network');
        $this->assertNull($exception->getStatusCode());
    }
}
