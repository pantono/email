<?php

namespace Pantono\Email\Model;

use Pantono\Database\Traits\SavableModel;
use Pantono\Contracts\Attributes\Filter;
use Pantono\Contracts\Attributes\DatabaseTable;
use Pantono\Contracts\Attributes\Database\OneToMany;
use Pantono\Contracts\Attributes\Database\OneToOne;
use Pantono\Contracts\Attributes\FieldName;

#[DatabaseTable('email_template')]
class EmailTemplate
{
    use SavableModel;

    private ?int $id = null;
    private string $name;
    private ?string $description = null;
    private string $category;
    private ?string $subject = null;
    private \DateTimeInterface $dateCreated;
    private \DateTimeInterface $dateUpdated;
    #[Filter('json_decode')]
    private array $requiredContext = [];
    /**
     * @var EmailTemplateBlock[]
     */
    #[OneToMany(targetModel: EmailTemplateBlock::class, mappedBy: 'template_id')]
    private array $blocks = [];
    private bool $deleted = false;
    private ?string $content = null;
    #[Filter('json_decode')]
    private array $meta = [];
    #[OneToOne(targetModel: EmailTemplateType::class), FieldName('type_id')]
    private ?EmailTemplateType $type = null;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
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

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): void
    {
        $this->subject = $subject;
    }

    public function getDateCreated(): \DateTimeInterface
    {
        return $this->dateCreated;
    }

    public function setDateCreated(\DateTimeInterface $dateCreated): void
    {
        $this->dateCreated = $dateCreated;
    }

    public function getDateUpdated(): \DateTimeInterface
    {
        return $this->dateUpdated;
    }

    public function setDateUpdated(\DateTimeInterface $dateUpdated): void
    {
        $this->dateUpdated = $dateUpdated;
    }

    public function getRequiredContext(): array
    {
        return $this->requiredContext;
    }

    public function setRequiredContext(array $requiredContext): void
    {
        $this->requiredContext = $requiredContext;
    }

    public function getBlocks(): array
    {
        return $this->blocks;
    }

    public function setBlocks(array $blocks): void
    {
        $this->blocks = $blocks;
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): void
    {
        $this->deleted = $deleted;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    public function getType(): ?EmailTemplateType
    {
        return $this->type;
    }

    public function setType(?EmailTemplateType $type): void
    {
        $this->type = $type;
    }

    public function getMeta(): array
    {
        return $this->meta;
    }

    public function setMeta(array $meta): void
    {
        $this->meta = $meta;
    }

    public function getMissingContexts(array $contexts): array
    {
        $missing = [];
        foreach ($this->getRequiredContext() as $contextName) {
            if (!isset($contexts[$contextName])) {
                $missing[] = $contextName;
            }
        }
        return $missing;
    }

    public function getBlockByBlockId(int $id): ?EmailTemplateBlock
    {
        foreach ($this->getBlocks() as $block) {
            if ($block->getId() === $id) {
                return $block;
            }
        }
        return null;
    }
}
