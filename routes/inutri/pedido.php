<?php

use \App\Http\Response;
use \App\Controller\Inutri;

$router->get('/inutri/pedidos', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::inutri-acessar-pedidos,inutri-acessar-pedidos-admin,admin'
  ],
  function ($request) {
    return new Response(200, Inutri\Pedido::getPedido($request));
  }
]);

$router->post('/inutri/pedidos/imprimir/{id}', [
  function ($request, $id) {
    return new Response(200, Inutri\Pedido::imprimirPedido($request, $id), 'application/json');
  }
]);

$router->get('/inutri/getPedidos', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::inutri-acessar-pedidos,admin'
  ],
  function ($request) {
    return new Response(200, Inutri\Pedido::getPedidosByUser($request));
  }
]);

$router->get('/inutri/getPedidos/{situation}', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::inutri-acessar-pedidos-admin,admin'
  ],
  function ($request, $situation) {
    return new Response(200, Inutri\Pedido::getPedidosBySituation($situation));
  }
]);

$router->post('/inutri/pedido/update/{pedido}', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::inutri-editar-pedidos,admin'
  ],
  function ($request, $pedido) {
    return new Response(200, Inutri\Pedido::updatePedido($request, $pedido), 'application/json');
  }
]);

$router->post('/inutri/getPedidosByDate/{situation}', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::inutri-acessar-pedidos-admin,admin'
  ],
  function ($request, $situation) {
    return new Response(200, Inutri\Pedido::getPedidosByRange($request, $situation));
  }
]);

$router->get('/inutri/getModalCancel/{pedido}', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::inutri-acessar-pedidos,inutri-acessar-pedidos-admin,admin'
  ],
  function ($request, $pedido) {
    return new Response(200, Inutri\Pedido::getModalCancel($request, $pedido));
  }
]);


$router->post('/inutri/cancelPedido/{pedido}', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::inutri-acessar-pedidos,inutri-acessar-pedidos-admin,admin'
  ],
  function ($request, $pedido) {
    return new Response(200, Inutri\Pedido::cancelPedido($request, $pedido), 'application/json');
  }
]);
