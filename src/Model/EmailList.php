<?php

namespace Pantono\Email\Model;

use Pantono\Contracts\Application\Interfaces\SavableInterface;
use Pantono\Database\Traits\SavableModel;
use Pantono\Contracts\Attributes\DatabaseTable;
use Pantono\Contracts\Attributes\Filter;

#[DatabaseTable('email_list')]
class EmailList implements SavableInterface
{
    use SavableModel;

    private ?int $id = null;
    private string $name;
    private bool $enabled;
    /**
     * @var array<mixed>
     */
    #[Filter('json_decode')]
    private array $meta = [];

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

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    /**
     * @return array<mixed>
     */
    public function getMeta(): array
    {
        return $this->meta;
    }

    public function setMeta(array $meta): void
    {
        $this->meta = $meta;
    }
}
