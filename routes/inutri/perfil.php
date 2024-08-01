<?php

use \App\Http\Response;
use \App\Controller\Inutri;

$router->get('/inutri/central_perfil', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::inutri-acessar-perfil,admin' 
  ],
  function ($request) {
    return new Response(200, Inutri\Perfil::getPerfil($request));
  }
]);

$router->get('/inutri/createPerfil', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::inutri-editar-perfil,admin' 
  ],
  function ($request) {
    return new Response(200, Inutri\Perfil::createPerfil());
  }
]);

$router->post('/inutri/createPerfil', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::inutri-editar-perfil,admin' 
  ],
  function ($request) {
    return new Response(200, Inutri\Perfil::insertPerfil($request), 'application/json');
  }
]);

$router->post('/inutri/updatePerfil', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::inutri-editar-perfil,admin' 
  ],
  function ($request) {
    return new Response(200, Inutri\Perfil::updatePerfil($request), 'application/json');
  }
]);

$router->post('/inutri/updatePerfilCondition/{id}', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::inutri-editar-perfil,admin' 
  ],
  function ($request, $id) {
    return new Response(200, Inutri\Perfil::updateCondition($request, $id), 'application/json');
  }
]);
