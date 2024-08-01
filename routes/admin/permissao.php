<?php

use \App\Http\Response;
use \App\Controller\Admin;

$router->get('/admin/permissao', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::admin'
  ],
  function ($request) {
    return new Response(200, Admin\Permissao::getPermissoes($request));
  }
]);

$router->post('/admin/permissao/novo', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::admin'
  ],
  function ($request) {
    return new Response(200, Admin\Permissao::setNewPermissao($request), 'application/json');
  }
]);

$router->get('/admin/permissao/{id}/edit', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::admin'
  ],
  function ($request, $id) {
    return new Response(200, Admin\Permissao::getEditPermissao($request, $id), 'application/json');
  }
]);

$router->post('/admin/permissao/{id}/edit', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::admin'
  ],
  function ($request, $id) {
    return new Response(200, Admin\Permissao::setEditPermissao($request, $id), 'application/json');
  }
]);
