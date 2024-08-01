<?php

namespace App\Model\SosMaqueiro;

use App\Db\Database;

class Setor
{
  public $id;
  public $codigo;
  public $nome;
  public $status;

  // Método responsável por cadastrar um novo setor
  public function cadastrar()
  {
    $this->id = (new Database('sosmaqueiro', 'setor'))->insert([
      'codigo' => $this->codigo,
      'nome' => $this->nome,
      'status' => $this->status,
    ]);
  }

  // Método responsável por atualizar o cadastro de um setor já existente
  public function atualizar()
  {
    return (new Database('sosmaqueiro', 'setor'))->update("id = " . $this->id,  [
      'codigo' => $this->codigo,
      'nome' => $this->nome,
      'status' => $this->status,
    ]);
  }

  // Método responsávelp por retornar todos os setores ativos
  public static function getSetores(){
    return (new Database('sosmaqueiro', 'setor'))->select('*', "`status` = 'A'")->fetchAll(\PDO::FETCH_CLASS, self::class);
  }

  // Método responsável por retornar um setor pelo código
  public static function getSetorByCodigo($codigo){
    return (new Database('sosmaqueiro', 'setor'))->select('*', "codigo = '" . $codigo . "'")->fetchObject(self::class);
  }
}
