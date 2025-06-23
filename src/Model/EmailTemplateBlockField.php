<?php

namespace Pantono\Email\Model;

use Pantono\Contracts\Attributes\Filter;
use Pantono\Database\Traits\SavableModel;

class EmailTemplateBlockField
{
    use SavableModel;

    private ?int $id = null;
    private int $blockTypeId;
    private string $name;
    private string $label;
    private string $type;
    private bool $required;
    private mixed $defaultValue;
    #[Filter('json_decode')]
    private array $options = [];
    #[Filter('json_decode')]
    private array $validationRules = [];
    private int $displayOrder;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getBlockTypeId(): int
    {
        return $this->blockTypeId;
    }

    public function setBlockTypeId(int $blockTypeId): void
    {
        $this->blockTypeId = $blockTypeId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function setRequired(bool $required): void
    {
        $this->required = $required;
    }

    public function getDefaultValue(): mixed
    {
        return $this->defaultValue;
    }

    public function setDefaultValue(mixed $defaultValue): void
    {
        $this->defaultValue = $defaultValue;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    public function getValidationRules(): array
    {
        return $this->validationRules;
    }

    public function setValidationRules(array $validationRules): void
    {
        $this->validationRules = $validationRules;
    }

    public function getDisplayOrder(): int
    {
        return $this->displayOrder;
    }

    public function setDisplayOrder(int $displayOrder): void
    {
        $this->displayOrder = $displayOrder;
    }
}
