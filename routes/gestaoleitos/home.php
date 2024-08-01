<?php

use \App\Http\Response;
use \App\Controller\GestaoLeitos;

$router->get('/gestaoleitos', [
    'middlewares' => [
        'jwt-auth',
        'maintenance',
        'check-permission::gleitos,admin'
    ],
    function ($request) {
        return new Response(200, GestaoLeitos\HomeController::getHome($request));
    }
]);

$router->post('/gestaoleitos/getModalDescricao', [
    'middlewares' => [
        'jwt-auth'
    ],
    function ($request) {
        return new Response(200, GestaoLeitos\HomeController::getModalDescricao($request));
    }
]);

$router->post('/gestaoleitos/searchLeitos', [
    'middlewares' => [
        'jwt-auth'
    ],
    function ($request) {
        return new Response(200, GestaoLeitos\HomeController::getSolicitationsByTimeAndFilter($request), "application/json");
    }
]);
