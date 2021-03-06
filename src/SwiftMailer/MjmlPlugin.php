<?php

namespace NotFloran\MjmlBundle\SwiftMailer;

use NotFloran\MjmlBundle\Mjml;
use Swift_Events_SendEvent;
use Swift_Events_SendListener;

class MjmlPlugin implements Swift_Events_SendListener
{
    /**
     * @var Mjml
     */
    private $mjml;

    public function __construct(Mjml $mjml)
    {
        $this->mjml = $mjml;
    }

    /**
     * @param Swift_Events_SendEvent $event
     */
    public function beforeSendPerformed(Swift_Events_SendEvent $event)
    {
        if ($event->getSource() instanceof \Swift_Transport_SpoolTransport) {
            return;
        }

        $message = $event->getMessage();

        if ('text/mjml' === $message->getContentType()) {
            $message->setBody($this->mjml->render($message->getBody()), 'text/html');
        }

        foreach ($message->getChildren() as $part) {
            if (0 === strpos($part->getContentType(), 'text/mjml')) {
                $part->setBody($this->mjml->render($part->getBody()), 'text/html');
            }
        }
    }

    /**
     * @param Swift_Events_SendEvent $event
     */
    public function sendPerformed(Swift_Events_SendEvent $event)
    {
    }
}
