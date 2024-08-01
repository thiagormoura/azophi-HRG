<?php

namespace App\Session\User;
use App\Model\Utils\Project;

class Auth
{

  // Método responsável por iniciar a sessão
  private static function init()
  {
    if (session_status() != PHP_SESSION_ACTIVE)
      session_start();
  }

  // Método responsável por criar o login do usuário
  public static function login($token)
  {
    self::init();
    $_SESSION['usuario']['token'] = $token;
    $_SESSION['ailton'] = Project::getProjects();
    return true;
  }
  // Método responsável por verificar se o usuário está logado
  public static function isLogged()
  {
    self::init();
    return $_SESSION['usuario']['token'];
  }
  // Método responsável por executar o logout do usuário;
  public static function logout()
  {
    self::init();
    unset($_SESSION['usuario']);
    self::dropSessionGestao(); // temporario
    return true;
  }
  public static function tempLogin($user)
  {
    self::init();
    $_SESSION['temp']['usuario'] = $user;
  }
  public static function getTempLogin()
  {
    self::init();
    return isset($_SESSION['temp']['usuario']) ? $_SESSION['temp']['usuario'] : false;
  }
  public static function dropTempLogin()
  {
    self::init();
    unset($_SESSION['temp']);
  }
  public static function sessionGestao($user = null, $permissao = null)
  {
    self::init();
    unset($user->senha);
    $_SESSION['gestaodeleitos']['user'] = (array) $user;
    $_SESSION['gestaodeleitos']['user']['perms'] = (array) $permissao;
    return isset($_SESSION['user']) ? $_SESSION['user'] : false;
  }
  public static function dropSessionGestao()
  {
    self::init();
    unset($_SESSION['gestaodeleitos']);
    return true;
  }
}
