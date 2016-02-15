<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Slim\Views\Twig;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class Front
{
    private $appConfig;
    private $logger;
    private $renderer;

    public function __construct(
        array $appConfig,
        LoggerInterface $logger,
        Twig $renderer
    ) {
        $this->appConfig = $appConfig;
        $this->logger = $logger;
        $this->renderer = $renderer;
    }

    public function home(Request $request, Response $response)
    {
        $viewData = ['config' => $this->appConfig];
        return $this->renderer->render($response, 'front/home.twig', $viewData);
    }
}
