<?php

namespace App\Http\Middleware;

use App\Model\CentralServicos\Permissao as PermissaoModel;

class Permissao
{
  private static $cancelaRecursividade = 0;
  // Método responsável por verificar se determinada permissão possui uma permissão pai
  private static function checkPermissaoPai($user, $permissao)
  {
    if (self::$cancelaRecursividade === 20) return false; // Método de segurança, caso a recursividade entre em looping.
    // Caso a permissão verificada não possua permissao pai, verifica se o usuário possui aquela permissão.
    if ($permissao->permissao_pai === null) {
      $hasPermissao = self::checkPermissaoUsuario($user, $permissao->codigo);
      if ($hasPermissao === true) return true;
      return false;
    }
    // Caso a permissão possua uma permissão pai, instancia a permissão pai
    $permissaoPai = PermissaoModel::getPermissaoById($permissao->permissao_pai);
    // Verifica se o usuário possui a permissão instanciada, caso não, ele não possui acesso a página.
    $hasPermissao = self::checkPermissaoUsuario($user, $permissaoPai->codigo);
    if ($hasPermissao === false) return false;
    // Caso o usuário possua a permissão, verifica se ela possui uma permissão pai. 
    if ($permissaoPai->permissao_pai === null) return true;
    // Caso a permissão possua uma permissão pai, chama a essa função recursivamente.
    self::$cancelaRecursividade++;
    return self::checkPermissaoPai($user, $permissaoPai);
  }
  // Método responsável por verificar se o usuário possui uma permissão pai
  private static function checkPermissaoUsuario($usuario, $permissao)
  {
    $permissoes_usuario = $usuario->permissoes;
    return in_array($permissao, $permissoes_usuario);
  }
  // Método responsável por percorrer o vetor com as permissoes requeridas e checar se o usuário possui acesso a página
  private static function getPermissoesRequeridas($user, $permissoes)
  {
    $hasAccess = false;
    foreach ($permissoes as $codigo_permissao) {
      $permissao = PermissaoModel::getPermissaoByCodigo($codigo_permissao);
      if (!self::checkPermissaoUsuario($user, $permissao->codigo)) continue;
      $hasAccess = self::checkPermissaoPai($user, $permissao); // Método responsável por verificar se determinada permissão possui uma permissão pai
      if ($hasAccess === true) break;
    }
    return $hasAccess;
  }

  // Método responsável por executar o middleware
  public function handle($request, $next, $permissoes)
  {
    $user = $request->user;
    $hasPermissao = self::getPermissoesRequeridas($user, $permissoes);
    // Verifica se o usuário tem permissão para acessar determinada página
    if ($hasPermissao === false) {
      $request->getRouter()->redirect('/error?page_not_found');
    }
    return $next($request);
  }
}
