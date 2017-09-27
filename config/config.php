<?php

return [
    'database.host' => 'localhost',
    'database.username' => 'root',
    'database.password' => 'tiramisu',
    'database.name' => 'PhpFormation',
    'views.path' => dirname(__DIR__) . '/views',
    'twig.extensions' => [
        \DI\get(\Framework\Router\RouterTwigExtension::class),
        \DI\get(\Framework\Twig\PagerFantaExtension::class),
        \DI\get(\Framework\Twig\TextExtension::class),
        \DI\get(\Framework\Twig\TimeExtension::class),
    ],
//    \Framework\Renderer\RendererInterface::class => \DI\object(\Framework\Renderer\TwigRenderer::class)->constructor(\DI\get('config.view_path'))
    \Framework\Renderer\RendererInterface::class => \DI\factory(\Framework\Renderer\TwigRendererFactory::class),
    \Framework\Router::class => \DI\object(),
    \PDO::class => function (\Psr\Container\ContainerInterface $c) {
        return new PDO(
            'mysql:host=' . $c->get('database.host') . ';dbname=' . $c->get('database.name'),
            $c->get('database.username'),
            $c->get('database.password'),
            [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]
        );
    }
];