<?php

// Mail sender

$container['mailer'] = function ($container) {
    $config = $container->settings['SwiftMailer'];
    $transport = false;

    if ('smtp' === $config['transport']) {
        $transport = new \Swift_SmtpTransport();
        $options = [
            'host', 'port', 'encryption',
            'auth_mode', 'username', 'password'
        ];
    } elseif ('sendmail' === $config['transport']) {
        $transport = new \Swift_SendmailTransport();
        $options = ['command'];
    }

    if ($transport) {
        if (isset($options) && is_array($options) && !empty($options)) {
            foreach ($options as $option) {
                if (isset($config[$option]) && $config[$option]) {
                    $methodName = str_replace('_', ' ', $option);
                    $methodName = ucwords($methodName);
                    $methodName = str_replace(' ', '', $methodName);
                    $methodName = 'set' . $methodName;
                    $transport->{$methodName}($config[$option]);
                }
            }
        }

        return new \Swift_Mailer($transport);
    }

    return false;
};

// Logger

$container['logger'] = function ($container) {
    $config = $container->settings['Monolog'];

    $logger = new Monolog\Logger($config['loggerName']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());

    $formatter = new Monolog\Formatter\LineFormatter();
    $formatter->includeStacktraces();

    $handler = 'StreamHandler';

    if (true === $config[$handler]['enable']) {
        $handler = new Monolog\Handler\StreamHandler(
            VAR_PATH.'/log/app-'.date('Y-m').'.log',
            $config[$handler]['logLevel']
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
            $config[$handler]['logLevel']
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
            $config[$handler]['logLevel']
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

    if (true === $container->settings['Security']['enable_csrf_token_persistence']) {
        $csrf->setPersistentTokenMode(true);
    }

    return $csrf;
};
