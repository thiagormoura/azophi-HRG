<?php

namespace App\Controller\Layout;

use App\Http\Request;
use \App\Utils\View;
use \App\Session\User\Auth as SessionUserLogin;
use \App\Session\User\AuthPaciente as SessionPacienteLogin;
use App\Model\Entity\User;
use \Firebase\JWT\JWT;
use App\Model\CentralServicos\Permissao;

class Layout
{

  // Módulos do painel
  private static $systems = [
    'Sistemas' => [
      'icon' => 'fad fa-th-large',
      'modules' => [
        'check_exame' => [
          'label' => 'Check Exame',
          'link' => URL.'/check_exame',
          'color-background' => 'bg-success',
          'icon' => 'fas fa-notes-medical',
          'description' => 'Gestão de Exames',
          'permission' => array('admin','check-exame')
        ],
        'check_os' => [
          'label' => 'CheckOS',
          'link' => URL.'/check_os',
          'color-background' => 'bg-success',
          'icon' => 'fas fa-file-spreadsheet',
          'description' => 'Gestão de OS',
          'permission' => array('admin','checkos')
        ],
        'chaves' =>[
          'label' => 'Chaves',
          'link' => URL. '/chaves',
          'color-background' => 'bg-success',
          'icon' => 'fal fa-key',
          'description' => 'Sistema de liberação de chaves.',
          'permission' => array('admin')
        ],
        'gestaoleitos' => [
          'label' => 'Gestão de Leitos',
          'link' => URL . '/gestaoleitos',
          'color-background' => 'bg-success',
          'icon' => 'fas fa-procedures',
          'description' => 'Gerenciamento de leitos.',
          'permission' => array('gleitos', 'admin'),
          'sub-modules' => [
            'gestao-home' => [
              'label' => 'Home',
              'link' => URL . '/gestaoleitos',
              'icon' => 'fas fa-home',
              'permission' => array('gleitos', 'admin'),
            ],
            'gestao-solicitacao' => [
              'label' => 'Solicitação de Vaga',
              'link' => URL . '/gestaoleitos/solicitacao',
              'icon' => 'fas fa-clipboard-list-check',
              'permission' => array('gleitos', 'admin'),
            ],
            'gestao-painel-leitos' => [
              'label' => 'Painel de Leitos',
              'link' => URL . '/gestaoleitos/painel_leitos',
              'icon' => 'fas fa-line-columns',
              'permission' => array('gleitos-painel-leitos', 'admin')
            ],
            'gestao-leitos' => [
              'label' => 'Gerenciar Leitos',
              'link' => URL . '/gestaoleitos/leitos',
              'icon' => 'fas fa-unlock-alt',
              'permission' => array('gleitos-gerenciar-leitos', 'admin'),
            ],
            'gestao-indicadores' => [
              'label' => 'Indicadores',
              'link' => URL . '/gestaoleitos/indicadores',
              'icon' => 'fas fa-chart-line',
              'permission' => array('gleitos-indicadores', 'admin'),
            ],
          ]
        ],
        'inutri' => [
          'label' => 'iNutri',
          'link' => URL . '/inutri',
          'color-background' => 'bg-success',
          'icon' => 'fas fa-soup',
          'description' => 'Sistema da nutrição.',
          'permission' => array('inutri', 'admin'),
          'sub-modules' => [
            'inutri-home' => [
              'label' => 'Home',
              'link' => URL . '/inutri',
              'icon' => 'fas fa-home',
              'permission' => array('inutri', 'admin'),
            ],
            'inutri-refeicao-proprio' => [
              'label' => 'Minha refeição',
              'link' => URL . '/inutri/solicitar_refeicao',
              'icon' => 'fas fa-utensils',
              'permission' => array('inutri-solicitar-refeicao-proprio', 'admin'),
            ],
            'inutri-refeicao-terceiros' => [
              'label' => 'Refeição Terceiros',
              'link' => URL . '/inutri/solicitar_refeicao/terceiros',
              'icon' => 'fas fa-clipboard-list-check',
              'permission' => array('inutri-solicitar-refeicao-terceiros', 'admin'),
            ],
            'inutri-refeicao-pacientes' => [
              'label' => 'Refeição Pacientes',
              'link' => URL . '/inutri/solicitar_refeicao/paciente',
              'icon' => 'fas fa-clipboard-list-check',
              'permission' => array('inutri-solicitar-refeicao-paciente', 'admin'),
            ],
            'inutri-pedidos' => [
              'label' => 'Pedidos',
              'link' => URL . '/inutri/pedidos',
              'icon' => 'fas fa-clipboard-list-check',
              'permission' => array('inutri-acessar-pedidos', 'admin'),
            ],
            'inutri-perfil' => [
              'label' => 'Perfil',
              'link' => URL . '/inutri/central_perfil',
              'icon' => 'fas fa-users',
              'permission' => array('inutri-acessar-perfil', 'admin'),
            ],
            'inutri-usuario' => [
              'label' => 'Usuario',
              'link' => URL . '/inutri/central_usuario',
              'icon' => 'fas fa-users-cog',
              'permission' => array('inutri-acessar-usuario', 'admin'),
            ],
            'inutri-dashboard' => [
              'label' => 'Dashboard',
              'link' => URL . '/inutri/dashboard',
              'icon' => 'fas fa-chart-line',
              'permission' => array('inutri-acessar-dashboard', 'admin'),
            ],
            'inutri-item-cardapio' => [
              'label' => 'Item Cardapios',
              'link' => URL . '/inutri/central_itemcardapio',
              'icon' => 'fas fa-carrot',
              'permission' => array('inutri-acessar-alimentos', 'admin'),
            ],
            'inutri-elaborar-cardapio' => [
              'label' => 'Elaborar Cardapio',
              'link' => URL . '/inutri/cardapios',
              'icon' => 'fas fa-calendar-check',
              'permission' => array('inutri-acessar-calendario-cardapio', 'admin'),
            ],
            'inutri-cardapio' => [
              'label' => 'Cardapios',
              'link' => URL . '/inutri/central_cardapio',
              'icon' => 'fas fa-calendar-alt',
              'permission' => array('inutri-acessar-cardapio', 'admin'),
            ],
          ]
        ],
        'sisnot' => [
          'label' => 'SisNot',
          'link' => URL . '/sisnot/home',
          'color-background' => 'bg-success',
          'icon' => 'fad fa-question',
          'description' => 'Notificação de incidentes.',
          'permission' => array('sisnot', 'admin'),
          'sub-modules' => [
            'sisnot-home' => [
              'label' => 'Home',
              'link' => URL . '/sisnot/home',
              'icon' => 'fas fa-home',
              'permission' => array('sisnot', 'admin'),
            ],
            'sisnot-notificacoes' => [
              'label' => 'Notificações',
              'link' => URL . '/sisnot/notificacoes',
              'icon' => 'fas fa-clipboard-list-check',
              'permission' => array('sisnot', 'admin'),
            ],
            'sisnot-dashboard' => [
              'label' => 'Dashboard',
              'link' => URL . '/sisnot/dashboard',
              'icon' => 'fas fa-chart-line',
              'permission' => array('sisnot', 'admin'),
            ]
          ]
        ],
        'avasis' => [
          'label' => 'Avasis',
          'link' => URL . '/avasis',
          'color-background' => 'bg-success',
          'icon' => 'fas fa-star',
          'description' => 'Sistema de avaliação',
          'permission' => array('avasis', 'admin'),
          'sub-modules' => [
            'avasis-dashboard' => [
              'label' => 'Dashboard',
              'link' => URL . '/avasis',
              'icon' => 'fas fa-home',
              'permission' => array('avasis', 'admin'),
            ],
            'avasis-gerenciador-index_gerenciador' => [
              'label' => 'Gerenciador de Questionarios',
              'link' => URL . '/avasis/gerenciador',
              'icon' => 'fas fa-columns',
              'permission' => array('avasis', 'admin'),
            ],
            'avasis-relatorios' => [
              'label' => 'Relatórios',
              'link' => URL . '/avasis/relatorios',
              'icon' => 'fas fa-chart-line',
              'permission' => array('avasis', 'admin'),
            ]
          ],
        ],
        'gestaoleitos' => [
          'label' => 'Gestão de Leitos',
          'link' => URL . '/gestaoleitos',
          'color-background' => 'bg-success',
          'icon' => 'fas fa-procedures',
          'description' => 'Gerenciamento de leitos.',
          'permission' => array('gleitos', 'admin'),
          'sub-modules' => [
            'gestao-home' => [
              'label' => 'Home',
              'link' => URL . '/gestaoleitos',
              'icon' => 'fas fa-home',
              'permission' => array('gleitos', 'admin'),
            ],
            'gestao-solicitacao' => [
              'label' => 'Solicitação de Vaga',
              'link' => URL . '/gestaoleitos/solicitacao',
              'icon' => 'fas fa-clipboard-list-check',
              'permission' => array('gleitos', 'admin'),
            ],
            'gestao-painel-leitos' => [
              'label' => 'Painel de Leitos',
              'link' => URL . '/gestaoleitos/painel_leitos',
              'icon' => 'fas fa-line-columns',
              'permission' => array('gleitos-painel-leitos', 'admin')
            ],
            'gestao-leitos' => [
              'label' => 'Gerenciar Leitos',
              'link' => URL . '/gestaoleitos/leitos',
              'icon' => 'fas fa-unlock-alt',
              'permission' => array('gleitos-gerenciar-leitos', 'admin'),
            ],
            'gestao-indicadores' => [
              'label' => 'Indicadores',
              'link' => URL . '/gestaoleitos/indicadores',
              'icon' => 'fas fa-chart-line',
              'permission' => array('gleitos-indicadores', 'admin'),
            ],
          ]
        ],
        'sosmaqueiro' => [
          'label' => 'SOS Maqueiro',
          'link' => URL . '/sosmaqueiro',
          'color-background' => 'bg-success',
          'icon' => 'fas fa-stretcher',
          'description' => 'Solicitação de maqueiros.',
          'permission' => array('sosmaqueiro', 'admin'),
        ],
        'ouvimed' => [
          'label' => 'OuviMed',
          'link' => URL . '/ouvimed',
          'color-background' => 'bg-success',
          'icon' => 'fas fa-headset',
          'description' => 'Gestão de manifestações.',
          'permission' => array('ouvimed', 'admin'),
        ]
      ]
    ],
    'Gestão' => [
      'icon' => 'fas fa-calculator',
      'modules' => [
        'monexm' => [
          'label' => 'MonExm',
          'link' => URL . '/monexm',
          'color-background' => 'bg-azul-esverdeado',
          'icon' => 'fas fa-file-medical-alt',
          'description' => 'Monitoramento dos exames',
          'permission' => array('monexm', 'admin'),
        ],
        'monps' => [
          'label' => 'MonPs',
          'link' => URL . '/monps',
          'color-background' => 'bg-azul-esverdeado',
          'icon' => 'fad fa-users',
          'description' => 'Monitoramento do pronto socorro.',
          'permission' => array('monps', 'admin'),
        ],
        'azophi' => [
          'label' => 'Azophi',
          'link' => URL . '/azophi',
          'color-background' => 'bg-azul-esverdeado',
          'icon' => 'fas fa-chart-line',
          'description' => 'Monitoramento Geral',
          'permission' => array('azophi', 'admin'),
        ],
        'azophicc' => [
          'label' => 'AzophiCC',
          'link' => URL . '/azophicc',
          'color-background' => 'bg-azul-esverdeado',
          'icon' => 'fas fa-heart-rate',
          'description' => 'Monitoramento do Centro C.',
          'permission' => array('azophicc', 'admin'),
        ],
        'SOS Maqueiros Dashboard' => [
          'label' => 'SOS Maqueiros Dashboard',
          'link' => URL . '/sosmaqueiro/dashboard',
          'color-background' => 'bg-azul-esverdeado',
          'icon' => 'fas fa-stretcher',
          'description' => 'Dashboard do sistema de SOS Maqueiros.',
          'permission' => array('admin'),
        ]
      ]
    ],
    'Painéis' => [
      'icon' => 'fas fa-columns',
      'modules' => [
        'pa-filas' => [
          'label' => 'P.A. Filas',
          'link' => URL . '/pafilas',
          'color-background' => 'bg-navy',
          'icon' => 'fas fa-columns',
          'description' => 'Monitoramento das filas do P.A.',
          'permission' => array('admin', 'pafilas'),
        ],
        'espera' => [
          'label' => 'Painel de espera Recepção',
          'link' => URL . '/espera',
          'color-background' => 'bg-navy',
          'icon' => 'fas fa-columns',
          'description' => 'Painel da espera dos pacientes no PS.',
          'permission' => array('admin'),
        ],
        'espera-ped' => [
          'label' => 'Painel de espera Pediátrico',
          'link' => URL . '/espera_ped',
          'color-background' => 'bg-navy',
          'icon' => 'fas fa-columns',
          'description' => 'Painel da espera dos pacientes na Ped.',
          'permission' => array('admin'),
        ],
        'painel-agm' => [
          'label' => 'Painel AGM',
          'link' => URL . '/painelagm',
          'color-background' => 'bg-navy',
          'icon' => 'fas fa-columns',
          'description' => 'Painel de agendamento de cirurgias.',
          'permission' => array('admin')
        ],
        'papem' => [
          'label' => 'Fila de espera (PAPEM)',
          'link' => URL . '/papem',
          'color-background' => 'bg-navy',
          'icon' => 'fas fa-columns',
          'description' => 'Fila de espera dos pacientes.',
          'permission' => array('admin'),
        ],
        'papem-recep' => [
          'label' => 'Fila de espera na recepção (PAPEM)',
          'link' => URL . '/papem_recep',
          'color-background' => 'bg-navy',
          'icon' => 'fas fa-columns',
          'description' => 'Fila de espera dos pacientes na recepção.',
          'permission' => array('admin'),
        ],
        'escala-medica-ps' => [
          'label' => 'Escala médica PS',
          'link' => URL . '/escalamedica',
          'color-background' => 'bg-navy',
          'icon' => 'fas fa-columns',
          'description' => 'Escala médica dos médicos do PS.',
          'permission' => array('admin'),
        ],
        'allog' => [
          'label' => 'Painel de login',
          'link' => URL . '/allog',
          'color-background' => 'bg-navy',
          'icon' => 'fas fa-users',
          'description' => 'Painel de login dos funcionarios.',
          'permission' => array('admin'),
        ]
      ]
    ],
    'Configurações' => [
      'icon' => 'fas fa-cog',
      'modules' => [
        'admin' => [
          'label' => 'Administrador',
          'link' => URL . '/admin',
          'color-background' => 'bg-light',
          'icon' => 'fas fa-user-shield',
          'description' => 'Configuração de administrador do sistema.',
          'permission' => array('admin'),
          'sub-modules' => [
            'admin-usuario' => [
              'label' => 'Usuário',
              'color-background' => 'bg-azul-esverdeado',
              'icon' => 'fas fa-users',
              'description' => '',
              'link' => URL . '/admin/usuario',
              'permission' => array('admin'),
            ],
            'admin-perfil' => [
              'label' => 'Perfil',
              'color-background' => 'bg-azul-esverdeado',
              'icon' => 'fas fa-users-cog',
              'description' => '',
              'link' => URL . '/admin/perfil',
              'permission' => array('admin'),
            ],
            'admin-permissao' => [
              'label' => 'Permissão',
              'color-background' => 'bg-azul-esverdeado',
              'icon' => 'fas fa-shield-check',
              'description' => '',
              'link' => URL . '/admin/permissao',
              'permission' => array('admin'),
            ],
            'admin-monlogin' => [
              'label' => 'Login Smart',
              'color-background' => 'bg-azul-esverdeado',
              'icon' => 'fas fa-search',
              'description' => '',
              'link' => URL . '/admin/monlogin',
              'permission' => array('admin'),
            ],
          ]
        ],
      ]
    ]
  ];

