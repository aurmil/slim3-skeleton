<?php

namespace App\Controllers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class FrontController extends Controller
{
    public function home(RequestInterface $request, ResponseInterface $response)
    {
        return $this->render($response, 'front/home.twig');
    }
}
