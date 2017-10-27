<?php

if (true === $container->settings['session']['enable']) {
    // session must be initialized (by another middleware)
    // before adding this Twig extension
    $app->add(function (
        Psr\Http\Message\RequestInterface $request,
        Psr\Http\Message\ResponseInterface $response,
        callable $next
    ) use ($container) {
        if ($container->view instanceof Slim\Views\Twig) {
            $container->view->addExtension(new App\TwigExtensions\FlashMessages(
                $container->flash
            ));
        }

        return $next($request, $response);
    });

    $app->add($container->csrf);
    $app->add(new RKA\SessionMiddleware($container->settings['session']));
}
