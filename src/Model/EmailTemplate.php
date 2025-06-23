<?php

namespace Pantono\Email\Model;

use Pantono\Database\Traits\SavableModel;
use Pantono\Contracts\Locator\UserInterface;
use Pantono\Contracts\Attributes\Locator;
use Pantono\Email\EmailTemplates;
use Pantono\Contracts\Attributes\FieldName;

class EmailTemplate
{
    use SavableModel;

    private ?int $id = null;
    private string $name;
    private ?string $description = null;
    private string $category;
    private \DateTimeInterface $dateCreated;
    private \DateTimeInterface $dateUpdated;
    private array $requiredContext = [];
    /**
     * @var EmailTemplateBlock[]
     */
    #[Locator(methodName: 'getBlocksForTemplate', className: EmailTemplates::class), FieldName('$this')]
    private array $blocks = [];

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
