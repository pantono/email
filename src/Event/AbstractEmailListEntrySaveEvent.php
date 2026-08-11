<?php

namespace Pantono\Email\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Pantono\Email\Model\EmailListEntry;

class AbstractEmailListEntrySaveEvent extends Event
{
    private EmailListEntry $current;
    private ?EmailListEntry $previous = null;

    public function getCurrent(): EmailListEntry
    {
        return $this->current;
    }

    public function setCurrent(EmailListEntry $current): void
    {
        $this->current = $current;
    }

    public function getPrevious(): ?EmailListEntry
    {
        return $this->previous;
    }

    public function setPrevious(?EmailListEntry $previous): void
    {
        $this->previous = $previous;
    }
}
