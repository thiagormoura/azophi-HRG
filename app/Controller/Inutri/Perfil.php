<?php

namespace App\Controller\Inutri;

use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;
use App\Model\Entity\User as UserModel;
use App\Model\Inutri\Perfil as PerfilModel;

class Perfil extends LayoutPage
{

  // Método responsável por atualizar os dados de um perfil
  public static function updatePerfil($request)
  {
    $postVars = $request->getPostVars();
    $idPerfis = $postVars['perfil-id'];
    $nomesPerfis = $postVars['perfil-nome'];
    for ($i = 0; $i < count($idPerfis); $i++) {
      if (empty($idPerfis[$i]) || empty($nomesPerfis[$i])) {
        return array(
          'success' => false,
          'message' => 'É necessário preencher todos os campos obrigatórios.'
        );
      }
    }
    for ($i = 0; $i < count($idPerfis); $i++) {
      $perfil = PerfilModel::getPerfilById($idPerfis[$i]);
      if (!$perfil->editavel) continue;
      PerfilModel::updatePerfil($idPerfis[$i], $nomesPerfis[$i]);
    }

    return array(
      'success' => true,
      'message' => 'Perfis atualizados com sucesso!'
    );
  }

  // Método responsável por atualizar as condições de um perfil (status e padronizado)
  public static function updateCondition($request, $id)
  {
    $postVars = $request->getPostVars();
    $type = $postVars['type'];
    $perfil = PerfilModel::getPerfilById($id);
    if (!$perfil) return array(
      'success' => false,
      'message' => 'Desculpe, mas o perfil que está tentando atualizar não existe.',
    );
    if (!$perfil->editavel) return array(
      'success' => false,
      'message' => 'Desculpe, mas você não pode alterar os status desse perfil.',
    );
    $status = 1;
    if ($perfil->$type) $status = 0;
    $statusAtual = $type === 'status' ? ($status === 0 ? 'Inátivo' : 'Ativo')
      : ($status === 0 ? 'Não padronizado' : 'Padronizado');

    PerfilModel::updateCondition($id, $type, $status);
    return array(
      'success' => true,
      'message' => 'O perfil ' . $perfil->nome . ' está ' . $statusAtual,
    );
  }

  // Método responsável por inserir um novo perfil
  public static function insertPerfil($request)
  {
    $postVars = $request->getPostVars();
    if (empty($postVars['nome'])) return array(
      'success' => false,
      'message' => 'É necessário preencher todos os campos obrigátorios.'
    );
    PerfilModel::insertPerfil($postVars['nome'], $postVars['padronizado']);
    return array(
      'success' => true,
      'message' => 'Perfil cadastrado com sucesso!'
    );
  }

  // Método responsável por retornar a página de adicionar perfil 
  public static function createPerfil()
  {
    return View::render('inutri/perfil/modals/modal_perfil', []);
  }

  // Método responsável por retornar os perfis existentes
  private static function getPerfis()
  {
    $perfis = PerfilModel::getPerfis();
    $pagePerfil = '';

    foreach ($perfis as $perfil) {
      $pagePerfil .= View::render('inutri/perfil/perfis', [
        'editavel' => $perfil->editavel,
        'disable-inputs' => !$perfil->editavel ? 'disabled' : '',
        'id' => $perfil->id,
        'nome' => $perfil->nome,
        'status' => $perfil->status,
        'status-perfil' => $perfil->status ? 'checked' : '',
        'padronizado-perfil' => $perfil->padronizado ? 'checked' : '',
      ]);
    }

    return $pagePerfil;
  }

  // Método responsável por retornar a página principal dos perfis
  public static function getPerfil($request)
  {
    $content = View::render('inutri/central_perfil', [
      'perfis' => self::getPerfis()
    ]);

    return parent::getPage('iNutri - Central Perfil', 'inutri', $content, $request);
  }
}
