<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface;
use Slim\Container;

class Controller
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param ResponseInterface $response
     * @param string $template
     * @param array $data
     */
    public function render(
        ResponseInterface $response,
        $template,
        array $data = []
    ) {
        $this->container->view->render($response, $template, $data);
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->container->get($name);
    }
}
