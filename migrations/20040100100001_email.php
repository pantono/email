<?php

declare(strict_types=1);

use Pantono\Database\Migration\Base\BasePantonoMigration;

final class Email extends BasePantonoMigration
{
    public function change(): void
    {
        $this->tablePrefix('email_disposable_domain', ['id' => false, 'primary_key' => ['domain']])
            ->addColumn('domain', 'string', ['null' => false])
            ->create();

        $this->tablePrefix('email_config', ['id' => false])
            ->addColumn('check_dns', 'boolean')
            ->addColumn('check_smtp', 'boolean')
            ->addColumn('check_disposable_domain', 'boolean')
            ->addColumn('default_from_address', 'string', ['null' => true])
            ->addColumn('default_from_name', 'string', ['null' => true])
            ->addColumn('deferred_send', 'boolean', ['default' => true])
            ->create();

        if ($this->isMigratingUp()) {
            $this->tablePrefix('email_config')
                ->insert([
                    ['check_dns' => 1, 'check_smtp' => 0, 'check_disposable_domain' => 1, 'default_from_name' => 'Pantono', 'default_from_address' => 'noreply@pantono.com']
                ])->saveData();
        }

        $this->tablePrefix('email_address')
            ->addColumn('email', 'string')
            ->addColumn('valid', 'boolean')
            ->addColumn('last_checked', 'datetime')
            ->addColumn('invalid_reason', 'string', ['null' => true])
            ->create();

        $this->tablePrefix('email_message')
            ->addColumn('date_added', 'datetime')
            ->addColumn('from_address', 'string')
            ->addColumn('from_name', 'string')
            ->addColumn('subject', 'string')
            ->addColumn('text_message', 'text')
            ->addColumn('html_message', 'text')
            ->addIndex('date_added')
            ->addIndex('subject')
            ->create();

        $this->tablePrefix('email_status')
            ->addColumn('name', 'string')
            ->addColumn('bounced', 'boolean')
            ->addColumn('complained', 'boolean')
            ->addColumn('sent', 'boolean')
            ->create();

        if ($this->isMigratingUp()) {
            $this->tablePrefix('email_status')
                ->insert([
                    ['name' => 'Pending', 'bounced' => 0, 'complained' => 0, 'sent' => 0],
                    ['name' => 'Sent', 'bounced' => 0, 'complained' => 0, 'sent' => 1],
                    ['name' => 'Delivered', 'bounced' => 0, 'complained' => 0, 'sent' => 1],
                    ['name' => 'Soft Bounce', 'bounced' => 1, 'complained' => 0, 'sent' => 1],
                    ['name' => 'Hard Bounce', 'bounced' => 1, 'complained' => 0, 'sent' => 1],
                    ['name' => 'Complained', 'bounced' => 0, 'complained' => 1, 'sent' => 1],
                    ['name' => 'Error', 'bounced' => 0, 'complained' => 0, 'sent' => 0],
                ])->saveData();
        }

        $this->tablePrefix('email_send')
            ->addLinkedColumn('email_message_id', $this->addTablePrefix('email_message'), 'id')
            ->addColumn('message_id', 'string')
            ->addColumn('date_sent', 'datetime', ['null' => true])
            ->addColumn('to_address', 'string')
            ->addColumn('to_name', 'string', ['null' => true])
            ->addLinkedColumn('status', $this->addTablePrefix('email_status'), 'id')
            ->addColumn('error_message', 'string', ['null' => true])
            ->addColumn('tracking_key', 'string')
            ->addIndex('to_address')
            ->addIndex('message_id')
            ->create();

        $this->tablePrefix('email_send_log')
            ->addLinkedColumn('email_send_id', $this->addTablePrefix('email_send'), 'id')
            ->addColumn('date', 'datetime')
            ->addColumn('entry', 'string')
            ->create();

        $this->tablePrefix('email_template_block_type')
            ->addColumn('name', 'string')
            ->addColumn('description', 'string', ['null' => true])
            ->addColumn('category', 'string')  // For grouping blocks: Layout, Content, Interactive etc.
            ->addColumn('icon', 'string', ['null' => true])  // For UI representation
            ->addColumn('template', 'text')    // Inky markup template
            ->addColumn('system', 'boolean', ['default' => false])
            ->addColumn('allowed_children', 'json', ['null' => true])  // List of block types that can be nested inside
            ->addColumn('max_children', 'integer', ['null' => true])   // Maximum number of child blocks allowed
            ->create();

        $this->tablePrefix('email_template_block_field')
            ->addLinkedColumn('block_type_id', $this->addTablePrefix('email_template_block_type'), 'id')
            ->addColumn('name', 'string')      // e.g., 'content', 'bgcolor', 'align'
            ->addColumn('label', 'string')     // Human readable label
            ->addColumn('type', 'string')      // text, number, color, select, etc.
            ->addColumn('required', 'boolean', ['default' => false])
            ->addColumn('default_value', 'string', ['null' => true])
            ->addColumn('options', 'json', ['null' => true])  // For select/radio fields
            ->addColumn('validation_rules', 'json', ['null' => true])
            ->addColumn('display_order', 'integer', ['default' => 0])
            ->create();

        $this->tablePrefix('email_template')
            ->addColumn('name', 'string')
            ->addColumn('description', 'string', ['null' => true])
            ->addColumn('category', 'string', ['null' => true])
            ->addColumn('date_created', 'datetime')
            ->addColumn('date_updated', 'datetime')
            ->addColumn('required_context', 'json')//Array of required context variables, order/user/product etc
            ->create();

        $this->tablePrefix('email_template_history')
            ->addLinkedColumn('template_id', $this->addTablePrefix('email_template'), 'id')
            ->addColumn('date', 'text')
            ->addLinkedColumn('user_id', $this->addTablePrefix('user'), 'id')
            ->addColumn('entry', 'string')
            ->create();

        $this->tablePrefix('email_template_block_history')
            ->addLinkedColumn('block_type_id', $this->addTablePrefix('email_template_block_type'), 'id')
            ->addColumn('date', 'text')
            ->addLinkedColumn('user_id', $this->addTablePrefix('user'), 'id')
            ->addColumn('entry', 'string')
            ->create();

        $this->tablePrefix('email_template_block')
            ->addLinkedColumn('template_id', $this->addTablePrefix('email_template'), 'id')
            ->addLinkedColumn('block_type_id', $this->addTablePrefix('email_template_block_type'), 'id')
            ->addLinkedColumn('parent_block_id', $this->addTablePrefix('email_template_block'), 'id', ['null' => true])
            ->addColumn('display_order', 'integer')
            ->addColumn('field_values', 'json')  // Stores all field values for this block instance
            ->create();

        if ($this->isMigratingUp()) {
            // Insert some default block types
            $this->tablePrefix('email_template_block_type')
                ->insert([
                    [
                        'name' => 'container',
                        'description' => 'Container',
                        'category' => 'Layout',
                        'template' => '<container>{{children|raw}}</container>',
                        'system' => true,
                        'allowed_children' => json_encode(['columns', 'row']),
                    ],
                    [
                        'name' => 'row',
                        'description' => 'Row of content',
                        'category' => 'Layout',
                        'template' => '<row>{{children|raw}}</row>',
                        'system' => true,
                        'allowed_children' => json_encode(['columns']),
                    ],
                    [
                        'name' => 'columns',
                        'description' => 'Column',
                        'category' => 'Layout',
                        'template' => '<columns large="{{columns}}">{{children|raw}}</columns>',
                        'system' => true,
                        'allowed_children' => json_encode(['*']),  // Allows any block type
                    ],
                    [
                        'name' => 'text',
                        'description' => 'Text content block',
                        'category' => 'Content',
                        'template' => '<p style="color: {{color}}; font-size: {{size}}px; text-align: {{align}};">{{content}}</p>',
                        'system' => true,
                    ],
                    [
                        'name' => 'button',
                        'description' => 'Button block',
                        'category' => 'Interactive',
                        'template' => '<button href="{{url}}" class="{{class}}">{{text}}</button>',
                        'system' => true,
                    ],
                ])
                ->save();
        }

        $this->tablePrefix('email_mapping', ['id' => false])
            ->addColumn('type_name', 'string')
            ->addLinkedColumn('template_id', $this->addTablePrefix('email_template'), 'id')
            ->addIndex('type_name', ['unique' => true])
            ->create();
    }
}
