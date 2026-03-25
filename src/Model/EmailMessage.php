<?php

namespace Pantono\Email\Model;

use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Pantono\Contracts\Application\Interfaces\SavableInterface;
use Pantono\Database\Traits\SavableModel;

class EmailMessage implements SavableInterface
{
    use SavableModel;

    private ?int $id = null;
    private \DateTimeInterface $dateAdded;
    private string $fromAddress;
    private string $fromName;
    private string $subject;
    private string $textMessage;
    private string $htmlMessage;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getDateAdded(): \DateTimeInterface
    {
        return $this->dateAdded;
    }

    public function setDateAdded(\DateTimeInterface $dateAdded): void
    {
        $this->dateAdded = $dateAdded;
    }

    public function createSymfonyMessage(): Email
    {
        return (new Email())->from(new Address($this->getFromAddress(), $this->getFromName()))
            ->subject($this->getSubject())
            ->html($this->getHtmlMessage())
            ->text($this->getTextMessage());
    }

    public function getFromAddress(): string
    {
        return $this->fromAddress;
    }

    public function setFromAddress(string $fromAddress): void
    {
        $this->fromAddress = $fromAddress;
    }

    public function getFromName(): string
    {
        return $this->fromName;
    }

    public function setFromName(string $fromName): void
    {
        $this->fromName = $fromName;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    public function getHtmlMessage(): string
    {
        return $this->htmlMessage;
    }

    public function setHtmlMessage(string $htmlMessage): void
    {
        $this->htmlMessage = $htmlMessage;
    }

    public function getTextMessage(): string
    {
        return $this->textMessage;
    }

    public function setTextMessage(string $textMessage): void
    {
        $this->textMessage = $textMessage;
    }

    public function createEmailSend(string $to, ?string $toName = null): EmailSend
    {
        $send = new EmailSend();
        $send->setEmailMessageId($this->getId());
        $send->setMessage($this);
        $send->setToAddress($to);
        $send->setTrackingKey(uniqid());
        $send->setToName($toName ?? null);

        return $send;
    }
}
