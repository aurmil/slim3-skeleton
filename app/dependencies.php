<?php

// DIC configuration

$container = $app->getContainer();

// Logger

$container['logger'] = function ($c) {
    $config = $c->get('settings')['Monolog'];

    $logger = new Monolog\Logger($config['loggerName']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());

    if (true === $config['StreamHandler']['enable']) {
        $logger->pushHandler(new Monolog\Handler\StreamHandler(
            VAR_PATH.'/log/app-'.date('Y-m').'.log',
            $config['StreamHandler']['logLevel']
        ));
    }

    if (true === $config['NativeMailerHandler']['enable']
        && '' != $config['NativeMailerHandler']['to']
    ) {
        $logger->pushHandler(new Monolog\Handler\NativeMailerHandler(
            $config['NativeMailerHandler']['to'],
            $config['NativeMailerHandler']['subject'],
            $config['NativeMailerHandler']['from'],
            $config['NativeMailerHandler']['logLevel']
        ));
    }

    return $logger;
};

// View renderer

$container['renderer'] = function ($c) {
    $config = $c->get('settings')['Twig'];
    $path = $config['templatesPath'];
    unset($config['templatesPath']);

    $twig = new Slim\Views\Twig($path, $config);
    $twig->addExtension(new Slim\Views\TwigExtension(
        $c->get('router'),
        $c->get('request')->getUri()
    ));

    $twig['config'] = $c->get('settings')['App'];

    return $twig;
};
