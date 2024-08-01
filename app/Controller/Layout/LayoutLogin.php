<?php

namespace App\Controller\Layout;

use \App\Utils\View;

class LayoutLogin
{

  // Método responsável por retornar o header do layout
  private static function getHeader()
  {
    return View::render('layout/login/header');
  }

  // Método responsável por retornar o footer do layout
  private static function getFooter()
  {
    return View::render('layout/login/footer');
  }

  public static function getLayout(String $title, String $content, $currentModule = '')
  {
    return View::render('layout/login/page', [
      'title' => $title,
      'header' => self::getHeader(),
      'content' => $content,
      'footer' => self::getFooter(),
    ]);
  }
}
