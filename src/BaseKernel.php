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
 * @package    Pulsar
 * @author    Devcoder.xyz <dev@devcoder.xyz>
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link    https://www.devcoder.xyz
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
        App::init($this->getProjectDir() . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'framework.php');
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
            $middleware = \current($this->middlewareCollection);
            \next($this->middlewareCollection);
            if ($middleware === false) {
                throw new \LogicException('The Middleware must return an instance of Psr\Http\Message\ResponseInterface.');
            }

            if (\is_string($middleware)) {
                $middleware = $this->container->get($middleware);
            }

            if (!$middleware instanceof MiddlewareInterface) {
                throw new \LogicException('The Middleware must be an instance of Psr\Http\Server\MiddlewareInterface.');
            }

            return $middleware->process($request, $this);
        } catch (Exception $exception) {
            \error_log($exception->getTraceAsString());
            \error_log($exception->getMessage());
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

    abstract protected function loadContainer($definitions): ContainerInterface;

    /**
     * @param array $middleware
     * @return array<MiddlewareInterface, string>
     */
    abstract protected function loadMiddleware(array $middleware): array;

    abstract protected function getProjectDir(): string;

    private function boot(): void
    {
        (new DotEnv($this->getProjectDir() . DIRECTORY_SEPARATOR . '.env'))->load();
        if (\getenv('APP_ENV') === 'dev') {
            \error_reporting(E_ALL);
            \ini_set('display_errors', '1');
        }

        $parameters = require $this->getProjectDir() . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'parameters.php';
        $services = require $this->getProjectDir() . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'services.php';
        $middlewares = require $this->getProjectDir() . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'middlewares.php';
        $middlewares = \array_filter($middlewares, function ($environments) {
            return \in_array(\getenv('APP_ENV'), $environments);
        });

        $parameters['pulsar.environment'] = \getenv('APP_ENV');
        $parameters['pulsar.project_dir'] = $this->getProjectDir();

        $this->container = $this->loadContainer(\array_merge($parameters, $services));
        $this->middlewareCollection = $this->loadMiddleware(\array_keys($middlewares));
    }
}
