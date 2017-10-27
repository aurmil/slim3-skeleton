<?php

$configFilePath = ROOT_PATH . '/config/config.yml';

if (!file_exists($configFilePath)
    || !is_file($configFilePath)
    || !is_readable($configFilePath)
) {
    throw new Exception('Configuration file not found.');
}

$env = getenv('ENVIRONMENT') ?: 'development';
$configCacheFilePath = VAR_PATH . "/cache/config/$env.json";
$config = false;

if (file_exists($configCacheFilePath)
    && is_file($configCacheFilePath)
    && is_readable($configCacheFilePath)
    && filemtime($configCacheFilePath) > filemtime($configFilePath)
) {
    $config = json_decode(file_get_contents($configCacheFilePath), true);
}

if (!$config) {
    $config = Symfony\Component\Yaml\Yaml::parse(file_get_contents($configFilePath));

    if (!isset($config[$env])) {
        throw new Exception("Environment $env not found in configuration file.");
    }

    $config = $config[$env];

    // Slim

    $slimConfig = $config['Slim'];
    unset($config['Slim']);

    if (isset($slimConfig['use_router_cache'])) {
        if (true === $slimConfig['use_router_cache']) {
            $slimConfig['router_cache_file'] = VAR_PATH . '/cache/fastroute.php';
        }

        unset($slimConfig['use_router_cache']);
    }

    foreach ($slimConfig as $k => $v) {
        $camelK = str_replace('_', '', lcfirst(ucwords($k, '_')));
        unset($slimConfig[$k]);
        $slimConfig[$camelK] = $v;
    }

    $config = array_merge($config, $slimConfig);

    // Monolog

    $handlerNames = ['StreamHandler', 'NativeMailerHandler'];

    foreach ($handlerNames as $handlerName) {
        $handlerConfig = $config['Monolog'][$handlerName];

        if (true === $handlerConfig['enable']
            && isset($handlerConfig['level'])
        ) {
            $level = 'Monolog\Logger::' . $handlerConfig['level'];

            if (!defined($level)) {
                throw new Exception("$handlerName log level is incorrect.");
            }

            $config['Monolog'][$handlerName]['level'] = constant($level);
        }
    }

    // Twig

    $config['Twig']['templatesPath'] = ROOT_PATH . '/templates';

    if (true === $config['Twig']['cache']) {
        $config['Twig']['cache'] = VAR_PATH . '/cache/twig';
    }

    // save config cache file

    file_put_contents($configCacheFilePath, json_encode($config));
}

return $config;
