<?php

namespace Pantono\Email\Renderer;

use Pantono\Email\Model\EmailTemplate;
use Twig\Environment;
use Pantono\Email\Model\EmailTemplateBlock;
use Pantono\Contracts\Attributes\ServiceName;

class TwigInkyBlocksRenderer extends AbstractEmailRenderer
{
    private Environment $twig;

    public function __construct(#[ServiceName('TwigEmail')] Environment $twig)
    {
        $this->twig = $twig;
    }

    public function renderTemplate(EmailTemplate $template, array $context = []): string
    {
        $content = '';
        foreach ($template->getBlocks() as $block) {
            if ($block->getParentBlockId()) {
                continue;
            }
            $content .= $this->renderBlock($block, $template, $context);
        }
        $context['content'] = $content;
        return $this->twig->render('email/inky-template.twig', $context);
    }

    private function renderBlock(EmailTemplateBlock $block, EmailTemplate $template, array $context = []): string
    {
        $children = '';
        foreach ($template->getBlocks() as $templateBlock) {
            if ($templateBlock->getParentBlockId() === $block->getId()) {
                if (!$block->getBlockType()->isChildAllowed($templateBlock->getBlockType()->getName())) {
                    throw new \RuntimeException('Block ' . $templateBlock->getBlockType()->getName() . ' is not allowed to be a child of ' . $block->getBlockType()->getName());
                }
                $children .= $this->renderBlock($templateBlock, $template, $context);
            }
        }
        $context['children'] = $children;
        return $block->render($this->twig, $context);
    }
}
