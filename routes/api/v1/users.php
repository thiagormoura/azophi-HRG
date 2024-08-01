<?php

use \App\Http\Response;
use \App\Controller\Api;

$router->get('/api/v1/users', [
  'middlewares' => [
    'api',
    'jwt-auth'
  ],
  function ($request) {
    return new Response(200, Api\User::getUsers($request), 'application/json');
  }
]);

// Rota consulta usuário atual
$router->get('/api/v1/users/me', [
  'middlewares' => [
    'api',
    'jwt-auth'
  ],
  function ($request) {
    return new Response(200, Api\User::getCurrentUser($request), 'application/json');
  }
]);

$router->get('/api/v1/users/{id}', [
  'middlewares' => [
    'api',
    'jwt-auth'
  ],
  function ($request, $id) {
    return new Response(200, Api\User::getUser($request, $id), 'application/json');
  }
]);

$router->post('/api/v1/users', [
  'middlewares' => [
    'api',
    'jwt-auth'
  ],
  function ($request) {
    return new Response(201, Api\User::setNewUser($request), 'application/json');
  }
]);

$router->put('/api/v1/users/{id}', [
  'middlewares' => [
    'api',
    'jwt-auth'
  ],
  function ($request, $id) {
    return new Response(200, Api\User::setEditUser($request, $id), 'application/json');
  }
]);

$router->delete('/api/v1/users/{id}', [
  'middlewares' => [
    'api',
    'jwt-auth'
  ],
  function ($request, $id) {
    return new Response(200, Api\User::setDeleteUser($request, $id), 'application/json');
  }
]);
