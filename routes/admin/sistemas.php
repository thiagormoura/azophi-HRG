<?php

use \App\Http\Response;
use \App\Controller\Admin;

$router->get('/admin/sistemas', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::admin'
  ],
  function ($request) {
    return new Response(200, Admin\Sistema::getHome($request));
  }
]);

$router->get('/admin/sistema/{id}/edit', [
    'middlewares' => [
      'jwt-auth',
      'check-permission::admin'
    ],
    function ($request, $id) {
      return new Response(200, Admin\Sistema::getEditSistema($request, $id), 'application/json');
    }
]);

$router->post('/admin/sistema/{id}/edit', [
    'middlewares' => [
      'jwt-auth',
      'check-permission::admin'
    ],
    function ($request, $id) {
      return new Response(200, Admin\Sistema::editSistema($request, $id), 'application/json');
    }
]);

$router->post('/admin/sistema/novo', [
    'middlewares' => [
      'jwt-auth',
      'check-permission::admin'
    ],
    function ($request) {
      return new Response(200, Admin\Sistema::createSystem($request), 'application/json');
    }
]);