<?php

// ========== PATHS ==========

require 'const.php';

// ========== PHP (static) ==========

// errors
error_reporting(-1);
ini_set('error_log', VAR_PATH . '/log/php-' . date('Y-m') . '.log');

// charset
ini_set('default_charset', 'UTF-8');

// ========== COMPOSER ==========

require ROOT_PATH . '/vendor/autoload.php';

// ========== CONFIGURATION ==========

$config = require 'config.php';

// ========== PHP (from configuration) ==========

// errors
ini_set('display_errors', $config['php']['display_errors']);
ini_set('display_startup_errors', $config['php']['display_startup_errors']);
ini_set('log_errors', $config['php']['log_errors']);

// time zone
date_default_timezone_set($config['php']['default_timezone']);

unset($config['php']);

// ========== SLIM ==========

$app = new Slim\App(['settings' => $config]);
$container = $app->getContainer();

require 'dependencies.php';
require 'middlewares.php';
require 'routes.php';

// Error handler
if (!$container->settings['displayErrorDetails']) {
    $container['errorHandler'] = function ($container) {
        return function (
            Psr\Http\Message\RequestInterface $request,
            Psr\Http\Message\ResponseInterface $response,
            Exception $exception
        ) use ($container) {
            $container->logger->error($exception);

            return $container->view->render(
                $response->withStatus(500),
                'errors/error.twig'
            );
        };
    };
}

$app->run();
