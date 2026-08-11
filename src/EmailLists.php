<?php

namespace Pantono\Email;

use Pantono\Email\Repository\EmailListRepository;
use Pantono\Hydrator\Hydrator;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Pantono\Email\Model\EmailList;
use Pantono\Email\Model\EmailListEntry;
use Pantono\Email\Event\PreEmailListEntrySaveEvent;
use Pantono\Email\Event\PostEmailListEntrySaveEvent;
use Pantono\Utilities\StringUtilities;

class EmailLists
{
    private EmailListRepository $repository;
    private Hydrator $hydrator;
    private EventDispatcher $dispatcher;

    public function __construct(EmailListRepository $repository, Hydrator $hydrator, EventDispatcher $dispatcher)
    {
        $this->repository = $repository;
        $this->hydrator = $hydrator;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @return EmailList[]
     */
    public function getAllLists(): array
    {
        return $this->hydrator->hydrateSet(EmailList::class, $this->repository->getAllLists());
    }

    public function getEntryForEmail(EmailList $list, string $emailAddress): ?EmailListEntry
    {
        return $this->hydrator->hydrate(EmailListEntry::class, $this->repository->getEntryForEmail($list, $emailAddress));
    }

    public function saveEmailList(EmailList $list): void
    {
        $this->repository->saveModel($list);
    }

    public function signupToList(EmailList $list, string $emailAddress, ?string $name = null): EmailListEntry
    {
        $entry = new EmailListEntry();
        $entry->setName($name);
        $entry->setEmailAddress($emailAddress);
        $entry->setListId($list->getId());
        $entry->setDateSignedUp(new \DateTime);
        $entry->setVerifyKey(StringUtilities::generateRandomToken(20));
        $entry->setUnsubscribed(false);
        $entry->setVerified(false);
        $this->saveEntry($entry);

        return $entry;
    }

    public function saveEntry(EmailListEntry $entry): void
    {
        $previous = $entry->getId() ? $this->hydrator->lookupRecord(EmailListEntry::class, $entry->getId()) : null;
        $event = new PreEmailListEntrySaveEvent();
        $event->setPrevious($previous);
        $event->setCurrent($entry);
        $this->dispatcher->dispatch($event);

        $this->repository->saveModel($entry);

        $event = new PostEmailListEntrySaveEvent();
        $event->setPrevious($previous);
        $event->setCurrent($entry);
        $this->dispatcher->dispatch($event);
    }
}
