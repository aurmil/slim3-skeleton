# Slim 3 Skeleton

## What's included?

* [Slim v3](https://www.slimframework.com/)
* [Slim Twig-View + Twig v2](https://github.com/slimphp/Twig-View)
* [Slim Flash Messages](https://github.com/slimphp/Slim-Flash)
* [Slim CSRF Protection](https://github.com/slimphp/Slim-Csrf)
* [Akrabat Slim Session Middleware](https://github.com/akrabat/rka-slim-session-middleware)
* [Symfony Yaml Component v3](http://symfony.com/doc/current/components/yaml.html)
* [Swift Mailer v6](http://swiftmailer.org/)
* [Monolog v1](https://github.com/Seldaek/monolog)

## Installation

Required: PHP 7 and [Composer](https://getcomposer.org/doc/00-intro.md)

Run the following command, replacing `[your-project-name]` with the name of the folder you want to create.

```sh
composer create-project aurmil/slim3-skeleton [your-project-name]
```

This skeleton includes a `.htaccess` file for Apache but [Slim supports other Web servers](https://www.slimframework.com/docs/v3/start/web-servers.html).

* Optional: create a virtual host that points to `public` folder
* When using Apache, make sure it has `AllowOverride All` for your project path (or a parent folder) for [Slim URL rewriting](https://www.slimframework.com/docs/v3/start/web-servers.html) to work
* Make sure `var` folder is writable by Web server

## Configuration

Configuration files are stored in `config` folder. There is one YAML file per subject/package, for better readability/management. Other package-specific configuration files can be stored there (and then need to be handled in application code). You can also add whatever you need into `app.yaml` file as it is up to you to use new configuration values in application code.

Some configuration values can change from an environment to another. Current environment name is read from `ENVIRONMENT` env variable (default = `development`). Environment-specific configuration files override values from global configuration. Simply copy-paste one existing YAML file into a folder whose name is a valid environment name. Then edit this file and remove everything except the values you want to change for this environment. There are examples in `development-example` and `production-example` folders.

Configuration is available in application through:

* `$config` variable in `src/bootstrap.php`
* Container's `settings` entry: `$container->settings` usually and `$this->settings` in controllers extending `App\Controllers\Controller`
* `config` variable in Twig templates: `{{ config.my_custom_setting_key }}`, but it contains only `app` and `security` configuration files

## Controllers

Controllers can inherit from `App\Controllers\Controller` class.

It provides a `render()` method and automatic access to Slim Container entries through `$this->my_service_entry_name`

## Session

In `session.yaml` configuration file, you can enable or disable session usage.

Session is required if you want to use Flash messages or CSRF protection.

## CSRF

If session is enabled, CSRF token is generated for each request.

In `security.yaml` configuration file, you can enable token persistence: a token is generated for each user but not for each request. Simplifies usage of Ajax but makes application vulnerable to replay attacks if you are not using HTTPS.

If CSRF check fails, the request has a `csrf_status` attribute set to false. You can check this attribute/value in routes/controllers:

```php
if (false === $request->getAttribute('csrf_status')) {
    // CSRF check failed
}
```

In Twig templates, you can add CSRF hidden fields with:

```twig
{{ csrf() }}
```

If you want to make something custom, you can also access to CSRF token values:

```twig
{{ csrf_token.keys.name }}
{{ csrf_token.keys.value }}
{{ csrf_token.name }}
{{ csrf_token.value }}
```

## Flash Messages

If session is enabled, Flash Messages are available.

To add a message within a route/controller:

```php
$this->flash->addMessage('my_key', 'my_value');
```

To get a message in a Twig template:

```twig
{% set my_var = flash('my_key') %}
```

To get all messages:

```twig
{% set my_var = flash() %}
```

## Emails

In `swiftmailer.yaml` configuration file, you can enable usage of SwiftMailer and then use `mailer` entry from container as Swift_Mailer object in your code.

By configuring `SwiftMailerHandler` (+ `swiftmailer.yaml` file) or `NativeMailerHandler` in `monolog.yaml` configuration file, you can enable or disable sending email with Monolog when an error occurs.

## HTML meta tags

Every `key: value` pair you add under `metas` in `app.yaml` configuration file will be output in HTML head section as a meta tag.

### Title

Page title is a special case. Obviously, `title` and `title_separator` entries won't be output as meta tags like the other ones.

A page title is compound as follows:
* content of the `metaTitle` block a template child could define

```twig
{% block metaTitle %}my custom page title{% endblock %}
```

* if `app.metas.title` configuration entry is not empty:
    * if `app.metas.title_separator` configuration entry is not empty, add the separator
    * add `app.metas.title`

## Code quality tools

Run the following commands to check your code:

* [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) `vendor/bin/phpcs --standard=PSR1,PSR2,PSR12 ./src/`
* [PHP Coding Standards Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer) `vendor/bin/php-cs-fixer fix ./src/ --verbose --dry-run --diff`
* [PHP Mess Detector](https://github.com/phpmd/phpmd) `vendor/bin/phpmd ./src/ text cleancode,codesize,controversial,design,naming,unusedcode`

## License

The MIT License (MIT). Please see [License File](https://github.com/aurmil/slim3-skeleton/blob/master/LICENSE.md) for more information.
