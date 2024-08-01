<?php

namespace App\Controller\Inutri;

use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;
use App\Model\CentralServicos\Permissao;
use App\Model\Entity\User as UserModel;
use App\Model\Inutri\Perfil as PerfilModel;

class Usuario extends LayoutPage
{

  // Método responsável por retornar a página principal dos perfis
  private static function getPerfis($perfis, $checkPerfil = null)
  {
    $perfilOption = '';

    foreach ($perfis as $perfil) {
      $perfilOption .= View::render('inutri/usuario/options_perfil', [
        'id' => $perfil->id,
        'nome' => $perfil->nome,
        'selected' => $perfil->id == $checkPerfil ? 'selected' : ''
      ]);
    }

    return $perfilOption;
  }

  // Método responsável por retornar a página principal dos perfis
  private static function getUsuarios()
  {
    $users = UserModel::getUsersByPermissao('inutri');
    $perfis = PerfilModel::getActivedPerfis();
    $usuariosPage = '';

    foreach ($users as $user) {
      $permissoes = Permissao::getPermissaoCodigoByUsuario($user->id);
      $user->permissoes = $permissoes;
      if (parent::checkPermissao($user, 'inutri-admin')) continue;
      $idPerfil = PerfilModel::getIdMainPerfilByUser($user->id);
      $perfilNome = $idPerfil ? PerfilModel::getPerfilById($idPerfil)->nome : 'Sem perfil';
      $usuariosPage .= View::render('inutri/usuario/usuario_perfil', [
        'id' => $user->id,
        'nome' => $user->nome." ".$user->sobrenome,
        'perfil' => $perfilNome,
        'id-perfil' => $idPerfil,
        'perfis' => self::getPerfis($perfis, $idPerfil)
      ]);
    }

    return $usuariosPage;
  }

  // Método responsável por retornar as options do select e os checkbox
  private static function getPerfilUsuario($id)
  {
    $activedPerfis = PerfilModel::getCheckedAndAllPerfisByUser($id);
    $main = '';
    $secondary = '';
    foreach ($activedPerfis as $activedPerfil) {
      $main .= View::render('inutri/usuario/modals/options', [
        'id' => $activedPerfil->id,
        'nome' => $activedPerfil->nome,
        'selected' => $activedPerfil->principal === 1 ? 'selected' : '',
      ]);

      $secondary .= View::render('inutri/usuario/modals/options_perfil', [
        'id' => $activedPerfil->id,
        'nome' => $activedPerfil->nome,
        'checked' => $activedPerfil->principal === 0 ? 'checked' : ''
      ]);
    }

    return array($main, $secondary);
  }

  // Método responsável por retornar o modal com a relação do perfil com o usuário
  public static function modalPerfilUsuario($id)
  {
    $perfisUsuario = self::getPerfilUsuario($id);
    return View::render('inutri/usuario/modals/perfil', [
      'user' => $id,
      'option-perfis' => $perfisUsuario[0],
      'perfis' => $perfisUsuario[1]
    ]);
  }

  // Método responsável por retornar definir os perfis do usuários
  public static function setPerfilUsuario($request, $id)
  {
    $postVars = $request->getPostVars();

    // Remove todos os perfis de determinado usuário
    PerfilModel::deletAllPerfisUsuario($id);

    // Insert principal perfil
    if (!empty($postVars['perfil-principal']))
      PerfilModel::insertPerfilUsuario($id, $postVars['perfil-principal'], 1);

    // Insert perfis secundários
    if (!empty($postVars['perfis-secundarios']))
      foreach ($postVars['perfis-secundarios'] as $perfil) {
        PerfilModel::insertPerfilUsuario($id, $perfil, 0);
      }

    $perfilAtual = PerfilModel::getMainPerfilByUser($id);
    return array(
      'success' => true,
      'message' => 'Perfis alterados com sucesso.',
      'perfil_principal' => $perfilAtual->nome,
    );
  }

  // Método responsável por retornar a página principal dos usuários
  public static function getHome($request)
  {
    $content = View::render('inutri/central_usuario', [
      'usuarios' => self::getUsuarios()
    ]);

    return parent::getPage('iNutri - Usuários', 'inutri', $content, $request);
  }
}
