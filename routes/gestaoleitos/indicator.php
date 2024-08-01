<?php

use \App\Http\Response;
use \App\Controller\GestaoLeitos;

$router->get('/gestaoleitos/indicadores', [
    'middlewares' => [
        'jwt-auth',
        'maintenance',
        'check-permission::gleitos-indicadores,admin'
    ],
    function ($request, $id) {
        return new Response(200, GestaoLeitos\IndicatorsController::getIndicators($request));
    }
]);

$router->get('/gestaoleitos/indicadores/get-data-points', [
    'middlewares' => [
        'jwt-auth',
        'maintenance',
        'check-permission::gleitos-indicadores,admin'
    ],
    function ($request, $id) {
        return new Response(200, GestaoLeitos\IndicatorsController::getDataPoints($request), 'application/json');
    }
]);