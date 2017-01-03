<?php

// Logger

$container['logger'] = function ($container) {
    $config = $container->settings['Monolog'];

    $logger = new Monolog\Logger($config['loggerName']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());

    $formatter = new Monolog\Formatter\LineFormatter();
    $formatter->includeStacktraces();

    if (true === $config['StreamHandler']['enable']) {
        $handler = new Monolog\Handler\StreamHandler(
            VAR_PATH.'/log/app-'.date('Y-m').'.log',
            $config['StreamHandler']['logLevel']
        );
        $handler->setFormatter($formatter);
        $logger->pushHandler($handler);
    }

    if (true === $config['NativeMailerHandler']['enable']
        && '' != $config['NativeMailerHandler']['to']
    ) {
        $handler = new Monolog\Handler\NativeMailerHandler(
            $config['NativeMailerHandler']['to'],
            $config['NativeMailerHandler']['subject'],
            $config['NativeMailerHandler']['from'],
            $config['NativeMailerHandler']['logLevel']
        );
        $handler->setFormatter($formatter);
        $logger->pushHandler($handler);
    }

    return $logger;
};

// View renderer

$container['view'] = function ($container) {
    $config = $container->settings['Twig'];

    $path = $config['templatesPath'];
    unset($config['templatesPath']);

    $view = new Slim\Views\Twig($path, $config);

    // Instantiate and add Slim specific extension
    $basePath = $container->request->getUri()->getBasePath();
    $basePath = rtrim(str_ireplace('index.php', '', $basePath), '/');
    $view->addExtension(new Slim\Views\TwigExtension(
        $container->router,
        $basePath
    ));

    // Slim Flash Messages Twig extension is added in middlewares.php

    $view->addExtension(new App\TwigExtensions\CsrfToken(
        $container->csrf
    ));

    if (true === $config['debug']) {
        $view->addExtension(new Twig_Extension_Debug());
    }

    return $view;
};

// Flash messages

$container['flash'] = function () {
    return new Slim\Flash\Messages();
};

// CSRF

$container['csrf'] = function ($container) {
    $csrf = new Slim\Csrf\Guard();

    $csrf->setFailureCallable(function (
        Psr\Http\Message\RequestInterface $request,
        Psr\Http\Message\ResponseInterface $response,
        callable $next
    ) use ($container) {
        $request = $request->withAttribute('csrf_status', false);

        return $next($request, $response);
    });

    if (true === $container->settings['CSRF']['enableTokenPersistence']) {
        $csrf->setPersistentTokenMode(true);
    }

    return $csrf;
};
