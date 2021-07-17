<?php

declare(strict_types=1);

namespace Pulsar\Core\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Pulsar\Core\App;
use Pulsar\Core\Http\HttpExceptionInterface;

/**
 * @author Devcoder.xyz <dev@devcoder.xyz>
 */
final class HttpExceptionHandlerMiddleware implements MiddlewareInterface
{
    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * RouterMiddleware constructor.
     * @param ResponseFactoryInterface|null $responseFactory
     */
    public function __construct(?ResponseFactoryInterface $responseFactory = null)
    {
        $this->responseFactory = $responseFactory ?: App::getResponseFactory();
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /*** @var ResponseInterface $response */
        try {
            return $handler->handle($request);
        } catch (HttpExceptionInterface $httpException) {
            $response = $this->responseFactory->createResponse($httpException->getStatusCode());
            $response->getBody()->write($httpException->getMessage());
        }
        return $response;
    }
}
