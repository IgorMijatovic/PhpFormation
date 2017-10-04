<?php
require dirname(__DIR__) . '/vendor/autoload.php';

$modules = [
    \App\Admin\AdminModule::class,
    \App\Blog\BlogModule::class
];

$builder = new \DI\ContainerBuilder();
$builder->addDefinitions(dirname(__DIR__) . '/config/config.php');
foreach ($modules as $module) {
    if ($module::DEFINITION) {
        $builder->addDefinitions($module::DEFINITION);
    }
}
$builder->addDefinitions(dirname(__DIR__) . '/config.php');

$container = $builder->build();

//$renderer = new \Framework\Renderer\PHPRenderer(dirname(__DIR__) . '/views');
//$renderer = new \Framework\Renderer\TwigRenderer(dirname(__DIR__) . '/views');
//$renderer = $container->get(\Framework\Renderer\RendererInterface::class);

/*$loader = new Twig_Loader_Filesystem(dirname(__DIR__) . '/views');
$twig = new Twig_Environment($loader, []);*/

$app = new Framework\App($container, $modules);

if (php_sapi_name() !== 'cli') {
    $response = $app->run(\GuzzleHttp\Psr7\ServerRequest::fromGlobals());
    \Http\Response\send($response);
}
