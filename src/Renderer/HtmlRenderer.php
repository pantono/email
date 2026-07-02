<?php

namespace Pantono\Email\Renderer;

use Pantono\Email\Model\EmailTemplate;
use Twig\Environment;
use Pantono\Contracts\Attributes\ServiceName;

class HtmlRenderer extends AbstractEmailRenderer
{
    private Environment $twig;

    public function __construct(#[ServiceName('TwigEmail')] Environment $twig)
    {
        $this->twig = $twig;
    }

    public function renderTemplate(EmailTemplate $template, array $context = []): string
    {
        $content = $this->twig->render($template->getContent(), $context);
        $context['content'] = $content;
        return $this->twig->render('email/html-template.twig', $context);
    }
}
