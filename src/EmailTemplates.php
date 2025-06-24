<?php

namespace Pantono\Email;

use Pantono\Email\Repository\EmailTemplatesRepository;
use Pantono\Hydrator\Hydrator;
use Pantono\Email\Model\EmailTemplateBlockType;
use Pantono\Email\Model\EmailTemplate;
use Pantono\Email\Model\EmailTemplateBlockField;
use Pantono\Contracts\Locator\UserInterface;
use Pantono\Email\Event\PreEmailTemplateSaveEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Pantono\Email\Event\PostEmailTemplateSaveEvent;
use Twig\Environment;
use Pantono\Email\Exception\MissingContext;
use Pantono\Email\Event\PreEmailBlockTypeSaveEvent;
use Pantono\Email\Event\PostEmailBlockTypeSaveEvent;
use Pantono\Email\Model\EmailTemplateBlock;

class EmailTemplates
{
    private EmailTemplatesRepository $repository;
    private Hydrator $hydrator;
    private EventDispatcher $dispatcher;
    private Environment $twig;

    public function __construct(EmailTemplatesRepository $repository, Hydrator $hydrator, EventDispatcher $dispatcher, Environment $twig)
    {
        $this->repository = $repository;
        $this->hydrator = $hydrator;
        $this->dispatcher = $dispatcher;
        $this->twig = $twig;
    }

    public function getTemplateById(int $id): ?EmailTemplate
    {
        return $this->hydrator->hydrate(EmailTemplate::class, $this->repository->getTemplateById($id));
    }

    public function getBlockTypeById(int $id): ?EmailTemplateBlockType
    {
        return $this->hydrator->hydrate(EmailTemplateBlockType::class, $this->repository->getBlockTypeById($id));
    }

    /**
     * @return EmailTemplateBlock[]
     */
    public function getBlocksForTemplate(EmailTemplate $template): array
    {
        return $this->hydrator->hydrateSet(EmailTemplateBlock::class, $this->repository->getBlocksForTemplate($template));
    }

    /**
     * @return EmailTemplateBlockField[]
     */
    public function getFieldsForBlockType(EmailTemplateBlockType $blockType): array
    {
        return $this->hydrator->hydrateSet(EmailTemplateBlockField::class, $this->repository->getFieldsForBlockType($blockType));
    }

    public function addHistoryToTemplate(EmailTemplate $template, UserInterface $user, string $entry): void
    {
        $this->repository->addHistoryToTemplate($template, $user, $entry);
    }

    public function saveTemplate(EmailTemplate $emailTemplate): void
    {
        $event = new PreEmailTemplateSaveEvent();
        $event->setCurrent($emailTemplate);
        $previous = $emailTemplate->getId() ? $this->getTemplateById($emailTemplate->getId()) : null;
        $event->setPrevious($previous);

        $this->dispatcher->dispatch($event);

        $this->repository->saveTemplate($emailTemplate);

        $event = new PostEmailTemplateSaveEvent();
        $event->setCurrent($emailTemplate);
        $event->setPrevious($previous);
        $this->dispatcher->dispatch($event);
    }

    public function saveBlockType(EmailTemplateBlockType $type): void
    {
        $event = new PreEmailBlockTypeSaveEvent();
        $event->setCurrent($type);
        $previous = $type->getId() ? $this->getBlockTypeById($type->getId()) : null;
        $event->setPrevious($previous);
        $this->dispatcher->dispatch($event);

        $this->repository->saveBlockType($type);

        $event = new PostEmailBlockTypeSaveEvent();
        $event->setCurrent($type);
        $event->setPrevious($previous);
        $this->dispatcher->dispatch($event);
    }

    public function renderTemplate(EmailTemplate $template, array $context = []): string
    {
        $missing = $template->getMissingContexts($context);
        if (!empty($missing)) {
            throw new MissingContext('Cannot render template ' . $template->getName() . ' without contexts: ' . implode(', ', $missing));
        }
        $content = '';
        foreach ($template->getBlocks() as $block) {
            $template = $this->twig->createTemplate($block->getBlockType()->getTemplate());
            $content .= $this->twig->render($template, $context);
        }
        return $this->twig->render('email/inky-template.twig', ['content' => $content]);
    }

    public function addHistoryToBlock(EmailTemplateBlockType $block, UserInterface $user, string $entry): void
    {
        $this->repository->addHistoryToBlock($block, $user, $entry);
    }
}
