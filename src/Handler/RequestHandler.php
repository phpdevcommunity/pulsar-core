<?php

namespace Pulsar\Core\Handler;

use LogicException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;
use function current;
use function is_string;
use function next;

final class RequestHandler implements RequestHandlerInterface
{
    /**
     * @var array<MiddlewareInterface, string>
     */
    private $middlewareCollection;
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var \Closure|null
     */
    private $then;

    /**
     * @param ContainerInterface $container
     * @param $middlewareCollection array<MiddlewareInterface, string>
     * @param \Closure|null $then
     */
    public function __construct(ContainerInterface $container, array $middlewareCollection, \Closure $then = null)
    {
        $this->container = $container;
        $this->middlewareCollection = $middlewareCollection;
        $this->then = $then;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws Throwable
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $middleware = current($this->middlewareCollection);
        next($this->middlewareCollection);
        if ($middleware === false) {
            $then = $this->then;
            if ($then instanceof \Closure) {
                return $then($request);
            }
            throw new LogicException('The Middleware must return an instance of Psr\Http\Message\ResponseInterface.');
        }

        if (is_string($middleware)) {
            $middleware = $this->container->get($middleware);
        }

        if (!$middleware instanceof MiddlewareInterface) {
            throw new LogicException(
                sprintf(
                    'The Middleware must be an instance of Psr\Http\Server\MiddlewareInterface, "%s" given.',
                    is_object($middleware) ? get_class($middleware) : gettype($middleware)
                )
            );
        }

        return $middleware->process($request, $this);
    }
}