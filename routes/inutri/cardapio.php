<?php

use \App\Http\Response;
use \App\Controller\Inutri;

$router->get('/inutri/cardapios', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::inutri-acessar-calendario-cardapio,admin'  
  ],
  function ($request) {
    return new Response(200, Inutri\Cardapio::getCardapio($request));
  }
]);

$router->get('/inutri/addGroup', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::inutri-editar-calendario-cardapio,admin' 
  ],
  function ($request) {
    return new Response(200, Inutri\Cardapio::getGroup($request));
  }
]);

$router->get('/inutri/addItemCardapio', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::inutri-editar-calendario-cardapio,admin' 
  ],
  function ($request) {
    return new Response(200, Inutri\Cardapio::getItemCardapio($request));
  }
]);

$router->post('/inutri/cardapio_perfil/{idPerfil}', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::inutri-editar-calendario-cardapio,admin' 
  ],
  function ($request, $idPerfil) {
    return new Response(200, Inutri\Cardapio::getCardapios($request, $idPerfil));
  }
]);

$router->post('/inutri/getCheckedCardapiosDays/{id}', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::inutri-editar-calendario-cardapio,admin' 
  ],
  function ($request, $id) {
    return new Response(200, Inutri\Cardapio::getCheckedCardapiosDays($request, $id), 'application/json');
  }
]);

$router->post('/inutri/insertItemCardapio/{id}', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::inutri-editar-calendario-cardapio,admin' 
  ],
  function ($request, $id) {
    return new Response(200, Inutri\Cardapio::insertCardapio($request, $id));
  }
]);

$router->post('/inutri/editItemCardapio/{id}', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::inutri-editar-calendario-cardapio,admin' 
  ],
  function ($request, $id) {
    return new Response(200, Inutri\Cardapio::updateCardapio($request, $id));
  }
]);

$router->post('/inutri/deleteItemCardapio/{id}', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::inutri-editar-calendario-cardapio,admin' 
  ],
  function ($request, $id) {
    return new Response(200, Inutri\Cardapio::deleteItemCardapio($request, $id));
  }
]);


