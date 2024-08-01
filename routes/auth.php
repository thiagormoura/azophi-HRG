<?php

use \App\Http\Response;
use \App\Controller\Auth;

$router->get('/login', [
  'middlewares' => [
    'required-logout',
    'required-logout-paciente'
  ],
  function ($request) {
    return new Response(201, Auth\Auth::getLogin($request));
  }
]);

$router->post('/login', [
  'middlewares' => [
    'required-logout',
    'required-logout-paciente'
  ],
  function ($request) {
    return new Response(200, Auth\Auth::setLogin($request));
  }
]);

$router->get('/login/alterar_senha', [
  'middlewares' => [
    'required-logout',
    'required-logout-paciente',
    'change-password',
  ],
  function ($request) {
    return new Response(200, Auth\Auth::getNewUserPassword($request));
  }
]);

$router->post('/login/alterar_senha', [
  'middlewares' => [
    'required-logout',
    'required-logout-paciente',
    'change-password',
  ],
  function ($request) {
    return new Response(200, Auth\Auth::setNewUserPassword($request));
  }
]);

$router->get('/login/esqueci_senha', [
  'middlewares' => [
    'required-logout',
    'required-logout-paciente',
  ],
  function ($request) {
    return new Response(200, Auth\Auth::getForgotPassword($request));
  }
]);

$router->post('/login/esqueci_senha', [
  'middlewares' => [
    'required-logout',
    'required-logout-paciente',
  ],
  function ($request) {
    return new Response(200, Auth\Auth::setForgotPassword($request));
  }
]);

$router->get('/login/esqueci_senha/novasenha', [
  'middlewares' => [
    'required-logout',
    'required-logout-paciente',
  ],
  function ($request) {
    return new Response(200, Auth\Auth::getChangePassword($request));
  }
]);

$router->post('/login/esqueci_senha/novasenha', [
  'middlewares' => [
    'required-logout',
    'required-logout-paciente',
  ],
  function ($request) {
    return new Response(200, Auth\Auth::setChangePassword($request));
  }
]);

$router->get('/logout', [
  'middlewares' => [
    'jwt-auth',
    'required-logout-paciente'
  ],
  function ($request) {
    return new Response(200, Auth\Auth::setLogout($request));
  }
]);

$router->get('/paciente/login', [
  'middlewares' => [
    'required-logout',
    'required-logout-paciente',
  ],
  function ($request) {
    return new Response(201, Auth\AuthPaciente::getAccess($request));
  }
]);

$router->post('/paciente/login', [
  'middlewares' => [
    'required-logout',
    'required-logout-paciente',
  ],
  function ($request) {
    return new Response(201, Auth\AuthPaciente::validLogin($request));
  }
]);
$router->get('/paciente/logout', [
  'middlewares' => [
    'required-logout',
    'jwt-auth-paciente'
  ],
  function ($request) {
    return new Response(201, Auth\AuthPaciente::setLogout($request));
  }
]);
$router->get('/paciente/auth', [
  'middlewares' => [
    'required-logout',
    'required-logout-paciente',
  ],
  function ($request) {
    return new Response(201, Auth\AuthPaciente::getLogin($request));
  }
]);
$router->post('/paciente/auth', [
  'middlewares' => [
    'required-logout',
    'required-logout-paciente',
  ],
  function ($request) {
    return new Response(201, Auth\AuthPaciente::setLogin($request));
  }
]);
