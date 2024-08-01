<?php

namespace App\Controller\Utils;

use \App\Utils\View;

class Senha
{
  public function validSenha($senha, $confirmSenha = null, $senhaAntiga = null)
  {
    if (strlen($senha) < 8) return array(
      'success' => false,
      'message' => 'A senha deve possuir no mínimo 8 caracteres.'
    );
    if (!preg_match('/[A-Z]/', $senha)) return array(
      'success' => false,
      'message' => 'A senha deve possuir no mínimo 1 caractere maiúsculo.'
    );
    if (!preg_match("/[\'^£$%&*()}{@#~?!><'>,|¨=_+¬-]/", $senha)) return array(
      'success' => false,
      'message' => 'A senha deve possuir no mínimo 1 caractere especial.'
    );
    if (!preg_match('/[0-9]/', $senha)) return array(
      'success' => false,
      'message' => 'A senha deve possuir no mínimo 1 caractere númerico.'
    );
    if ($confirmSenha !== null && $senha !== $confirmSenha) return array(
      'success' => false,
      'message' => 'As senhas deve ser iguais.'
    );
    if ($senhaAntiga !== null && password_verify($senha, $senhaAntiga)) return array(
      'success' => false,
      'message' => 'A nova senha deve ser diferente da senha antiga.'
    );
    if ($senhaAntiga !== null && $senha === $senhaAntiga) return array(
      'success' => false,
      'message' => 'A nova senha deve ser diferente da senha antiga.'
    );
    return array('success' => true);
  }

  public function validAtualSenha($senha, $hashSenha){
    if (!password_verify($senha, $hashSenha)) return array(
      'success' => false,
      'message' => 'A senha cadastrada anteriormente não corresponde a essa.'
    );
    return array('success' => true);
  }

  public function validSenhaAndAntigaSenha($senha, $senhaAntiga)
  {
    if ($senhaAntiga !== null && password_verify($senha, $senhaAntiga)) return array(
      'success' => false,
      'message' => 'A nova senha deve ser diferente da senha antiga.'
    );
    return array('success' => true);
  }
}
