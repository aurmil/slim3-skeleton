<?php

namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class FrontController extends BaseController
{
    public function home(Request $request, Response $response)
    {
        return $this->renderer->render($response, 'front/home.twig');
    }
}
