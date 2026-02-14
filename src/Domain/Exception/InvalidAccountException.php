<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Exception;

final class InvalidAccountException extends StanbicException
{
    protected static function defaultStatusCode(): ?int
    {
        return 400;
    }
}