  protected static function getSystemBox($user_permissoes)
  {
    $menuBox = '';
    foreach (self::$systems as $hash => $values) {
      if ($hash === 'Administrador') continue;
      
      $modules = self::getMenuPage($values['modules'], $user_permissoes);
      if (!empty($modules)) {

        if (array_key_first(self::$systems) == $hash)
          $menuBox .= "<br><h5 class='pb-2 mb-3 border-bottom'>" . ucfirst($hash) . "</h5>";

        else
          $menuBox .= "</div><div class='row justify-content-left itens-container mb-3'><br><h5 class='pb-2 mb-3 border-bottom'>" . ucfirst($hash) . "</h5>";

        $menuBox .= $modules;
      }
    }
    return $menuBox;
  }

  protected static function getMenuPage($itens, $user_permissoes)
  {
    $menuItems = '';
    foreach ($itens as $item) {
      if (!array_intersect($item['permission'], $user_permissoes)) continue;
      $menuItems .= View::render('utils/box_menu', [
        'id' => $item['id'] ?? '',
        'label' => $item['label'],
        'description' => $item['description'],
        'color-background' => $item['color-background'],
        'icon' => $item['icon'],
        'link' => $item['link'],
      ]);
    }
    return $menuItems;
  }

  // Método responsável por retornar a role do usuário
  protected static function checkPermissao($user, ...$role)
  {
    return array_intersect($role, $user->permissoes);
  }

