<?php

namespace App\Controller\Inutri;

use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;
use App\Model\Inutri\Cardapio as CardapioModel;
use App\Model\Inutri\Perfil as PerfilModel;
use App\Model\Inutri\ItemCardapio as ItemCardapioModel;
use App\Model\Inutri\Pedido as PedidoModel;
use App\Model\Utils\Setor as SetorModel;

class Solicitante extends LayoutPage
{
  // Método responsável por retornar a página de itens
  private static function getPadronizadoPage()
  {
    return View::render('inutri/solicitar_refeicao/padronizado', []);
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

  // Método responsável por retornar os setores 
  private static function getDestinoOption()
  {
    $setores = SetorModel::getStores();
    $options = '';

    foreach ($setores as $setor) {
      $options .= View::render('utils/option', [
        'id' => $setor->codigo,
        'nome' => $setor->nome
      ]);
    }

    return $options;
  }

  // Método responsável por retornar o collapse do cardápio
  private static function getCollapseCardapio($request, $cardapio, $date, $perfil)
  {
    $idUser = $request->user->id;
    $idPerfil = is_null($perfil) ? PerfilModel::getIdMainPerfilByUser($idUser) : $perfil;
    $perfil = PerfilModel::getPerfilById($idPerfil);

    return View::render('inutri/solicitar_refeicao/collapse_pedido', [
      'id-user' => $idUser,
      'id-cardapio' => $cardapio->id,
      'solicitante' => $request->user->nome,
      'destinatario' => $perfil == NULL ? '' : View::render('inutri/solicitar_refeicao/destinatario'),
      'destino' => self::getDestinoOption(),
      'hora-limite' => $cardapio->hora_limite,
      'grupos' => self::getGroupsCardapios($cardapio, $date),
      'padronizado' => !$perfil->padronizado ? self::getPadronizadoPage() : '',
      'btn-submit' => 'submit-button'
    ]);
  }

  // Método responsável por retornar os cardápios disponível para aquele usuário
  public static function getCardapios($request, $perfil = null)
  {
    $date = date('Y-m-d');
    $hour = date("H:i:sa");
    $idUser = $request->user->id;
    $idPerfil = is_null($perfil) ? PerfilModel::getIdMainPerfilByUser($idUser) : $perfil;
    if (!$idPerfil)
      return View::render('utils/alert', [
        'mensagem' => 'Desculpe, mas você não está vinculado a nenhum perfil. 
        Por favor, entre em contato com a equipe de nutrição.',
        'color' => 'danger'
      ]);
    $cardapios = CardapioModel::getCardapiosByPerfil($idPerfil);
    $cardapioPage = '';

    foreach ($cardapios as $cardapio) {
      $itens = ItemCardapioModel::getItemByDateAndIds($cardapio->id, $idPerfil, $date);
      $cardapioIsDisable = ($cardapio->hora_inicio > $hour  or $cardapio->hora_limite < $hour or empty($itens));

      $cardapioPage .= View::render('inutri/solicitar_refeicao/cardapio', [
        'id' => $cardapio->id,
        'nome' => $cardapio->nome,
        'check-cardapio' => $cardapioIsDisable ? 'disabled' : '',
        'collapse-pedido' => $cardapioIsDisable ? '' : self::getCollapseCardapio($request, $cardapio, $date, $perfil),
      ]);
    }

    return $cardapioPage;
  }

  // Método responsável por verificar qual o tipo de usuário e qual a página redirecionada
  public static function solicitarRefeicao($request, $perfil = null)
  {
    setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
    date_default_timezone_set('America/Recife');
    $postVars = $request->getPostVars();
    $idUser = $request->user->id;
    $idPerfil = is_null($perfil) ? PerfilModel::getIdMainPerfilByUser($idUser) : $perfil;
    $cardapio = CardapioModel::getCardapioById($postVars['idCardapio']);

    // Verifica se o perfil tem acesso aquele cardápio
    $perfilHasCardapio = CardapioModel::getPerfilCardapio($cardapio->id, $idPerfil);
    if (!$perfilHasCardapio) return array(
      'success' => false,
      'message' => 'Desculpe, infelizmente você não possui acesso a esse cardápio.'
    );

    // Verifica se o pedido foi realizado dentro do tempo limite.
    $itensPedido = json_decode($postVars['pedido']);
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
      if ($itemPedido->value == 0) return array(
        'success' => false,
        'message' => 'Verifique se todos os campos estão preenchidos corretamente.'
      );
      $porcao += $itemPedido->value;
      if ($porcao > $postVars['limite_porcao']) return array(
        'success' => false,
        'message' => 'Verifique se todos os campos estão preenchidos corretamente.'
      );
    }

    if ($postVars['item_adicional'] == 'undefined') $postVars['item_adicional'] = null;
    if ($postVars['observacao'] == 'undefined') $post['observacao'] = null;

    $date = date('Y-m-d H:i:s');

    $destinatario = empty($postVars['destinatario']) ? $request->user->nome : $postVars['destinatario'];
    $tipoPedido = is_null($perfil) ? 'Próprio' : 'Terceiros';

    $idPedido = PedidoModel::insertPedido($idUser, $postVars['idCardapio'], $tipoPedido, $destinatario, $postVars['destino'], null, $postVars['observacao'], $postVars['item_adicional'], $date);

    foreach ($itensPedido as $itemPedido) {
      PedidoModel::insertComidaPedido($idPedido, $itemPedido->idComida, $itemPedido->value);
    }

    return array(
      'success' => true,
      'message' => 'Recebemos sua solicitação de pedido! Iremos redirecionar você para que você possa acompanhar seu pedido.',
      'redirect' => URL . '/inutri/pedidos',
    );
  }

  // Método responsável por retornar a página de solicitação do pedido
  public static function getSolicitacao($request)
  {
    $content = View::render('inutri/solicitar_refeicao', [
      'cardapios' => self::getCardapios($request),
    ]);

    return parent::getPage('iNutri', 'inutri', $content, $request);
  }
}
