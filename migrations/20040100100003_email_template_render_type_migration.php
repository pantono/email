<?php

declare(strict_types=1);

use Pantono\Database\Migration\Base\BasePantonoMigration;

final class EmailTemplateRenderTypeMigration extends BasePantonoMigration
{
    public function change(): void
    {
        $this->tablePrefix('email_template_type')
            ->addColumn('name', 'string')
            ->addColumn('renderer_class', 'string')
            ->addColumn('enabled', 'boolean', ['default' => 1])
            ->create();

        $this->insertOnCreate($this->addTablePrefix('email_template_type'), [
            ['id' => 1, 'name' => 'Inky Blocks', 'renderer_class' => \Pantono\Email\Renderer\TwigInkyBlocksRenderer::class],
            ['id' => 2, 'name' => 'Inky', 'renderer_class' => \Pantono\Email\Renderer\TwigInkyRenderer::class],
            ['id' => 3, 'name' => 'HTML', 'renderer_class' => \Pantono\Email\Renderer\HtmlRenderer::class]
        ]);

        $this->tablePrefix('email_template')
            ->addLinkedColumn('type_id', $this->addTablePrefix('email_template_type'), 'id', ['default' => 1, 'signed' => false])
            ->addColumn('content', 'text', ['null' => true])
            ->addColumn('meta', 'json', ['null' => true])
            ->update();
    }
}
