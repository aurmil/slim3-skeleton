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

    $slimConfig = $config['slim'];
    unset($config['slim']);

    if (isset($slimConfig['use_router_cache'])) {
        if (true === $slimConfig['use_router_cache']) {
            $slimConfig['router_cache_file'] = VAR_PATH . '/cache/fastroute.php';
        }

        unset($slimConfig['use_router_cache']);
    }

    foreach ($slimConfig as $optionKey => $optionValue) {
        $camelCaseOptionKey = str_replace('_', '', lcfirst(ucwords($optionKey, '_')));
        unset($slimConfig[$optionKey]);
        $slimConfig[$camelCaseOptionKey] = $optionValue;
    }

    $config = array_merge($config, $slimConfig);

    // Monolog

    foreach ($config['monolog'] as $handlerName => $handlerConfig) {
        if (strlen($handlerName) - strlen('Handler') === strpos($handlerName, 'Handler')
            && is_array($handlerConfig)
            && isset($handlerConfig['enable'])
            && true === $handlerConfig['enable']
            && isset($handlerConfig['level'])
        ) {
            $level = 'Monolog\Logger::' . $handlerConfig['level'];

            if (!defined($level)) {
                throw new Exception("$handlerName log level is incorrect.");
            }

            $config['monolog'][$handlerName]['level'] = constant($level);
        }
    }

    // Twig

    $config['twig']['templatesPath'] = ROOT_PATH . '/templates';
    $config['twig']['cache'] = false;

    if (isset($config['twig']['use_cache'])) {
        if (true === $config['twig']['use_cache']) {
            $config['twig']['cache'] = VAR_PATH . '/cache/twig';
        }

        unset($config['twig']['use_cache']);
    }

    // save config cache file

    file_put_contents($configCacheFilePath, json_encode($config));
}

return $config;
