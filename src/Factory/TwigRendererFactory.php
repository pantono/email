<?php

namespace Pantono\Email\Factory;

use Pantono\Contracts\Locator\FactoryInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Pantono\Utilities\ApplicationHelper;
use Twig\Extension\DebugExtension;
use Twig\Extra\Inky\InkyExtension;
use Twig\Extra\CssInliner\CssInlinerExtension;
use Twig\Extension\StringLoaderExtension;
use Pantono\Config\Config;

class TwigRendererFactory implements FactoryInterface
{
    private string $path;
    private array $options;
    private Config $config;

    public function __construct(string $path, array $options, Config $config)
    {
        $this->path = $path;
        $this->options = $options;
        $this->config = $config;
    }

    public function createInstance(): Environment
    {
        $paths = [];
        if (file_exists(ApplicationHelper::getApplicationRoot() . '/' . $this->path)) {
            $paths[] = ApplicationHelper::getApplicationRoot() . '/' . $this->path;
        }
        if (file_exists(ApplicationHelper::getApplicationRoot() . '/vendor/pantono/email/views')) {
            $paths[] = ApplicationHelper::getApplicationRoot() . '/vendor/pantono/email/views';
        }
        $loader = new FilesystemLoader($paths);

        $twig = new Environment($loader, $this->options);
        $twig->addGlobal('config', $this->config->getApplicationConfig()->toArray());
        $twig->addExtension(new InkyExtension());
        $twig->addExtension(new CssInlinerExtension());
        $twig->addExtension(new DebugExtension());
        $twig->addExtension(new StringLoaderExtension());
        return $twig;
    }
}
