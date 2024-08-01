<?php

namespace App\Controller\Pages;

use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;

class CentralFuncionario extends LayoutPage
{

  // Método responsável por retornar a página home dos funcionários
  public static function getFuncionario($request)
  {
    $content = View::render('home/home_funcionario', [
      'name' => $request->user->nome,
      'menu' => self::getSystemBox($request->user->permissoes),
    ]);

    return parent::getPage('Central de serviços - Funcionário', 'home', $content, $request);
  }
}
