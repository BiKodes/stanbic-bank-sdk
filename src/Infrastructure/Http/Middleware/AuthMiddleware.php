<?php

declare(strict_types=1);

namespace Stanbic\SDK\Infrastructure\Http\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Client\ClientInterface;
use Stanbic\SDK\Infrastructure\Http\AccessToken;

/**
 * Middleware to inject a Bearer token into the Authorization header.
 *
 */
final class AuthMiddleware implements MiddlewareInterface
{
    private AccessToken $token;

    public function __construct(AccessToken $token)
    {
        $this->token = $token;
    }

    /**
     * @param RequestInterface $request
     * @param callable(RequestInterface): ResponseInterface $next
     * @return ResponseInterface
     */
    public function __invoke(RequestInterface $request, callable $next): ResponseInterface
    {
        $request = $request->withHeader('Authorization', 'Bearer ' . $this->token->token);
        return $next($request);
    }
}
