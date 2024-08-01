<?php

use \App\Http\Response;
use \App\Controller\OuviMed;

$router->get('/ouvimed', [
    'middlewares' => [
        'jwt-auth',
        'maintenance',
        'check-permission::ouvimed,admin'
    ],
    function ($request) {
        return new Response(200, OuviMed\HomeController::getHome($request));
    }
]);

$router->get('/ouvimed/criar', [
    'middlewares' => [
        'jwt-auth',
        'maintenance',
        'check-permission::ouvimed-criar,admin'
    ],
    function ($request) {
        return new Response(200, OuviMed\CreateController::getCreatePage($request));
    }
]);

$router->post('/ouvimed/getNewIdentificacaoElement', [
    'middlewares' => [
        'jwt-auth',
        'maintenance',
        'check-permission::ouvimed-criar,admin'
    ],
    function ($request) {
        return new Response(200, OuviMed\CreateController::getNewIdentificacaoElement($request));
    }
]);

$router->post('/ouvimed/criar', [
    'middlewares' => [
        'jwt-auth',
        'maintenance',
        'check-permission::ouvimed-criar,admin'
    ],
    function ($request) {
        return new Response(200, OuviMed\CreateController::createManifestacao($request), 'application/json');
    }
]);

$router->post('/ouvimed/getManifestacoes', [
    'middlewares' => [
        'jwt-auth',
        'maintenance',
        'check-permission::ouvimed,admin'
    ],
    function ($request) {
        return new Response(200, OuviMed\HomeController::getManifestacoes($request), 'application/json');
    }
]);

$router->get('/ouvimed/manifestacao/{id}', [
    'middlewares' => [
        'jwt-auth',
        'maintenance',
        'check-permission::ouvimed-visualizar,admin'
    ],
    function ($request, $id) {
        return new Response(200, OuviMed\HomeController::getManifestacaoPage($request, $id));
    }
]);

$router->get('/ouvimed/cancelar-manifestacao/{id}', [
    'middlewares' => [
        'jwt-auth',
        'maintenance',
        'check-permission::ouvimed-cancelar,admin'
    ],
    function ($request, $id) {
        return new Response(200, OuviMed\CreateController::cancelarManifestacao($request, $id), 'application/json');
    }
]);

$router->get('/ouvimed/editar/{id}', [
    'middlewares' => [
        'jwt-auth',
        'maintenance',
        'check-permission::ouvimed-editar,admin'
    ],
    function ($request, $id) {
        return new Response(200, OuviMed\CreateController::getEditarManifestacaoPage($request, $id));
    }
]);

$router->post('/ouvimed/editar/{id}', [
    'middlewares' => [
        'jwt-auth',
        'maintenance',
        'check-permission::ouvimed-editar,admin'
    ],
    function ($request, $id) {
        return new Response(200, OuviMed\CreateController::editarManifestacao($request, $id), 'application/json');
    }
]);

$router->post('/ouvimed/processar/{id}', [
    'middlewares' => [
        'jwt-auth',
        'maintenance',
        'check-permission::ouvimed-processar,admin'
    ],
    function ($request, $id) {
        return new Response(200, OuviMed\CreateController::processarManifestacao($request, $id), 'application/json');
    }
]);

$router->post('/ouvimed/cancelarProcessamento/{id}', [
    'middlewares' => [
        'jwt-auth',
        'maintenance',
        'check-permission::ouvimed-cancelarProcessamento,admin'
    ],
    function ($request, $id) {
        return new Response(200, OuviMed\CreateController::cancelarProcessamento($request, $id), 'application/json');
    }
]);

$router->get('/ouvimed/atualizarAcao/{id}', [
    'middlewares' => [
        'jwt-auth',
        'maintenance',
        'check-permission::ouvimed-atualizarAcao,admin'
    ],
    function ($request, $id) {
        return new Response(200, OuviMed\CreateController::getAtualizarAcaoPage($request, $id));
    }
]);

$router->post('/ouvimed/atualizarAcao/{id}', [
    'middlewares' => [
        'jwt-auth',
        'maintenance',
        'check-permission::ouvimed-atualizarAcao,admin'
    ],
    function ($request, $id) {
        return new Response(200, OuviMed\CreateController::AtualizarAcao($request, $id), 'application/json');
    }
]);

$router->post('/ouvimed/finalizarProcessamento/{id}', [
    'middlewares' => [
        'jwt-auth',
        'maintenance',
        'check-permission::ouvimed-finalizarProcessamento,admin'
    ],
    function ($request, $id) {
        return new Response(200, OuviMed\CreateController::finalizarProcessamento($request, $id), 'application/json');
    }
]);

$router->get('/ouvimed/dashboard', [
    'middlewares' => [
        'jwt-auth',
        'maintenance',
        'check-permission::ouvimed-dashboard,admin'
    ],
    function ($request, $id) {
        return new Response(200, OuviMed\DashboardController::getDashboardPage($request));
    }
]);

$router->post('/ouvimed/getDashboardsData', [
    'middlewares' => [
        'jwt-auth',
        'maintenance',
        'check-permission::ouvimed-dashboard,admin'
    ],
    function ($request, $id) {
        return new Response(200, OuviMed\DashboardController::getDashboardsData($request), 'application/json');
    }
]);

$router->post('/ouvimed/getManifestacaoDataPDF/{id}', [
    'middlewares' => [
        'jwt-auth',
        'maintenance',
        'check-permission::ouvimed-visualizar,admin'
    ],
    function ($request, $id) {
        return new Response(200, OuviMed\HomeController::getManifestacaoDataPDF($request, $id), 'application/json');
    }
]);


