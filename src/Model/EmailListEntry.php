<?php

namespace Pantono\Email\Model;

use Pantono\Contracts\Application\Interfaces\SavableInterface;
use Pantono\Database\Traits\SavableModel;

class EmailListEntry implements SavableInterface
{
    use SavableModel;

    private ?int $id = null;
    private int $listId;
    private ?string $name = null;
    private string $emailAddress;
    private \DateTimeInterface $dateSignedUp;
    private string $signupMethod;
    private bool $unsubscribed;
    private ?\DateTimeInterface $unsubscribedDate;
    private ?int $userId = null;
    private bool $verified;
    private string $verifyKey;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getListId(): int
    {
        return $this->listId;
    }

    public function setListId(int $listId): void
    {
        $this->listId = $listId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(string $emailAddress): void
    {
        $this->emailAddress = $emailAddress;
    }

    public function getDateSignedUp(): \DateTimeInterface
    {
        return $this->dateSignedUp;
    }

    public function setDateSignedUp(\DateTimeInterface $dateSignedUp): void
    {
        $this->dateSignedUp = $dateSignedUp;
    }

    public function getSignupMethod(): string
    {
        return $this->signupMethod;
    }

    public function setSignupMethod(string $signupMethod): void
    {
        $this->signupMethod = $signupMethod;
    }

    public function isUnsubscribed(): bool
    {
        return $this->unsubscribed;
    }

    public function setUnsubscribed(bool $unsubscribed): void
    {
        $this->unsubscribed = $unsubscribed;
    }

    public function getUnsubscribedDate(): ?\DateTimeInterface
    {
        return $this->unsubscribedDate;
    }

    public function setUnsubscribedDate(?\DateTimeInterface $unsubscribedDate): void
    {
        $this->unsubscribedDate = $unsubscribedDate;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $userId): void
    {
        $this->userId = $userId;
    }

    public function isVerified(): bool
    {
        return $this->verified;
    }

    public function setVerified(bool $verified): void
    {
        $this->verified = $verified;
    }

    public function getVerifyKey(): string
    {
        return $this->verifyKey;
    }

    public function setVerifyKey(string $verifyKey): void
    {
        $this->verifyKey = $verifyKey;
    }
}
