<?php

use \App\Http\Response;
use \App\Controller\Papem;

$router->get('/papem', [
    'middlewares' => [
        'jwt-auth',
        'check-permission::admin',
    ],
    function ($request) {
        return new Response(200, Papem\PapemController::getPapem($request));
    }
]);

$router->get('/papem_recep', [
    'middlewares' => [
        'jwt-auth',
        'check-permission::admin',
    ],
    function ($request) {
        return new Response(200, Papem\PapemController::getPapemRecep($request));
    }
]);