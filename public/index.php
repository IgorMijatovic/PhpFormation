<?php
use App\Admin\AdminModule;
use App\Blog\BlogModule;
use Framework\Middleware\DispatcherMiddleware;
use Framework\Middleware\MethodMiddleware;
use Framework\Middleware\NotFoundMiddleware;
use Framework\Middleware\RouterMiddleware;
use Framework\Middleware\TrailingSlashMiddleware;
use GuzzleHttp\Psr7\ServerRequest;
use Middlewares\Whoops;

require dirname(__DIR__) . '/vendor/autoload.php';

$modules = [
    AdminModule::class,
    BlogModule::class
];

//$renderer = new \Framework\Renderer\PHPRenderer(dirname(__DIR__) . '/views');
//$renderer = new \Framework\Renderer\TwigRenderer(dirname(__DIR__) . '/views');
//$renderer = $container->get(\Framework\Renderer\RendererInterface::class);

/*$loader = new Twig_Loader_Filesystem(dirname(__DIR__) . '/views');
$twig = new Twig_Environment($loader, []);*/

$app = (new Framework\App(dirname(__DIR__) . '/config/config.php'))
        ->addModule(AdminModule::class)
        ->addModule(BlogModule::class)
        ->pipe(Whoops::class)
        ->pipe(TrailingSlashMiddleware::class)
        ->pipe(MethodMiddleware::class)
        ->pipe(RouterMiddleware::class)
        ->pipe(DispatcherMiddleware::class)
        ->pipe(NotFoundMiddleware::class);

if (php_sapi_name() !== 'cli') {
    $response = $app->run(ServerRequest::fromGlobals());
    \Http\Response\send($response);
}
