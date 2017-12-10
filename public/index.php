<?php
use App\Account\AccountModule;
use App\Admin\AdminModule;
use App\Auth\AuthModule;
use App\Auth\ForbiddenMiddleware;
use App\Blog\BlogModule;
use App\Contact\ContactModule;
use Framework\Auth\LoggedInMiddleware;
use Framework\Auth\RoleMiddlewareFactory;
use Framework\Middleware\CsrfMiddleware;
use Framework\Middleware\DispatcherMiddleware;
use Framework\Middleware\MethodMiddleware;
use Framework\Middleware\NotFoundMiddleware;
use Framework\Middleware\RouterMiddleware;
use Framework\Middleware\TrailingSlashMiddleware;
use GuzzleHttp\Psr7\ServerRequest;
use Middlewares\Whoops;

//echo phpinfo();
//die();
//dossier courant est le dossier parant
chdir(dirname(__DIR__));

require 'vendor/autoload.php';

$app = (new Framework\App('config/config.php'))
        ->addModule(AdminModule::class)
        ->addModule(ContactModule::class)
        ->addModule(BlogModule::class)
        ->addModule(AuthModule::class)
        ->addModule(AccountModule::class);
$container = $app->getContainer();
$app->pipe(Whoops::class)
    ->pipe(TrailingSlashMiddleware::class)
    ->pipe(ForbiddenMiddleware::class)
    ->pipe(
        $container->get('admin.prefix'),
        $container->get(RoleMiddlewareFactory::class)->makeForRole('admin')
    )
    ->pipe(MethodMiddleware::class)
    ->pipe(CsrfMiddleware::class)
    ->pipe(RouterMiddleware::class)
    ->pipe(DispatcherMiddleware::class)
    ->pipe(NotFoundMiddleware::class);

if (php_sapi_name() !== 'cli') {
    $response = $app->run(ServerRequest::fromGlobals());
    \Http\Response\send($response);
}
