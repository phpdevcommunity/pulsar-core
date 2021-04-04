<?php

declare(strict_types=1);

namespace Pulsar\Core\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Pulsar\Core\App;

/**
 * @author Devcoder.xyz <dev@devcoder.xyz>
 */
abstract class Controller
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    protected function get(string $id)
    {
        return $this->container->get($id);
    }

    protected function redirect(string $url, int $status = 302): ResponseInterface
    {
        $response = App::getResponseFactory()->createResponse($status);
        return $response->withHeader('Location', $url);
    }

    protected function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        return App::getResponseFactory()->createResponse($code, $reasonPhrase);
    }
}
