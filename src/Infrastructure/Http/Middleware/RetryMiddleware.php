<?php

declare(strict_types=1);

namespace Stanbic\SDK\Infrastructure\Http\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Client\ClientExceptionInterface;

/**
 * Middleware for retrying failed HTTP requests with exponential backoff and jitter.
 */
final class RetryMiddleware implements MiddlewareInterface
{
    private int $maxAttempts;
    private int $baseDelayMs;

    public function __construct(int $maxAttempts = 3, int $baseDelayMs = 100)
    {
        $this->maxAttempts = $maxAttempts;
        $this->baseDelayMs = $baseDelayMs;
    }

    /**
     * @param RequestInterface $request
     * @param callable(RequestInterface): ResponseInterface $next
     * @return ResponseInterface
     */
    public function __invoke(RequestInterface $request, callable $next): ResponseInterface
    {
        $attempt = 0;
        /** @var list<ClientExceptionInterface> $exceptions */
        $exceptions = [];
        while (true) {
            try {
                $response = $next($request);
                $code = $response->getStatusCode();
                // Retry only for server errors (>=500) or specific client retryable codes
                $shouldRetry = ($code >= 500) || in_array($code, [408, 429], true);
                if (!$shouldRetry) {
                    return $response;
                }
            } catch (ClientExceptionInterface $e) {
                $exceptions[] = $e;
            }
            $attempt++;
            if ($attempt >= $this->maxAttempts) {
                if (!empty($exceptions)) {
                    throw $exceptions[0];
                }
                throw new \RuntimeException('Max retry attempts reached');
            }
            $exponent = $attempt - 1;
            $multiplier = 1 << $exponent; // safe integer power of two
            $delay = $this->baseDelayMs * $multiplier;
            $jitterMax = intdiv($this->baseDelayMs, 2);
            $jitter = random_int(0, $jitterMax);
            $sleepUs = ($delay + $jitter) * 1000;
            $sleepUs = (int) $sleepUs;
            usleep($sleepUs);
        }
    }
}
