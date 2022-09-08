<?php

declare(strict_types=1);

namespace Pulsar\Core\Middlewares;

use LogicException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Pulsar\Core\Http\Exception\NotFoundException;
use Pulsar\Core\Middlewares\Router\AuraRouterMiddleware;
use Pulsar\Core\Middlewares\Router\SymfonyRouterMiddleware;
use function is_a;

/**
 * @author Devcoder.xyz <dev@devcoder.xyz>
 */
final class RouterMiddleware implements MiddlewareInterface
{
    private const ROUTERS = [
        'composer require devcoder-xyz/php-router',
        'composer require aura/router',
        'composer require symfony/routing',
    ];

    /**
     * @var object
     */
    private $router;

    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    public function __construct(object $router, ResponseFactoryInterface $responseFactory)
    {
        $this->router = $router;
        $this->responseFactory = $responseFactory;
    }

    public function process(
        ServerRequestInterface  $request,
        RequestHandlerInterface $handler): ResponseInterface
    {
        $response = null;
        if (is_a($this->router, \DevCoder\Router::class)) {
            $response = (new \DevCoder\RouterMiddleware($this->router, $this->responseFactory))
                ->process($request, $handler);
        } elseif (is_a($this->router, \Aura\Router\RouterContainer::class)) {
            $response = (new AuraRouterMiddleware($this->router, $this->responseFactory))
                ->process($request, $handler);
        } elseif (is_a($this->router, \Symfony\Component\Routing\RouteCollection::class)) {
            $response = (new SymfonyRouterMiddleware($this->router, $this->responseFactory))
                ->process($request, $handler);
        }

        if ($response instanceof ResponseInterface) {
            if ($response->getStatusCode() === 404) {
                throw new NotFoundException();
            }
            return $response;
        }

        throw new LogicException(
            sprintf(
                'You cannot use "Pulsar Framework" as router is not installed. Try running %s.',
                implode(' OR ', self::ROUTERS)
            )
        );
    }
}
