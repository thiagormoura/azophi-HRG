<?php

namespace App\Model\Utils;

use App\Db\Smart;

class Setor
{
  // Método responsavel por retornar todos os setores
  public static function getStores()
  {
    $tables = "STR";
    $fields = "STR_COD as codigo, STR_NOME as nome";
    $where = "STR_STATUS = 'A' AND STR_COD NOT IN ('999')";
    $limit = null;
    $group = null;
    $order = null;

    return (new Smart($tables))->select($fields, $where, $limit, $group, $order)->fetchAll(\PDO::FETCH_OBJ);
  }

  // Método responsavel por retornar o setor por código
  public static function getSetorByCode($code)
  {
    $tables = "STR";
    $fields = "STR_COD as codigo, STR_NOME as nome";
    $where = "STR_STATUS = 'A' AND STR_COD = '$code'";

    return (new Smart($tables))->select($fields, $where)->fetch(\PDO::FETCH_OBJ);
  }

  public static function getUnitsByCode($codes)
  {
    $tables = "STR";
    $fields = "STR_COD as codigo, STR_NOME as nome";
    $where = "STR_STATUS = 'A' AND STR_COD IN('$codes')";

    return (new Smart($tables))->select($fields, $where)->fetchAll(\PDO::FETCH_CLASS, self::class);
  }
}