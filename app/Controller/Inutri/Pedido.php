<?php

namespace App\Controller\Inutri;

use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;
use App\Controller\Utils\Setor;
use App\Http\Request;
use App\Model\Entity\User as UserModel;
use App\Model\Inutri\Configuration;
use App\Model\Inutri\ItemCardapio as ItemCardapioModel;
use App\Model\Inutri\Pedido as PedidoModel;
use App\Model\Utils\Setor as SetorModel;
use DateTime;
use DateTimeZone;
use Mike42\Escpos\CapabilityProfile;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;

class Pedido extends LayoutPage
{
  // Módulos do painel
  private static $pedidosPages = [
    'pendente' => [
      'label' => 'Pendente',
      'situation' => 'pendente',
      'color' => 'bg-pendente'
    ],
    'progresso' => [
      'label' => 'Progresso',
      'situation' => 'progresso',
      'color' => 'bg-progresso'
    ],
    'pronto' => [
      'label' => 'Pronto',
      'situation' => 'pronto',
      'color' => 'bg-pronto'
    ],
    'entregando' => [
      'label' => 'Em Entrega',
      'situation' => 'entregando',
      'color' => 'bg-entregando'
    ],
    'entregue' => [
      'label' => 'Entregues',
      'situation' => 'entregue',
      'color' => 'bg-entregue'
    ],
    'cancelado' => [
      'label' => 'Cancelados',
      'situation' => 'cancelado',
      'color' => 'bg-cancelado'
    ],
  ];

  // Método responsável por retornar o menu do painel
  private static function getMenu($currentModule = null)
  {
    $links = '';
    $hashComparable = $currentModule;

    foreach (self::$pedidosPages as $hash => $module) {
      if ($currentModule == null) $hashComparable = $hash;

      $links .= View::render('inutri/pedido/link_pedido', [
        'label' => $module['label'],
        'situation' => $module['situation'],
        'color' => $module['color'],
        'current' => $hash == $hashComparable ? 'active' : ''
      ]);
    }

    return View::render('inutri/pedido/menu_pedido', [
      'links' => $links
    ]);
  }

  // Método responsável por retornar os botões das páginas
  private static function getButton($pedido, $situation)
  {

    if ($situation === null) {
      switch ($pedido->situacao) {
        case 'pendente':
          return View::render("inutri/pedido/buttons/cancelar");
        case 'cancelado':
          return View::render("inutri/pedido/buttons/cancelado", []);
        default:
          return '';
      }
    }

    switch (true) {
      case $pedido->situacao == 'pendente':
        $buttonsPage = View::render("inutri/pedido/buttons/confirmar", []);
        $buttonsPage .= View::render("inutri/pedido/buttons/cancelar");
        return $buttonsPage;
      case $pedido->situacao != 'entregue':
        $buttonSituation = View::render("inutri/pedido/buttons/$pedido->situacao", []);
        $printerButton = View::render("inutri/pedido/buttons/imprimir", [
          'margem' => 'ms-2',
        ]);
        return  $buttonSituation . $printerButton;
      default:
        return View::render("inutri/pedido/buttons/imprimir");
    }
  }

  // Método responsável por retornar os pedidos na página
  private static function getPedidosPage($pedidos, $situation = null)
  {
    $pedidosPage = '';
    if ($situation == 'entregue' or $situation == 'cancelado')
      $pedidosPage = View::render('inutri/pedido/calendario_pedidos');

    foreach ($pedidos as $pedido) {
      $solicitante = $pedido->id_solicitante != null ?
      UserModel::getUserById($pedido->id_solicitante)->nome.' '.UserModel::getUserById($pedido->id_solicitante)->sobrenome : 'Paciente';

      $dataCurrentSituacao = 'data_' . $pedido->situacao;

      $pedidosPage .= View::render('inutri/pedido/pedidos', [
        'categoria' => $pedido->tipo_pedido,
        'id-pedido' => $pedido->id,
        'id-solicitante' => $pedido->id_solicitante,
        'situation-label' => ucfirst($pedido->situacao),
        'data' => $pedido->$dataCurrentSituacao,
        'situation' => $pedido->situacao,
        'solicitado' => date('d/m H:i', strtotime($pedido->data_pendente)),
        'cardapio' => $pedido->nome_cardapio,
        'tipo-usuario' => $pedido->tipo_pedido == 'Próprio' ? 'user' : 'heart',
        'destinatario' => $pedido->destinatario,
        'solicitante' => $solicitante ?? "Paciente #".$pedido->id_solicitante,
        'destino' => $pedido->tipo_pedido == 'Paciente' ?
          "$pedido->unidade, $pedido->leito" : SetorModel::getSetorByCode($pedido->unidade)->nome,
        'observacao' => !empty($pedido->observacao) ? View::render('inutri/pedido/observacao', [
          'observacoes' => $pedido->observacao
        ]) : View::render('inutri/pedido/icon_observacao'),
        'comida-pedido' => self::getComidaByPedido($pedido->id),
        'itens' => '',
        'horario' => date('d/m H:i', strtotime($pedido->$dataCurrentSituacao)),
        'buttons' => self::getButton($pedido, $situation),
        "personalizado" => ""
      ]);
    }

    return $pedidosPage;
  }

