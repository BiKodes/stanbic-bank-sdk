<?php

declare(strict_types=1);

namespace Stanbic\SDK\Infrastructure\Http\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Middleware to enforce a request timeout (header-based, for compatible clients).
 */
final class TimeoutMiddleware implements MiddlewareInterface
{
    private int $timeoutSeconds;

    public function __construct(int $timeoutSeconds = 30)
    {
        $this->timeoutSeconds = $timeoutSeconds;
    }

    /**
     * @param RequestInterface $request
     * @param callable(RequestInterface): ResponseInterface $next
     * @return ResponseInterface
     */
    public function __invoke(RequestInterface $request, callable $next): ResponseInterface
    {
        // Assumes the underlying client respects the timeout option
        $request = $request->withHeader('X-Timeout-Seconds', (string)$this->timeoutSeconds);
        return $next($request);
    }
}
