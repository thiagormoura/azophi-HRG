<?php

use \App\Http\Response;
use \App\Controller\Inutri;

$router->get('/inutri', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::inutri,admin'
  ],
  function ($request) {
    return new Response(200, Inutri\Home::getHome($request));
  }
]);

$router->get('/inutri/configuracoes', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::admin'
  ],
  function ($request) {
    return new Response(200, Inutri\Home::getConfigurationPage($request));
  }
]);

$router->post('/inutri/configuracoes', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::admin'
  ],
  function ($request) {
    return new Response(200, Inutri\Home::setConfigurationPage($request));
  }
]);
