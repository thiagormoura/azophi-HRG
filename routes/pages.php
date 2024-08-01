<?php

use \App\Http\Response;
use \App\Controller\Pages;
use \App\Controller\Ouvidoria;
use \App\Controller\Monps;

$router->get('/', [
  'middlewares' => [
    'required-logout',
    'required-logout-paciente',
  ],
  function ($request) {
    return new Response(200, Pages\Home::getHome($request));
  }
]);

$router->get('/projetos', [
  'middlewares' => [
    'required-logout-paciente',
  ],
  function ($request) {
    return new Response(200, Pages\Home::getProjects($request));
  }
]);

$router->get('/home/funcionario', [
  'middlewares' => [
    'required-logout-paciente',
    'jwt-auth'
  ],
  function ($request) {
    return new Response(200, Pages\CentralFuncionario::getFuncionario($request));
  }
]);

$router->get('/home/paciente', [
  'middlewares' => [
    'required-logout'
    //'jwt-auth-paciente'
  ],
  function ($request) {
    return new Response(200, Pages\CentralPaciente::getPaciente($request));
  }
]);

$router->get('/ouvidoria/getModal', [
  'middlewares' => [
    'required-logout',
    'required-logout-paciente',
  ],
  function ($request) {
    return new Response(200, Ouvidoria\Home::getModal($request));
  }
]);

$router->post('/ouvidoria/getModal', [
  'middlewares' => [
    'required-logout',
    'required-logout-paciente',
  ],
  function ($request) {
    return new Response(200, Ouvidoria\Home::sendMessage($request));
  }
]);

$router->get('/home/paciente/monps', [
  'middlewares' => [
    'required-logout'
    //'jwt-auth-paciente'
  ],
  function ($request) {
    return new Response(200, Monps\HomePaciente::getModal($request));
  }
]);
