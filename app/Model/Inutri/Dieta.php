<?php

namespace App\Model\Inutri;

use App\Db\Smart;

class Dieta
{
  // Método responsavel por inserir um novo grupo
  public static function getDietas()
  {
    $tables = "ADP";
    $fields = "ADP_COD as codigo, ADP_NOME as nome";
    $where = "ADP_TIPO = 'D' AND ADP_STATUS IS NULL";
    $limit = null;
    $group = null;
    $order = null;

    return (new Smart($tables))->select($fields, $where, $limit, $group, $order)->fetchAll(\PDO::FETCH_OBJ);
  }
}
