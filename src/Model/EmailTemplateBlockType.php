<?php

namespace Pantono\Email\Model;

use Pantono\Contracts\Attributes\Filter;
use Pantono\Database\Traits\SavableModel;
use Pantono\Contracts\Attributes\Locator;
use Pantono\Email\EmailTemplates;

class EmailTemplateBlockType
{
    use SavableModel;

    private ?int $id = null;
    private string $name;
    private string $description;
    private string $category;
    private ?string $icon = null;
    private string $template;
    private bool $system;
    #[Filter('json_decode')]
    private array $allowedChildren;
    private ?int $maxChildren = null;
    /**
     * @var EmailTemplateBlockField[]
     */
    #[Locator(methodName: 'getFieldsForBlockType', className: EmailTemplates::class)]
    private array $fields = [];

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

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $category): void
    {
        $this->category = $category;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): void
    {
        $this->icon = $icon;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function setTemplate(string $template): void
    {
        $this->template = $template;
    }

    public function isSystem(): bool
    {
        return $this->system;
    }

    public function setSystem(bool $system): void
    {
        $this->system = $system;
    }

    public function getAllowedChildren(): array
    {
        return $this->allowedChildren;
    }

    public function setAllowedChildren(array $allowedChildren): void
    {
        $this->allowedChildren = $allowedChildren;
    }

    public function getMaxChildren(): ?int
    {
        return $this->maxChildren;
    }

    public function setMaxChildren(?int $maxChildren): void
    {
        $this->maxChildren = $maxChildren;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function setFields(array $fields): void
    {
        $this->fields = $fields;
    }

    public function isChildAllowed(string $child): bool
    {
        if (in_array('*', $this->allowedChildren)) {
            return true;
        }
        return in_array($child, $this->allowedChildren);
    }
}
