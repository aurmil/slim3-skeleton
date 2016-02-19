<?php

$yaml = APP_PATH.'/config.yml';

if (file_exists($yaml) && is_file($yaml) && is_readable($yaml)) {
    $env = getenv('ENVIRONMENT') ?: 'development';

    $configCache = VAR_PATH."/cache/config/$env.json";

    if (file_exists($configCache) && is_file($configCache)
        && is_readable($configCache)
        && filemtime($configCache) > filemtime($yaml)
    ) {
        $config = json_decode(file_get_contents($configCache), true);
    } else {
        $config = Symfony\Component\Yaml\Yaml::parse(file_get_contents($yaml));

        if (isset($config[$env])) {
            $config = $config[$env];

            // Slim

            $config = array_merge($config, $config['Slim']);
            unset($config['Slim']);

            // Logger

            if (isset($config['Monolog']['StreamHandler']['logLevel'])) {
                $level = 'Monolog\Logger::'.$config['Monolog']['StreamHandler']['logLevel'];

                if (defined($level)) {
                    $config['Monolog']['StreamHandler']['logLevel'] = constant($level);
                } else {
                    throw new \Exception('StreamHandler log level is incorrect.');
                }
            }

            if (true === $config['Monolog']['NativeMailerHandler']['enable']
                && isset($config['Monolog']['NativeMailerHandler']['logLevel'])
            ) {
                $level = 'Monolog\Logger::'.$config['Monolog']['NativeMailerHandler']['logLevel'];

                if (defined($level)) {
                    $config['Monolog']['NativeMailerHandler']['logLevel'] = constant($level);
                } else {
                    throw new \Exception('NativeMailerHandler log level is incorrect.');
                }
            }

            // View renderer

            $config['Twig']['templatesPath'] = APP_PATH.'/templates';

            if (true === $config['Twig']['cache']) {
                $config['Twig']['cache'] = VAR_PATH.'/cache/twig';
            }

            // save config cache file

            file_put_contents($configCache, json_encode($config));
        } else {
            throw new \Exception("Environment $env not found in configuration file.");
        }
    }
} else {
    throw new \Exception('Configuration file not found.');
}

return $config;
