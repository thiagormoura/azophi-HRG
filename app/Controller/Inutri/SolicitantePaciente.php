<?php

namespace App\Controller\Inutri;

use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;
use App\Model\CentralServicos\Paciente;
use App\Model\Entity\User;
use App\Model\Inutri\Cardapio as CardapioModel;
use App\Model\Inutri\Perfil as PerfilModel;
use App\Model\Inutri\ItemCardapio as ItemCardapioModel;
use App\Model\Inutri\Pedido as PedidoModel;
use App\Model\Inutri\Paciente as PacienteModel;
use App\Model\Utils\Setor as SetorModel;
use DateTime;

class SolicitantePaciente extends LayoutPage
{
  const perfilPaciente = 1;
  // Método responsável por retornar as unidades
  private static function getUnidades()
  {
    $unidades = PacienteModel::getUnidades();
    $unidadesPage = '';
    foreach ($unidades as $unidade) {
      $unidadesPage .= View::render('inutri/solicitar_refeicao/option_select', [
        'code' => rtrim($unidade->code),
        'nome' => rtrim($unidade->nome)
      ]);
    }

    return $unidadesPage;
  }

  // Método responsável por retornar a página de itens
  private static function getItemCardapioPage($idGroup)
  {
    $itens = ItemCardapioModel::geItemComidaByIdGroup($idGroup);
    $itemPage = '';
    foreach ($itens as $item) {
      $itemPage .= View::render('inutri/solicitar_refeicao/item_grupo', [
        'id-comida' => $item->id_comida,
        'nome' => $item->nome,
        'porcao' => $item->porcao_comida,
      ]);
    }
    return $itemPage;
  }

  // Método responsável por retornar os grupos do cardápio
  private static function getGroupsCardapios($cardapio, $date)
  {
    $groups = CardapioModel::getCardapioGroupsByIdAndDate($cardapio->id, $date);

    $groupPage = '';
    foreach ($groups as $group) {
      $groupPage .= View::render('inutri/solicitar_refeicao/grupo_refeicao', [
        'descricao' => $group->descricao,
        'limite' => $group->quantidade_itens,
        'itens-grupo' => self::getItemCardapioPage($group->id),
      ]);
    }
    return $groupPage;
  }

