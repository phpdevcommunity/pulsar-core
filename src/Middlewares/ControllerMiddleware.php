<?php

declare(strict_types=1);

namespace Pulsar\Core\Middlewares;

use BadMethodCallException;
use LogicException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Pulsar\Core\Controller\Controller;
use Pulsar\Core\Handler\RequestHandler;
use function array_merge;
use function array_values;
use function get_class;
use function is_callable;
use function method_exists;
use function sprintf;

/**
 * @author Devcoder.xyz <dev@devcoder.xyz>
 */
final class ControllerMiddleware implements MiddlewareInterface
{
    public const CONTROLLER = '_controller';
    public const ACTION = '_action';
    public const NAME = '_name';

    /*** @var ContainerInterface */
    private $container;

    /**
     * RouterMiddleware constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $controller = $this->resolveController($request);
        if ($controller instanceof Controller) {
            $controller->setContainer($this->container);
            $requestHandler = new RequestHandler(
                $this->container,
                $controller->getMiddlewares(),
                static function (ServerRequestInterface $request) use ($controller) {
                    return self::callController($request, $controller);
                }
            );
            return $requestHandler->handle($request);
        }

        return self::callController($request, $controller);
    }

    private function resolveController(ServerRequestInterface $request): callable
    {
        $controller = $this->container->get($request->getAttribute(self::CONTROLLER));
        if (is_callable($controller)) {
            return $controller;
        }

        $action = $request->getAttribute(self::ACTION);
        if (method_exists($controller, $action) === false) {
            throw new BadMethodCallException(
                $action === null
                    ? sprintf('Please use a Method on class %s.', get_class($controller))
                    : sprintf('Method "%s" on class %s does not exist.', $action, get_class($controller))
            );
        }
        return [$controller, $action];
    }

    private static function getArguments(ServerRequestInterface $request): array
    {
        $attributes = $request->getAttributes();
        unset($attributes[self::CONTROLLER]);
        unset($attributes[self::ACTION]);
        unset($attributes[self::NAME]);

        return array_values($attributes);
    }

    private static function callController(ServerRequestInterface $request, $controller): ResponseInterface
    {
        $arguments = array_merge([$request], self::getArguments($request));
        /**
         * @var ResponseInterface $response
         */
        $response = $controller(...$arguments);
        if (!$response instanceof ResponseInterface) {
            throw new LogicException(
                'The controller must return an instance of Psr\Http\Message\ResponseInterface.'
            );
        }
        return $response;
    }
}
