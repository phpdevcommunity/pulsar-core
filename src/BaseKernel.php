<?php

namespace Pulsar\Core;

use DevCoder\DotEnv;
use Exception;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class Kernel
 * @package App
 */
abstract class BaseKernel implements RequestHandlerInterface
{
    const VERSION = '1.0.0';

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var MiddlewareInterface[]
     */
    private $middlewareCollection = [];

    /**
     * BaseKernel constructor.
     */
    public function __construct()
    {
        $this->boot();
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            /**
             * @var MiddlewareInterface $middleware
             */
            $middleware = current($this->middlewareCollection);
            next($this->middlewareCollection);
            if ($middleware === false) {
                throw new \LogicException('The Middleware must return an instance of Psr\Http\Message\ResponseInterface.');
            }

            if (is_string($middleware)) {
                $middleware = $this->container->get($middleware);
            }

            return $middleware->process($request, $this);

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     *
     * @return void
     */
    protected function boot(): void
    {
        (new DotEnv($this->getProjectDir() . '/.env'))->load();
        $middlewareFile = '/middlewares.php';
        if (getenv('APP_ENV') === 'dev') {
            error_reporting(E_ALL);
            ini_set("display_errors", 1);
            $middlewareFile = sprintf('/middlewares.%s.php', getenv('APP_ENV'));
        }

        $parameters = require $this->getProjectDir() . '/config/parameters.php';
        $services = require $this->getProjectDir() . '/config/services.php';
        $middleware = require $this->getProjectDir() . '/config' . $middlewareFile;

        $this->container = $this->loadContainer($parameters, $services);
        $this->middlewareCollection = $this->loadMiddleware($middleware);
    }

    /**
     * @param array $parameters
     * @param array $services
     * @return ContainerInterface
     */
    abstract protected function loadContainer(array $parameters, array $services): ContainerInterface;

    /**
     * @param array $middleware
     * @return MiddlewareInterface[]|string
     */
    abstract protected function loadMiddleware(array $middleware): array;

    /**
     * @return string
     */
    abstract protected function getProjectDir(): string;
}
