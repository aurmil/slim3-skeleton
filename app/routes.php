<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

// Controllers

$container = $app->getContainer();

$container['App\Controller\Front'] = function ($c) {
    return new App\Controller\Front(
        $c->get('settings')['App'],
        $c->get('logger'),
        $c->get('renderer')
    );
};

// Routes

$app->get('/', 'App\Controller\Front:home')
    ->setName('home');

// Page not found handler

$container['notFoundHandler'] = function ($c) {
    return function (Request $request, Response $response) use ($c) {
        return $c->get('renderer')->render(
            $response->withStatus(404),
            'errors/not-found.twig'
        );
    };
};
