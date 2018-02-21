<?php

// Mail sender

$container['mailer'] = function ($container) {
    $config = $container->settings['swiftmailer'];
    $allowedTransportTypes = ['smtp', 'sendmail'];

    if (false === $config['enable']
        || !in_array($config['transport_type'], $allowedTransportTypes)
    ) {
        return false;
    }

    if ('smtp' === $config['transport_type']) {
        $transport = new \Swift_SmtpTransport();
    } elseif ('sendmail' === $config['transport_type']) {
        $transport = new \Swift_SendmailTransport();
    }

    if (isset($config[$config['transport_type']])
        && is_array($config[$config['transport_type']])
        && count($config[$config['transport_type']])
    ) {
        foreach ($config[$config['transport_type']] as $optionKey => $optionValue) {
            $methodName = 'set' . str_replace('_', '', ucwords($optionKey, '_'));
            $transport->{$methodName}($optionValue);
        }
    }

    return new \Swift_Mailer($transport);
};

// Logger

$container['logger'] = function ($container) {
    $config = $container->settings['monolog'];

    $logger = new Monolog\Logger($config['logger_name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());

    $formatter = new Monolog\Formatter\LineFormatter();
    $formatter->includeStacktraces();

    $handler = 'StreamHandler';

    if (true === $config[$handler]['enable']) {
        $handler = new Monolog\Handler\StreamHandler(
            VAR_PATH . '/log/app-' . date('Y-m') . '.log',
            $config[$handler]['level']
        );
        $handler->setFormatter($formatter);
        $logger->pushHandler($handler);
    }

    $handler = 'NativeMailerHandler';

    if (true === $config[$handler]['enable']
        && $config[$handler]['to']
    ) {
        $handler = new Monolog\Handler\NativeMailerHandler(
            $config[$handler]['to'],
            $config[$handler]['subject'],
            $config[$handler]['from'],
            $config[$handler]['level']
        );
        $handler->setFormatter($formatter);
        $logger->pushHandler($handler);
    }

    $handler = 'SwiftMailerHandler';

    if (true === $config[$handler]['enable']
        && $config[$handler]['to']
        && $container->mailer instanceof \Swift_Mailer
    ) {
        $message = new \Swift_Message($config[$handler]['subject']);
        $message->setFrom($config[$handler]['from'])
            ->setTo($config[$handler]['to']);

        $handler = new Monolog\Handler\SwiftMailerHandler(
            $container->mailer,
            $message,
            $config[$handler]['level']
        );
        $handler->setFormatter($formatter);
        $logger->pushHandler($handler);
    }

    return $logger;
};

// View renderer

$container['view'] = function ($container) {
    $config = $container->settings['twig'];

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

    // Pass some config data to view

    $view['config'] = array_merge(
        $container->settings['app'],
        $container->settings['security']
    );

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

    if (true === $container->settings['security']['enable_csrf_token_persistence']) {
        $csrf->setPersistentTokenMode(true);
    }

    return $csrf;
};
