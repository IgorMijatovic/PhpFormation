<?php

use App\Auth\AuthTwigExtension;
use App\Auth\DatabaseAuth;
use App\Auth\ForbiddenMiddleware;
use App\Auth\UserTable;
use Framework\Auth;

return [
    'auth.login' => '/login',
    'auth.entity' => \App\Auth\User::class,
    'twig.extensions' =>\DI\add([
        \DI\get(AuthTwigExtension::class)
    ]),
    Auth\User::class => \DI\factory(function (Auth $auth) {
        return $auth->getUser();
    })->parameter('auth', \DI\get(Auth::class)),
    Auth::class => \DI\get(DatabaseAuth::class),
    UserTable::class => \DI\object()->constructorParameter('entity', \DI\get('auth.entity')),
    ForbiddenMiddleware::class => \DI\object()->constructorParameter('loginPath', \DI\get('auth.login'))
];
