<?php

namespace App\Session\User;

class AuthPaciente
{

  // Método responsável por iniciar a sessão
  private static function init()
  {
    if (session_status() != PHP_SESSION_ACTIVE) session_start();
  }

  // Método responsável por criar o login do usuário
  public static function login($token)
  {
    self::init();
    $_SESSION['paciente']['token'] = $token;
    return true;
  }
  public static function tempLogin($user, $limit)
  {
    self::init();
    setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
    date_default_timezone_set('America/Recife');
    $_SESSION['temp_paciente']['paciente'] = $user;
    $_SESSION['temp_paciente']['created'] = time();
    $_SESSION['temp_paciente']['limit'] = $limit;
  }
  public static function getTempLogin()
  {
    self::init();
    if (isset($_SESSION['temp_paciente']) && (time() - $_SESSION['temp_paciente']['created'] > $_SESSION['temp_paciente']['limit'])) {
      session_unset($_SESSION['temp_paciente']);
      return false;
    }
    return isset($_SESSION['temp_paciente']) ? $_SESSION['temp_paciente'] : false;
  }
  public static function dropTempLogin()
  {
    self::init();
    unset($_SESSION['temp_paciente']);
  }
  // Método responsável por verificar se o usuário está logado
  public static function isLogged()
  {
    self::init();
    return isset($_SESSION['paciente']['token']) ? $_SESSION['paciente']['token'] : false;
  }
  // Método responsável por executar o logout do usuário;
  public static function logout()
  {
    self::init();
    unset($_SESSION['paciente']);

    return true;
  }
}
