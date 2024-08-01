<?php

use \App\Http\Response;
use \App\Controller\Monexm;

$router->get('/monexm', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::monexm,admin',
  ],
  function ($request) {
    return new Response(200, Monexm\Home::getHome($request));
  }
]);

$router->post('/monexm/getExames', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::monexm,admin',
  ],
  function ($request) {
    return new Response(200, Monexm\Home::getTableRows($request));
  }
]);

$router->post('/monexm/getCharts', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::monexm,admin',
  ],
  function ($request) {
    return new Response(200, Monexm\Home::getDataPoints($request), 'application/json');
  }
]);

$router->post('/monexm/getOs/{setor}', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::monexm,admin',
  ],
  function ($request, $setor) {
    return new Response(200, Monexm\Home::getOs($request, $setor));
  }
]);
