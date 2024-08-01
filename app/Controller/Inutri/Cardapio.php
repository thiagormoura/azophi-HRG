<?php

namespace App\Controller\Inutri;

use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;
use App\Model\Inutri\Perfil as PerfilModel;
use App\Model\Inutri\Cardapio as CardapioModel;
use App\Model\Inutri\ItemCardapio as ItemCardapioModel;

class Cardapio extends LayoutPage
{
  // Método responsável por inserir os itens no cardápio
  public static function insertCardapio($request, $id)
  {
    $postVars = $request->getPostVars();
    $dataSemana = $postVars['selectedDate'];
    $opcoes = $postVars['opcao'];
    $porcoes = $postVars['porcao'];

    $idGrupo = CardapioModel::insertGroup($postVars['opcao-grupo'], $postVars['quantidade-grupo']);

    for ($i = 0; $i < count($opcoes); $i++) {
      ItemCardapioModel::insertItemCardapio($idGrupo, $id, $opcoes[$i], $porcoes[$i], $dataSemana);
    }

    return [
      'success' => true,
      'message' => 'teste'
    ];
  }

  // Método responsável por deleter os itens de um determinado cardápio
  public static function deleteItemCardapio($request, $id)
  {
    $postVars = $request->getPostVars();
    $date = $postVars['seletectedDate'];
    ItemCardapioModel::deleteItemCardapio($id, $date);
    $groups = CardapioModel::getCardapioGroupsByIdAndDate($id, $date);
    foreach ($groups as $group) {
      CardapioModel::deleteGroupById($group->id);
    }
  }

  // Método responsável por retornar o cardapio de determinado dia
  public static function updateCardapio($request, $id)
  {
    setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
    $postVars = $request->getPostVars();

    $cardapio = CardapioModel::getCardapioById($id);
    $groups = CardapioModel::getCardapioGroupsByIdAndDate($id, $postVars['data']);
    $date = ucwords(strftime('%A, %d/%m/%Y', strtotime($postVars['data'])));
    $groupsPage = '';

    if (!empty($groups)) {
      foreach ($groups as $group) {
        $itensCardapio = ItemCardapioModel::geItemComidaByIdGroup($group->id);
        $itensPage = '';
        foreach ($itensCardapio as $itemCardapio) {
          $itensPage .= self::getItemCardapio($request, $itemCardapio);
        }

        $groupsPage .= self::getGroup($request, $group, $itensPage);
      }
    } else {

      $groupsPage = self::getGroup($request);
    }

    return View::render('inutri/cardapios/modals/update_cardapio', [
      'id' => $cardapio->id,
      'data' => $postVars['data'],
      'nome-cardapio' => $cardapio->nome,
      'data-formatada' => $date,
      'grupos' => $groupsPage,
      'copia-cardapio' => !empty($groups) ? self::getCopyCardapio() : '',
    ]);
  }

  // Método responsável por retornar os dias que aquele cardápio está preenchido
  public static function getCheckedCardapiosDays($request, $id)
  {
    $postVars = $request->getPostVars();
    $diasCopias = explode(",", $postVars['diasSelecionados']);
    $diasPreenchidos = array();

    for ($i = 0; $i < count($diasCopias); $i++) {
      $dia = CardapioModel::getDaysOfItemByDate($id, $diasCopias[$i]);
      if ($dia != false) $diasPreenchidos[] = $dia;
    }
    $isEmpty = empty($diasPreenchidos);

    if ($isEmpty)
      return array(
        'success' => true,
        'message' => 'Os cardápios foram copiados com sucesso!',
      );

    return array(
      'success' => false,
      'dates' => $diasPreenchidos,
    );
  }

  // Método responsável por retornar a página de grupo de um cardápio
  public static function getGroup($request, $group = null, $itensPage = null)
  {
    return View::render('inutri/cardapios/modals/grupo_cardapio', [
      'id' => $group == null ? '' : $group->id,
      'descricao' => $group == null ? '' : $group->descricao,
      'legend' => $group == null ? 'Opção 1' : $group->descricao,
      'quantidade' => $group == null ? '' : $group->quantidade_itens,
      'preenchido' => $group == null ? '' : 'cardapio-preenchido',
      'itens' => $itensPage == null ? self::getItemCardapio($request) : $itensPage
    ]);
  }

  // Método responsável por retornar a página de itens de um cardápio
  public static function getItemCardapio($request, $itemCardapio = null)
  {
    $comidas = ItemCardapioModel::getActivedItensCardapio();
    $comidasPage = '';
    foreach ($comidas as $comida) {
      $comidasPage .= View::render('inutri/cardapios/modals/options_comida', [
        'id' => $comida->id,
        'nome' => $comida->nome,
        'selected' => $itemCardapio !== null && $itemCardapio->id_comida == $comida->id ? 'selected' : ''
      ]);
    }

    return View::render('inutri/cardapios/modals/item_cardapio', [
      'porcao' => $itemCardapio == null ? '' : $itemCardapio->porcao_comida,
      'comidas' => $comidasPage,
    ]);
  }

  // Método responsável por retornar a página de grupo de um cardápio
  private static function getCopyCardapio()
  {
    return View::render('inutri/cardapios/modals/copia_cardapio');
  }

  // Método responsável por retornar as opções de perfis
  private static function getPerfisOption()
  {
    $activedPerfis = PerfilModel::getActivedPerfis();
    $options = '';
    foreach ($activedPerfis as $activedPerfil) {
      $options .= View::render('inutri/cardapios/options_perfil', [
        'id' => $activedPerfil->id,
        'nome' => $activedPerfil->nome
      ]);
    }

    return $options;
  }

  // Método responsável por retornar os cardápios de determinado perfil
  public static function getCardapios($request, $idPerfil)
  {
    setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
    date_default_timezone_set('America/Recife');

    $postVars = $request->getPostVars();
    $dates = array($postVars['firstDate'], $postVars['secondDate']);

    $diffDates = abs(strtotime($dates[0]) - strtotime($dates[1]));
    $diffDates = round($diffDates / (60 * 60 * 24)) + 1;
    if ($dates[0] == $dates[1]) $diffDates = 1;

    $pageCardapioPerfil = '';
    $pageCardapios = '';

    $cardapios = CardapioModel::getCardapiosByPerfil($idPerfil);
    for ($i = 0; $i < $diffDates; $i++) {

      foreach ($cardapios as $cardapio) {
        $itemCardapio = ItemCardapioModel::getItemByDateAndIds($cardapio->id, $idPerfil, strftime('%Y-%m-%d', strtotime($dates[0] . ' + ' . $i . ' days')));
        $pageCardapios .= View::render('inutri/cardapios/cardapio_perfil', [
          'id' => $cardapio->id,
          'nome' => $cardapio->nome,
          'color-status' => !empty($itemCardapio) ? 'success' : 'danger',
          'validation' => !empty($itemCardapio) ? 'check' : 'times'
        ]);
      }

      $pageCardapioPerfil .= View::render('inutri/cardapios/card_cardapio', [
        'dia' => ucwords(strftime('%A, %d/%m/%Y', strtotime($dates[0] . ' + ' . $i . ' days'))),
        'cardapios' => $pageCardapios
      ]);

      $pageCardapios = '';
    }

    return $pageCardapioPerfil;
  }

  // Método responsável por retornar a página inical dos cardápios
  public static function getCardapio($request)
  {
    $content = View::render('inutri/cardapios', [
      'options-perfil' => self::getPerfisOption()
    ]);

    return parent::getPage('iNutri - Cardápios', 'inutri', $content, $request);
  }
}
