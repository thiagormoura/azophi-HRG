<?php

namespace App\Session\SisNot;

class Notificacao
{

  // Método responsável por iniciar a sessão
  private static function init()
  {
    if (session_status() != PHP_SESSION_ACTIVE) session_start();
  }

  // Método responsável por criar o login do usuário
  public static function createNotificacao(...$content)
  {
    self::init();
    $_SESSION['sisnot']['active'] = true;
    $_SESSION['sisnot']['content'] = $content;
    return true;
  }
  // Método responsável por verificar se o usuário está logado
  public static function getNotificacaoActive()
  {
    self::init();
    return isset($_SESSION['sisnot']['active']) ? true : false;
  }
  public static function getNotificacaoContent()
  {
    self::init();
    return isset($_SESSION['sisnot']['active']) ? $_SESSION['sisnot']['content'][0] : false;
  }
  // Método responsável por executar o logout do usuário;
  public static function dropNotificacao()
  {
    self::init();
    unset($_SESSION['sisnot']);

    return true;
  }
}
