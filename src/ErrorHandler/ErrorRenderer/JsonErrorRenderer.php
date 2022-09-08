<?php

namespace Pulsar\Core\ErrorHandler\ErrorRenderer;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Pulsar\Core\Http\Exception\HttpExceptionInterface;
use function json_encode;

final class JsonErrorRenderer
{
    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * @var bool
     */
    private $debug;

    public function __construct(ResponseFactoryInterface $responseFactory, bool $debug = false)
    {
        $this->responseFactory = $responseFactory;
        $this->debug = $debug;
    }

    public function __invoke(HttpExceptionInterface $exception): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($exception->getStatusCode());
        $data = [
            'status' => $exception->getStatusCode(),
            'detail' => $this->isDebug() ? $exception->getMessage() : $exception->getDefaultMessage()
        ];

        $e = $exception->getPrevious() ?: $exception;
        if ($this->isDebug() === true) {
            $data['debug']['class'] = get_class($e);
            $data['debug']['file'] = $e->getFile();
            $data['debug']['line'] = $e->getLine();
            $data['debug']['trace'] = array_merge($e->getTrace(), $exception->getTrace());
        }

        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->debug;
    }
}
