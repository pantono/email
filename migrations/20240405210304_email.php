<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Email extends AbstractMigration
{
    public function change(): void
    {
        $this->table('email_disposable_domain', ['id' => false, 'primary_key' => ['domain']])
            ->addColumn('domain', 'string', ['null' => false])
            ->create();

        $this->table('email_config', ['id' => false])
            ->addColumn('check_dns', 'boolean')
            ->addColumn('check_smtp', 'boolean')
            ->addColumn('check_disposable_domain', 'boolean')
            ->create();

        if ($this->isMigratingUp()) {
            $this->table('email_config')
                ->insert([
                    ['check_dns' => 1, 'check_smtp' => 0, 'check_disposable_domain' => 1]
                ])->saveData();
        }

        $this->table('email_address')
            ->addColumn('email', 'string')
            ->addColumn('valid', 'boolean')
            ->addColumn('last_checked', 'datetime')
            ->addColumn('invalid_reason', 'string', ['null' => true])
            ->create();

        $this->table('email_message')
            ->addColumn('date_added', 'datetime')
            ->addColumn('from_address', 'string')
            ->addColumn('from_name', 'string')
            ->addColumn('subject', 'string')
            ->addColumn('text_message', 'text')
            ->addColumn('html_message', 'text')
            ->addIndex('date_added')
            ->addIndex('subject')
            ->create();

        $this->table('email_status')
            ->addColumn('name', 'string')
            ->addColumn('bounced', 'boolean')
            ->addColumn('complained', 'boolean')
            ->addColumn('sent', 'boolean')
            ->create();

        if ($this->isMigratingUp()) {
            $this->table('email_status')
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

        $this->table('email_send')
            ->addColumn('email_message_id', 'integer', ['signed' => false])
            ->addColumn('message_id', 'string')
            ->addColumn('date_sent', 'datetime', ['null' => true])
            ->addColumn('to_address', 'string')
            ->addColumn('to_name', 'string', ['null' => true])
            ->addColumn('status', 'integer', ['signed' => false])
            ->addColumn('error_message', 'string', ['null' => true])
            ->addColumn('tracking_key', 'string')
            ->addForeignKey('status', 'email_status', 'id')
            ->addForeignKey('email_message_id', 'email_message', 'id')
            ->create();

        $this->table('email_send_log')
            ->addColumn('email_send_id', 'integer', ['signed' => false])
            ->addColumn('date', 'datetime')
            ->addColumn('entry', 'string')
            ->addForeignKey('email_send_id', 'email_send', 'id')
            ->create();

        $this->table('email_template_block_type')
            ->addColumn('name', 'string')
            ->addColumn('description', 'string', ['null' => true])
            ->addColumn('category', 'string')  // For grouping blocks: Layout, Content, Interactive etc.
            ->addColumn('icon', 'string', ['null' => true])  // For UI representation
            ->addColumn('template', 'text')    // Inky markup template
            ->addColumn('system', 'boolean', ['default' => false])
            ->addColumn('allowed_children', 'json', ['null' => true])  // List of block types that can be nested inside
            ->addColumn('max_children', 'integer', ['null' => true])   // Maximum number of child blocks allowed
            ->create();

        $this->table('email_template_block_field')
            ->addColumn('block_type_id', 'integer', ['signed' => false])
            ->addColumn('name', 'string')      // e.g., 'content', 'bgcolor', 'align'
            ->addColumn('label', 'string')     // Human readable label
            ->addColumn('type', 'string')      // text, number, color, select, etc.
            ->addColumn('required', 'boolean', ['default' => false])
            ->addColumn('default_value', 'string', ['null' => true])
            ->addColumn('options', 'json', ['null' => true])  // For select/radio fields
            ->addColumn('validation_rules', 'json', ['null' => true])
            ->addColumn('display_order', 'integer', ['default' => 0])
            ->addForeignKey('block_type_id', 'email_template_block_type', 'id')
            ->create();

        $this->table('email_template')
            ->addColumn('name', 'string')
            ->addColumn('description', 'string', ['null' => true])
            ->addColumn('category', 'string', ['null' => true])
            ->addColumn('date_created', 'datetime')
            ->addColumn('date_updated', 'datetime')
            ->addColumn('required_context', 'json')//Array of required context variables, order/user/product etc
            ->create();

        $this->table('email_template_history')
            ->addColumn('template_id', 'integer', ['signed' => false])
            ->addColumn('date', 'text')
            ->addColumn('user_id', 'integer', ['signed' => false])
            ->addColumn('entry', 'string')
            ->addForeignKey('template_id', 'email_template', 'id')
            ->addForeignKey('user_id', 'user', 'id')
            ->create();

        $this->table('email_template_block_history')
            ->addColumn('block_type_id', 'integer', ['signed' => false])
            ->addColumn('date', 'text')
            ->addColumn('user_id', 'integer', ['signed' => false])
            ->addColumn('entry', 'string')
            ->addForeignKey('block_type_id', 'email_template_block_type', 'id')
            ->addForeignKey('user_id', 'user', 'id')
            ->create();

        $this->table('email_template_block')
            ->addColumn('template_id', 'integer', ['signed' => false])
            ->addColumn('block_type_id', 'integer', ['signed' => false])
            ->addColumn('parent_block_id', 'integer', ['null' => true, 'signed' => false])
            ->addColumn('display_order', 'integer')
            ->addColumn('field_values', 'json')  // Stores all field values for this block instance
            ->addForeignKey('template_id', 'email_template', 'id')
            ->addForeignKey('block_type_id', 'email_template_block_type', 'id')
            ->addForeignKey('parent_block_id', 'email_template_block', 'id')
            ->create();

        if ($this->isMigratingUp()) {
            // Insert some default block types
            $this->table('email_template_block_type')
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

        $this->table('email_mapping', ['id' => false])
            ->addColumn('type_name', 'string')
            ->addColumn('template_id', 'integer', ['signed' => false])
            ->addForeignKey('template_id', 'email_template', 'id')
            ->addIndex('type_name', ['unique' => true])
            ->create();
    }
}
