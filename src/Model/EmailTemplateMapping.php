<?php

namespace Pantono\Email\Model;

use Pantono\Contracts\Attributes\DatabaseTable;
use Pantono\Contracts\Attributes\Database\OneToOne;
use Pantono\Contracts\Attributes\FieldName;

#[DatabaseTable('email_mapping', 'type_name')]
class EmailTemplateMapping
{
    private string $typeName;
    #[OneToOne(EmailTemplate::class), FieldName('template_id')]
    private EmailTemplate $template;

    public function getId(): string
    {
        return $this->getTypeName();
    }

    public function getTypeName(): string
    {
        return $this->typeName;
    }

    public function setTypeName(string $typeName): void
    {
        $this->typeName = $typeName;
    }

    public function getTemplate(): EmailTemplate
    {
        return $this->template;
    }

    public function setTemplate(EmailTemplate $template): void
    {
        $this->template = $template;
    }
}
