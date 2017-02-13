<?php

$configFilePath = APP_PATH.'/config/config.yml';

if (!file_exists($configFilePath)
    || !is_file($configFilePath)
    || !is_readable($configFilePath)
) {
    throw new Exception('Configuration file not found.');
}

$env = getenv('ENVIRONMENT') ?: 'development';
$configCacheFilePath = VAR_PATH."/cache/config/$env.json";
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

    $config = array_merge($config, $config['Slim']);
    unset($config['Slim']);

    // Monolog

    $handlerNames = ['StreamHandler', 'NativeMailerHandler'];

    foreach ($handlerNames as $handlerName) {
        $handlerConfig = $config['Monolog'][$handlerName];

        if (true === $handlerConfig['enable']
            && isset($handlerConfig['logLevel'])
        ) {
            $level = 'Monolog\Logger::'.$handlerConfig['logLevel'];

            if (!defined($level)) {
                throw new Exception("$handlerName log level is incorrect.");
            }

            $config['Monolog'][$handlerName]['logLevel'] = constant($level);
        }
    }

    // Twig

    $config['Twig']['templatesPath'] = APP_PATH.'/templates';

    if (true === $config['Twig']['cache']) {
        $config['Twig']['cache'] = VAR_PATH.'/cache/twig';
    }

    // save config cache file

    file_put_contents($configCacheFilePath, json_encode($config));
}

return $config;
