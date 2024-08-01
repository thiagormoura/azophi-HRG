<?php

use \App\Http\Response;
use \App\Controller\Espera;

$router->get('/espera', [
    'middlewares' => [
        'jwt-auth',
        'check-permission::admin',
    ],
    function ($request) {
        return new Response(200, Espera\EsperaController::getEspera($request));
    }
]);

$router->get('/espera_ped', [
    'middlewares' => [
        'jwt-auth',
        'check-permission::admin',
    ],
    function ($request) {
        return new Response(200, Espera\EsperaController::getEsperaPed($request));
    }
]);