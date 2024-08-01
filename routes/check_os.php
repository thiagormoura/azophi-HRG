<?php

use \App\Http\Response;
use \App\Controller\Check_OS;

$router->get('/check_os', [
    'middlewares' => [
        'jwt-auth',
        'maintenance',
        'check-permission::admin,checkos',
    ],
    function ($request) {
        return new Response(200, Check_OS\Check_OSController::getHome($request));
    }
]);

$router->post('/check_os/ajaxReloadTable', [
    'middlewares' => [
      'jwt-auth'
    ],
    function ($request) {
      return new Response(200, Check_OS\Check_OSController::ajaxReloadTable($request), "application/json");
    }
]);

$router->post('/check_os/getOSModal', [
    'middlewares' => [
        'jwt-auth',
        'check-permission::admin,checkos',
    ],
    function ($request) {
        return new Response(200, Check_OS\Check_OSController::getOSModal($request));
    }
]);

$router->post('/check_os/verifyOS', [
    'middlewares' => [
        'jwt-auth',
        'check-permission::admin,checkos',
    ],
    function ($request) {
        return new Response(200, Check_OS\Check_OSController::verifyOS($request), "application/json");
    }
]);

$router->get('/check_os/getLegend', [
    'middlewares' => [
        'jwt-auth'
    ],
    function ($request) {
        return new Response(200, Check_OS\Check_OSController::getLegend($request));
    }
]);

$router->get('/check_os/getOSModalExame', [
    'middlewares' => [
        'jwt-auth'
    ],
    function ($request) {
        return new Response(200, Check_OS\Check_OSController::getOSModalExame($request));
    }
]);

$router->post('/check_os/submitObsExame', [
    'middlewares' => [
        'jwt-auth',
        'check-permission::admin,checkos',
    ],
    function ($request) {
        return new Response(200, Check_OS\Check_OSController::submitObsExame($request), "application/json");
    }
]);