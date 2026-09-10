<?php

declare(strict_types=1);

use Pantono\Database\Migration\Base\BasePantonoMigration;

final class EmailTemplateSubjectMigration extends BasePantonoMigration
{
    public function change(): void
    {
        $this->tablePrefix('email_template')
            ->addColumn('subject', 'string', ['null' => true])
            ->update();
    }
}
