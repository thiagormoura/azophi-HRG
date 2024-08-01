<?php

namespace App\Controller\Api;

use App\Model\Entity\Paciente as PacienteModel;
use App\Controller\Auth;
use Exception;
use Firebase\JWT\JWT;

class AuthPaciente extends Api
{
  // Método responsável por gerar um token JWT
  public static function generateToken($request, $user =  null)
  {
    $userData = $user === null ? $request->getPostVars() : $user;
    if (empty($userData['registro']) or empty($userData['cpf']) or empty($userData['dataNascimento']) or empty($userData['email'])) {
      echo Auth\AuthPaciente::getAccess($request, 'Todos os campos são obrigatórios.');
      exit;
    }
    $paciente = PacienteModel::getPaciente($userData['registro'], $userData['dataNascimento'], $userData['cpf']);
    if (!$paciente) {
      echo Auth\AuthPaciente::getAccess($request, 'Dados inseridos estão incorretos.');
      exit;
    }
    $payload = [
      'registro' => $paciente->registro,
      'data_nascimento' => $paciente->data_nascimento,
      'cpf' => $paciente->cpf
    ];
    return JWT::encode($payload, getenv('JWT_KEY'));
  }
}
