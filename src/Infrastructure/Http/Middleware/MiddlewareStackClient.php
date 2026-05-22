<?php

declare(strict_types=1);

namespace Stanbic\SDK\Infrastructure\Http\Middleware;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * PSR-18 client that applies a stack of middleware before delegating to the real client.
 */
final class MiddlewareStackClient implements ClientInterface
{
    /** @var list<MiddlewareInterface> */
    private array $middleware;
    private ClientInterface $client;

    /**
     * @param ClientInterface $client The underlying PSR-18 client
     * @param list<MiddlewareInterface> $middleware Middleware stack (outermost first)
     */
    public function __construct(ClientInterface $client, array $middleware)
    {
        $this->client = $client;
        $this->middleware = $middleware;
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        /**
         * @var callable(RequestInterface): ResponseInterface $handler
         */
        $handler = array_reduce(
            array_reverse($this->middleware),
            /**
             * @param callable(RequestInterface):ResponseInterface $next
             * @param MiddlewareInterface $mw
             * @return callable(RequestInterface):ResponseInterface
             */
            fn(callable $next, MiddlewareInterface $mw) => fn(RequestInterface $req) => $mw($req, $next),
            /**
             * @return ResponseInterface
             */
            fn(RequestInterface $req) => $this->client->sendRequest($req)
        );

        return $handler($request);
    }
}
