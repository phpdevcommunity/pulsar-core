<?php

namespace Pulsar\Core\Controller;

use Psr\Container\ContainerInterface;

/**
 * Class Controller
 * @package App\Controller
 */
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

    /**
     * @param string $id
     * @return mixed
     */
    protected function get(string $id)
    {
        return $this->container->get($id);
    }
}
