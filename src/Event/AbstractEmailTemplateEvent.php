<?php

namespace Pantono\Email\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Pantono\Email\Model\EmailTemplate;

abstract class AbstractEmailTemplateEvent extends Event
{
    private EmailTemplate $current;
    private ?EmailTemplate $previous = null;

    public function getCurrent(): EmailTemplate
    {
        return $this->current;
    }

    public function setCurrent(EmailTemplate $current): void
    {
        $this->current = $current;
    }

    public function getPrevious(): ?EmailTemplate
    {
        return $this->previous;
    }

    public function setPrevious(?EmailTemplate $previous): void
    {
        $this->previous = $previous;
    }
}
