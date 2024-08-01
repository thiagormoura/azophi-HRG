<?php

namespace App\Controller\Api;

use App\Model\Entity\User;
use Firebase\JWT\JWT;
use App\Controller\Auth as AuthController;

class Auth extends Api
{
  // Método responsável por gerar um token JWT
  public static function generateToken($request)
  {
    $postVars = $request->getPostVars();

    if (!isset($postVars['email']) or !isset($postVars['senha'])) {
      return AuthController\Auth::getLogin($request, 'Todos os campos são obrigatórios.');
      exit;
    }

    $obUser = User::getUserByEmail($postVars['email']);
    if (!$obUser instanceof User) {
      echo AuthController\Auth::getLogin($request, 'Usuário ou senha inválidos.');
      exit;
    }
    if (!password_verify($postVars['senha'], $obUser->senha)) {
      echo AuthController\Auth::getLogin($request, 'Usuário ou senha inválidos.');
      exit;
    }
    if ($obUser->status === 'D') {
      echo AuthController\Auth::getLogin($request, 'Desculpe, seu usuário está desativado. <br>Entre em contato com a equipe de TI para mais informações - Ramal: 1240.');
      exit;
    }
    
    $payload = [
      'email' => $obUser->email
    ];

    return JWT::encode($payload, getenv('JWT_KEY'));
  }
}
