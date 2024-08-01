<?php

use \App\Http\Response;
use \App\Controller\Monps;

$router->get('/home/paciente/monps', [
  'middlewares' => [
    'jwt-auth-paciente',
  ],
  function ($request) {
    return new Response(200, Monps\HomePaciente::getModal($request), 'application/json');
  }
]);
