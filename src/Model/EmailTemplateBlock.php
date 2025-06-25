<?php

namespace Pantono\Email\Model;

use Pantono\Contracts\Attributes\Filter;
use Pantono\Contracts\Attributes\Locator;
use Pantono\Email\EmailTemplates;
use Pantono\Contracts\Attributes\FieldName;
use Pantono\Database\Traits\SavableModel;
use Twig\Environment;

class EmailTemplateBlock
{
    use SavableModel;

    private ?int $id = null;
    private int $templateId;
    #[Locator(methodName: 'getBlockTypeById', className: EmailTemplates::class), FieldName('block_type_id')]
    private ?EmailTemplateBlockType $blockType = null;
    private int $displayOrder;
    #[Filter('json_decode')]
    private array $fieldValues;
    private ?int $parentBlockId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getTemplateId(): int
    {
        return $this->templateId;
    }

    public function setTemplateId(int $templateId): void
    {
        $this->templateId = $templateId;
    }

    public function getBlockType(): ?EmailTemplateBlockType
    {
        return $this->blockType;
    }

    public function setBlockType(?EmailTemplateBlockType $blockType): EmailTemplateBlock
    {
        $this->blockType = $blockType;
        return $this;
    }

    public function getDisplayOrder(): int
    {
        return $this->displayOrder;
    }

    public function setDisplayOrder(int $displayOrder): void
    {
        $this->displayOrder = $displayOrder;
    }

    public function getFieldValues(): array
    {
        return $this->fieldValues;
    }

    public function setFieldValues(array $fieldValues): void
    {
        $this->fieldValues = $fieldValues;
    }

    public function getFieldValueByName(string $name): mixed
    {
        return $this->getFieldValues()[$name] ?? null;
    }

    public function getParentBlockId(): ?int
    {
        return $this->parentBlockId;
    }

    public function setParentBlockId(?int $parentBlockId): EmailTemplateBlock
    {
        $this->parentBlockId = $parentBlockId;
        return $this;
    }

    public function render(Environment $twig, array $context): string
    {
        $context = array_merge($context, $this->getFieldValues());
        return $twig->render($twig->createTemplate($this->getBlockType()->getTemplate()), $context);
    }
}
