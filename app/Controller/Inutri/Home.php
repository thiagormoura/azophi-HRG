<?php

namespace App\Controller\Inutri;

use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;
use \App\Model\Utils\Spy;
use App\Http\Request;
use App\Model\Inutri\Configuration;
use App\Model\Inutri\Usuario;

class Home extends LayoutPage
{
  // Módulos do painel
  private static $menu = [
    'Atendimento' => [
      'pedido' => [
        'label' => 'Pedidos',
        'description' => 'Atendimento de Pedidos',
        'link' => URL . '/inutri/pedidos',
        'color-background' => 'bg-azul-medio text-white',
        'icon' => 'fas fa-clipboard-list-check',
        'permission' => array('inutri-acessar-pedidos', 'admin'),
      ],
      'propria' => [
        'label' => 'Solicitar',
        'description' => 'Minha Refeicao',
        'link' => URL . '/inutri/solicitar_refeicao',
        'color-background' => 'bg-azul-medio text-white',
        'icon' => 'fas fa-clipboard-list-check',
        'permission' => array('inutri-solicitar-refeicao-proprio', 'admin'),
      ]
    ],
    'Gerenciamento' => [
      'perfil' => [
        'label' => 'Perfis',
        'description' => 'Gerenciar Perfis',
        'link' => URL . '/inutri/central_perfil',
        'color-background' => 'bg-azul-medio2 text-white',
        'icon' => 'fas fa-users',
        'permission' => array('inutri-acessar-perfil', 'admin'),
      ],
      'usuario' => [
        'label' => 'Usuários',
        'description' => 'Gerenciar Usuários',
        'link' => URL . '/inutri/central_usuario',
        'color-background' => 'bg-azul-medio2 text-white',
        'icon' => 'fas fa-users-cog',
        'permission' => array('inutri-acessar-usuario', 'admin'),
      ],
      'dashboard' => [
        'label' => 'Dashboard',
        'description' => 'Info. das solicitações',
        'link' => URL . '/inutri/dashboard',
        'color-background' => 'bg-azul-medio2 text-white',
        'icon' => 'fas fa-chart-line',
        'permission' => array('inutri-acessar-dashboard', 'admin'),
      ],
      'configuracoes' => [
        'label' => 'Configurações',
        'description' => 'Configurações do sistema',
        'link' => URL . '/inutri/configuracoes',
        'color-background' => 'bg-secondary text-white',
        'icon' => 'fas fa-cog',
        'permission' => array('admin'),
      ]
    ],
    'Planejamento' => [
      'alimento' => [
        'label' => 'Alimentos',
        'description' => 'Alimentos para cardápio',
        'link' => URL . '/inutri/central_itemcardapio',
        'color-background' => 'bg-azul-esverdeado text-white',
        'icon' => 'fas fa-carrot',
        'permission' => array('inutri-acessar-alimentos', 'admin'),
      ],
      'calendario' => [
        'label' => 'Calendário',
        'description' => 'Elaborar cardápio diário',
        'link' => URL . '/inutri/cardapios',
        'color-background' => 'bg-azul-esverdeado text-white',
        'icon' => 'fas fa-calendar-check',
        'permission' => array('inutri-acessar-calendario-cardapio', 'admin'),
      ],
      'cardapio' => [
        'label' => 'Cardápios',
        'description' => 'Meus cardápios',
        'link' => URL . '/inutri/central_cardapio',
        'color-background' => 'bg-azul-esverdeado text-white',
        'icon' => 'fas fa-calendar-alt',
        'permission' => array('inutri-acessar-cardapio', 'admin'),
      ],
    ],
  ];

  private static function getMenu($permissoes)
  {
    $menuItems = '';

    foreach (self::$menu as $title => $group) {

      $menuItems .= View::render('utils/title', [
        'titulo' => $title
      ]);

      foreach ($group as $item) {
        if (!array_intersect($item['permission'], $permissoes))
          continue;

        $menuItems .= View::render('utils/box_menu', [
          'label' => $item['label'],
          'description' => $item['description'],
          'color-background' => $item['color-background'],
          'icon' => $item['icon'],
          'link' => $item['link'],
        ]);
      }
    }
    return $menuItems;
  }

  // Método responsável por verificar qual o tipo de usuário e qual a página redirecionada
  public static function getHome($request)
  {
    $user = $request->user;
    $isNutricao = parent::checkPermissao($user, 'inutri-admin', 'admin');
    if ($isNutricao) $content = View::render('inutri/home', [
      'menu' => self::getMenu($request->user->permissoes),
    ]);
    else $content = View::render('inutri/home_user', [
      'solicitar-minha-refeicao' => View::render('inutri/home/solicitar_minha_refeicao'),
      'solicitar-paciente' => parent::checkPermissao($user, 'inutri-solicitar-refeicao-paciente') ? View::render('inutri/home/solicitar_paciente', []) : '',
      'solicitar-terceiros' => parent::checkPermissao($user, 'inutri-solicitar-refeicao-terceiros') ? View::render('inutri/home/solicitar_terceiros', []) : ''
    ]);

    // Atualiza o acesso do usuario nesse sistema
    Spy::updateAcess($request->user, 13, 'inutri');

    return parent::getPage('iNutri', 'inutri', $content, $request);
  }

  public static function getConfigurationPage(Request $request)
  {
    $config = new Configuration();

    $content = View::render('inutri/configuracoes', [
      'shared-printer' => $config->getSharedPrinter(),
      'host-printer' => $config->getHostPrinter(),
      'menu' => self::getMenu($request->user->permissoes),
    ]);

    return parent::getPage('iNutri', 'inutri', $content, $request);
  }

  public static function setConfigurationPage(Request $request)
  {
    $postVars = $request->getPostVars();

    if ($postVars['host-printer'] === '' && $postVars['host-printer'] === '')
      return false;

    $config = new Configuration();
    $config->setHostPrinter($postVars['host-printer']);
    $config->setSharedPrinter($postVars['shared-printer']);

    $request->getRouter()->redirect('/inutri/configuracoes');
  }
}
