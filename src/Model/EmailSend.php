<?php

namespace Pantono\Email\Model;

use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email as SymfonyModel;
use Pantono\Database\Traits\SavableModel;
use Pantono\Contracts\Attributes\Locator;
use Pantono\Contracts\Attributes\FieldName;
use Pantono\Contracts\Attributes\Lazy;
use Pantono\Contracts\Attributes\NoSave;
use Pantono\Email\Email;

class EmailSend
{
    use SavableModel;

    private ?int $id = null;
    private int $emailMessageId;
    #[Locator(methodName: 'getEmailMessageById', className: Email::class), FieldName('email_message_id'), Lazy, NoSave]
    private ?EmailMessage $message = null;
    private string $messageId;
    private string $toAddress;
    private string $toName;
    private ?\DateTimeImmutable $dateSent = null;
    #[Locator(methodName: 'getStatusById', className: Email::class), FieldName('status_id')]
    private ?EmailStatus $status = null;
    private ?string $errorMessage = null;
    private string $trackingKey;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getEmailMessageId(): int
    {
        return $this->emailMessageId;
    }

    public function setEmailMessageId(int $emailMessageId): void
    {
        $this->emailMessageId = $emailMessageId;
    }

    public function getMessage(): ?EmailMessage
    {
        return $this->message;
    }

    public function setMessage(?EmailMessage $message): void
    {
        $this->message = $message;
    }

    public function getMessageId(): string
    {
        return $this->messageId;
    }

    public function setMessageId(string $messageId): void
    {
        $this->messageId = $messageId;
    }

    public function getDateSent(): ?\DateTimeImmutable
    {
        return $this->dateSent;
    }

    public function setDateSent(?\DateTimeImmutable $dateSent = null): void
    {
        $this->dateSent = $dateSent;
    }

    public function getStatus(): ?EmailStatus
    {
        return $this->status;
    }

    public function setStatus(?EmailStatus $status): EmailSend
    {
        $this->status = $status;
        return $this;
    }
    
    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(?string $errorMessage): void
    {
        $this->errorMessage = $errorMessage;
    }

    public function getTrackingKey(): string
    {
        return $this->trackingKey;
    }

    public function setTrackingKey(string $trackingKey): void
    {
        $this->trackingKey = $trackingKey;
    }

    public function createSymfonyModel(): SymfonyModel
    {
        return $this->message->createSymfonyMessage()->addTo(new Address($this->getToAddress(), $this->getToName()));
    }

    public function getToAddress(): string
    {
        return $this->toAddress;
    }

    public function setToAddress(string $toAddress): void
    {
        $this->toAddress = $toAddress;
    }

    public function getToName(): string
    {
        return $this->toName;
    }

    public function setToName(string $toName): void
    {
        $this->toName = $toName;
    }
}

