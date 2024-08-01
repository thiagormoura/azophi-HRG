<?php

use \App\Http\Response;
use \App\Controller\GestaoLeitos;

$router->get('/gestaoleitos/solicitacao', [
    'middlewares' => [
        'jwt-auth',
        'maintenance',
        'check-permission::gleitos-solicitar,admin'
    ],
    function ($request) {
        return new Response(200, GestaoLeitos\SolicitationController::getSolicitation($request));
    }
]);
$router->get('/gestaoleitos/solicitacao/paciente/{paciente}', [
    'middlewares' => [
        'jwt-auth',
        'maintenance'
    ],
    function ($request, $paciente) {
        return new Response(200, GestaoLeitos\SolicitationController::getPatientFormData($paciente));
    }
]);

$router->post('/gestaoleitos/solicitacao/{id}/{unidade}/leitos', [
    'middlewares' => [
        'jwt-auth'
    ],
    function ($request, $id, $unidade) {
        return new Response(200, GestaoLeitos\SolicitationController::getHospitalBeds($request, $unidade), 'application/json');
    }
]);

$router->get('/gestaoleitos/solicitacao/{id}', [
    'middlewares' => [
        'jwt-auth',
        'maintenance',
        'check-permission::gleitos,admin'
    ],
    function ($request, $id) {
        return new Response(200, GestaoLeitos\SolicitationController::getSolicitation($request, $id));
    }
]);

$router->get('/gestaoleitos/editarSolicitacao/{id}', [
    'middlewares' => [
        'jwt-auth',
        'maintenance',
        'check-permission::gleitos-editar-solicitacao,admin'
    ],
    function ($request, $id) {
        return new Response(200, GestaoLeitos\SolicitationController::getSolicitation($request, $id, true));
    }
]);

$router->post('/gestaoleitos/getAllSectorsWithoutDifference', [
    'middlewares' => [
        'jwt-auth'
    ],
    function ($request) {
        return new Response(200, GestaoLeitos\SolicitationController::getAllSectorsAndBedsWithoutDifference($request), 'application/json');
    }
]);

$router->post('/gestaoleitos/getAllBedsWithoutDifference', [
    'middlewares' => [
        'jwt-auth'
    ],
    function ($request) {
        return new Response(200, GestaoLeitos\SolicitationController::getAllSectorsAndBedsWithoutDifference($request), 'application/json');
    }
]);


$router->post('/gestaoleitos/criarSolicitacao', [
    'middlewares' => [
        'jwt-auth'
    ],
    function ($request) {
        return new Response(200, GestaoLeitos\SolicitationController::createSolicitation($request), "application/json");
    }
]);

$router->post('/gestaoleitos/preparateBed', [
    'middlewares' => [
        'jwt-auth'
    ],
    function ($request) {
        return new Response(200, GestaoLeitos\SolicitationController::preparateBed($request), "application/json");
    }
]);

$router->post('/gestaoleitos/cancelSolicitation', [
    'middlewares' => [
        'jwt-auth'
    ],
    function ($request) {
        return new Response(200, GestaoLeitos\SolicitationController::cancelSolicitation($request), "application/json");
    }
]);

$router->post('/gestaoleitos/vericarReserva', [
    'middlewares' => [
        'jwt-auth'
    ],
    function ($request) {
        return new Response(200, GestaoLeitos\SolicitationController::verifyReservationToChange($request), "application/json");
    }
]);

$router->post('/gestaoleitos/getSectorAndBedLiberate', [
    'middlewares' => [
        'jwt-auth'
    ],
    function ($request) {
        return new Response(200, GestaoLeitos\SolicitationController::getSectorAndBedLiberate($request), "application/json");
    }
]);

$router->post('/gestaoleitos/confirmChangeReserve', [
    'middlewares' => [
        'jwt-auth'
    ],
    function ($request) {
        return new Response(200, GestaoLeitos\SolicitationController::confirmChangeReserve($request), "application/json");
    }
]);

$router->post('/gestaoleitos/finishReserve', [
    'middlewares' => [
        'jwt-auth'
    ],
    function ($request) {
        return new Response(200, GestaoLeitos\SolicitationController::finishReserve($request), "application/json");
    }
]);

$router->post('/gestaoleitos/cancelPreparation', [
    'middlewares' => [
        'jwt-auth'
    ],
    function ($request) {
        return new Response(200, GestaoLeitos\SolicitationController::cancelPreparation($request), "application/json");
    }
]);

$router->post('/gestaoleitos/getHistoricoModal', [
    'middlewares' => [
        'jwt-auth'
    ],
    function ($request) {
        return new Response(200, GestaoLeitos\SolicitationController::getHistoricoModal($request));
    }
]);


$router->post('/gestaoleitos/getAdequateBedsBySector', [
    'middlewares' => [
        'jwt-auth'
    ],
    function ($request) {
        return new Response(200, GestaoLeitos\CommonsController::getAdequateBedsBySector($request), 'application/json');
    }
]);


$router->post('/gestaoleitos/getAdequateSectors', [
    'middlewares' => [
        'jwt-auth'
    ],
    function ($request) {
        return new Response(200, GestaoLeitos\CommonsController::getAdequateSectors($request), 'application/json');
    }
]);



$router->post('/gestaoleitos/checkPermission', [
    'middlewares' => [
        'jwt-auth'
    ],
    function ($request) {
        return new Response(200, GestaoLeitos\CommonsController::checkPermissaoByAjax($request));
    }
]);
