<?php

declare(strict_types=1);

namespace Pulsar\Core\Middlewares\Router;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Pulsar\Core\Middlewares\ControllerMiddleware;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

/**
 * @author Devcoder.xyz <dev@devcoder.xyz>
 */
final class SymfonyRouterMiddleware implements MiddlewareInterface
{
    /**
     * @var \Symfony\Component\Routing\RouteCollection
     */
    private \Symfony\Component\Routing\RouteCollection $routeCollection;
    /**
     * @var ResponseFactoryInterface
     */
    private ResponseFactoryInterface $responseFactory;

    public function __construct(\Symfony\Component\Routing\RouteCollection $routeCollection, ResponseFactoryInterface $responseFactory)
    {
        $this->routeCollection = $routeCollection;
        $this->responseFactory = $responseFactory;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (! \class_exists('Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory')) {
            throw new \LogicException('You cannot use the "Symfony Routing Component" if the Bridge HttpFoundationFactory is not installed : "composer require symfony/psr-http-message-bridge".');
        }

        $httpFoundationFactory = new HttpFoundationFactory();
        $symfonyRequest = $httpFoundationFactory->createRequest($request);
        $context = new RequestContext();
        $context->fromRequest($symfonyRequest);
        $matcher = new UrlMatcher($this->routeCollection, $context);

        try {
            $attributes = $matcher->match($symfonyRequest->getPathInfo());
            $attributes = \array_merge([
                ControllerMiddleware::CONTROLLER => $attributes['_controller'],
                ControllerMiddleware::ACTION => $attributes['_action'] ?? null,
                ControllerMiddleware::NAME => $attributes['_route'],
            ], $attributes);

            unset($attributes['_route']);

            foreach ($attributes as $key => $value) {
                $request = $request->withAttribute($key, $value);
            }
            return $handler->handle($request);

        } catch (ResourceNotFoundException $exception) {
            return $this->responseFactory->createResponse(404);
        }
    }
}
