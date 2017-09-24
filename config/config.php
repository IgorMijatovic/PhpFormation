<?php

return [
    'database.host' => 'localhost',
    'database.username' => 'root',
    'database.password' => 'tiramisu',
    'database.name' => 'PhpFormation',
    'views.path' => dirname(__DIR__) . '/views',
    'twig.extensions' => [
      \DI\get(\Framework\Router\RouterTwigExtension::class)
    ],
//    \Framework\Renderer\RendererInterface::class => \DI\object(\Framework\Renderer\TwigRenderer::class)->constructor(\DI\get('config.view_path'))
    \Framework\Renderer\RendererInterface::class => \DI\factory(\Framework\Renderer\TwigRendererFactory::class),
    \Framework\Router::class => \DI\object()
];