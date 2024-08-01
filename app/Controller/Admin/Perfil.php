<?php

namespace App\Controller\Admin;

use App\Controller\Auth\Alert;
use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;
use App\Http\Middleware\Permissao;
use App\Model\CentralServicos\Perfil as PerfilModel;
use App\Model\CentralServicos\Permissao as PermissaoModel;
use App\Model\CentralServicos\Sistema as SistemaModel;
use stdClass;

class Perfil extends LayoutPage
{
  // Método responsável por retornar as linhas da tabela dos usuários
  private static function getPerfilTabela($isSuperAdmin)
  {
    $linhasTabela = '';
    $perfis = PerfilModel::getPerfis();
    $number_linha = 0;
    foreach ($perfis as $perfil) {
      if ($perfil->codigo === 'admin' && !$isSuperAdmin) continue;

      $number_linha++;
      $linhasTabela .= View::render('admin/perfil/tabela_linha', [
        'numero-linha' => $number_linha,
        'id' => $perfil->id,
        'nome' => $perfil->nome,
        'descricao' => $perfil->descricao,
        'icon' => $perfil->status == 'A' ? 'times' : 'check',
        'icon-color' => $perfil->status == 'A' ? 'text-danger' : 'text-success',
        'status-color' => $perfil->status == 'A' ? 'text-success' : 'text-danger',
        'status' => $perfil->status == 'A' ? 'Ativo' : 'Inativo',
      ]);
    }
    return $linhasTabela;
  }
  // Método responsável por retornar a página de perfis 
  public static function getPerfis($request, $errorMessage = null, $successMessage = null)
  {
    $user = $request->user;
    $superAdmin = parent::checkPermissao($user, 'admin');
    $status = !is_null($errorMessage) ? Alert::getError($errorMessage) : (!is_null($successMessage) ? Alert::getSuccess($successMessage) : '');
    $content = View::render('admin/perfil', [
      'status' => $status,
      'perfis' => self::getPerfilTabela($superAdmin),
    ]);
    return parent::getPage('Painel administrativo - Perfil', 'admin-perfil', $content, $request);
  }
  // Método responsável por retornar as permissões por sistemas
  private static function getPermissaoSistema($isSuperAdmin, $perfil = null)
  {
    $sistemas = SistemaModel::getAllSistemas();
    $permissoesUsuario = '';
    foreach ($sistemas as $sistema) {
      if ($sistema->id === 1 && !$isSuperAdmin) continue;

      $permissaoSistema = $perfil === null ? PermissaoModel::getPermissaoBySistemaId($sistema->id)
        : PermissaoModel::getPermissaoByPerfilAndSistema($perfil->id, $sistema->id);

      $permissaoCheck = '';

      $vinculado = false;
      foreach ($permissaoSistema as $permissao) {
        if (!$vinculado && $permissao->vinculado) $vinculado = true;

        $permissaoCheck .= View::render('admin/permissao/permission_check', [
          'id' => $permissao->id,
          'descricao' => $permissao->descricao,
          'checked' => $permissao->vinculado ? 'checked' : '',
        ]);
      }
      $permissoesUsuario .= View::render('admin/permissao/accordion_sistema', [
        'text-vinculado' => !$vinculado ? 'text-dark' : '',
        'id' => $sistema->id,
        'sistema' => $sistema->nome,
        'descricao' => $sistema->descricao,
        'permissoes-check' => $permissaoCheck,
      ]);
    }
    return $permissoesUsuario;
  }
  // Método responsável por retornar a pagina de criação do perfil caso seja passado
  // id perfil = null por parâmetro ou a pagina de edição do perfil, caso seja passado o id 
  public static function getEditPerfil($request, $idPerfil = null)
  {
    $user = $request->user;
    $superAdmin = parent::checkPermissao($user, 'admin');
    $perfil = $idPerfil === null ? false : PerfilModel::getPerfilById($idPerfil);
    if (!$perfil && $idPerfil !== null) $request->getRouter()->redirect('/admin/perfil/novo');
    $content = View::render('admin/perfil/perfil', [
      'nome' => $perfil ? $perfil->nome : '',
      'codigo' =>  $perfil ? $perfil->codigo : '',
      'disabled-codigo' =>  $perfil ? 'disabled' : '',
      'descricao' => $perfil ? $perfil->descricao : '',
      'permissoes' => $perfil ? self::getPermissaoSistema($superAdmin, $perfil) : self::getPermissaoSistema($superAdmin),
    ]);
    return parent::getPage('Painel administrativo - Perfil', 'admin-perfil', $content, $request);
  }
  // Método responsável por realizar a criação do perfil caso seja passado
  // id perfil = null por parâmetro ou a edição do perfil, caso seja passado o id 
  public static function setEditPerfil($request, $idPerfil = null)
  {
    $postVars = $request->getPostVars();
    $user = $request->user;
    $isSuperAdmin = parent::checkPermissao($user, 'admin');
    if (empty($postVars['nome']) || (empty($postVars['codigo']) && $idPerfil === null) || empty($postVars['descricao']))
      return array('success' => false, 'message' => 'Todos os campos são obrigatórios.');

    $perfil = $idPerfil === null ? false : PerfilModel::getPerfilById($idPerfil);

    if (!$perfil) {
      $perfilByCodigo = PerfilModel::getPerfilByCodigo($postVars['codigo']);
      if ($perfilByCodigo instanceof PerfilModel) return array('success' => false, 'message' => 'Desculpe, já possui um perfil com esse código.');
      $perfil = new stdClass;
      $perfil->nome = $postVars['nome'];
      $perfil->codigo = $postVars['codigo'];
      $perfil->descricao = $postVars['descricao'];
      $perfil->id = PerfilModel::cadastrarPerfil($perfil);
      $response = array('success' => true, 'message' => 'Perfil cadastrado com sucesso.', 'redirect' => URL . '/admin/perfil');
    } else {
      $perfil->nome = $postVars['nome'];
      $perfil->descricao = $postVars['descricao'];
      PerfilModel::atualizarPerfil($perfil);
      $response = array('success' => true, 'message' => 'Perfil alterado com sucesso.', 'redirect' => URL . '/admin/perfil');
    }
    PermissaoModel::deletePermissaoByPerfil($perfil->id);
    foreach ($postVars['permissao'] as $permissao) {
      $permissaoPerfil = PermissaoModel::getPermissaoById($permissao);
      if (!$isSuperAdmin && $permissaoPerfil->codigo === 'admin') continue;
      PermissaoModel::insertPermissaoPerfil($permissaoPerfil->id, $perfil->id);
    }
    return $response;
  }
  // Método responsável por atualizar os status do perfil
  public static function updateStatus($request, $id)
  {
    $perfil = PerfilModel::getPerfilById($id);
    if ($perfil->status === 'A') $perfil->status = 'D';
    else $perfil->status = 'A';
    PerfilModel::atualizarPerfil($perfil);
    $statusPerfil = $perfil->status == 'A' ? 'Ativo' : 'Inativo';
    return array(
      'success' => true,
      'message' => 'O perfil ' . $perfil->nome . ' está ' . $statusPerfil . '.',
      'icon' => $perfil->status == 'A' ? 'fas fa-times' : 'fas fa-check',
      'status' => $statusPerfil,
    );
  }
}
