<?php

namespace Pantono\Email\Event;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Pantono\Email\Model\EmailTemplateBlockType;

class AbstractEmailBlockTypeSaveEvent extends EventDispatcher
{
    private EmailTemplateBlockType $current;
    private ?EmailTemplateBlockType $previous = null;

    public function getCurrent(): EmailTemplateBlockType
    {
        return $this->current;
    }

    public function setCurrent(EmailTemplateBlockType $current): void
    {
        $this->current = $current;
    }

    public function getPrevious(): ?EmailTemplateBlockType
    {
        return $this->previous;
    }

    public function setPrevious(?EmailTemplateBlockType $previous): void
    {
        $this->previous = $previous;
    }
}
