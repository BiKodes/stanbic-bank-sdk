<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Exception;

use RuntimeException;
use Throwable;

class StanbicException extends RuntimeException
{
    private ?int $statusCode;

    public function __construct(
        string $message = '',
        ?int $statusCode = null,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->statusCode = $statusCode ?? static::defaultStatusCode();
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    protected static function defaultStatusCode(): ?int
    {
        return null;
    }
}
