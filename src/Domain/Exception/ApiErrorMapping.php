<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Exception;

final class ApiErrorMapping
{
    /** @var array<int, class-string<StanbicException>> */
    private const STATUS_CODE_MAP = [
        400 => InvalidRequestException::class,
        401 => UnauthorizedException::class,
        403 => ForbiddenException::class,
        404 => NotFoundException::class,
        408 => TimeoutException::class,
        409 => ConflictException::class,
    ];

    /** @var array<string, class-string<StanbicException>> */
    private const ERROR_CODE_MAP = [
        'INSUFFICIENT_FUNDS' => InsufficientFundsException::class,
        'DUPLICATE_TRANSACTION' => DuplicateTransactionException::class,
        'INVALID_ACCOUNT' => InvalidAccountException::class,
    ];

    private function __construct()
    {
    }

    public static function fromError(
        ?string $errorCode,
        string $message = '',
        ?int $statusCode = null
    ): StanbicException {
        if ($errorCode !== null && isset(self::ERROR_CODE_MAP[$errorCode])) {
            $exceptionClass = self::ERROR_CODE_MAP[$errorCode];
            return new $exceptionClass($message, $statusCode);
        }

        return self::fromStatusCode($statusCode, $message);
    }

    public static function fromStatusCode(
        ?int $statusCode,
        string $message = ''
    ): StanbicException {
        if ($statusCode !== null) {
            if ($statusCode >= 500) {
                return new ServerErrorException($message, $statusCode);
            }

            if (isset(self::STATUS_CODE_MAP[$statusCode])) {
                $exceptionClass = self::STATUS_CODE_MAP[$statusCode];
                return new $exceptionClass($message, $statusCode);
            }
        }

        return new StanbicException($message, $statusCode);
    }
}
