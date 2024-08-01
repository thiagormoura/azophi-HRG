<?php

use \App\Http\Response;
use \App\Controller\Monexm;

$router->get('/monexm', [
  'middlewares' => [
    'jwt-auth'
  ],
  function ($request) {
    return new Response(200, Monexm\Home::getHome($request));
  }
]);

$router->post('/monexm/getExames', [
  'middlewares' => [
    'jwt-auth'
  ],
  function ($request) {
    return new Response(200, Monexm\Home::getTableRows($request));
  }
]);

$router->post('/monexm/getCharts', [
  'middlewares' => [
    'jwt-auth'
  ],
  function ($request) {
    return new Response(200, Monexm\Home::getDataPoints($request), 'application/json');
  }
]);


