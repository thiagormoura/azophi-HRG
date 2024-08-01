<?php

use \App\Http\Response;
use \App\Controller\Inutri;

$router->get('/inutri/dashboard', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::inutri-acessar-dashboard,admin' 
  ],
  function ($request) {
    return new Response(200, Inutri\Dashboard::getHome($request));
  }
]);

$router->post('/inutri/dashboard', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::inutri-acessar-dashboard,admin' 
  ],
  function ($request) {
    return new Response(200, Inutri\Dashboard::getDashboard($request));
  }
]);

$router->post('/inutri/getDataPoints', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::inutri-acessar-dashboard,admin' 
  ],
  function ($request) {
    return new Response(200, Inutri\Dashboard::getDataPoints($request), 'application/json');
  }
]);