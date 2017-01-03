<?php

namespace App\TwigExtensions;

class CsrfToken extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
    /**
     * @var \Slim\Csrf\Guard
     */
    private $csrf;

    /**
     * @param \Slim\Csrf\Guard $csrf
     */
    public function __construct(\Slim\Csrf\Guard $csrf)
    {
        $this->csrf = $csrf;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('csrf', [$this, 'getHtml'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @return string
     */
    public function getHtml()
    {
        $token = $this->getGlobals()['csrf_token'];

        return <<<EOT
<input type="hidden" name="{$token['keys']['name']}" value="{$token['name']}">
<input type="hidden" name="{$token['keys']['value']}" value="{$token['value']}">
EOT;
    }

    /**
     * @return array
     */
    public function getGlobals()
    {
        return [
            'csrf_token' => [
                'keys' => [
                    'name' => $this->csrf->getTokenNameKey(),
                    'value' => $this->csrf->getTokenValueKey(),
                ],
                'name' => $this->csrf->getTokenName(),
                'value' => $this->csrf->getTokenValue(),
            ],
        ];
    }
}
