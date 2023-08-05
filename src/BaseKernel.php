<?php
declare(strict_types=1);

namespace Pulsar\Core;

use Closure;
use DateTimeImmutable;
use DevCoder\DotEnv;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Pulsar\Core\ErrorHandler\ErrorHandler;
use Pulsar\Core\ErrorHandler\ExceptionHandler;
use Pulsar\Core\Handler\RequestHandler;
use Pulsar\Core\Http\Exception\HttpExceptionInterface;
use Pulsar\Core\Package\PackageInterface;
use Symfony\Component\Console\Application;
use Throwable;
use function array_filter;
use function array_keys;
use function array_merge;
use function date_default_timezone_set;
use function error_reporting;
use function get_class;
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

    private ?string $env = null;
    protected ContainerInterface $container;
    /**
     * @var array<MiddlewareInterface, string>
     */
    private array $middlewareCollection = [];
    protected ?float $startTime = null;

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
    final public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $requestHandler = new RequestHandler($this->container, $this->middlewareCollection);
            $response = $requestHandler->handle($request);
            if ($this->startTime !== null) {
                $diff = (microtime(true) - $this->startTime) * 1000;
            }
            return $response;
        } catch (Throwable $exception) {
            if (!$exception instanceof HttpExceptionInterface) {
                $this->log($exception);
            }

            $exceptionHandler = $this->container->get(ExceptionHandler::class);
            return $exceptionHandler->render($request, $exception);
        }
    }

    final public function getEnv(): string
    {
        return $this->env;
    }

    final public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    abstract public function getProjectDir(): string;

    abstract public function getCacheDir(): string;

    abstract public function getLogDir(): string;

    abstract public function getConfigDir(): string;

    protected function loadContainer(array $definitions): ContainerInterface
    {
        $containerBuilder = App::createContainerBuilder();
        return $containerBuilder($definitions, ['cache_dir' => $this->getCacheDir()]);
    }

    protected function log(Throwable $exception): void
    {
        $data = [
            'date' => (new DateTimeImmutable())->format('c'),
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTrace(),
        ];

        error_log(
            json_encode($data) . PHP_EOL,
            3,
            $this->getLogDir() . DIRECTORY_SEPARATOR . $this->container->get('pulsar.environment') . '.log'
        );
    }

    final private function boot(): void
    {
        (new DotEnv($this->getProjectDir() . DIRECTORY_SEPARATOR . '.env'))->load();
        $this->initEnv(getenv('APP_ENV'));

        date_default_timezone_set(getenv('APP_TIMEZONE') ?: 'UTC');

        error_reporting(0);
        if ($this->getEnv() === 'dev') {
            $this->startTime = microtime(true);
            ErrorHandler::register();
        }

        $middlewares = (require $this->getConfigDir() . DIRECTORY_SEPARATOR . 'middlewares.php');
        $middlewares = array_filter($middlewares, function ($environments) {
            return in_array($this->getEnv(), $environments);
        });
        $this->middlewareCollection = array_keys($middlewares);

        list($services, $parameters, $listeners, $routes, $commands) = (new Dependency($this))->load();
        $this->container = $this->loadContainer(array_merge(
            $parameters,
            $services,
            [
                '__pulsar_commands' => $commands,
                '__pulsar_listeners' => $listeners,
                '__pulsar_routes' => $routes,
                BaseKernel::class => $this
            ]
        ));
    }

    final private function initEnv($env): void
    {
        $environments = self::getAvailableEnvironments();
        if (!in_array($env, $environments)) {
            throw new InvalidArgumentException(sprintf(
                    'The env "%s" do not exist. Defined environments are: "%s".',
                    $env,
                    implode('", "', $environments))
            );
        }
        $this->env = $env;
    }

    final private static function getAvailableEnvironments(): array
    {
        return array_unique(array_merge(self::DEFAULT_ENVIRONMENTS, App::getCustomEnvironments()));
    }
}
