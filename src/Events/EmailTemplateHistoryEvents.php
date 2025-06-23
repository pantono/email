<?php

namespace Pantono\Email\Events;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Pantono\Email\EmailTemplates;
use Pantono\Email\Event\PostEmailTemplateSaveEvent;
use Pantono\Email\Event\PostEmailBlockTypeSaveEvent;
use Pantono\Contracts\Security\SecurityContextInterface;
use Pantono\Contracts\Locator\UserInterface;

class EmailTemplateHistoryEvents implements EventSubscriberInterface
{
    private EmailTemplates $templates;
    private SecurityContextInterface $securityContext;

    public function __construct(EmailTemplates $templates, SecurityContextInterface $securityContext)
    {
        $this->templates = $templates;
        $this->securityContext = $securityContext;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PostEmailTemplateSaveEvent::class => [['saveTemplateHistory', 255]],
            PostEmailBlockTypeSaveEvent::class => [['saveBlockHistory', 255]]
        ];
    }

    public function saveBlockHistory(PostEmailBlockTypeSaveEvent $event): void
    {
        $current = $event->getCurrent();
        $previous = $event->getPrevious();

        if (!$previous) {
            $this->templates->addHistoryToBlock($current, $this->getUser(), 'Created new block');
            return;
        }
    }

    public function saveTemplateHistory(PostEmailTemplateSaveEvent $event): void
    {
        $current = $event->getCurrent();
        $previous = $event->getPrevious();
        if (!$previous) {
            $this->templates->addHistoryToTemplate($current, $this->getUser(), 'Created new template');
            return;
        }
        if ($current->getDescription() !== $previous->getDescription()) {
            $this->templates->addHistoryToTemplate($current, $this->getUser(), 'Changed description from ' . $previous->getDescription() . ' to ' . $current->getDescription());
        }
        if ($current->getName() !== $previous->getName()) {
            $this->templates->addHistoryToTemplate($current, $this->getUser(), 'Changed name from ' . $previous->getName() . ' to ' . $current->getName());
        }
        if ($current->getCategory() !== $previous->getCategory()) {
            $this->templates->addHistoryToTemplate($current, $this->getUser(), 'Changed category from ' . $previous->getCategory() . ' to ' . $current->getCategory());
        }

        if (json_encode($current->getRequiredContext()) !== json_encode($previous->getRequiredContext())) {
            $this->templates->addHistoryToTemplate($current, $this->getUser(), 'Changed required context from ' . implode(', ', $previous->getRequiredContext()) . ' to ' . implode(',', $current->getRequiredContext()));
        }

        foreach ($current->getBlocks() as $block) {
            $previousBlock = $previous->getBlockByBlockId($block->getId());
            if ($previousBlock === null) {
                $this->templates->addHistoryToTemplate($current, $this->getUser(), 'Added block ' . $block->getBlockType()->getName());
            } else {
                foreach ($block->getFieldValues() as $key => $value) {
                    $previousValue = $previousBlock->getFieldValueByName($key);
                    if ($value !== $previousValue) {
                        $this->templates->addHistoryToTemplate($current, $this->getUser(), 'Changed field ' . $key . ' on block ' . $block->getBlockType()->getName() . ' from ' . $previousValue . ' to ' . $value);
                    }
                }
            }
        }
        foreach ($previous->getBlocks() as $block) {
            if ($current->getBlockByBlockId($block->getId()) === null) {
                $this->templates->addHistoryToTemplate($current, $this->getUser(), 'Removed block ' . $block->getBlockType()->getName());
            }
        }
    }

    private function getUser(): UserInterface
    {
        /**
         * @var $user UserInterface
         */
        return $this->securityContext->get('user');
    }
}
