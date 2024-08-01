<?php

use \App\Http\Response;
use \App\Controller\Monps;

$router->get('/monps', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::monps,admin',
  ],
  function ($request) {
    return new Response(200, Monps\Home::getHome($request));
  }
]);
$router->post('/monps/getModalPaciente', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::monps,admin',
  ],
  function ($request) {
    return new Response(200, Monps\Home::getModalPaciente($request));
  }
]);
$router->get('/monps/getPacienteFilas', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::monps,admin',
  ],
  function ($request) {
    return new Response(200, Monps\Home::getPacientesFilas($request));
  }
]);


