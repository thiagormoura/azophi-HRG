<?php

use \App\Http\Response;
use \App\Controller\PaFilas;

$router->get('/pafilas', [
    'middlewares' => [
        'jwt-auth',
        'check-permission::pafilas,admin',
    ],
    function ($request) {
        return new Response(200, PaFilas\Home::getHome($request));
    }
]);

$router->get('/pafilas/filas', [
    'middlewares' => [
        'jwt-auth',
        'check-permission::pafilas,admin',
    ],
    function ($request) {
        return new Response(200, PaFilas\Home::getFilas($request));
    }
]);
