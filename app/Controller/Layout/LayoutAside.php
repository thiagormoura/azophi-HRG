<?php

namespace App\Controller\Layout;

use \App\Utils\View;
use \App\Session\User\Auth as SessionUserLogin;
use \App\Session\User\AuthPaciente as SessionPacienteLogin;

class LayoutAside
{

  // Módulos do painel
  private static $systems = [
    'Gestão' => [
      'monexm' => [
        'label' => 'MonExm',
        'link' => URL . '/monexm',
        'permission' => array('monexm', 'admin'),
      ],
      'monps' => [
        'label' => 'MonPs',
        'link' => URL . '/monps',
        'permission' => array('monps', 'admin'),
      ],
      'azophicc' => [
        'label' => 'AzophiCC',
        'link' => URL . '/azophicc',
        'permission' => array('azophicc', 'admin'),
      ]
    ],
    'Sistemas' => [
      'inutri' => [
        'label' => 'iNutri',
        'link' => URL . '/inutri',
        'permission' => array('inutri', 'admin'),
      ],
      'notsis' => [
        'label' => 'NotSis',
        'link' => URL . '/notsis/home',
        'permission' => array('notsis', 'admin'),
      ],
      'avasis' => [
        'label' => 'Avasis',
        'link' => '/avasis',
        'permission' => array('avasis', 'admin'),
      ]
    ],
    'Administrador' => [
      'admin' => [
        'label' => 'Inicio',
        'link' => URL . '/admin',
        'permission' => array('admin'),
      ],
      'admin-usuario' => [
        'label' => 'Usuários',
        'link' => URL . '/admin/usuario',
        'permission' => array('admin'),
      ],
    ]
  ];

  // Método responsável por retornar a role do usuário
  protected static function getRole($request, ...$role)
  {
    // return in_array($role, $request->user->permissoes);
    return array_intersect($role, $request->user->permissoes);
  }

  // Método responsável por retornar o menu do painel
  private static function getMenu($currentModule, $permissoes)
  {
    $links = '';
    $categorys = '';
    foreach (self::$systems as $category => $modules) {
      foreach ($modules as $hash => $module) {
        if (array_intersect($module['permission'], $permissoes)) {
          $links .= View::render('layout/centralservicos/menu/link', [
            'label' => $module['label'],
            'link' => $module['link'],
            'current' => $hash == $currentModule ? 'active' : ''
          ]);
        }
      }
      if (!empty($links)) {
        $categorys .= View::render('layout/centralservicos/menu/category', [
          'category' => $category,
          'links' => $links
        ]);
        $links = '';
      }
    }
    return View::render('layout/centralservicos/menu/box', [
      'menu-links' => $categorys
    ]);
  }

  // Método responsável por retornar o header do layout verificando se o usuário está logado ou não
  private static function getHeader($request)
  {
    if (SessionUserLogin::isLogged()) return View::render('layout/centralservicos/header', [
      'usuario' => $request->user->nome . ' ' . $request->user->sobrenome
    ]);
    return View::render('layout/centralservicos/header_no_user', [
      'user-logged' => SessionPacienteLogin::isLogged() ? View::render('layout/centralservicos/menu/logged') : '',
    ]);
  }

  // Método responsável por retornar o footer do layout
  private static function getFooter()
  {
    return View::render('layout/centralservicos/footer');
  }

  private static function getPaginationLink($queryParams, $page, $url, $label = null)
  {
    $queryParams['page'] = $page['page'];
    $link = $url . '?' . http_build_query($queryParams);

    return View::render('layout/centralservicos/pagination/link', [
      'page' => $label ?? $page['page'],
      'link' => $link,
      'active' => $page['current'] ? 'active' : '',
    ]);
  }

  public static function getPagination($request, $obPagination)
  {
    $pages = $obPagination->getPages();

    if (count($pages) <= 1) return '';

    $links = '';
    $url = $request->getRouter()->getCurrentUrl();
    $queryParams = $request->getQueryParams();

    $currentPage = $queryParams['page'] ?? 1;
    $limit = getenv('PAGINATION_LIMIT');
    $middle = ceil($limit / 2);
    $start = $middle > $currentPage ? 0 : $currentPage - $middle;
    $limit = $limit + $start;

    if ($start > 0) {
      $links .= self::getPaginationLink($queryParams, reset($pages), $url, '<<');
    }

    if ($limit > count($pages)) {
      $diff = $limit - count($pages);
      $start = $start - $diff;
    }

    foreach ($pages as $page) {
      // verifica o start da paginação
      if ($page['page']  <= $start) continue;
      if ($page['page'] > $limit) {
        $links .= self::getPaginationLink($queryParams, end($pages), $url, '>>');

        break;
      }
      $links .= self::getPaginationLink($queryParams, $page, $url);
    }

    return View::render('layout/centralservicos/pagination/box', [
      'links' => $links
    ]);
  }

  public static function getPage($title, $content, $currentModule = '', $request = null)
  {
    return View::render('layout/centralservicos/page', [
      'title' => $title,
      'header' => self::getHeader($request),
      'content' => $content,
      'footer' => self::getFooter(),
      'menu' => self::getMenu($currentModule, $request->user->permissoes)
    ]);
  }
}
