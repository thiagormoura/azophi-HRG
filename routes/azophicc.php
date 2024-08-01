<?php

use \App\Http\Response;
use \App\Controller\AzophiCC;

$router->get('/azophicc', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::azophicc,admin',
  ],
  function ($request) {
    return new Response(200, AzophiCC\AzophiCC::getHome($request));
  }
]);

$router->post('/azophicc/getCamposAzophiCC', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::azophicc,admin',
  ],
  function ($request) {
    return new Response(200, AzophiCC\AzophiCC::getCamposAzophiCC($request));
  }
]);

$router->post('/azophicc/getDataPoints', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::azophicc,admin',
  ],
  function ($request) {
    return new Response(200, AzophiCC\AzophiCC::getDataPoints($request), 'application/json');
  }
]);

$router->post('/azophicc/getConvenios', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::azophicc,admin',
  ],
  function ($request) {
    return new Response(200, AzophiCC\AzophiCC::getConvenios($request));
  }
]);