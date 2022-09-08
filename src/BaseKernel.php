<?php
declare(strict_types=1);

namespace Pulsar\Core;

use DateTimeImmutable;
use DevCoder\DotEnv;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Pulsar\Core\ErrorHandler\ErrorHandler;
use Pulsar\Core\ErrorHandler\ExceptionHandler;
use Pulsar\Core\Handler\RequestHandler;
use Pulsar\Core\Http\Exception\HttpException;
use Pulsar\Core\Http\Exception\HttpExceptionInterface;
use Throwable;
use function array_filter;
use function array_keys;
use function array_merge;
use function date_default_timezone_set;
use function getenv;
use function implode;
use function in_array;
use function json_encode;
use function sprintf;

/**
 * @package    Pulsar
 * @author    Devcoder.xyz <dev@devcoder.xyz>
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link    https://www.devcoder.xyz
 */
abstract class BaseKernel
{
    public const VERSION = '1.0.0';
    public const NAME = 'Pulsar';

    private const DEFAULT_ENVIRONMENTS = [
        'dev',
        'prod'
    ];

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
        App::init($this->getConfigDir() . DIRECTORY_SEPARATOR . 'framework.php');
        $this->boot();
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws Throwable
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $requestHandler = new RequestHandler($this->container, $this->middlewareCollection);
            return $requestHandler->handle($request);
        } catch (Throwable $exception) {
            if (!$exception instanceof HttpExceptionInterface) {
                $this->log($exception);
            }

            $exceptionHandler = $this->container->get(ExceptionHandler::class);
            return $exceptionHandler->render($request, $exception);
        }
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    protected function loadParameters(array $parameters): array
    {
        $parameters['pulsar.environment'] = getenv('APP_ENV');
        $parameters['pulsar.debug'] = getenv('APP_ENV') === 'dev';
        $parameters['pulsar.project_dir'] = $this->getProjectDir();
        $parameters['pulsar.cache_dir'] = $this->getCacheDir();
        $parameters['pulsar.logs_dir'] = $this->getLogDir();
        $parameters['pulsar.config_dir'] = $this->getConfigDir();

        return $parameters;
    }

    abstract protected function getProjectDir(): string;

    abstract protected function getCacheDir(): string;

    abstract protected function getLogDir(): string;

    abstract protected function getConfigDir(): string;

    protected function loadContainer(array $definitions): ContainerInterface
    {
        $containerBuilder = App::createContainerBuilder();
        return $containerBuilder($definitions, ['cache_dir' => $this->getCacheDir()]);
    }

    protected function log(Throwable $exception): void
    {
        $data = [
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTrace(),
        ];

        error_log(
            sprintf('[%s] : %s',
                (new DateTimeImmutable())->format('c'), json_encode($data) . PHP_EOL
            ),
            3,
            $this->getLogDir() . DIRECTORY_SEPARATOR . $this->container->get('pulsar.environment') . '.log'
        );
    }

    private function boot(): void
    {
        date_default_timezone_set(getenv('APP_TIMEZONE') ?: 'UTC');
        $environments = self::getAvailableEnvironments();
        
        (new DotEnv($this->getProjectDir() . DIRECTORY_SEPARATOR . '.env'))->load();
        if (!in_array(getenv('APP_ENV'), $environments)) {
            throw new InvalidArgumentException(sprintf(
                    'The env "%s" do not exist. Defined environments are: "%s".',
                    getenv('APP_ENV'),
                    implode('", "', $environments))
            );
        }
        \error_reporting(0);
        if (getenv('APP_ENV') === 'dev') {
            ErrorHandler::register();
        }

        $parameters = $this->loadParameters(require $this->getConfigDir() . DIRECTORY_SEPARATOR . 'parameters.php');
        $services = require $this->getConfigDir() . DIRECTORY_SEPARATOR . 'services.php';
        $middlewares = require $this->getConfigDir() . DIRECTORY_SEPARATOR . 'middlewares.php';

        $middlewares = array_filter($middlewares, function ($environments) {
            return in_array(getenv('APP_ENV'), $environments);
        });

        $this->container = $this->loadContainer(array_merge($parameters, $services));
        $this->middlewareCollection = array_keys($middlewares);
    }

    private static function getAvailableEnvironments(): array
    {
        return array_unique(array_merge(self::DEFAULT_ENVIRONMENTS, App::getCustomEnvironments()));
    }
}
