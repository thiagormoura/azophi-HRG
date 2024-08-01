<?php

use \App\Http\Response;
use \App\Controller\SosMaqueiro;

$router->get('/sosmaqueiro', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::sosmaqueiro,admin'
  ],
  function ($request) {
    return new Response(200, SosMaqueiro\Home::getSolicitations($request));
  }
]);

$router->get('/sosmaqueiro/dashboard', [
  'middlewares' => [
    'jwt-auth'
  ],
  function ($request) {
    return new Response(200, SosMaqueiro\Home::dashBoard($request));
  }
]);

$router->post('/sosmaqueiro/dashboard', [
  'middlewares' => [
    'jwt-auth'
  ],
  function ($request) {
    return new Response(200, SosMaqueiro\Home::dashBoardInfos($request), 'application/json');
  }
]);

$router->get('/sosmaqueiro/atender/{solicitacao}/{situacao}', [
  'middlewares' => [
    'jwt-auth'
  ],
  function ($request, $solicitacao, $situacao) {
    return new Response(200, SosMaqueiro\Home::getSolicitationInfo($request, $solicitacao, $situacao));
  }
]);


$router->post('/sosmaqueiro/atender/{solicitacao}', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::sosmaqueiro-atender,admin'
  ],
  function ($request, $solicitacao) {
    return new Response(200, SosMaqueiro\Home::updateChamado($request, $solicitacao), 'application/json');
  }
]);

$router->get('/sosmaqueiro/solicitar', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::sosmaqueiro-solicitar,admin'
  ],
  function ($request) {
    return new Response(200, SosMaqueiro\Home::getSolicitationModal($request));
  }
]);

$router->post('/sosmaqueiro/solicitar', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::sosmaqueiro-solicitar,admin'
  ],
  function ($request) {
    return new Response(200, SosMaqueiro\Home::setSolicitation($request), 'application/json');
  }
]);

$router->get('/sosmaqueiros/getAbrirChamadoButton', [
    'middlewares' => [
      'jwt-auth',
      'check-permission::sosmaqueiro-solicitar,admin'
    ],
    function ($request) {
      return new Response(200, SosMaqueiro\Home::getAbrirChamadoButton($request));
    }
]);

$router->post('/sosmaqueiros/reloadTable', [
  'middlewares' => [
    'jwt-auth'
  ],
  function ($request) {
    return new Response(200, SosMaqueiro\Home::getTableSolicitations($request), 'application/json');
  }
]);
