<?php

declare(strict_types=1);

namespace Stanbic\SDK\Infrastructure\Http\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Middleware for logging HTTP requests and responses using PSR-3.
 */
final class LoggingMiddleware implements MiddlewareInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param RequestInterface $request
     * @param callable(RequestInterface): ResponseInterface $next
     * @return ResponseInterface
     */
    public function __invoke(RequestInterface $request, callable $next): ResponseInterface
    {
        $this->logger->info('HTTP Request', [
            'method' => $request->getMethod(),
            'uri' => (string)$request->getUri(),
            'headers' => $request->getHeaders(),
        ]);
        $response = $next($request);
        $this->logger->info('HTTP Response', [
            'status' => $response->getStatusCode(),
            'headers' => $response->getHeaders(),
        ]);
        return $response;
    }
}
