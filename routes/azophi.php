<?php

use \App\Http\Response;
use \App\Controller\Azophi;

$router->get('/azophi', [
    'middlewares' => [
        'jwt-auth',
        'check-permission::azophi,admin',
    ],
    function ($request) {
        return new Response(200, Azophi\Home::getHome($request));
    }
]);

$router->get('/azophi/getDataPoints', [
    'middlewares' => [
        'jwt-auth',
        'check-permission::azophi,admin',
    ],
    function ($request) {
        return new Response(200, Azophi\Home::getDataPoints($request), 'application/json');
    }
]);

$router->post('/azophi/searchConvenios', [
    'middlewares' => [
        'jwt-auth',
        'check-permission::azophi,admin',
    ],
    function ($request) {
        return new Response(200, Azophi\Home::getDataPoints($request, true), 'application/json');
    }
]);

$router->get('/azophi/setor/{setor}', [
    'middlewares' => [
        'jwt-auth',
        'check-permission::azophi,admin',
    ],
    function ($request, $setor) {
        return new Response(200, Azophi\Home::getModalSector($request, $setor));
    }
]);

$router->post('/azophi/reloadPage', [
    'middlewares' => [
        'jwt-auth',
        'check-permission::azophi,admin',
    ],
    function ($request) {
        return new Response(200, Azophi\Home::reloadPage($request), 'application/json');
    }
]);