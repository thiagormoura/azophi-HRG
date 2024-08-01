<?php

namespace App\Model\Inutri;

use App\Db\Database;

class Perfil
{

  // Método responsavel por inserir um novo perfil
  public static function insertPerfil($nome, $padronizado)
  {
    return (new Database('inutricao', 'perfil'))->insert([
      'nome' => $nome,
      'padronizado' => $padronizado,
      'status' => 1,
      'editavel' => 1,
    ]);
  }

  // Método responsavel por definir os perfis de determinado usuários
  public static function insertPerfilUsuario($idUser, $idPerfil, $principal)
  {
    return (new Database('inutricao', 'perfil_usuario'))->insert([
      'id_user' => $idUser,
      'id_perfil' => $idPerfil,
      'principal' => $principal
    ]);
  }

  // Método responsável por retornar o id do perfil principal do usuário pelo id dele
  public static function getIdMainPerfilByUser($id)
  {
    return (new Database('inutricao', 'perfil_usuario'))->select("id_perfil", "id_user = $id AND principal = 1", null, null, null)->fetchColumn();
  }

  // Método responsável por retornar o perfil principal do usuário pelo id dele
  public static function getMainPerfilByUser($id)
  {
    return (new Database('inutricao', 'perfil_usuario pu INNER JOIN perfil p ON pu.id_perfil = p.id'))->select("p.*", "pu.id_user = $id AND pu.principal = 1 AND p.status = 1", null, null, null)->fetch(\PDO::FETCH_OBJ);
  }

  // Método responsável por retornar os perfis secundários do usuário pelo id dele
  public static function getSecondaryPerfisByUser($id)
  {
    return (new Database('inutricao', 'perfil_usuario pu INNER JOIN perfil p ON pu.id_perfil = p.id'))->select("p.*", "pu.id_user = $id AND pu.principal = 0 AND p.status = 1", null, null, null)->fetchAll(\PDO::FETCH_OBJ);
  }

  public static function getCheckedAndAllPerfisByUser($id)
  {
    return (new Database('inutricao', 'perfil_usuario pu, perfil p'))
      ->select("TRUE as vinculado, p.id, p.nome, pu.principal", "pu.id_user = $id AND p.id = pu.id_perfil AND p.status = 1 GROUP BY p.nome
    UNION ALL SELECT FALSE as vinculado, p.id, p.nome, NULL from perfil p WHERE p.id NOT IN (SELECT pu.id_perfil FROM perfil_usuario pu WHERE pu.id_user = $id) AND p.status = 1 GROUP BY p.nome", null, null, null)->fetchAll(\PDO::FETCH_OBJ);
  }

  // Método responsável por retornar um perfil pelo id
  public static function getPerfilById($id)
  {
    return (new Database('inutricao', 'perfil'))->select("*", "id = $id", null, null, null)->fetch(\PDO::FETCH_OBJ);
  }

  // Método responsável por retornar um perfil pelo id
  public static function getPerfilByNome($nome)
  {
    return (new Database('inutricao', 'perfil'))->select("*", "nome = '$nome'", null, null, null)->fetch(\PDO::FETCH_OBJ);
  }

  // Método responsável por retornar todos os perfis
  public static function getPerfis()
  {
    return (new Database('inutricao', 'perfil'))->select("*", null, null, null, null)->fetchAll(\PDO::FETCH_OBJ);
  }

  // Método responsável por retornar todos os perfis ativos
  public static function getActivedPerfis()
  {
    return (new Database('inutricao', 'perfil'))->select("*", 'status = 1', null, null, null)->fetchAll(\PDO::FETCH_OBJ);
  }

  // Método responsável por retornar os perfis por id do cardápio
  public static function getAllPerfisByCardapio($idCardapio)
  {
    return (new Database('inutricao', 'perfil_cardapio pc, perfil p'))
      ->select("TRUE as vinculado, p.id, p.nome", "pc.id_cardapio = $idCardapio AND p.id = pc.id_perfil AND p.status = 1 GROUP BY p.nome
      UNION ALL SELECT FALSE as vinculado, p.id, p.nome from perfil p WHERE p.id NOT IN (SELECT pc.id_perfil FROM perfil_cardapio pc WHERE pc.id_cardapio = $idCardapio) AND p.status = 1 GROUP BY p.nome", null, null, null)->fetchAll(\PDO::FETCH_OBJ);
  }

  // Método responsável por atualizar o status do cardápio
  public static function updateCondition($id, $condition, $value)
  {
    return (new Database('inutricao', 'perfil'))->update('id = ' . $id,  [
      "$condition" => $value,
    ]);
  }

  // Método responsável por atualizar os dados de um perfil
  public static function updatePerfil($id, $nome)
  {
    return (new Database('inutricao', 'perfil'))->update('id = ' . $id,  [
      "nome" => $nome,
    ]);
  }

  // Método responsável por deletar os itens cardápios de determinado dia
  public static function deletAllPerfisUsuario($idUsuario)
  {
    return (new Database('inutricao', 'perfil_usuario'))->delete("id_user = $idUsuario");
  }
}
