<?php

namespace App\Model\CentralServicos;

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
    $this->id = (new Database('centralservicos', 'local'))->insert([
      'codigo' => $this->codigo,
      'nome' => $this->nome,
      'status' => $this->status,
    ]);
  }

  // Método responsável por atualizar o cadastro de um local já existente
  public function atualizar()
  {
    return (new Database('centralservicos', 'local'))->update("id = " . $this->id,  [
      'codigo' => $this->codigo,
      'nome' => $this->nome,
      'status' => $this->status,
    ]);
  }


  // Método responsávelp por retornar todos os locais ativos
  public static function getLocaisAndSetores()
  {
    return (new Database('centralservicos', 'local l left join setor s on l.setor_codigo = s.codigo'))->select('s.nome as setor, l.nome as local, l.codigo', "l.`status` = 'A'")->fetchAll(\PDO::FETCH_CLASS, self::class);
  }

  // Método responsávelp por retornar todos os locais ativos
  public static function getLocais()
  {
    return (new Database('centralservicos', '`local`'))->select('*', "`status` = 'A'")->fetchAll(\PDO::FETCH_CLASS, self::class);
  }

  // Método responsável por retornar um local pelo código
  public static function getLocalByCodigo($codigo)
  {
    return (new Database('centralservicos', 'local'))->select('*', "codigo = '" . $codigo . "'")->fetchObject(self::class);
  }
}
