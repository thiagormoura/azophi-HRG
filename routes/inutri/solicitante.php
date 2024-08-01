<?php

use \App\Http\Response;
use \App\Controller\Inutri;

$router->get('/inutri/solicitar_refeicao', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::inutri-solicitar-refeicao-proprio,admin'
  ],
  function ($request) {
    return new Response(200, Inutri\Solicitante::getSolicitacao($request));
  }
]);

$router->post('/inutri/solicitar_refeicao', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::inutri-solicitar-refeicao-proprio,admin'
  ],
  function ($request) {
    return new Response(200, Inutri\Solicitante::solicitarRefeicao($request), 'application/json');
  }
]);

$router->get('/inutri/solicitar_refeicao/paciente', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::inutri-solicitar-refeicao-paciente,admin'
  ],
  function ($request) {
    return new Response(200, Inutri\SolicitantePaciente::getSolicitacao($request));
  }
]);

$router->post('/inutri/solicitar_refeicao/getPacientes/unidade/{unidade}', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::inutri-solicitar-refeicao-paciente,admin'
  ],
  function ($request, $unidade) {
    return new Response(200, Inutri\SolicitantePaciente::getPacienteByUnidade($unidade));
  }
]);

$router->post('/inutri/solicitar_refeicao/getPacientes/dieta/{registro}', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::inutri-solicitar-refeicao-paciente,admin'
  ],
  function ($request, $registro) {
    return new Response(200, Inutri\SolicitantePaciente::getDietaPaciente($request, $registro));
  }
]);

$router->post('/inutri/solicitar_refeicao/paciente/{registro}', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::inutri-solicitar-refeicao-paciente,admin'
  ],
  function ($request, $registro) {
    return new Response(200, Inutri\SolicitantePaciente::solicitarPedido($request, $registro), 'application/json');
  }
]);

$router->get('/inutri/solicitar_refeicao/terceiros', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::inutri-solicitar-refeicao-terceiros,admin'
  ],
  function ($request) {
    return new Response(200, Inutri\SolicitanteTerceiros::getSolicitacao($request));
  }
]);

$router->get('/inutri/solicitar_refeicao/getCardapios/perfil/{perfil}', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::inutri-solicitar-refeicao-proprio,admin'
  ],
  function ($request, $perfil) {
    return new Response(200, Inutri\Solicitante::getCardapios($request, $perfil));
  }
]);

$router->post('/inutri/solicitar_refeicao/terceiros/solicitar/{perfil}', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::inutri-solicitar-refeicao-terceiros,admin'
  ],
  function ($request, $perfil) {
    return new Response(200, Inutri\Solicitante::solicitarRefeicao($request, $perfil), 'application/json');
  }
]);

$router->get('/inutri/paciente/', [
  'middlewares' => [
    'jwt-auth-paciente'
  ],
  function ($request) {
    return new Response(200, Inutri\SolicitantePaciente::getPagePaciente($request));
  }
]);

$router->post('/inutri/paciente/{registro}', [
  'middlewares' => [
    'jwt-auth-paciente'
  ],
  function ($request, $registro) {
    return new Response(200, Inutri\SolicitantePaciente::solicitarPedido($request, $registro), 'application/json');
  }
]);

