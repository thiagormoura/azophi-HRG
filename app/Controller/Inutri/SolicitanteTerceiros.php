<?php

namespace App\Controller\Inutri;

use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;
use \App\Model\Inutri\Perfil as PerfilModel;
use \App\Model\Inutri\Cardapio as CardapioModel;
use \App\Model\Inutri\ItemCardapio as ItemCardapioModel;
use App\Model\Utils\Setor as SetorModel;

class SolicitanteTerceiros extends LayoutPage
{
  // Método responsável por retornar os perfis secundários de determinado usuário
  private static function getPerfis($request)
  {
    $idUser = $request->user->id;
    $perfis = PerfilModel::getSecondaryPerfisByUser($idUser);
    $options = '';
    foreach ($perfis as $perfil) {
      $options .= View::render('inutri/solicitar_refeicao/option_select', [
        'nome' => $perfil->nome,
        'code' => $perfil->id
      ]);
    }
    return $options;
  }
  // Método responsável por retornar a página de solicitação do pedido para terceiros
  public static function getSolicitacao($request)
  {
    $content = View::render('inutri/solicitar_refeicao_terceiros', [
      'options' => self::getPerfis($request)
    ]);

    return parent::getPage('iNutri', 'inutri', $content, $request);
  }

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
  private static function getDestinoOption(){
    $setores = SetorModel::getStores();
    $options = '';
    
    foreach($setores as $setor){
      $options .= View::render('utils/option', [
        'id' => $setor->codigo,
        'nome' => $setor->nome
      ]);
    }

    return $options;
  }

  // Método responsável por retornar o collapse do cardápio
  private static function getCollapseCardapio($request, $cardapio, $date)
  {
    $idUser = $request->user->id;
    $idPerfil = PerfilModel::getIdMainPerfilByUser($idUser);
    $perfil = PerfilModel::getPerfilById($idPerfil);
    return View::render('inutri/solicitar_refeicao/collapse_pedido', [
      'id-user' => $idUser,
      'id-cardapio' => $cardapio->id,
      'solicitante' => $request->user->nome,
      'destinatario' => is_null($perfil) ? '' : View::render('inutri/solicitar_refeicao/destinatario'),
      'destino' => self::getDestinoOption(),
      'hora-limite' => $cardapio->hora_limite,
      'grupos' => self::getGroupsCardapios($cardapio, $date),
      'padronizado' => !$perfil->padronizado ? self::getPadronizadoPage() : '',
      'btn-submit' => 'submit-button'
    ]);
  }

  // Método responsável por retornar todos os cardápios daquele respectivo perfil
  public static function getCardapios($request, $idPerfil)
  {
    $date = date('Y-m-d');
    $hour = date("H:i:sa");
    $cardapios = CardapioModel::getCardapiosByPerfil($idPerfil);
    $cardapioPage = '';

    foreach ($cardapios as $cardapio) {
      $itens = ItemCardapioModel::getItemByDateAndIds($cardapio->id, $idPerfil, $date);
      $cardapioIsDisable = ($cardapio->hora_inicio > $hour  or $cardapio->hora_limite < $hour or empty($itens));
      $cardapioPage .= View::render('inutri/solicitar_refeicao/cardapio', [
        'id' => $cardapio->id,
        'nome' => $cardapio->nome,
        'check-cardapio' => $cardapioIsDisable ? 'disabled' : '',
        'collapse-pedido' => $cardapioIsDisable ? '' : self::getCollapseCardapio($request, $cardapio, $date),
      ]);
    }
    return $cardapioPage;
  }
}
