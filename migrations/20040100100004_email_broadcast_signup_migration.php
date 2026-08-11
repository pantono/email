<?php

declare(strict_types=1);

use Pantono\Database\Migration\Base\BasePantonoMigration;

final class EmailBroadcastSignupMigration extends BasePantonoMigration
{
    public function change(): void
    {
        $this->tablePrefix('email_list')
            ->addColumn('name', 'string')
            ->addColumn('enabled', 'boolean')
            ->addColumn('meta', 'json')
            ->create();

        $this->tablePrefix('email_list_entry')
            ->addLinkedColumn('list_id', $this->addTablePrefix('email_list'), 'id')
            ->addColumn('name', 'string', ['null' => true])
            ->addColumn('email_address', 'string')
            ->addColumn('date_signed_up', 'datetime')
            ->addColumn('signup_method', 'string')
            ->addColumn('unsubscribed', 'boolean')
            ->addColumn('verified', 'boolean')
            ->addColumn('verify_key', 'string')
            ->addColumn('unsubscribe_key', 'string')
            ->addColumn('date_unsubscribed', 'datetime', ['null' => true])
            ->addLinkedColumn('user_id', $this->addTablePrefix('user'), 'id', ['null' => true])
            ->addIndex('email_address')
            ->addIndex(['email_address', 'list_id'], ['unique' => true])
            ->create();
    }
}
