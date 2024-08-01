<?php

use \App\Http\Response;
use \App\Controller\GestaoLeitos;

$router->get('/gestaoleitos/painel_leitos', [
    'middlewares' => [
        'jwt-auth',
        'maintenance',
        'check-permission::gleitos-painel-leitos,admin'
    ],
    function ($request) {
        return new Response(200, GestaoLeitos\BedPanelController::getBedPanel($request));
    }
]);