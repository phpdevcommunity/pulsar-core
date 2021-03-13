<?php

declare(strict_types=1);

namespace Pulsar\Core\Controller;

use Psr\Container\ContainerInterface;

abstract class Controller
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    protected function get(string $id)
    {
        return $this->container->get($id);
    }
}
