<?php

use \App\Http\Response;
use \App\Controller\Inutri;

$router->get('/inutri/central_cardapio', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::inutri-acessar-cardapio,admin'
  ],
  function ($request) {
    return new Response(200, Inutri\CentralCardapio::getCardapio($request));
  }
]);

$router->get('/inutri/createCardapio', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::inutri-editar-cardapio,admin'
  ],
  function ($request) {
    return new Response(200, Inutri\CentralCardapio::getCardapioModal());
  }
]);

$router->post('/inutri/createCardapio', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::inutri-editar-cardapio,admin'
  ],
  function ($request) {
    return new Response(200, Inutri\CentralCardapio::insertCardapio($request), 'application/json');
  }
]);

$router->get('/inutri/updateCardapio/{id}', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::inutri-editar-cardapio,admin'
  ],
  function ($request, $id) {
    return new Response(200, Inutri\CentralCardapio::getCardapioModal($id));
  }
]);

$router->post('/inutri/updateCardapio/{id}', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::inutri-editar-cardapio,admin'
  ],
  function ($request, $id) {
    return new Response(200, Inutri\CentralCardapio::updateCardapio($request, $id), 'application/json');
  }
]);

$router->post('/inutri/updateStatusCardapio/{id}', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::inutri-editar-cardapio,admin'
  ],
  function ($request, $id) {
    return new Response(200, Inutri\CentralCardapio::updateStatus($id), 'application/json');
  }
]);