  // Método responsável por retornar os alerts da página de pedidos
  private static function getAlertPedido($message)
  {
    if (empty($pedidos)) return View::render('inutri/pedido/alert/alert_pedido', [
      'message' => $message
    ]);
  }

  // Método responsável por retornar a situação formatada
  private static function formatSituation($situation)
  {
    $newSituation = '';
    switch ($situation) {
      case 'progresso':
        $newSituation = 'em progresso';
        break;
      case 'entregando':
        $newSituation = 'em entrega';
        break;
      default:
        $newSituation = $situation;
        break;
    }
    return $newSituation;
  }

  // Método responsável por retornar as comidas por pedidos
  private static function getComidaByPedido($pedido)
  {
    $comidas = ItemCardapioModel::getItensByPedido($pedido);
    $itens = '';
    foreach ($comidas as $comida) {
      $itens .= View::render('inutri/pedido/comida_pedido', [
        'comida' => $comida->nome,
        'porcao' => $comida->porcao
      ]);
    }
    return $itens;
  }

  // Método responsável por cancelar o pedido
  public static function cancelPedido($request, $pedido)
  {
    $postVars = $request->getPostVars();
    if (!$postVars['motivo-cancel']) return array(
      'success' => false,
      'message' => 'É necessário informar um motivo para cancelar um pedido.'
    );

    $pedido = PedidoModel::getPedidoById($pedido);
    if ($pedido->situacao != 'pendente') return array(
      'success' => false,
      'message' => 'Desculpe, o pedido não pode ser cancelado pois já está em preparo. Atualize a página.'
    );

    PedidoModel::cancelPedido($pedido->id, $postVars['motivo-cancel'], $request->user->id);
    $pedido->situacao = 'cancelado';
    $isAdmin = parent::checkPermissao($request->user, 'inutri-acessar-pedidos-admin', 'admin');
    return array(
      'success' => true,
      'admin' => (bool) $isAdmin,
      'button' => self::getButton($pedido, 'cancelado'),
      'data-cancelamento' => date('d/m H:i'),
      'message' => 'Pedido cancelado com sucesso.',
      "content" => View::render('inutri/pedido/alert/alert_pedido', [
        'message' => "Não há nenhum pedido pendente.",
      ])
    );
  }

  // Método responsável por retornar o modal de cancelamento
  public static function getModalCancel($request, $pedido)
  {
    $obPedido = PedidoModel::getPedidoById($pedido);

    if ($obPedido->situacao == 'cancelado') {
      $obUser = UserModel::getUserById($obPedido->id_atendente);

      return View::render('inutri/pedido/modals/cancelamento_pedido', [
        'motivo' => $obPedido->motivo_cancel,
        'disabled' => 'disabled',
        'button-enviar' => 'd-none',
        'pedido' =>  $obPedido->id ?? $pedido,
        'cancelador' => $obUser->nome . ' ' . $obUser->sobrenome,
        'cancelador-id' => $obUser->id
      ]);
    }

    return View::render('inutri/pedido/modals/cancelamento_pedido', [
      'motivo' => '',
      'disabled' => '',
      'button-enviar' => '',
      'pedido' =>  $obPedido->id ?? $pedido,
      'cancelador' => $request->user->nome . ' ' . $request->user->sobrenome,
      'cancelador-id' => $request->user->id
    ]);
  }

  // Método responsável por retornar os pedidos dentro de um range de datas
  public static function getPedidosByRange($request, $situation)
  {
    $postVars = $request->getPostVars();
    $pedidos = PedidoModel::getPedidosByRange($situation, array($postVars['firstDate'], $postVars['secondDate']));

    if (empty($pedidos))
      return self::getAlertPedido('Não há nenhum pedido no período selecionado.');

    return self::getPedidosPage($pedidos, $situation);
  }

