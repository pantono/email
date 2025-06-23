<?php

namespace Pantono\Email\Tests;

use Pantono\Email\EmailTemplates;
use PHPUnit\Framework\TestCase;
use Pantono\Email\Repository\EmailTemplatesRepository;
use Pantono\Hydrator\Hydrator;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Twig\Environment;
use PHPUnit\Framework\MockObject\MockObject;
use Pantono\Email\Model\EmailTemplate;
use Pantono\Email\Model\EmailTemplateBlock;
use Pantono\Email\Model\EmailTemplateBlockType;
use Twig\Loader\LoaderInterface;
use Twig\Loader\FilesystemLoader;
use Pantono\Utilities\ApplicationHelper;
use Pantono\Email\Factory\TwigRendererFactory;

class EmailTemplatesTest extends TestCase
{
    private EmailTemplatesRepository|MockObject $repository;
    private Hydrator|MockObject $hydrator;
    private EventDispatcher|MockObject $dispatcher;
    private Environment|MockObject $twig;

    public function setUp(): void
    {
        $this->repository = $this->getMockBuilder(EmailTemplatesRepository::class)->disableOriginalConstructor()->getMock();
        $this->hydrator = $this->getMockBuilder(Hydrator::class)->disableOriginalConstructor()->getMock();
        $this->dispatcher = $this->getMockBuilder(EventDispatcher::class)->disableOriginalConstructor()->getMock();
        if (!defined('APPLICATION_PATH')) {
            define('APPLICATION_PATH', __DIR__ . '/../../');
        }
        $factory = new TwigRendererFactory('views', []);
        $this->twig = $factory->createInstance();
    }

    public function testRenderSingleBlockTemplate(): void
    {
        $template = $this->createSingleBlockTemplate('<p>Test</p>');

        $output = $this->createClass()->renderTemplate($template);
        $this->assertStringContainsString('>Test</p>', $output);
    }

    public function testRenderSingleBlockWithVariable(): void
    {
        $template = $this->createSingleBlockTemplate('<p>Test: {{ test }}</p>');
        $output = $this->createClass()->renderTemplate($template, ['test' => 'Output']);
        $this->assertStringContainsString('>Test: Output</p>', $output);
    }

    public function testSimpleInkyTemplate(): void
    {
        $template = $this->createSingleBlockTemplate('<row><columns large="1"><p>{{test_variable}}</p></columns></row>');
        $output = $this->createClass()->renderTemplate($template, ['test_variable' => 'SOME CONTENT HERE']);
        // Test the structural elements are present
        $this->assertStringContainsString('<table', $output);
        $this->assertStringContainsString('<tr', $output);
        $this->assertStringContainsString('<th', $output);
        $this->assertStringContainsString('<p', $output);

        // Test the content is preserved
        $this->assertStringContainsString('SOME CONTENT HERE', $output);
    }

    private function createSingleBlockTemplate(string $blockContent): EmailTemplate
    {
        $template = new EmailTemplate();
        $block = new EmailTemplateBlock();
        $type = new EmailTemplateBlockType();
        $type->setTemplate($blockContent);
        $block->setBlockType($type);
        $template->setBlocks([$block]);
        return $template;
    }


    private function createClass(): EmailTemplates
    {
        return new EmailTemplates($this->repository, $this->hydrator, $this->dispatcher, $this->twig);
    }
}
