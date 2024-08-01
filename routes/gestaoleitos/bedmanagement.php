<?php

use \App\Http\Response;
use \App\Controller\GestaoLeitos;


$router->get('/gestaoleitos/leitos', [
    'middlewares' => [
        'jwt-auth',
        'maintenance',
        'check-permission::gleitos-gerenciar-leitos,admin'
    ],
    function ($request) {
        return new Response(200, GestaoLeitos\BedManagementController::getBedManagement($request));
    }
]);

$router->get('/gestaoleitos/leitos/unidade/{unidade}', [
    'middlewares' => [
        'jwt-auth',
        'maintenance'
    ],
    function ($request, $unidade) {
        return new Response(200, GestaoLeitos\BedManagementController::getBeds($request, $unidade));
    }
]);

$router->get('/gestaoleitos/leitos/{leito}', [
    'middlewares' => [
        'jwt-auth',
        'maintenance'
    ],
    function ($request, $leito) {
        return new Response(200, GestaoLeitos\BedManagementController::getBedModal($request, $leito));
    }
]);


$router->post('/gestaoleitos/leitos/{leito}', [
    'middlewares' => [
        'jwt-auth'
    ],
    function ($request, $leito) {
        return new Response(200, GestaoLeitos\BedManagementController::setBlockBed($request, $leito), "application/json");
    }
]);

$router->post('/gestaoleitos/edition', [
    'middlewares' => [
        'jwt-auth'
    ],
    function ($request) {
        return new Response(200, GestaoLeitos\SolicitationController::editSolicitation($request), 'application/json');
    }
]);
