<?php

declare(strict_types=1);

use Pantono\Database\Migration\Base\BasePantonoMigration;

final class EmailTemplateDeletedMigration extends BasePantonoMigration
{
    public function change(): void
    {
        $this->tablePrefix('email_template')
            ->addColumn('deleted', 'boolean', ['default' => false])
            ->update();

        $this->tablePrefix('email_template_block_type')
            ->addColumn('deleted', 'boolean', ['default' => false])
            ->update();
    }
}
