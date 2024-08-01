<?php

use \App\Http\Response;
use \App\Controller\Avasis;

$router->get('/avasis/avaliar', [
  'middlewares' => [
    'required-logout-paciente'
  ],
  function ($request) {
    return new Response(200, Avasis\PacienteController::getHome($request));
  }
]);

$router->get('/avasis/puxarQuestionario/{id}', [
  'middlewares' => [
    'required-logout-paciente'
  ],
  function ($request, $id) {
    return new Response(200, Avasis\PacienteController::getQuestionario($request, $id), 'application/json');
  }
]);

$router->post('/avasis/enviarQuestionario', [
  'middlewares' => [
    'required-logout-paciente'
  ],
  function ($request) {
    return new Response(200, Avasis\PacienteController::enviarQuestionario($request), 'application/json');
  }
]);

$router->post('/avasis/getModalEnviar', [
  function ($request) {
    return new Response(200, Avasis\PacienteController::getModalEnviar($request));
  }
]);