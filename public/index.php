<?php
require '../vendor/autoload.php';

//$renderer = new \Framework\Renderer\PHPRenderer(dirname(__DIR__) . '/views');
$renderer = new \Framework\Renderer\TwigRenderer(dirname(__DIR__) . '/views');

/*$loader = new Twig_Loader_Filesystem(dirname(__DIR__) . '/views');
$twig = new Twig_Environment($loader, []);*/

$app = new Framework\App([
    \App\Blog\BlogModule::class
], [
    'renderer' => $renderer
]);

$response = $app->run(\GuzzleHttp\Psr7\ServerRequest::fromGlobals());

\Http\Response\send($response);
