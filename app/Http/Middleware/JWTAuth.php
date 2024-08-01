<?php

namespace App\Http\Middleware;

use  \App\Model\Entity\User;
use App\Model\CentralServicos\Permissao;
use \App\Session\User\Auth as SessionUserLogin;
use \App\Session\Request\Request as SessionRequest;
use \Firebase\JWT\JWT;

class JWTAuth
{

  // Método responsável retornar uma instância de um usuário
  private function getJWTAuthUser(&$request)
  {
    $token = SessionUserLogin::isLogged();
    // Token JWT
    $jwt = !empty($token) ? $token : '';
    try {
      $decode = (array) JWT::decode($jwt, JWT_KEY, ['HS256']);
      $email = $decode['email'] ?? '';
    } catch (\Exception $e) {
      $requestedUrl = $request->getRouter()->getUri();
      SessionRequest::setRequestedUrl($requestedUrl);
      $request->getRouter()->redirect('/login');
    }

    $obUser = User::getUserByEmail($email);
    $permissoes = Permissao::getPermissaoCodigoByUsuario($obUser->id);
    SessionUserLogin::sessionGestao($obUser, $permissoes);
    $obUser->permissoes = $permissoes;
    return $obUser instanceof User ? $obUser : false;
  }

  // Método responsável por validar o acesso via JWT
  private function auth($request)
  {
    if ($obUser = $this->getJWTAuthUser($request)) {
      $request->user = $obUser;
      return true;
    }
    throw new \Exception("Acesso negado", 403);
  }

  // Método responsável por executar o middleware
  public function handle($request, $next, $args)
  {
    // Auth
    $this->auth($request);
    return $next($request);
  }
}
