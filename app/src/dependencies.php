<?php

// DIC configuration

$container = $app->getContainer();

// Logger

$container['logger'] = function ($c) {
    $config = $c->settings['Monolog'];

    $logger = new Monolog\Logger($config['loggerName']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());

    $formatter = new Monolog\Formatter\LineFormatter;
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

$container['renderer'] = function ($c) {
    $config = $c->settings['Twig'];
    $path = $config['templatesPath'];
    unset($config['templatesPath']);

    $twig = new Slim\Views\Twig($path, $config);
    $twig->addExtension(new Slim\Views\TwigExtension(
        $c->router,
        $c->request->getUri()
    ));

    $twig['config'] = $c->settings['App'];

    return $twig;
};
