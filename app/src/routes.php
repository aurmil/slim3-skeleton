<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

$container = $app->getContainer();

// Controllers

$controllers = [
    'App\Controller\FrontController'
];

foreach ($controllers as $controllerClassName) {
    $container[$controllerClassName] = function ($c) use ($controllerClassName) {
        /* @var $controller App\Controller\BaseController */
        $controller = new $controllerClassName();
        $controller->setContainer($c);
        return $controller;
    };
}

// Routes

$app->get('/', 'App\Controller\FrontController:home')
    ->setName('home');

// Page not found handler

$container['notFoundHandler'] = function ($c) {
    return function (Request $request, Response $response) use ($c) {
        return $c->renderer->render(
            $response->withStatus(404),
            'errors/not-found.twig'
        );
    };
};
