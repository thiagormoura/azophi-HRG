<?php

namespace App\Http\Middleware;

use  \App\Model\Entity\User;
use App\Model\CentralServicos\Permissao;
use App\Model\Entity\Paciente;
use \App\Session\User\AuthPaciente as SessionUserLogin;
use \Firebase\JWT\JWT;

class JWTAuthPaciente
{

  // Método responsável retornar uma instância de um usuário
  private function getJWTAuthPaciente(&$request)
  {
    $token = SessionUserLogin::isLogged();
    // Token JWT
    $jwt = !empty($token) ? $token : '';
    try {
      // decode token
      $decode = (array)JWT::decode($jwt, getenv('JWT_KEY'), ['HS256']);
      $registro = $decode['registro'] ?? '';
      $data_nascimento = $decode['data_nascimento'] ?? '';
      $cpf = $decode['cpf'] ?? '';
    } catch (\Exception $e) {
      $request->getRouter()->redirect('/paciente/login');
      return false;
    }
    $obPaciente = Paciente::getPaciente($registro, $data_nascimento, $cpf);
    return $obPaciente instanceof Paciente ? $obPaciente : false;
  }

  // Método responsável por validar o acesso via JWT
  private function auth($request)
  {
    if ($obUser = $this->getJWTAuthPaciente($request)) {
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
