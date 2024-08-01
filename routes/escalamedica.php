<?php

use \App\Http\Response;
use \App\Controller\EscalaMedica;

$router->get('/escalamedica', [
    'middlewares' => [
        'jwt-auth',
        'check-permission::admin',
    ],
    function ($request) {
        return new Response(200, EscalaMedica\EscalaMedicaController::getHome($request));
    }
]);

$router->post('/escalamedica/getMedicosPlantaoTable', [
    'middlewares' => [
        'jwt-auth'
    ],
    function ($request) {
        return new Response(200, EscalaMedica\EscalaMedicaController::getMedicosPlantaoTable($request), "application/json");
    }
]);

$router->post('/escalamedica/insertDoctorInDuty', [
    'middlewares' => [
        'jwt-auth'
    ],
    function ($request) {
        return new Response(200, EscalaMedica\EscalaMedicaController::insertDoctorInDuty($request), "application/json");
    }
]);

$router->get('/escalamedica/painel', [
    'middlewares' => [
        'jwt-auth',
    ],
    function ($request) {
        return new Response(200, EscalaMedica\EscalaMedicaController::getPainel($request));
    }
]);