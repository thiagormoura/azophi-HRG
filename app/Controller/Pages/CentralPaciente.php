<?php

namespace App\Controller\Pages;

use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;
use \App\Session\User\AuthPaciente as SessionUserLogin;

class CentralPaciente extends LayoutPage
{

  // Método responsável por retornar os serviços sem autenticação
  public static function getPaciente($request)
  {
    $content = View::render('home/home_paciente', [
      'name' => $request->user->nome
    ]);

    return parent::getPage('Central de serviços - Paciente',  'home', $content, $request);
  }
}
