<?php
namespace App\Account;

use App\Account\Actions\AccountAction;
use App\Account\Actions\AccountEditAction;
use App\Account\Actions\SignupAction;
use Framework\Auth\LoggedInMiddleware;
use Framework\Module;
use Framework\Renderer\RendererInterface;
use Framework\Router;

class AccountModule extends Module
{
    const MIGRATIONS = __DIR__ . '/db/migrations';

    const DEFINITION = __DIR__ . '/config.php';

    public function __construct(Router $router, RendererInterface $renderer)
    {
        $renderer->addPath('account', __DIR__ . '/views');
        $router->get('/inscription', SignupAction::class, 'account.signup');
        $router->post('/inscription', SignupAction::class);
        $router->get('/mon-profile', [LoggedInMiddleware::class, AccountAction::class], 'account');
        $router->post('/mon-profile', [LoggedInMiddleware::class, AccountEditAction::class]);
    }
}
