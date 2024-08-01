<?php

namespace App\Utils;

class ValidCPF
{
  public static function validCPF($cpf)
  {
    $cpf = str_replace('-', '', str_replace('.', '', $cpf));
    $sum = 0;
    $rest = '';

    // Verifica se foi informado um cpf com tamanho válido
    if(strlen($cpf) < 11) return false;
    
    //Verifica se foi informada uma sequência de digitos repetidos. Ex: 111.111.111-11
    if (preg_match('/(\d)\1{10}/', $cpf)) return false;

    // Validação dos digitos do CPF
    for ($i = 0; $i <= 8; $i++) {
      $sum = $sum + intval($cpf[$i]) * (10 - $i);
    }
    $rest = ($sum * 10) % 11;
    
    if (($rest == 10) || ($rest == 11)) $rest = 0;
    if ($rest != intval($cpf[9])) return false;
    $sum = 0;
    for ($i = 0; $i <= 9; $i++) {
      $sum += intval($cpf[$i]) * (11 - $i);
    }
    $rest = ($sum * 10) % 11;
    
    if (($rest == 10) || ($rest == 11)) $rest = 0;
    if ($rest != intval($cpf[10])) return false;
    return true;
  }
}
