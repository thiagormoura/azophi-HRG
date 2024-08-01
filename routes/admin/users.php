<?php

use \App\Http\Response;
use \App\Controller\Admin;
use \App\Controller\Auth;
use \App\Controller\Layout\Layout;

$router->get('/admin/usuario', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::admin'
  ],
  function ($request) {
    return new Response(200, Admin\Usuario::getUsuarios($request));
  }
]);

$router->post('/admin/usuario/novo', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::admin'
  ],
  function ($request) {
    return new Response(200, Admin\Usuario::setNewUser($request), 'application/json');
  }
]);

$router->get('/admin/usuario/{id}/edit', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::admin'
  ],
  function ($request, $id) {
    return new Response(200, Admin\Usuario::getEditUser($request, $id));
  }
]);

$router->post('/admin/usuario/{id}/edit', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::admin'
  ],
  function ($request, $id) {
    return new Response(200, Admin\Usuario::setEditUser($request, $id), 'application/json');
  }
]);

$router->post('/admin/usuario/{id}/edit/permissao', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::admin'
  ],
  function ($request, $id) {
    return new Response(200, Admin\Usuario::setEditUserPermission($request, $id), 'application/json');
  }
]);
$router->post('/admin/usuario/{id}/updateStatus', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::admin'
  ],
  function ($request, $id) {
    return new Response(200, Admin\Usuario::updateStatus($request, $id), 'application/json');
  }
]);

$router->post('/admin/usuario/{id}/edit/perfil/getPermissao', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::admin'
  ],
  function ($request) {
    return new Response(200, Admin\Usuario::getPermissaoPerfil($request), 'application/json');
  }
]);

$router->post('/admin/usuario/reset', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::admin'
  ],
  function ($request) {
    return new Response(200, Auth\Auth::setForgotPasswordByAdmin($request), 'application/json');
  }
]);

$router->post('/admin/usuario/view/{id}', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::admin'
  ],
  function ($request, $id) {
    return new Response(200, Auth\Auth::getModalViewUser($request, $id), 'application/json');
  }
]);

$router->get('/usuario', [
  'middlewares' => [
    'jwt-auth'
  ],
  function($request){
    return new Response(200, Layout::getUserInfos($request));
  }
]);