<?php

namespace Pulsar\Core\ErrorHandler;

use DevCoder\Resolver\Option;
use DevCoder\Resolver\OptionsResolver;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Pulsar\Core\ErrorHandler\ErrorRenderer\HtmlErrorRenderer;
use Pulsar\Core\ErrorHandler\ErrorRenderer\JsonErrorRenderer;
use Pulsar\Core\Http\Exception\HttpException;
use Pulsar\Core\Http\Exception\HttpExceptionInterface;

class ExceptionHandler
{
    private ResponseFactoryInterface $responseFactory;
    private array $options;

    public function __construct(ResponseFactoryInterface $responseFactory, array $options = [])
    {
        $this->responseFactory = $responseFactory;
        $resolver = (new OptionsResolver(
            [
                (new Option('debug'))
                    ->validator(static function ($value) {
                        return is_bool($value);
                    })
                    ->setDefaultValue(false),
                (new Option('json_response'))
                    ->validator(static function ($value) {
                        return  is_callable($value);
                    })
                    ->setDefaultValue(new JsonErrorRenderer($this->responseFactory, $options['debug'])),
                (new Option('html_response'))
                    ->validator(static function ($value) {
                        return is_callable($value);
                    })
                    ->setDefaultValue(new HtmlErrorRenderer($this->responseFactory, $options['debug'])),
            ]
        ));
        $this->options = $resolver->resolve($options);
    }

    public function render(ServerRequestInterface $request, \Throwable $exception): ResponseInterface
    {
        if (!$exception instanceof HttpExceptionInterface) {
            $exception = new HttpException(500, $exception->getMessage(), $exception->getCode(), $exception);
        }

        if ($request->getHeaderLine('accept') === 'application/json') {
            return $this->renderJsonResponse($exception);
        }
        return $this->renderHtmlResponse($exception);
    }

    protected function renderJsonResponse(HttpExceptionInterface $exception): ResponseInterface
    {
        $renderer = $this->options['json_response'];
        return $renderer($exception);
    }

    protected function renderHtmlResponse(HttpExceptionInterface $exception): ResponseInterface
    {
        $renderer = $this->options['html_response'];
        return $renderer($exception);
    }
}
