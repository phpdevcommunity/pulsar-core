<?php

namespace Pulsar\Core\Package;

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Pulsar\Core\App;
use Pulsar\Core\Command\CacheClearCommand;
use Pulsar\Core\ErrorHandler\ErrorRenderer\HtmlErrorRenderer;
use Pulsar\Core\ErrorHandler\ExceptionHandler;
use Pulsar\Core\Middlewares\RouterMiddleware;
use Pulsar\Core\Router\Bridge\RouteFactory;
use Symfony\Component\Console\Application;

final class PulsarCorePackage implements PackageInterface
{
    public function getDefinitions(): array
    {
        $services = [];
        if (class_exists(\DevCoder\Listener\EventDispatcher::class)) {
            $services[EventDispatcherInterface::class] = static function (ContainerInterface $container): ?EventDispatcherInterface {
                $events = $container->get('__pulsar_listeners');
                $provider = new \DevCoder\Listener\ListenerProvider();
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
                return new \DevCoder\Listener\EventDispatcher($provider);
            };
        }

        if (class_exists(\DevCoder\Router::class)) {
            $services['router'] = static function (ContainerInterface $container): object {
                /**
                 * @var array<\DevCoder\Route> $routes
                 */
                $routes = $container->get('__pulsar_routes');
                $factory = new RouteFactory();

                $router = new \DevCoder\Router([], $container->get('app.url'));
                foreach ($routes as $route) {
                    $router->add($factory->createDevCoderRoute($route));
                }
                return $router;
            };
        }

        return [
                Application::class => static function (ContainerInterface $container): Application {
                    $commandList = $container->get('__pulsar_commands');
                    $commands = [];
                    foreach ($commandList as $commandName) {
                        $commands[] = $container->get($commandName);
                    }
                    $application = new Application();
                    $application->addCommands($commands);
                    return $application;
                },
                RouterMiddleware::class => static function (ContainerInterface $container) {
                    return new RouterMiddleware($container->get('router'), App::getResponseFactory());
                },
                ExceptionHandler::class => static function (ContainerInterface $container) {
                    return new ExceptionHandler(App::getResponseFactory(), [
                            'debug' => $container->get('pulsar.debug'),
                            'html_response' => new HtmlErrorRenderer(
                                App::getResponseFactory(),
                                $container->get('pulsar.debug'),
                                App::getTemplateDir() . DIRECTORY_SEPARATOR . '_exception'
                            )
                        ]
                    );
                }
            ] + $services;
    }

    public function getParameters(): array
    {
        return [];
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
