<?php

namespace App\Controller\Auth;

use \App\Utils\View;

class Alert
{

  // Método resposável por retornar uma mensagem de sucesso
  public static function getSuccess($message)
  {
    return View::render('login/alert/status', [
      'tipo' => 'success',
      'mensagem' => $message
    ]);
  }
  // Método resposável por retornar uma mensagem de error
  public static function getError($message)
  {
    return View::render('login/alert/status', [
      'tipo' => 'danger',
      'mensagem' => $message
    ]);
  }
}
