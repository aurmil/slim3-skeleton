<?php

namespace App\Controller;

use Interop\Container\ContainerInterface;

/**
 * @link https://github.com/juliangut/slim-controller
 */
class BaseController
{
    /**
     * @var $container \Interop\Container\ContainerInterface
     */
    protected $container;

    /**
     * @param \Interop\Container\ContainerInterface $container
     */
    final public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return \Interop\Container\ContainerInterface
     */
    final public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param string $name
     */
    final public function __get($name)
    {
        return $this->container->get($name);
    }

    /**
     * @param string $name
     */
    final public function __isset($name)
    {
        return $this->container->has($name);
    }
}
