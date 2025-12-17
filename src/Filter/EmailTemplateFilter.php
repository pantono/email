<?php

namespace Pantono\Email\Filter;

use Pantono\Contracts\Filter\PageableInterface;
use Pantono\Database\Traits\Pageable;

class EmailTemplateFilter implements PageableInterface
{
    use Pageable;

    private ?string $search = null;
    private ?string $category = null;

    public function getSearch(): ?string
    {
        return $this->search;
    }

    public function setSearch(?string $search): EmailTemplateFilter
    {
        $this->search = $search;
        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): EmailTemplateFilter
    {
        $this->category = $category;
        return $this;
    }
}
