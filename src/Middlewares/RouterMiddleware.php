<?php

declare(strict_types=1);

namespace Pulsar\Core\Middlewares;

use Aura\Router\RouterContainer;
use DevCoder\Router;
use LogicException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Pulsar\Core\Http\Exception\NotFoundException;
use Pulsar\Core\Middlewares\Router\AuraRouterMiddleware;
use Pulsar\Core\Middlewares\Router\SymfonyRouterMiddleware;
use Symfony\Component\Routing\RouteCollection;
use function array_values;
use function implode;
use function is_a;
use function sprintf;

/**
 * @author Devcoder.xyz <dev@devcoder.xyz>
 */
final class RouterMiddleware implements MiddlewareInterface
{
    public const ROUTERS = [
        'dev_coder' => 'composer require devcoder-xyz/php-router',
        'aura' => 'composer require aura/router',
        'symfony' => 'composer require symfony/routing'
    ];

    private object $router;
    private ResponseFactoryInterface $responseFactory;

    public function __construct(object $router, ResponseFactoryInterface $responseFactory)
    {
        $this->router = $router;
        $this->responseFactory = $responseFactory;
    }

    public function process(
        ServerRequestInterface  $request,
        RequestHandlerInterface $handler): ResponseInterface
    {
        /**
         * @todo a tester !!!
         */
        $response = null;
        if (is_a($this->router, Router::class)) {
            $response = (new \DevCoder\RouterMiddleware($this->router, $this->responseFactory))
                ->process($request, $handler);
        } elseif (is_a($this->router, RouterContainer::class)) {
            $response = (new AuraRouterMiddleware($this->router, $this->responseFactory))
                ->process($request, $handler);
        } elseif (is_a($this->router, RouteCollection::class)) {
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
                implode(' OR ', array_values(self::ROUTERS))
            )
        );
    }
}
