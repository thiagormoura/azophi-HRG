<?php

namespace App\Model\SosMaqueiro;

use App\Db\Database;

class Local
{
  public $id;
  public $codigo;
  public $nome;
  public $status;

  // Método responsável por cadastrar um novo local
  public function cadastrar()
  {
    $this->id = (new Database('sosmaqueiro', 'local'))->insert([
      'codigo' => $this->codigo,
      'nome' => $this->nome,
      'status' => $this->status,
    ]);
  }

  // Método responsável por atualizar o cadastro de um local já existente
  public function atualizar()
  {
    return (new Database('sosmaqueiro', 'local'))->update("id = " . $this->id,  [
      'codigo' => $this->codigo,
      'nome' => $this->nome,
      'status' => $this->status,
    ]);
  }


  // Método responsávelp por retornar todos os locais ativos
  public static function getLocaisAndSetores()
  {

// SELECT l.setor_codigo, null, l.nome as local, l.codigo  FROM `local` l WHERE l.setor_codigo IS NULL
// UNION ALL
// SELECT s.codigo as setor_codigo, s.nome as setor, l.nome as local, l.codigo as codigo FROM setor s left join `local` l on s.codigo = l.setor_codigo;

    $unionQuery = "SELECT s.codigo as setor_codigo, s.nome as setor, l.nome as local, l.codigo as codigo
    FROM setor s left join `local` l on s.codigo = l.setor_codigo WHERE s.status = 'A'";

    return (new Database('sosmaqueiro', "`local` l"))->unionAll('l.setor_codigo, null as setor, l.nome as local, l.codigo', "l.setor_codigo IS NULL AND l.status = 'A'", null, null, null, $unionQuery)->fetchAll(\PDO::FETCH_CLASS, self::class);
  }

  // Método responsávelp por retornar todos os locais ativos
  public static function getLocais()
  {
    return (new Database('sosmaqueiro', '`local`'))->select('*', "`status` = 'A'")->fetchAll(\PDO::FETCH_CLASS, self::class);
  }

  // Método responsável por retornar um local pelo código
  public static function getLocalByCodigo($codigo)
  {
    return (new Database('sosmaqueiro', 'local'))->select('*', "codigo = '" . $codigo . "'")->fetchObject(self::class);
  }
}
