<?php

use \App\Http\Response;
use \App\Controller\Chaves;

$router->get('/chaves', [
    'middlewares' => [
        'jwt-auth',
        'maintenance',
        'check-permission::admin',
    ],
    function ($request) {
        return new Response(200, Chaves\ChavesController::getHome($request));
    }
]);

$router->post('/chaves/searchRegistration', [
    'middlewares' => [
        'jwt-auth',
    ],
    function ($request) {
        return new Response(200, Chaves\ChavesController::searchRegistration($request), "application/json");
        // 
    }
]);

$router->post('/chaves/modalLocker', [
    'middlewares' => [
        'jwt-auth',
    ],
    function ($request) {
        return new Response(200, Chaves\ChavesController::getModalLocker($request));
    }
]);

$router->post('/chaves/addFuncionario', [
    'middlewares' => [
        'jwt-auth',
    ],
    function ($request) {
        return new Response(200, Chaves\ChavesController::addFuncionario($request), 'application/json');
    }
]);

$router->post('/chaves/getSetores', [
    'middlewares' => [
        'jwt-auth',
    ],
    function ($request) {
        return new Response(200, Chaves\ChavesController::getAllSetores($request));
    }
]);

$router->post('/chaves/alugarLocker', [
    'middlewares' => [
        'jwt-auth',
    ],
    function ($request) {
        return new Response(200, Chaves\ChavesController::alugarLocker($request), "application/json");
    }
]);

$router->post('/chaves/devolverLocker', [
    'middlewares' => [
        'jwt-auth',
    ],
    function ($request) {
        return new Response(200, Chaves\ChavesController::devolverLocker($request), "application/json");
    }
]);

$router->post('/chaves/getAllLockers', [
    'middlewares' => [
        'jwt-auth',
    ],
    function ($request) {
        return new Response(200, Chaves\ChavesController::getLockersHTML());
    }
]);

$router->post('/chaves/getHistorico', [
    'middlewares' => [
        'jwt-auth',
    ],
    function ($request) {
        return new Response(200, Chaves\ChavesController::getHistorico($request), "application/json");
    }
]);

$router->post('/chaves/getReverseLockers', [
    'middlewares' => [
        'jwt-auth',
    ],
    function ($request, $order) {
        return new Response(200, Chaves\ChavesController::getLockersHTML($request, 1));
    }
]);

$router->post('/chaves/getFuncionariosData', [
    'middlewares' => [
        'jwt-auth',
    ],
    function ($request) {
        return new Response(200, Chaves\ChavesController::getFuncionariosData($request), "application/json");
    }
]);