  // Método responsável por atualizar a situação do pedido
  public static function updatePedido($request, $idPedido)
  {
    $user = $request->user;
    $postVars = $request->getPostVars();
    $situation = $postVars['situacao'];
    $pedido = PedidoModel::getPedidoById($idPedido);

    if ($situation != $pedido->situacao) {
      return array(
        "success" => false,
        "message" => "O pedido não se encontra mais no status " . self::formatSituation($situation) . "."
      );
    }

    $nextSituation = '';
    switch ($pedido->situacao) {
      case 'pendente':
        $nextSituation = 'progresso';
        break;
      case 'progresso':
        $nextSituation = 'pronto';
        break;
      case 'pronto':
        $nextSituation = 'entregando';
        break;
      case 'entregando':
        $nextSituation = 'entregue';
        break;
    }
    setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
    date_default_timezone_set('America/Recife');

    $dataSituation = 'data_' . $nextSituation;
    $date = date('Y-m-d H:i:s');

    PedidoModel::updatePedido($pedido->id, $user->id, $nextSituation, $dataSituation, $date);
    if ($nextSituation === 'progresso') {
      $pedido = PedidoModel::getPedidoById($pedido->id);
      self::imprimirPedido($request, $pedido->id);
    }

    return array(
      "success" => true,
      "message" => 'Pedido atualizado com sucesso!',
      "content" => View::render('inutri/pedido/alert/alert_pedido', [
        'message' => "Não há mais pedido " . self::formatSituation($situation)
      ])
    );
  }

  // Método responsável por retornar os pedidos por situação
  public static function getPedidosBySituation($situation)
  {
    if ($situation == 'pendente')
      return self::getCurrentPedidos($situation);

    $pedidos = PedidoModel::getPedidosBySituationAndDays($situation, 7);

    if (empty($pedidos)) {
      $message = "Não há nenhum pedido " . self::formatSituation($situation) . " nos últimos 7 dias.";
      return self::getAlertPedido($message);
    }

    return self::getPedidosPage($pedidos, $situation);
  }

  // Método responsável por retornar os pedidos solicitados nas últimas 12h
  public static function getPedidosByUser($request)
  {
    $idSolicitante = $request->user->id;
    $pedidos = PedidoModel::getPedidoBySolicitanteId($idSolicitante);
    $message = "Nenhum pedido encontrado.";

    if (empty($pedidos))
      return self::getAlertPedido($message);

    return self::getPedidosPage($pedidos);
  }

  // Método responsável por retornar os pedidos solicitados nas últimas 12h
  private static function getCurrentPedidos($situation)
  {
    $currentPedidos = PedidoModel::getCurrentPedidos($situation);

    $situation = self::formatSituation($situation);
    $message = "Não há nenhum pedido $situation.";
    if (empty($currentPedidos))
      return self::getAlertPedido($message);

    return self::getPedidosPage($currentPedidos, $situation);
  }

  private static function getFiltros($user = null)
  {
    $filtrosPage = '';
    $filtros = [
      'todos' => [
        'id' => 'todos',
        'label' => 'Todos',
        'permission' => 'inutri',
      ],
      'proprio' => [
        'id' => 'proprio',
        'label' => 'Próprio',
        'permission' => 'inutri-solicitar-refeicao-proprio',
      ],
      'paciente' => [
        'id' => 'paciente',
        'label' => 'Paciente',
        'permission' => 'inutri-solicitar-refeicao-paciente',
      ],
      'terceiros' => [
        'id' => 'terceiros',
        'label' => 'Terceiros',
        'permission' => 'inutri-solicitar-refeicao-terceiros',
      ],
    ];
    foreach ($filtros as $filtro) {
      if ($user !== null && !parent::checkPermissao($user, $filtro['permission'])) continue;
      $filtrosPage .= View::render('inutri/pedido/filtro', [
        'id' => $filtro['id'],
        'label' => $filtro['label'],
      ]);
    }
    return $filtrosPage;
  }

  // Método responsável por retornar a página de pedidos  
  public static function getPedido($request)
  {
    $user = $request->user;
    $isNutricao = parent::checkPermissao($user, 'inutri-acessar-pedidos-admin', 'admin');

    if ($isNutricao) {
      $content = View::render('inutri/pedidos', [
        'menu-pedidos' => self::getMenu('pendente'),
        'pedidos' => self::getCurrentPedidos('pendente'),
        'filtros' => self::getFiltros(),
      ]);
    } else {
      $content = View::render('inutri/pedidos', [
        'menu-pedidos' => self::getMenu(),
        'pedidos' => self::getPedidosByUser($request),
        'filtros' => self::getFiltros($user),
      ]);
    }

    return parent::getPage('iNutri - Pedidos', 'inutri', $content, $request);
  }

