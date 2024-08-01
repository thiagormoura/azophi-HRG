<?php

namespace App\Model\CentralServicos;

use App\Db\Database;

class Token
{
  // Método responsável por inserir um novo reset de senha
  public static function insertResetSenha($email, $selector, $token, $expires)
  {
    return (new Database('centralservicos', 'token_acesso'))->insert([
      'usuario_email' => $email,
      'selector' => $selector,
      'token' => $token,
      'tempo_expiracao' => $expires, // em segundos
    ]);
  }
  // Método responsável por retornar o reset de senha por token
  public static function verifyTokenByEmail($email)
  {
    return (new Database('centralservicos', 'token_acesso'))->select('count(*) as quantidade', "usuario_email = '" . $email . "'")->fetch(\PDO::FETCH_ASSOC);
  }
  // Método responsável por retornar o reset de senha por token
  public static function getResetSenhaBySelectorAndDate($selector, $expireDate)
  {
    return (new Database('centralservicos', 'token_acesso'))->select('*', "selector = '" . $selector . "' AND tempo_expiracao >= " . "'" . $expireDate . "'")->fetchObject(self::class);
  }
  // Método responsável por deletar o registro de reset de senha do usuário
  public static function deleteResetSenha($email)
  {
    return (new Database('centralservicos', 'token_acesso'))->delete("usuario_email = '" . $email . "'");
  }
}
