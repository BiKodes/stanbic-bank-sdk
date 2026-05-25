<?php

declare(strict_types=1);

namespace Stanbic\SDK\Infrastructure\Http\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Stanbic\SDK\Infrastructure\Http\AccessToken;
use Stanbic\SDK\Infrastructure\Http\TokenProviderInterface;

/**
 * Middleware to inject a Bearer token into the Authorization header.
 *
 */
final class AuthMiddleware implements MiddlewareInterface
{
    private AccessToken|TokenProviderInterface $tokenSource;

    public function __construct(AccessToken|TokenProviderInterface $tokenSource)
    {
        $this->tokenSource = $tokenSource;
    }

    /**
     * @param RequestInterface $request
     * @param callable(RequestInterface): ResponseInterface $next
     * @return ResponseInterface
     */
    public function __invoke(RequestInterface $request, callable $next): ResponseInterface
    {
        $token = $this->tokenSource instanceof AccessToken
            ? $this->tokenSource->token
            : $this->tokenSource->getToken();

        $request = $request->withHeader('Authorization', 'Bearer ' . $token);
        return $next($request);
    }
}
