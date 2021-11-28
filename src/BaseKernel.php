<?php

declare(strict_types=1);

namespace Pulsar\Core;

use DevCoder\DotEnv;
use Exception;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @package	Pulsar
 * @author	Devcoder.xyz <dev@devcoder.xyz>
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://www.devcoder.xyz
 */
abstract class BaseKernel implements RequestHandlerInterface
{
    public const VERSION = '1.0.0';

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var array<MiddlewareInterface, string>
     */
    private $middlewareCollection = [];

    /**
     * BaseKernel constructor.
     */
    public function __construct()
    {
        App::init($this->getProjectDir() . '/config/framework.php');
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
            $middleware = \current($this->middlewareCollection);
            \next($this->middlewareCollection);
            if ($middleware === false) {
                throw new \LogicException('The Middleware must return an instance of Psr\Http\Message\ResponseInterface.');
            }

            if (\is_string($middleware)) {
                $middleware = $this->container->get($middleware);
            }

            return $middleware->process($request, $this);
        } catch (Exception $exception) {
            \error_log($exception->getMessage(), 0);
            throw $exception;
        }
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    abstract protected function loadContainer(array $parameters, array $services): ContainerInterface;

    /**
     * @param array $middleware
     * @return array<MiddlewareInterface, string>
     */
    abstract protected function loadMiddleware(array $middleware): array;

    abstract protected function getProjectDir(): string;

    private function boot(): void
    {
        (new DotEnv($this->getProjectDir() . '/.env'))->load();
        $middlewareFile = '/middlewares.php';
        if (\getenv('APP_ENV') === 'dev') {
            \error_reporting(E_ALL);
            \ini_set('display_errors', '1');
            $middlewareFile = \sprintf('/middlewares.%s.php', getenv('APP_ENV'));
        }

        $parameters = require $this->getProjectDir() . '/config/parameters.php';
        $services = require $this->getProjectDir() . '/config/services.php';
        $middleware = require $this->getProjectDir() . '/config' . $middlewareFile;

        $parameters = \array_merge([
            'pulsar.environment' => \getenv('APP_ENV'),
            'pulsar.project_dir' =>  $this->getProjectDir(),
        ], $parameters);

        $this->container = $this->loadContainer($parameters, $services);
        $this->middlewareCollection = $this->loadMiddleware($middleware);
    }
}
