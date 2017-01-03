<?php

namespace App\TwigExtensions;

class FlashMessages extends \Twig_Extension
{
    /**
     * @var \Slim\Flash\Messages
     */
    private $flashMessages;

    /**
     * @param \Slim\Flash\Messages $flashMessages
     */
    public function __construct(\Slim\Flash\Messages $flashMessages)
    {
        $this->flashMessages = $flashMessages;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('flash', [$this, 'getMessages']),
        ];
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getMessages($key = null)
    {
        if (null !== $key) {
            $message = $this->flashMessages->getMessage($key);

            if (is_array($message) && 1 === count($message)) {
                $message = reset($message);
            }

            return $message;
        }

        return $this->flashMessages->getMessages();
    }
}
