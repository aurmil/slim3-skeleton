<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

// ========== PATHS ==========

define('ROOT_PATH',   dirname(dirname(__DIR__)));
define('APP_PATH',    ROOT_PATH.'/app');
define('VAR_PATH',    ROOT_PATH.'/var');

// ========== PHP (static) ==========

// errors

error_reporting(-1);
ini_set('error_log', VAR_PATH.'/log/php-'.date('Y-m').'.log');

// charset

ini_set('default_charset', 'UTF-8');

if (5 === PHP_MAJOR_VERSION && PHP_MINOR_VERSION < 6) {
    iconv_set_encoding('internal_encoding', 'UTF-8');
    ini_set('mbstring.internal_encoding', 'UTF-8');
}

// ========== COMPOSER ==========

require ROOT_PATH.'/vendor/autoload.php';

// ========== CONFIGURATION ==========

$config = require APP_PATH.'/src/config.php';

// ========== PHP (from configuration) ==========

// time zone

date_default_timezone_set($config['PHP']['default_timezone']);

// errors

ini_set('display_errors',         $config['PHP']['display_errors']);
ini_set('display_startup_errors', $config['PHP']['display_startup_errors']);
ini_set('log_errors',             $config['PHP']['log_errors']);

// session

if (true === $config['PHP']['need_session']) {
    session_cache_limiter(false);
    session_set_cookie_params(0, '/', '', $config['PHP']['session_cookie_secure'], true);
    session_start();
}

unset($config['PHP']);

// ========== SLIM ==========

$app = new \Slim\App(['settings' => $config]);

require APP_PATH.'/src/dependencies.php';
require APP_PATH.'/src/middlewares.php';
require APP_PATH.'/src/routes.php';

// Error handler
$container = $app->getContainer();
if (!$container->settings['displayErrorDetails']) {
    $container['errorHandler'] = function ($c) {
        return function (Request $request, Response $response, \Exception $exception) use ($c) {
            $c->logger->error($exception);

            return $c->renderer->render(
                $response->withStatus(500),
                'errors/error.twig'
            );
        };
    };
}

$app->run();