  // remove all accents from string
  private static function stripAccents(string $str)
  {
    return strtr(utf8_decode($str), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
  }

  public static function imprimirPedido(Request $request, string $pedidoId): array
  {
    $pedido = PedidoModel::getPedidoById($pedidoId);

    if ($pedido->situacao === 'pendente')
      return [];

    $itensPedidos = ItemCardapioModel::getItensByPedido($pedido->id);

    $fortalezaTimezone = new DateTimeZone(CURRENT_TIMEZONE);
    $dataSolicitacao = new DateTime($pedido->data_pendente, $fortalezaTimezone);
    $dataAtendimento = new DateTime($pedido->data_progresso, $fortalezaTimezone);

    $solicitante = UserModel::getUserById($pedido->id_solicitante);
    $atendente = UserModel::getUserById($pedido->id_atendente);
    $leito = $pedido->leito ? ", " . $pedido->leito : "";
    $destino = trim(Setor::getSetorByCode($pedido->unidade)) . trim($leito);
    $solicitante = $solicitante->nome . ' ' . $solicitante->sobrenome;
    $atendente = $atendente->nome . ' ' . $atendente->sobrenome;
    $destinatario = $pedido->destinatario;
    $observacoes = !empty($pedido->observacao) ? "\&Observações: " . $pedido->observacao : '';
    $itensAdicionais = !empty($pedido->item_adicional) ? "...................................................................................\&Itens adicionais: " . $pedido->item_adicional : '';

    $itens = '';
    foreach ($itensPedidos as $item) {
      $itens .= "[ ] " . $item->porcao . ' x ' . $item->nome . "\&";
    }

    $dataImpressao = new DateTime('now', $fortalezaTimezone);
    try {
      $profile = CapabilityProfile::load("simple");
      $configurationPrinter = new Configuration;
      $printerPath = 'smb://remoto:remoto1@' . $configurationPrinter->getHostPrinter() . "/" . $configurationPrinter->getSharedPrinter();
      $connector = new WindowsPrintConnector($printerPath);
      $printer = new Printer($connector, $profile);
      $logoHrg = "^FO30,30^GFA,4437,4437,51,L01LF8,:::::M0LF8,::::M07KF8,:::M03KF8,::M01KF8,::N0KF8,:N07JF8,:FFL03JF8,IFCJ03QF,JFCI01QF,KF8001QF,KFEI0QFJ07003807F801FE1FF039IFC0400EJ03FE03807FK07F81FEI04,LF8007PFJ0700380E1E038E1E3C39IFC0E00EJ03C78381E3CI01IF1FFC00600C0031FFC03FFC,LFE007PFJ070038380707061C0E3807I0E00EJ0381C38380EI0380F1C0E00E00E0031C0F03FF8,MF803PFJ070038300386021C0E3807001E00EJ0380E387007I070071C0E00E00F0031C03838,MFC01PFJ0700387001C6001C073807001F00EJ0380E38E003800E0021C0701F00F8031C01C38,MFE00PFJ070038E001C6001C073807003700EJ0380E38E003801CI01C0701B00F8031C01E38,NF807OFJ070038E001E7001C073807003380EJ0380E39C001C01CI01C07033809C031C00E38,NFC07OFJ070038EI0E7801C0E3807006380EJ0380E39C001C03CI01C07033809E021C00F38,NFE03OFJ07IF8EI0E3F01C0E3807006180EJ0381C39C001C038I01C0E061C08F021C00738,OF01OFJ07IF9EI0E1FC1C1C38070041C0EJ0387839C001C038I01C1C061C087821C0073FF,OF80OFJ070039EI0E07E1FF0380700C1C0EJ03FE039C001C038I01FF8040C083821C0073FF,OFC03MFEJ070039EI0E01F1C00380700FFE0CJ038E039C001C0380071C700C0E081C21C00738,OFE01MFEJ070038EI0E0071C00380701FFE0CJ0386039C001C0380071C700FFE081E21C00738,PF00MFEJ070038E001C0071C003807018070CJ0387039C001C01C0071C381807080F21C00738,PF803LFEJ070038F001C0071C003807030070CJ0383839E001C01C0071C181807080721C00738,PF801LFEJ0700387001C0071C003807030070EJ0383838E003801E0071C1C30030803E1C00E38,PFC007KFEJ07003838038C071C003807070038EJ0381C387003I0E0071C0E30038801E1C00E38,PFE001KFEJ0700383C070E061C003807060038E0200380E383807I070071C0F70038801E1C01C38,PFEI07JFEJ0700380FBE0F9C1C0038070E003CFFE00380F381E1CI03E0E1C07E001DC00E1C0783C0C,QFI01JFEJ06003803F003F81C0038070C001CFFE0038073807F8J0FF81C07E001DC0061FFE03FFC,QF8I03IFEiT0C,IFDMF8J03FFE,L03JFCL07E,L01JFC,L01JFE,:L01KFS03F807FE0C0020IFC3FCI04007,L01KFS0E3E0FFE0E0071IFC38FI0E007J01FF003FF8J07F03FF07FC0E0031C00FF803FC007F,L01KFR018060EI0F00300700303800E007J01C3C01FF8J0C383FF070F0E0061C0383C060700C3,L01KF8Q030020EI0F80200300301C00F007J0180E018K018183800703070061C070080C038181,L01KF8Q070020EI0F80200300301C01F007J01807018K018003800703870061C0E0081801C18,L01KF8Q0EJ06I0DC0200300301C013007J01803818K0180038007038700C1C0CI03801C18,L01KFCQ0EJ06I0CE0200300301C033807J01801C18K01C0038007038380C1C1CI03800E1C,L01KFCQ0EJ06I0CF02003003018021807J01801C18K01E003800703038181C1CI03I0E1C,L01KFCQ0EJ07FC0C702003003038061C07J01801C18L0F803800707018181C38I07I0E0F8,L01KFEP01EJ07FC0C3820030039F0061C07J01801C1FFK07E03FE070E01C101C38I07I0E07E,L01KFEP01EJ06I0C1C2003003FC00C0C07J01801C18L01F038007F800C301C38I07I0E01F,L01KFEQ0EJ06I0C0E20030030C00FFE07J01801C18M078380071800C201C3CI07I0E0078,L01KFEQ0EJ06I0C0F20030030E00C0E07J01801C18M038380071C00E601C1CI03800E0038,L01LFQ0EJ06I0C072003003070180707J01801C18M01C380070C006601C1CI03800E0038,L01LFQ0FJ06I0C03E003003070180707J01801C18M01C380070E007C01C1EI03800C0018,L01LFQ078010EI0C01E003003038300307J01803818K010183800707003C01C0E0041C01C0038,L01LFQ038060EI0C01E00300301C300387J01807018K018383800703003801C0700C0E0383038,L01LFQ01E1C0IF0C00E00700381C600387FF801C1E01CF8I03C3039F8703803801C03C3807070387,L01LFR07F80IF0C00600780780E6001C7FF801FF803FF8I01FE03FF8701C01001C01FE003FC01FE,L01LF8iT03,L01LF8iT02,L01LF8iT038,jO0C,:jN03,,::::::^FS";
      $text = "^XA
      ^CI28
      ^XZ
      ^XA
      ^MMT
      ^PW639
      ^LL915
      ^LS0
      " . $logoHrg . "

      ^FO30,125^A0N,24,24^FB588,150,2,C^FDiNutri - Sistema de solicitação de refeição^FS
      ^FO30,160^GB583,1,1,B,0^FS
      ^FO410,67^A0N,20,20^FB200,150,2,R^FDData impressão\&" . $dataImpressao->format('d/m/Y H:i') . "^FS

      ^FO30,180^A0N,24,24^FB588,150,2,C^FDPEDIDO #" . $pedido->id . "^FS

      ^FO30,220^A0N,24,24^FB588,750,10,L^FH^FDData solicitacao: " . $dataSolicitacao->format('d/m/Y H:i') . "\&Solicitante: " . $solicitante . "\&Data atendimento: " . $dataAtendimento->format('d/m/Y H:i') . "\&Atendente: " . $atendente . "\&Destinatario: " . $destinatario . "\&Destino: " . $destino . $observacoes . "\&...................................................................................\&Itens do pedido\&" . $itens . $itensAdicionais . "\&\&Recebido por ................................................................^FS

      ^PQ1,0,1,Y
      ^XZ";

      $printer->text(self::stripAccents($text));
      $printer->cut();
      $printer->close();
    } catch (\Exception $e) {
      var_dump($e->getMessage());
      return [
        'success' => false,
        'message' => 'Ocorreu uma erro na sua solicitação, por favor, tente novamente.'
      ];
    }

    return [
      'success' => true,
      'message' => 'Pedido impresso com sucesso!'
    ];
  }
}
