<?php

namespace App\Model\SosMaqueiro;

use App\Db\Database;

class Transporte
{
  // Método responsável por retornar todos os transportes ativos
  public static function getTransportes(){
    return (new Database('sosmaqueiro', 'transporte'))->select('*', "`status` = 'A'")->fetchAll(\PDO::FETCH_CLASS, self::class);
  }
}
