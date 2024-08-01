<?php

namespace App\Model\CentralServicos;

use App\Db\Database;

class Perfil
{
  // Método responsável por retornar o perfil pelo código
  public static function getPerfilByCodigo($codigo)
  {
    return (new Database('centralservicos', 'perfil'))->select('*', "codigo = '" . $codigo . "'")->fetchObject(self::class);
  }
  // Método responsável por retornar todos os perfis existentes
  public static function getPerfis()
  {
    return (new Database('centralservicos', 'perfil'))->select()->fetchAll(\PDO::FETCH_CLASS, self::class);
  }
  // Método responsável por retornar todos os perfis ativos existentes
  public static function getActivedPerfis()
  {
    return (new Database('centralservicos', 'perfil'))->select('*', "`status` = 'A'")->fetchAll(\PDO::FETCH_CLASS, self::class);
  }
  // Método responsável por retornar um perfil pelo seu id
  public static function getPerfilById($id)
  {
    return (new Database('centralservicos', 'perfil'))->select('*', 'id = ' . $id)->fetchObject(self::class);
  }
  // Método responsável por atualizar um cadastro de um sistema já existente
  public static function atualizarPerfil($perfil)
  {
    return (new Database('centralservicos', 'perfil'))->update('id = ' . $perfil->id,  [
      "nome" => $perfil->nome,
      "codigo" => $perfil->codigo,
      "descricao" => $perfil->descricao,
      "status" => $perfil->status,
    ]);
  }
  // Método responsável por cadastrar um novo perfil
  public static function cadastrarPerfil($perfil)
  {
    return $perfil->id = (new Database('centralservicos', 'perfil'))->insert([
      "nome" => $perfil->nome,
      "codigo" => $perfil->codigo,
      "descricao" => $perfil->descricao,
    ]);
  }
}
