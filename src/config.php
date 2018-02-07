<?php

$env = getenv('ENVIRONMENT') ?: 'development';
$configCacheFilePath = VAR_PATH . "/cache/config/$env.json";
$config = false;
$configDirPath = ROOT_PATH . '/config';

// search config files

$globalConfigFiles = glob($configDirPath . '/*.yaml');
$globalConfigFiles = array_filter($globalConfigFiles, function ($filename) {
    return is_file($filename);
});

$envConfigFiles = glob($configDirPath . '/' . $env . '/*.yaml');
$envConfigFiles = array_filter($envConfigFiles, function ($filename) {
    return is_file($filename);
});

$allConfigFiles = array_merge($globalConfigFiles, $envConfigFiles);

if (!count($allConfigFiles)) {
    throw new Exception('No configuration file found.');
}

// read cache

if (file_exists($configCacheFilePath)) {
    $config = json_decode(file_get_contents($configCacheFilePath), true);
}

// if cache found, check cache timestamp

if ($config) {
    $configFilesTimestamps = array_map(function ($filePath) {
        return filemtime($filePath);
    }, $allConfigFiles);

    if (max($configFilesTimestamps) > filemtime($configCacheFilePath)) {
        $config = false;
    }
}

// if cache not found or not valid, read config files and process config values

if (!$config) {
    // read config

    foreach ($globalConfigFiles as $filePath) {
        $tmpConfig = Symfony\Component\Yaml\Yaml::parse(
            file_get_contents($filePath)
        );

        if ($tmpConfig) {
            $fileName = basename($filePath, '.yaml');
            $config[$fileName] = $tmpConfig;
        }
    }

    $envConfig = [];

    foreach ($envConfigFiles as $filePath) {
        $tmpConfig = Symfony\Component\Yaml\Yaml::parse(
            file_get_contents($filePath)
        );

        if ($tmpConfig) {
            $fileName = basename($filePath, '.yaml');
            $envConfig[$fileName] = $tmpConfig;
        }
    }

    $config = array_replace_recursive($config, $envConfig);

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
        $camelCaseOptionKey = str_replace('_', '', lcfirst(
            ucwords($optionKey, '_')
        ));
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
