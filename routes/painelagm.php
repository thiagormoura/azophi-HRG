<?php

use \App\Http\Response;
use \App\Controller\PainelAGM;

$router->get('/painelagm', [
    'middlewares' => [
        'jwt-auth',
        'check-permission::admin',
    ],
    function ($request) {
        return new Response(200, PainelAGM\PainelAGMController::getHome($request));
    }
]);

$router->post('/painelagm/paginate', [
    'middlewares' => [
        'jwt-auth',
        'check-permission::admin',
    ],
    function ($request) {
        return new Response(200, PainelAGM\PainelAGMController::getNewPage($request), "application/json");
    }
]);