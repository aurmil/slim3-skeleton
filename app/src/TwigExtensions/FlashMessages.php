<?php
declare(strict_types = 1);

namespace App\TwigExtensions;

class FlashMessages extends \Twig_Extension
{
    /**
     * @var \Slim\Flash\Messages
     */
    private $flashMessages;

    public function __construct(\Slim\Flash\Messages $flashMessages)
    {
        $this->flashMessages = $flashMessages;
    }

    /**
     * @return Twig_SimpleFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new \Twig_SimpleFunction('flash', [$this, 'getMessages']),
        ];
    }

    /**
     * @return mixed
     */
    public function getMessages(string $key = '')
    {
        if ('' !== $key) {
            $message = $this->flashMessages->getMessage($key);

            if (is_array($message) && 1 === count($message)) {
                $message = reset($message);
            }

            return $message;
        }

        return $this->flashMessages->getMessages();
    }
}
