<?php

use \App\Http\Response;
use \App\Controller\Admin;

$router->get('/admin/perfil', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::admin'
  ],
  function ($request) {
    return new Response(200, Admin\Perfil::getPerfis($request));
  }
]);
$router->get('/admin/perfil/novo', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::admin'
  ],
  function ($request) {
    return new Response(200, Admin\Perfil::getEditPerfil($request));
  }
]);
$router->post('/admin/perfil/novo', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::admin'
  ],
  function ($request) {
    return new Response(200, Admin\Perfil::setEditPerfil($request), 'application/json');
  }
]);
$router->get('/admin/perfil/{id}/edit', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::admin'
  ],
  function ($request, $id) {
    return new Response(200, Admin\Perfil::getEditPerfil($request, $id));
  }
]);
$router->post('/admin/perfil/{id}/edit', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::admin'
  ],
  function ($request, $id) {
    return new Response(200, Admin\Perfil::setEditPerfil($request, $id), 'application/json');
  }
]);
$router->post('/admin/perfil/{id}/updateStatus', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::admin'
  ],
  function ($request, $id) {
    return new Response(200, Admin\Perfil::updateStatus($request, $id), 'application/json');
  }
]);
