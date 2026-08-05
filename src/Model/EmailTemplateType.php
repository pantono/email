<?php

namespace Pantono\Email\Model;

use Pantono\Contracts\Attributes\DatabaseTable;

#[DatabaseTable('email_template_type')]
class EmailTemplateType
{
    private ?int $id = null;
    private string $rendererClass;
    private string $name;
    private bool $enabled;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getRendererClass(): string
    {
        return $this->rendererClass;
    }

    public function setRendererClass(string $rendererClass): void
    {
        $this->rendererClass = $rendererClass;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }
}
