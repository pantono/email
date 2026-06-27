<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class EmailTemplateDeletedMigration extends AbstractMigration
{
    public function change(): void
    {
        $this->table('email_template')
            ->addColumn('deleted', 'boolean', ['default' => false])
            ->update();

        $this->table('email_template_block_type')
            ->addColumn('deleted', 'boolean', ['default' => false])
            ->update();
    }
}
