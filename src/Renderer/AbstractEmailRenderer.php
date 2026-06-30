<?php

namespace Pantono\Email\Renderer;

use Pantono\Email\Model\EmailTemplate;

abstract class AbstractEmailRenderer
{
    abstract public function renderTemplate(EmailTemplate $template, array $context = []): string;
}
