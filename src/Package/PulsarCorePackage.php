<?php

namespace Pulsar\Core\Package;

use DevCoder\Listener\EventDispatcher;
use DevCoder\Listener\ListenerProvider;
use DevCoder\Renderer\PhpRenderer;
use DevCoder\Route;
use DevCoder\Router;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Pulsar\Core\Command\CacheClearCommand;
use Pulsar\Core\ErrorHandler\ErrorRenderer\HtmlErrorRenderer;
use Pulsar\Core\ErrorHandler\ExceptionHandler;
use Pulsar\Core\Middlewares\RouterMiddleware;
use Pulsar\Core\Router\Bridge\RouteFactory;
use Symfony\Component\Console\Application;
use function getenv;

final class PulsarCorePackage implements PackageInterface
{
    public function getDefinitions(): array
    {
        $services = [];
        if (class_exists(EventDispatcher::class)) {
            $services[EventDispatcherInterface::class] = static function (ContainerInterface $container): ?EventDispatcherInterface {
                $events = $container->get('pulsar.listeners');
                $provider = new ListenerProvider();
                foreach ($events as $event => $listeners) {
                    if (is_array($listeners)) {
                        foreach ($listeners as $listener) {
                            $provider->addListener($event, $container->get($listener));
                        }
                    } elseif (is_object($listeners)) {
                        $provider->addListener($event, $listeners);
                    } else {
                        $provider->addListener($event, $container->get($listeners));
                    }
                }
                return new EventDispatcher($provider);
            };
        }

        if (class_exists(Router::class)) {
            $services['router'] = static function (ContainerInterface $container): object {
                /**
                 * @var array<Route> $routes
                 */
                $routes = $container->get('pulsar.routes');
                $factory = new RouteFactory();

                $router = new Router([], $container->get('app.url'));
                foreach ($routes as $route) {
                    $router->add($factory->createDevCoderRoute($route));
                }
                return $router;
            };
        }

        return [
                Application::class => static function (ContainerInterface $container): Application {
                    $commandList = $container->get('pulsar.commands');
                    $commands = [];
                    foreach ($commandList as $commandName) {
                        $commands[] = $container->get($commandName);
                    }
                    $application = new Application();
                    $application->addCommands($commands);
                    return $application;
                },
                'render' => static function (ContainerInterface $container) {
                    return $container->get(PhpRenderer::class);
                },
                PhpRenderer::class => static function (ContainerInterface $container) {
                    return new PhpRenderer($container->get('app.template_dir'));
                },
                RouterMiddleware::class => static function (ContainerInterface $container) {
                    return new RouterMiddleware($container->get('router'), response_factory());
                },
                ExceptionHandler::class => static function (ContainerInterface $container) {
                    return new ExceptionHandler(response_factory(), [
                            'debug' => $container->get('pulsar.debug'),
                            'html_response' => new HtmlErrorRenderer(
                                response_factory(),
                                $container->get('pulsar.debug'),
                                $container->get('app.template_dir') . DIRECTORY_SEPARATOR . '_exception'
                            )
                        ]
                    );
                }
            ] + $services;
    }

    public function getParameters(): array
    {
        return [
            'app.url' => getenv('APP_URL') ?? '',
            'app.locale' => getenv('APP_LOCALE') ?? 'en',
            'app.template_dir' => getenv('APP_TEMPLATE_DIR') ?? '',
        ];
    }

    public function getRoutes(): array
    {
        return [];
    }

    public function getListeners(): array
    {
        return [];
    }

    public function getCommands(): array
    {
        return [
            CacheClearCommand::class
        ];
    }
}
