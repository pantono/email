<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Email extends AbstractMigration
{
    public function change(): void
    {
        $this->table('email_message')
            ->addColumn('from_address', 'string')
            ->addColumn('from_name', 'string')
            ->addColumn('subject', 'string')
            ->addColumn('date_added', 'datetime')
            ->addColumn('text_message', 'text')
            ->addColumn('html_message', 'text')
            ->addIndex('date_added')
            ->create();

        $this->table('email_sends')
            ->addColumn('email_message_id', 'integer')
            ->addColumn('message_id', 'string')
            ->addColumn('to_address', 'string')
            ->addColumn('to_name', 'string', ['null' => true])
            ->addColumn('date_sent', 'datetime')
            ->addColumn('status', 'string')
            ->addColumn('error_message', 'string', ['null' => true])
            ->addColumn('complained', 'boolean')
            ->addColumn('tracking_key', 'string')
            ->create();
        $this->table('email_address')
            ->addColumn('email', 'string')
            ->addColumn('valid', 'boolean')
            ->addColumn('last_checked', 'datetime')
            ->addColumn('invalid_reason', 'string', ['null' => true])
            ->create();
    }
}
