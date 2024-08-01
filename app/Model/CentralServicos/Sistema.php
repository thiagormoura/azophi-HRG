<?php

namespace App\Model\CentralServicos;

use App\Db\Database;

class Sistema
{
  // Método responsável por retornar todos os ativos sistemas cadastrados
  public static function getAllSistemas()
  {
    return (new Database('centralservicos', 'sistema'))->select('*', "`status` = 'A'")->fetchAll(\PDO::FETCH_CLASS, self::class);
  }

  public static function getSistemaById($id)
  {
    return (new Database('centralservicos', 'sistema'))->select('*', "`status` = 'A' AND id = ".$id)->fetchAll(\PDO::FETCH_CLASS, self::class);
  }

  public static function getSystemByNome($nome)
  {
    return (new Database('centralservicos', 'sistema'))->select('*', "`status` = 'A' AND LOWER(nome) = '".strtolower($nome)."'")->fetchAll(\PDO::FETCH_CLASS, self::class);
  }

  public static function insertSistema($sistema)
  {
    return (new Database('centralservicos', 'sistema'))->insert([
      'nome' => $sistema['nome'],
      'descricao' =>  $sistema['descricao'],
    ]);
  }

  public static function updateSistema($sistema)
  {
    return (new Database('centralservicos', 'sistema'))->update('id = ' . $sistema['id_sistema'],  [
      'nome' => $sistema['nome'],
      'descricao' => $sistema['descricao'],
    ]);
  }
}
