<?php
namespace Pulsar\Core\Router\Bridge;

use Pulsar\Core\Middlewares\RouterMiddleware;
use DevCoder\Route as DevCoderRoute;
use Symfony\Component\Routing\Route as SymfonyRoute;
use Aura\Router\Route as AuraRoute;
use Pulsar\Core\Router\Route;

final class RouteFactory
{
    public function createDevCoderRoute(Route $route) : DevCoderRoute
    {
        if (! \class_exists(DevCoderRoute::class)) {
            $composer = RouterMiddleware::ROUTERS['dev_coder'];
            throw new \LogicException(
                "This class use RouteFactory::createDevCoderRoute(), but DevCoder Router is not installed. Please run {$composer}."
            );
        }

        return new DevCoderRoute(
            $route->getName(),
            $route->getPath(),
            $route->getHandler(),
            $route->getMethods()
        );
    }

    public function createSymfonyRoute(Route $route) : SymfonyRoute
    {
        if (! \class_exists(SymfonyRoute::class)) {
            $composer = RouterMiddleware::ROUTERS['symfony'];
            throw new \LogicException(
                "This class use RouteFactory::createSymfonyRoute(), but Symfony Router is not installed. Please run {$composer}."
            );
        }

        $handler = $route->getHandler();
        return new SymfonyRoute($route->getPath(), [
            '_controller' => $handler[0],
            '_action' => $handler[1] ?? null
        ], [], [], null, null, $route->getMethods());
    }

    public function createAuraRoute(Route $route) : AuraRoute
    {
        if (! \class_exists(AuraRoute::class)) {
            $composer = RouterMiddleware::ROUTERS['aura'];
            throw new \LogicException(
                "This class use RouteFactory::createAuraRoute(), but Aura Router is not installed. Please run {$composer}."
            );
        }

        return (new AuraRoute())
                ->name($route->getName())
                ->allows($route->getMethods())
                ->path($route->getPath())
                ->handler($route->getHandler());
    }
}
