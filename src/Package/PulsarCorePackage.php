<?php

namespace Pulsar\Core\Package;

use Psr\Container\ContainerInterface;
use Pulsar\Core\App;
use Pulsar\Core\Command\CacheClearCommand;
use Pulsar\Core\ErrorHandler\ErrorRenderer\HtmlErrorRenderer;
use Pulsar\Core\ErrorHandler\ExceptionHandler;
use Pulsar\Core\Middlewares\RouterMiddleware;

final class PulsarCorePackage implements PackageInterface
{
    public function getDefinitions(): array
    {
        return [
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
            },
        ];
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
