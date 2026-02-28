<?php

declare(strict_types=1);

namespace Stanbic\SDK\Infrastructure\Http\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Middleware to inject a unique X-Request-ID header into each request.
 */
final class RequestIdMiddleware implements MiddlewareInterface
{
    /**
     * @param RequestInterface $request
     * @param callable(RequestInterface): ResponseInterface $next
     * @return ResponseInterface
     */
    public function __invoke(RequestInterface $request, callable $next): ResponseInterface
    {
        $requestId = bin2hex(random_bytes(16));
        $request = $request->withHeader('X-Request-ID', $requestId);
        return $next($request);
    }
}
