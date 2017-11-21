<?php

use App\Auth\{
    AuthTwigExtension, DatabaseAuth, ForbiddenMiddleware
};
use Framework\Auth;

return [
    'auth.login' => '/login',
    'twig.extensions' =>\DI\add([
        \DI\get(AuthTwigExtension::class)
    ]),
    Auth\User::class => \DI\factory(function (Auth $auth) {
        return $auth->getUser();
    })->parameter('auth', \DI\get(Auth::class)),
    Auth::class => \DI\get(DatabaseAuth::class),
    ForbiddenMiddleware::class => \DI\object()->constructorParameter('loginPath', \DI\get('auth.login'))
];