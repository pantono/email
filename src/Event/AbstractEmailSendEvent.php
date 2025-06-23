<?php

namespace Pantono\Email\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Pantono\Email\Model\EmailSend;

abstract class AbstractEmailSendEvent extends Event
{
    private EmailSend $send;

    public function getSend(): EmailSend
    {
        return $this->send;
    }

    public function setSend(EmailSend $send): void
    {
        $this->send = $send;
    }
}
