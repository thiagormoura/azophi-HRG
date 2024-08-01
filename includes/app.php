<?php

require __DIR__ . '/../vendor/autoload.php';

use \App\Utils\View;
use \App\Common\Enviroment;
use \App\Db\Database;
use \App\Db\Smart;
use \App\Db\SmartPainel;
use \App\Http\Middleware\Queue as MiddlewareQueue;

session_set_cookie_params(0, '/', '');

Enviroment::load(__DIR__ . '/../');

define('CURRENT_TIMEZONE', 'America/Fortaleza');
define('SISNOT_EMAIL', getenv('SISNOT_EMAIL'));
define('URL_GESTAO', getenv('URL_GESTAO'));
define('JWT_KEY', getenv('JWT_KEY'));

Database::config(
  getenv('DB_HOST_MYSQL'),
  getenv('DB_USER_MYSQL'),
  getenv('DB_PASS_MYSQL')
);

Smart::config(
  getenv('DB_HOST_SMART'),
  getenv('DB_NAME_SMART'),
  getenv('DB_USER_SMART'),
  getenv('DB_PASS_SMART'),
  getenv('DB_PORT_SMART')
);

SmartPainel::config(
  getenv('DB_HOST_SMART_PAINEL'),
  getenv('DB_NAME_SMART_PAINEL'),
  getenv('DB_USER_SMART_PAINEL'),
  getenv('DB_PASS_SMART_PAINEL'),
  getenv('DB_PORT_SMART_PAINEL')
);

if($_SERVER['SERVER_NAME'] != "cs.hospitalriogrande.com.br" && $_SERVER['SERVER_NAME'] != "localhost")
  define('URL', "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/centralservicos");

else define('URL', getenv('URL'));



View::init([
  'URL' => URL
]);

// Define o mapeamento de middlewares
MiddlewareQueue::setMap([
  'maintenance' => \App\Http\Middleware\Maintenance::class,
  'required-logout' => \App\Http\Middleware\RequireLogout::class,
  'required-login' => \App\Http\Middleware\RequireLogin::class,
  'required-logout-paciente' => \App\Http\Middleware\RequireLogoutPaciente::class,
  'required-login-paciente' => \App\Http\Middleware\RequireLoginPaciente::class,
  'api' => \App\Http\Middleware\Api::class,
  'user-basic-auth' => \App\Http\Middleware\UserBasicAuth::class,
  'jwt-auth' => \App\Http\Middleware\JWTAuth::class,
  'jwt-auth-paciente' => \App\Http\Middleware\JWTAuthPaciente::class,
  'check-permission' => \App\Http\Middleware\Permissao::class,
  'required-notification' => \App\Http\Middleware\Notificacao::class,
  'change-password' =>  \App\Http\Middleware\ChangePassword::class,
]);

// Define o mapeamento de middlewares default (Execute em todas as rotas)
MiddlewareQueue::setDefault([
  'maintenance'
]);
