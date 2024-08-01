<?php

use \App\Http\Response;
use \App\Controller\Inutri;

$router->get('/inutri/central_itemcardapio', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::inutri-acessar-alimentos,admin'
  ],
  function ($request) {
    return new Response(200, Inutri\ItemCardapio::getItemCardapio($request));
  }
]);

$router->post('/inutri/updateStatusItem/{id}', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::inutri-editar-alimentos,admin'
  ],
  function ($request, $id) {
    return new Response(200, Inutri\ItemCardapio::updateStatus($request, $id), 'application/json');
  }
]);

$router->post('/inutri/updateItemCardapio', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::inutri-editar-alimentos,admin'
  ],
  function ($request) {
    return new Response(200, Inutri\ItemCardapio::updateItemCardapio($request), 'application/json');
  }
]);

$router->get('/inutri/createItemCardapio', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::inutri-editar-alimentos,admin'
  ],
  function ($request) {
    return new Response(200, Inutri\ItemCardapio::getCreateItemCardapio($request));
  }
]);

$router->post('/inutri/createItemCardapio', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::inutri-editar-alimentos,admin'
  ],
  function ($request) {
    return new Response(200, Inutri\ItemCardapio::insertItemCardapio($request));
  }
]);
