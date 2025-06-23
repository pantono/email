<?php

namespace Pantono\Email\Model;

use Pantono\Database\Traits\SavableModel;

class EmailStatus
{
    use SavableModel;

    private ?int $id = null;
    private string $name;
    private bool $bounced;
    private bool $complained;
    private bool $sent;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function isBounced(): bool
    {
        return $this->bounced;
    }

    public function setBounced(bool $bounced): void
    {
        $this->bounced = $bounced;
    }

    public function isComplained(): bool
    {
        return $this->complained;
    }

    public function setComplained(bool $complained): void
    {
        $this->complained = $complained;
    }

    public function isSent(): bool
    {
        return $this->sent;
    }

    public function setSent(bool $sent): void
    {
        $this->sent = $sent;
    }
}
