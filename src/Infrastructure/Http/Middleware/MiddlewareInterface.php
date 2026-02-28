<?php

declare(strict_types=1);

namespace Stanbic\SDK\Infrastructure\Http\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Common interface for HTTP middleware.
 */
interface MiddlewareInterface
{
    /**
     * @param RequestInterface $request
     * @param callable(RequestInterface): ResponseInterface $next
     * @return ResponseInterface
     */
    public function __invoke(RequestInterface $request, callable $next): ResponseInterface;
}
