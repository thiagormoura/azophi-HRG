<?php

namespace App\Controller\Agenda;

use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;

class Home extends LayoutPage
{

  // Método responsável por retornar a página inicial da agenda
  public static function getHome()
  {
    $content = View::render('paciente/agenda/home', [
      'url-back' => URL . '/home/paciente'
    ]);

    return parent::getPage('Agenda', 'agenda', $content);
  }
}