  // Método responsável por retornar a página de cardápios
  private static function getCardapio($request, $dieta)
  {

    $cardapioPage = '';
    $data = date('Y-m-d');

    if ($dieta->paciente_registro) $pedido = PedidoModel::getLastPacientePedido($dieta->paciente_registro);

    if ($pedido) {
      date_default_timezone_set('America/Recife');
      $agora = new DateTime();
      $pendente = new DateTime($pedido->data_pendente);
      $pendente->modify("+10 hours");

      if ($agora <= $pendente && $pedido->situacao != "cancelado"){

        $cardapioPage = View::render('inutri/solicitar_refeicao/error', [
          'color' => 'azul-medio',
          'mensagem' => "Paciente possui a última solicitação registrada às " . date('d/m/Y H:i', strtotime($pedido->data_pendente))
        ]);

        $dataCurrentSituacao = 'data_' . $pedido->situacao;

        $cardapioPage .= View::render('inutri/pedido/pedidos', [
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
          'buttons' => "",
          "personalizado" => "mx-auto mt-3"
        ]);

        return $cardapioPage;
      }
    }
    
    $cardapio = CardapioModel::getCardapioByCodExtAndPerfil(self::perfilPaciente, $dieta->dieta_code);
    if (!$cardapio) {
      return View::render('inutri/solicitar_refeicao/error', [
        'color' => 'danger',
        'mensagem' => 'Paciente sem dieta cadastrada!'
      ]);
    }

    ItemCardapioModel::getItemByDateAndIds($cardapio->id, self::perfilPaciente, $data);
    return $cardapioPage .= View::render('inutri/solicitar_refeicao/cardapio_paciente', [
      'cardapio' => $cardapio->id,
      'registro' => $dieta->paciente_registro,
      'nome' => $cardapio->nome,
      'data-prescricao' => "Prescrição às " . date('d/m/Y H:i', strtotime($dieta->data_prescricao)),
      'id-user' => $request->user->id,
      'id-cardapio' => $cardapio->id,
      'solicitante' => $request->user->nome ?? $dieta->paciente,
      'destinatario' => rtrim($dieta->paciente),
      'unidade' => rtrim($dieta->setor_nome),
      'leito' => rtrim($dieta->loc_nome),
      'unidade-leito' => rtrim($dieta->setor_code) == 'PAT' ? rtrim($dieta->setor_nome) : rtrim($dieta->setor_nome) . ', ' . rtrim($dieta->loc_nome),
      'hora-limite' => $cardapio->hora_limite,
      'grupos' => self::getGroupsCardapios($cardapio, $data)
    ]);
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

  // Método responsável por retornar a dieta de determinado paciente
  public static function getDietaPaciente($request, $registro)
  {
    $dieta = PacienteModel::getDietaPaciente($registro);
  
    return self::getCardapio($request, $dieta);
  }

  public static function getPagePaciente($request)
  {
    $registro = $request->user->registro;

    $content = View::render('paciente/inutri/home', [
      'cardapio' => self::getDietaPaciente($request, $registro)
    ]);

    return parent::getPage('iNutri', 'inutri', $content, $request, true);
  }

  // Método responsável por retornar a lista de pacientes em determinado setor
  public static function getPacienteByUnidade($unidade)
  {
    $paciente = PacienteModel::getPacienteByUnidade($unidade);
    $pacientePage = '';

    foreach ($paciente as $paciente) {
      $pacientePage .= View::render('inutri/solicitar_refeicao/option_select', [
        'code' => rtrim($paciente->registro),
        'nome' => !empty($paciente->local) ? rtrim($paciente->local) . ' - ' . rtrim($paciente->nome) : rtrim($paciente->nome)
      ]);
    }

    return View::render('inutri/solicitar_refeicao/select_paciente', [
      'options' => $pacientePage
    ]);
  }

  // Método responsável por retornar a página de solicitação do pedido do paciente
  public static function getSolicitacao($request)
  {
    $content = View::render('inutri/solicitar_refeicao_paciente', [
      'options' => self::getUnidades()
    ]);

    return parent::getPage('iNutri', 'inutri', $content, $request);
  }

  // Método responsável por solicitar a refeição do paciente
  public static function solicitarPedido($request, $registro)
  {
    setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
    date_default_timezone_set('America/Recife');

    $postVars = $request->getPostVars();
    if($request->user instanceof \App\Model\Entity\Paciente){
      $idUser = $request->user->registro;
    }
    else{
      $usuario = User::getUserById($request->user->id);
  
      $idUser = null;
      if ($usuario instanceof User)
        $idUser = $usuario->id;
    }

    $cardapio = CardapioModel::getCardapioById($postVars['cardapio']);

    // Verifica se o perfil tem acesso aquele cardápio
    $perfilHasCardapio = CardapioModel::getPerfilCardapio($cardapio->id, self::perfilPaciente);
    if (!$perfilHasCardapio) return array(
      'success' => false,
      'message' => 'Desculpe, infelizmente você não possui acesso a esse cardápio.'
    );

    $itensPedido = json_decode($postVars['pedido']);

    // Verifica se o pedido foi realizado dentro do tempo limite.
    if ($cardapio->hora_limite < date('H:i:s')) return array(
      'success' => false,
      'message' => 'Tempo para solicitação do pedido expirou.'
    );

    $porcao = 0;

    foreach ($itensPedido as $itemPedido) {
      if (!ItemCardapioModel::getComidaItemByDateAndCardapio(date('Y-m-d'), $cardapio->id, $itemPedido->idComida)) {
        return array(
          'success' => false,
          'message' => 'Desculpe, infezliemente você não possui acesso a esse cardápio.'
        );
      }
      if ($itemPedido->value == 0)
        return array(
          'success' => false,
          'message' => 'Verifique se todos os campos estão preenchidos corretamente.'
        );
      $porcao += $itemPedido->value;
      if ($porcao > $postVars['limite_porcao'])
        return array(
          'success' => false,
          'message' => 'Verifique se todos os campos estão preenchidos corretamente.'
        );
    }

    if ($postVars['item_adicional'] == 'undefined')
      $postVars['item_adicional'] = null;
    if ($postVars['observacao'] == 'undefined')
      $post['observacao'] = null;

    $date = date('Y-m-d H:i:s');
    $destinatario = PacienteModel::getDietaPaciente($registro);
    $idPedido = PedidoModel::insertPedido($idUser, $postVars['cardapio'], 'Paciente', $destinatario->paciente, $postVars['unidade'], $postVars['leito'], $postVars['observacao'], $postVars['item_adicional'], $date, $registro);

    foreach ($itensPedido as $itemPedido) {
      PedidoModel::insertComidaPedido($idPedido, $itemPedido->idComida, $itemPedido->value);
    }

    return array(
      'success' => true,
      'message' => 'Recebemos sua solicitação de pedido! Iremos redirecionar você para que você possa acompanhar seu pedido.',
      'redirect' => URL . '/inutri/pedidos',
    );
  }
}
