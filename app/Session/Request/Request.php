<?php

namespace App\Session\Request;

class Request
{

  // Método responsável por iniciar a sessão
  private static function init()
  {
    if (session_status() != PHP_SESSION_ACTIVE) session_start();
  }

  // Método responsável por criar o login do usuário
  public static function setRequestedUrl($url)
  {
    self::init();
    $_SESSION['REQUEST_URL'] = $url;
    return true;
  }
  // Método responsável por verificar se o usuário está logado
  public static function getRequestedUrl()
  {
    self::init();
    return $_SESSION['REQUEST_URL'];
  }
  // Método responsável por executar o logout do usuário;
  public static function dropRequestedUrl()
  {
    self::init();
    unset($_SESSION['REQUEST_URL']);

    return true;
  }
}
