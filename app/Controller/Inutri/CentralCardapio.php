<?php

namespace App\Controller\Inutri;

use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;
use App\Model\Inutri\Cardapio as CardapioModel;
use App\Model\Inutri\Perfil as PerfilModel;
use App\Model\Inutri\Dieta as DietaModel;

class CentralCardapio extends LayoutPage
{

  // Método responsável por inserir um novo cardápio
  public static function insertCardapio($request)
  {
    $postVars = $request->getPostVars();
    if (empty($postVars['horaInicio']) || empty($postVars['horaLimite']) || empty($postVars['nomeCardapio']) || empty($postVars['perfis'])) {
      return array(
        'success' => false,
        'message' => 'É necessário preencher todos os campos obrigatórios.'
      );
    }
    $perfis = $postVars['perfis'];
    $dietaCodigo = empty($postVars['dieta']) ? NULL : rtrim($postVars['dieta']);
    $id = CardapioModel::insertCardapio($postVars['nomeCardapio'], $postVars['horaInicio'], $postVars['horaLimite'], $dietaCodigo);
    foreach ($perfis as $perfil) {
      CardapioModel::insertCardapioPerfil($id, $perfil);
    }
    return array(
      'success' => true,
      'message' => 'Cardápio criado com sucesso!'
    );
  }

  // Método responsável por atualizar os status por id
  public static function updateStatus($id)
  {
    $cardapio = CardapioModel::getCardapioById($id);
    if (!$cardapio) return array(
      'success' => false,
      'message' => 'Desculpe, mas o cardápio que está tentando atualizar não existe.',
    );
    $status = 1;
    if ($cardapio->status) $status = 0;
    CardapioModel::updateStatus($id, $status);
    $statusAtual = $status === 0 ? 'Inátivo' : 'Ativo';
    return array(
      'success' => true,
      'message' => 'O cardápio ' . $cardapio->nome . ' está ' . $statusAtual,
    );
  }

  // Método responsável por atualizar os dados dos cardápios
  public static function updateCardapio($request, $id)
  {
    $postVars = $request->getPostVars();
    if (empty($postVars['horaInicio']) || empty($postVars['horaLimite']) || empty($postVars['nomeCardapio']) || empty($postVars['perfis'])) {
      return array(
        'success' => false,
        'message' => 'É necessário preencher todos os campos obrigatórios.'
      );
    }
    $perfis = $postVars['perfis'];

    // Atualiza o cardápio
    $dietaCodigo = empty($postVars['dieta']) ? null : rtrim($postVars['dieta']);
    CardapioModel::updateCardapio($id, $postVars['nomeCardapio'], $postVars['horaInicio'], $postVars['horaLimite'], $dietaCodigo);

    // Remove os perfis vinculado aquele cardápio
    CardapioModel::deletePerfisCardapioById($id);

    // Inserir novos perfis aquele cardápio
    foreach ($perfis as $perfil) {
      CardapioModel::insertCardapioPerfil($id, $perfil);
    }

    return array(
      'success' => true,
      'message' => 'Cardápio atualizado com sucesso!',
      'id_cardapio' => $id,
      'nome_cardapio' => $postVars['nomeCardapio'],
    );
  }

  // Método responsável por retornar os cardápios existentes
  private static function getCardapios()
  {
    $cardapios = CardapioModel::getCardapios();
    $pageCardapio = '';

    foreach ($cardapios as $cardapio) {
      $pageCardapio .= View::render('inutri/central_cardapio/cardapio', [
        'id' => $cardapio->id,
        'nome' => $cardapio->nome,
        'status' => $cardapio->status,
        'checked' => $cardapio->status ? 'checked' : '',
      ]);
    }

    return $pageCardapio;
  }

  // Método responsável por retornar a página de opções de perfis do cardápio
  private static function getPerfisCardapio($perfis = null)
  {
    if ($perfis == null) $perfis = PerfilModel::getActivedPerfis();

    $perfilPage = '';
    foreach ($perfis as $perfil) {
      $perfilPage .= View::render('inutri/central_cardapio/modals/perfis_cardapio', [
        'id' => $perfil->id,
        'nome' => $perfil->nome,
        'checked' => $perfil->vinculado ? 'checked' : ''
      ]);
    }

    return $perfilPage;
  }

  // Método responsável por retornar todas as opções de dietas existentes
  private static function getOptionsDieta($codigo = null)
  {
    $dietas = DietaModel::getDietas();
    $options = '';
    foreach ($dietas as $dieta) {
      $options .= View::render('utils/option', [
        'id' => $dieta->codigo,
        'nome' => $dieta->nome,
        'selected' => $codigo != null && rtrim($dieta->codigo) == $codigo ? 'selected' : ''
      ]);
    }

    return $options;
  }

  // Método responsável por retornar o modal do cardápio
  public static function getCardapioModal($id = null)
  {
    if ($id != null) {
      $cardapio = CardapioModel::getCardapioById($id);
      $perfisCardapio = PerfilModel::getAllPerfisByCardapio($id);
    }

    return View::render('inutri/central_cardapio/modals/cardapio', [
      'id' => $id == null ? '' : $cardapio->id,
      'nome' => $id == null ?  '' : $cardapio->nome,
      'hora-inicio' => $id == null ?  '' : $cardapio->hora_inicio,
      'hora-limite' => $id == null ?  '' : $cardapio->hora_limite,
      'options' =>  $id == null ? self::getOptionsDieta() : self::getOptionsDieta($cardapio->codigo_externo),
      'perfis' => $id == null ? self::getPerfisCardapio() : self::getPerfisCardapio($perfisCardapio),
      'button-type' => $id == null ?  'create' : 'save',
      'button' => $id == null ?  'Criar' : 'Salvar',
    ]);
  }

  // Método responsável por retornar a página princiap da central de cardápios
  public static function getCardapio($request)
  {
    $content = View::render('inutri/central_cardapio', [
      'cardapios' => self::getCardapios(),
    ]);

    return parent::getPage('iNutri - Central Cardápio', 'inutri', $content, $request);
  }
}
