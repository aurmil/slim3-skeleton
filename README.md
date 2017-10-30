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

Run the following command, replacing __[your-project-name]__ with the name of the folder you want to create.
```sh
composer create-project aurmil/slim3-skeleton [your-project-name]
```

* Create a virtual host that points to your project path __/public__
* For Apache, make sure it has __AllowOverride All__ for [Slim URL rewriting](http://www.slimframework.com/docs/start/web-servers.html)
* Make sure __var__ folder is writable by Web server

## Web server choice

This skeleton includes a __.htaccess__ file for Apache.

Feel free to read the [Slim documentation about Web servers](http://www.slimframework.com/docs/start/web-servers.html).

## Configuration

Configuration files are stored in `/config` folder. There is one YAML file per subject/package, for better readability/management.

Other package-specific configuration files can be stored there (and then need to be handled in application code).

Some configuration values can change from an environment to another. Current environment name is read from `ENVIRONMENT` env variable (default = `development`). Environment-specific configuration files override values from global configuration.

Simply copy-paste one existing YAML file into a folder whose name is a valid environment name. Then edit that file, remove what does not need to change and modify the values that need to be different for this environment.

You can see examples in `development-example` and `production-example` folders.

You can add whatever you need into `app.yaml` file as it is up to you to use new configuration values in application code.

### Access config in PHP code

In __/src/bootstrap.php__, the whole configuration is in the __$config__ variable.

Configuration is also available through the container in the __settings__ entry (so __$this->settings__ is accessible in routes/controllers).

### Access config in Twig template

Only the contents of __app__ and __security__ configuration files are available in the __config__ variable.

```twig
{{ config.my_custom_setting_key }}
```

## Controllers

Controllers can inherit from __App\Controllers\Controller__ class.

It provides a __render()__ method and automatic access to Slim Container entries through

```php
$this->my_container_entry_name
```

## Session

In configuration file, you can enable or disable session usage.

Session is required if you want to use Flash messages or CSRF protection.

## CSRF

If session is enabled, CSRF token is generated for each request.

In configuration file, you can enable token persistence: a token is generated for each user but not for each request. Simplifies usage of Ajax but makes application vulnerable to replay attacks if you are not using HTTPS.

If CSRF check fails, the request has an attribute __csrf_status__ set to false. You can check this attribute/value in routes/controllers:

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

By configuring __SwiftMailer__ section in configuration file, you can use "mailer" entry from container as Swift_Mailer object in your code.

## Application errors by email

By configuring __Monolog.NativeMailerHandler__ or __SwiftMailer__ + __Monolog.SwiftMailerHandler__ section(s), you can enable or disable sending email with Monolog when an error occurs.

## Meta tags

Every __key: value__ pair you add under __app.metas__ will be output in HTML head section as a meta tag.

### Title

Page title is a special case. Obviously, __title__ and __title_separator__ entries won't be output as meta tags like the other ones.

A page title is formed as follows:
* content of the __metaTitle__ block a template child could define
```twig
{% block metaTitle %}my custom page title{% endblock %}
```
* if __app.metas.title__ is not empty:
    * if __app.metas.title_separator__ is not empty: add the separator
    * add the config title

## License

The MIT License (MIT). Please see [License File](https://github.com/aurmil/slim3-skeleton/blob/master/LICENSE.md) for more information.
