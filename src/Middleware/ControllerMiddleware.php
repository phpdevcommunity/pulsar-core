<?php

namespace Pulsar\Core\Middleware;

use Exception;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Pulsar\Core\Controller\Controller;

/**
 * Class ControllerMiddleware
 * @package LiteApp\Middleware
 */
class ControllerMiddleware implements MiddlewareInterface
{
    const CONTROLLER = '_controller';
    const ACTION = '_action';
    const NAME = '_name';

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * RouterMiddleware constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $controller = $this->getController($request);
        if (\is_array($controller) && $controller[0] instanceof Controller) {
            $controller[0]->setContainer($this->container);
        }

        $arguments = array_merge([$request], $this->getArguments($request));
        /**
         * @var ResponseInterface $response
         */
        $response = $controller(...$arguments);
        if (!$response instanceof ResponseInterface) {
            throw new \Exception('The controller must return an instance of Psr\Http\Message\ResponseInterface.');
        }

        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     * @return callable
     */
    private function getController(ServerRequestInterface $request): callable
    {
        $controller = $this->container->get($request->getAttribute(self::CONTROLLER));
        if (is_callable($controller)) {
            return $controller;
        }

        if (!method_exists($controller, $action = $request->getAttribute(self::ACTION))) {

            if ($action === null) {
                throw new \BadMethodCallException(sprintf('Please use a Method on class %s.', get_class($controller)));
            }

            throw new \BadMethodCallException(sprintf('Method "%s" on class %s does not exist.', $action, get_class($controller)));
        }

        return [$controller, $action];
    }

    /**
     * @param ServerRequestInterface $request
     * @return array
     */
    private function getArguments(ServerRequestInterface $request): array
    {
        $attributes = $request->getAttributes();
        unset($attributes[self::CONTROLLER]);
        unset($attributes[self::ACTION]);
        unset($attributes[self::NAME]);

        return array_values($attributes);
    }
}