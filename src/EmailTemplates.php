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
use Pantono\Email\Exception\MissingContext;
use Pantono\Email\Event\PreEmailBlockTypeSaveEvent;
use Pantono\Email\Event\PostEmailBlockTypeSaveEvent;
use Pantono\Email\Model\EmailTemplateBlock;
use Pantono\Email\Filter\EmailTemplateFilter;
use Pantono\Email\Filter\EmailTemplateBlockFilter;
use Pantono\Email\Model\EmailTemplateMapping;
use Pantono\Contracts\Locator\LocatorInterface;
use Pantono\Email\Model\EmailTemplateType;
use Pantono\Email\Renderer\AbstractEmailRenderer;

class EmailTemplates
{
    private EmailTemplatesRepository $repository;
    private Hydrator $hydrator;
    private EventDispatcher $dispatcher;
    private LocatorInterface $locator;

    public function __construct(
        EmailTemplatesRepository $repository,
        Hydrator                 $hydrator,
        EventDispatcher          $dispatcher,
        LocatorInterface         $locator
    )
    {
        $this->repository = $repository;
        $this->hydrator = $hydrator;
        $this->dispatcher = $dispatcher;
        $this->locator = $locator;
    }

    public function getTemplateById(int $id): ?EmailTemplate
    {
        return $this->hydrator->hydrate(EmailTemplate::class, $this->repository->getTemplateById($id));
    }

    /**
     * @return EmailTemplate[]
     */
    public function getTemplatesByFilter(EmailTemplateFilter $filter): array
    {
        return $this->hydrator->hydrateSet(EmailTemplate::class, $this->repository->getEmailTemplatesByFilter($filter));
    }

    /**
     * @return EmailTemplateBlockType[]
     */
    public function getTemplateBlockTypesByFilter(EmailTemplateBlockFilter $filter): array
    {
        return $this->hydrator->hydrateSet(EmailTemplateBlockType::class, $this->repository->getEmailTemplateBlockTypesByFilter($filter));
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
        return $this->getRenderer($template->getType())->renderTemplate($template, $context);
    }

    public function addHistoryToBlock(EmailTemplateBlockType $block, UserInterface $user, string $entry): void
    {
        $this->repository->addHistoryToBlock($block, $user, $entry);
    }

    public function getTemplateForType(string $typeName): ?EmailTemplate
    {
        return $this->hydrator->hydrate(EmailTemplate::class, $this->repository->getTemplateForType($typeName));
    }

    public function saveTemplateMapping(string $type, EmailTemplate $template): void
    {
        $this->repository->saveMappingForType($type, $template);
    }

    /**
     * @return EmailTemplateMapping[]
     */
    public function getAllMappings(): array
    {
        return $this->hydrator->hydrateSet(EmailTemplateMapping::class, $this->repository->getAllMappings());
    }

    private function getRenderer(EmailTemplateType $type): AbstractEmailRenderer
    {
        $renderer = $type->getRendererClass();
        if (!class_exists($renderer)) {
            throw new \RuntimeException('E-mail renderer class ' . $renderer . ' does not exist');
        }

        $class = $this->locator->getClassAutoWire($renderer);
        if (!$class instanceof AbstractEmailRenderer) {
            throw new \RuntimeException('E-mail renderer class ' . $renderer . ' does not implement AbstractEmailRenderer');
        }
        return $class;
    }
}
