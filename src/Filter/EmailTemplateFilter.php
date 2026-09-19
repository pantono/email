<?php

namespace Pantono\Email\Filter;

use Pantono\Contracts\Filter\PageableInterface;
use Pantono\Database\Traits\Pageable;
use Pantono\Database\Filter\SortableFilter;

class EmailTemplateFilter extends SortableFilter implements PageableInterface
{
    use Pageable;

    private ?string $search = null;
    private ?string $category = null;

    public function getSortableFields(): array
    {
        return [
            'id', 'name', 'subject', 'category'
        ];
    }

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
}
