<?php

use \App\Http\Response;
use \App\Controller\SisNot;

$router->get('/sisnot/home', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::sisnot,admin'
  ],
  function ($request) {
    return new Response(200, SisNot\Home::getAdminHome($request));
  }
]);

$router->get('/sisnot/dashboard', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::sisnot-dashboard,admin'
  ],
  function ($request) {
    return new Response(200, SisNot\Dashboard::getHome($request));
  }
]);

$router->post('/sisnot/dashboard', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::sisnot-dashboard,admin'
  ],
  function ($request) {
    return new Response(200, SisNot\Dashboard::getDashboard($request));
  }
]);

$router->post('/sisnot/getDataPoints', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::sisnot-dashboard,admin'
  ],
  function ($request) {
    return new Response(200, SisNot\Dashboard::getDataPoints($request), 'application/json');
  }
]);

$router->get('/sisnot/notificacoes', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::sisnot-notificacoes,admin'
  ],
  function ($request) {
    return new Response(200, SisNot\Home::getNotifications($request));
  }
]);

$router->post('/sisnot/notificacoes', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::sisnot-notificacoes,admin'
  ],
  function ($request) {
    return new Response(200, SisNot\Home::getNotificationsByDate($request));
  }
]);

$router->get('/sisnot/notificacoes/{notificacao}', [
  'middlewares' => [
    'jwt-auth',
    'check-permission::sisnot-notificacoes,admin'
  ],
  function ($request, $notificacao) {
    return new Response(200, SisNot\Notificacao::getFilledForm($request, $notificacao));
  }
]);

$router->get('/sisnot', [
  'middlewares' => [
    'required-logout'
  ],
  function ($request) {
    return new Response(200, SisNot\Home::getHome($request));
  }
]);

$router->post('/sisnot', [
  'middlewares' => [
    'required-logout'
  ],
  function ($request) {
    return new Response(200, SisNot\Home::createNotification($request));
  }
]);

$router->get('/sisnot/notificacao/{notificacao}/{incidente}', [
  'middlewares' => [
    'required-logout',
    'required-notification'
  ],
  function ($request, $notificacao, $incidente) {
    return new Response(200, SisNot\Notificacao::getEmptyForm($request, $notificacao, $incidente));
  }
]);

$router->post('/sisnot/notificacao/{notificacao}/{incidente}', [
  'middlewares' => [
    'required-logout',
    'required-notification'
  ],
  function ($request, $notificacao, $incidente) {
    return new Response(200, SisNot\Notificacao::setForm($request, $notificacao, $incidente), 'application/json');
  }
]);

$router->get('/sisnot/notificacao/{notificacao}', [
  'middlewares' => [
    'required-logout',
    'required-notification'
  ],
  function ($request, $notificacao) {
    return new Response(200, SisNot\Notificacao::getIncidents($request, $notificacao));
  }
]);