  // Método responsável por retornar o menu do painel
  private static function getMenu($currentModule, $permissions)
  {
    $links = '';
    $categorys = '';
    foreach (self::$systems as $category => $values) {
      foreach ($values['modules'] as $hash => $module) {
        if (is_null($permissions)) $permissions = [];
        if (array_intersect($module['permission'], $permissions)) {
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

  private static function getLoggedUser()
  {
    $token = SessionUserLogin::isLogged();
    try {
      $decode = (array) JWT::decode($token, JWT_KEY, ['HS256']);
      $email = $decode['email'] ?? '';

      $user = User::getUserByEmail($email);
      $permissoes = Permissao::getPermissaoCodigoByUsuario($user->id);
      $user->permissoes = $permissoes;
      return $user;
    } catch (\Exception $e) {
      return false;
    }
  }

  // Método responsável por retornar o header do layout verificando se o usuário está logado ou não
  private static function getHeader($request)
  {

    if (SessionUserLogin::isLogged()){

      $fortalezaTimeZone = new \DateTimeZone(CURRENT_TIMEZONE);
      $dataCriacao = new \DateTime($request->user->data_criacao, $fortalezaTimeZone);

      return View::render('layout/centralservicos/header', [
        'usuario' => $request->user->nome . ' ' . $request->user->sobrenome,
        'email' => $request->user->email,
        'date-registered' => $dataCriacao->format('d/m/Y H:i')
      ]);
    }

    return View::render('layout/centralservicos/header_no_user', [
      'user-logged' => SessionPacienteLogin::isLogged() ? View::render('layout/centralservicos/menu/logged') : '',
    ]);
  }

  // Método responsável por retornar o footer do layout
  private static function getFooter()
  {
    return View::render('layout/centralservicos/footer');
  }

  public static function getPanelLayout(string $title, string $content)
  {
    return View::render('layout/centralservicos/panel', [
      'title' => $title,
      'content' => $content,
    ]);
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

  private static function getScriptsAndLinks(string $currentModule)
  {
    $systems = self::$systems;
    $user = self::getLoggedUser();
    $permissions = $user->permissoes;
    $scriptFolder = '';

    // Percorre todas as categorias de sistemas
    foreach ($systems as $options) {

      // Verifica se já foi encontrado a pasta do script do sistema atual
      if (!empty($scriptFolder))
        break;
      //Percorre todos os sistemas da categoria atual
      foreach ($options['modules'] as $index => $module) {
        // Verifica se o usuário possui acesso a esse sistema
        if (!array_intersect($module['permission'], $permissions ?? []))
          continue;
        // Verifica se o modulo atual possui submodulos
        if (isset($module['sub-modules'])) {
          $active = false;
          // Percorre os submodulos do modulo atual
          foreach ($module['sub-modules'] as $subModule) {
            if (!array_intersect($subModule['permission'], $permissions ?? []))
              continue;
            //Verifica se o link do submodulo é igual ao link atual,
            // caso não pula para o próximo submodulo

            // if ($currentModule !== $subModule['link'])
            //   continue;
            if (!str_contains($subModule['link'], $currentModule))
              continue;

            // Caso seja, define que o submodulo está ativo
            $active = true;
            break;
          }
          // Caso o submodulo não esteja ativo, pula para o próximo modulo
          if ($active === false)
            continue;
          // Caso esteja ativo, definimos a pasta do script
          // como o index do modulo
          $scriptFolder = $index;
          break;
        }

        // Verifica se o link do modulo é igual ao modulo atual
        // caso não seja, pula para o próximo modulo

        if (!str_contains($module['link'], $currentModule))
          continue;
        // Caso seja, define a pasta do script como o index do modulo
        $scriptFolder = $index;
        break;
      }
    }

    if ($scriptFolder === '')
      return false;

    $path_script = __DIR__ . '/../../../resources/js/customs/' . $scriptFolder;
    $path_link = __DIR__ . '/../../../resources/css/customs/' . $scriptFolder;
    $files_script = scandir($path_script);
    $files_link = scandir($path_link);

    $scripts = '';
    $links = '';

    if ($files_script) {
      for ($i = 2; $i < count($files_script); $i++) {
        $scripts .= View::render('layout/centralservicos/script', [
          'folder' => $scriptFolder,
          'script' => $files_script[$i]
        ]);
      }
    }

    if ($files_link) {
      for ($i = 2; $i < count($files_link); $i++) {
        $links .= View::render('layout/centralservicos/link', [
          'folder' => $scriptFolder,
          'script' => $files_link[$i]
        ]);
      }
    }

    return [
      "scripts" => $scripts,
      "links" => $links
    ];
  }

  private static function getCurrentModule(Request $request)
  {
    $queryParams = $request->getQueryParams();
    $system = explode('/', $queryParams['p']);
    $currentModule = $system[0] ?? '';

    return $currentModule;
  }

  private static function formatCnpjCpf($value)
  {
    $CPF_LENGTH = 11;
    $cnpj_cpf = preg_replace("/\D/", '', $value);
    
    if (strlen($cnpj_cpf) === $CPF_LENGTH) {
      return preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "\$1.\$2.\$3-\$4", $cnpj_cpf);
    } 
    
    return preg_replace("/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/", "\$1.\$2.\$3/\$4-\$5", $cnpj_cpf);
  }

  public static function getPage(String $title, String $currentModule, String $content, Request $request = null, $isDefault=false)
  {
    $currentModule = is_null($request) ? "" : self::getCurrentModule($request);
    $currentLink = is_null($request) ? "" : $request->getRouter()->getCurrentUrl();

    if (!empty($currentLink) && $currentModule == 'gestaoleitos') {
      $link_exploded = explode("/", $currentLink);
      $element = array_pop($link_exploded);
      if (is_numeric($element))
        $currentLink = implode("/", $link_exploded);

      $currentLink = str_replace("editarSolicitacao", 'solicitacao', $currentLink);
    }

    $scriptsAndLinks = self::getScriptsAndLinks($currentModule);
    
    $options = [
      'title' => $title,
      'header' => self::getHeader($request),
      'content' => $content,
      'footer' => self::getFooter(),
      'menu' => self::getMenu($currentModule, $request->user->permissoes),
      'scripts' => $scriptsAndLinks['scripts'],
      'links-css' => $scriptsAndLinks['links']
    ];

    if($currentModule == 'ouvimed'){
      $pageContent = 'ouviMed';
      $options['title-nav'] = explode("/", $title)[1];
      $options['title'] = explode("/", $title)[0];

      $subModule = trim(strtolower(explode("-", $options['title-nav'])[1]));
      if($subModule == 'home') {
        $options['active-home'] = 'active disabled';
        $options['active-name'] = 'Registrar';
      }
      elseif($subModule == 'registrar manifestação') {
        $options['active-new-manifestacao'] = 'active disabled';
        $options['active-name'] = 'Registrar';
      }
      elseif(explode(' ', $subModule)[0] == 'manifestação'){
        $options['active-new-manifestacao'] = 'active disabled';
        $options['active-name'] = 'Visualizar';
      }
      elseif(str_contains(trim($subModule), 'editar manifestação')){
        $options['active-new-manifestacao'] = 'active disabled';
        $options['active-name'] = 'Editar';
      }
      elseif(str_contains(trim($subModule), 'atualizar')){
        $options['active-new-manifestacao'] = 'active disabled';
        $options['active-name'] = 'Atualizar';
      }
      elseif($subModule == 'dashboard') {
        $options['active-indicadores'] = 'active disabled';
        $options['active-name'] = 'Registrar';
      }
    }
    elseif($currentModule == 'avasis' && $isDefault){
      $pageContent = "avasisPaciente";
    }
    elseif($isDefault) $pageContent = "defaultPage";
    else $pageContent = 'page';

    return View::render('layout/centralservicos/'.($pageContent), $options);
  }

  public static function getUserInfos($request){

    $fortalezaTimeZone = new \DateTimeZone(CURRENT_TIMEZONE);
    $dataCriacao = new \DateTime($request->user->data_criacao, $fortalezaTimeZone);

    $content = View::render('layout/centralservicos/user_info',
    [
      "usuario" => $request->user->nome . ' ' . $request->user->sobrenome,
      "email" => $request->user->email,
      "cpf" => self::formatCnpjCpf($request->user->cpf),
      "date-registered" => $dataCriacao->format('d/m/Y H:i')

    ]);
    return self::getPage('Usuário', 'check_os', $content, $request);
  }
}