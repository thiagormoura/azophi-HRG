<?php

use \App\Http\Response;
use \App\Controller\Inutri;

$router->get('/inutri/central_usuario', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::inutri-acessar-usuario,admin'
  ],
  function ($request) {
    return new Response(200, Inutri\Usuario::getHome($request));
  }
]);

$router->get('/inutri/modalUsuarioPerfil/{id}', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::inutri-editar-usuario,admin'
  ],
  function ($id) {
    return new Response(200, Inutri\Usuario::modalPerfilUsuario($id));
  }
]);

$router->post('/inutri/modalUsuarioPerfil/{id}', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::inutri-editar-usuario,admin'
  ],
  function ($request, $id) {
    return new Response(200, Inutri\Usuario::setPerfilUsuario($request, $id), 'application/json');
  }
]);

