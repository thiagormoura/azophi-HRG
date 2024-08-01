<?php

use \App\Http\Response;
use \App\Controller\Allog\AllogController;

$router->get('/allog', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::admin'
  ], function ($request) {
    return new Response(200, AllogController::getHome($request));
  }
]);

// $router->post('/allog/ajaxReloadTable', [
//     'middlewares' => [
//       'jwt-auth'
//     ],
//     function ($request) {
//       return new Response(200, AllogController::ajaxReloadTable($request), "application/json");
//     }
// ]);

$router->post('/allog/getGraphics', [
    'middlewares' => [
      'jwt-auth'
    ],
    function ($request) {
      return new Response(200, AllogController::getGraphics($request), "application/json");
    }
]);

$router->post('/allog/getUsersBySystem', [
  'middlewares' => [
    'jwt-auth'
  ],
  function ($request) {
    return new Response(200, AllogController::getUsersAccessBySystem($request), "application/json");
  }
]);