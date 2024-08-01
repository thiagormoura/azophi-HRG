<?php

namespace App\Controller\Admin;

use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;

class Home extends LayoutPage
{
  // Método responsável por retornar a página inicial da agenda
  public static function getHome($request)
  {
    $content = View::render('admin/home', []);

    return parent::getPage('Painel administrativo', 'admin', $content, $request);
  }
}
