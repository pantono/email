<?php

namespace Pantono\Email\Filter;

use Pantono\Database\Traits\Pageable;
use Pantono\Contracts\Filter\PageableInterface;

class EmailTemplateBlockFilter implements PageableInterface
{
    use Pageable;

    private ?string $search = null;
    private ?string $category = null;
    private ?string $contentSearch = null;

    public function getSearch(): ?string
    {
        return $this->search;
    }

    public function setSearch(?string $search): void
    {
        $this->search = $search;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): void
    {
        $this->category = $category;
    }

    public function getContentSearch(): ?string
    {
        return $this->contentSearch;
    }

    public function setContentSearch(?string $contentSearch): void
    {
        $this->contentSearch = $contentSearch;
    }
}
