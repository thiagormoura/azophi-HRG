<?php

namespace App\Model\Inutri;

use App\Db\Database;

class Cardapio
{

  // Método responsavel por inserir um novo grupo
  public static function insertGroup($descricao, $quantidade)
  {
    return (new Database('inutricao', 'grupo_cardapio'))->insert([
      'descricao' => $descricao,
      'quantidade_itens' => $quantidade
    ]);
  }
  // Método responsavel por inserir um novo cardápio
  public static function insertCardapio($nome, $horaInicio, $horaLimite, $codigo = null)
  {
    return (new Database('inutricao', 'cardapio'))->insert([
      'nome' => $nome,
      'hora_inicio' => $horaInicio,
      'hora_limite' => $horaLimite,
      'status' => 1,
      'codigo_externo' => $codigo
    ]);
  }
  // Método responsavel por inserir uma nova relação de cardápio e perfil
  public static function insertCardapioPerfil($idCardapio, $idPerfil)
  {
    return (new Database('inutricao', 'perfil_cardapio'))->insert([
      'id_cardapio' => $idCardapio,
      'id_perfil' => $idPerfil,
    ]);
  }

  // Método responsavel por inserir uma nova relação de cardápio e perfil
  public static function getPerfilCardapio($idCardapio, $idPerfil)
  {
    return (new Database('inutricao', 'perfil_cardapio'))->select('*', "id_perfil = $idPerfil and id_cardapio = $idCardapio", null)->fetchColumn();
  }

  // Método responsável por retornar os dias que o cardápio está preenchido
  public static function getDaysOfItemByDate($id, $date)
  {
    return (new Database('inutricao', 'item_cardapio'))->select("data_semana", "data_semana = '$date' AND id_cardapio = $id", null, "data_semana", null)->fetchColumn();
  }

  // Método responsável por retornar os itens dos pedidos
  public static function getCardapios()
  {
    return (new Database('inutricao', 'cardapio'))->select("*", null, null, null, "nome")->fetchAll(\PDO::FETCH_OBJ);
  }

  // Método responsável por retornar o cardápio por id
  public static function getCardapioById($id)
  {
    return (new Database('inutricao', 'cardapio'))->select("*", "id = $id", null, null, "nome")->fetch(\PDO::FETCH_OBJ);
  }

  // Método responsável por retornar os cardápios por perfil
  public static function getCardapioByPerfil($idPerfil)
  {
    return (new Database('inutricao', 'cardapio c INNER JOIN perfil_cardapio pc ON pc.id_cardapio = c.id'))
      ->select("c.*", "c.status = '1' AND pc.id_perfil = $idPerfil", null, null, "hora_limite, nome")->fetch(\PDO::FETCH_OBJ);
  }

  // Método responsável por retornar os cardápios por perfil
  public static function getCardapioByCodExtAndPerfil($perfil, $codigo)
  {
    return (new Database('inutricao', 'cardapio c INNER JOIN perfil_cardapio pc ON pc.id_cardapio = c.id'))
      ->select("c.*", "c.status = '1' AND pc.id_perfil = $perfil AND c.codigo_externo = '".trim($codigo)."'", null, null, "hora_limite, nome")->fetch(\PDO::FETCH_OBJ);
  }

  // Método responsável por retornar os cardápios por perfil
  public static function getCardapiosByPerfil($idPerfil)
  {
    return (new Database('inutricao', 'cardapio c INNER JOIN perfil_cardapio pc ON pc.id_cardapio = c.id'))
      ->select("c.*", "c.status = '1' AND pc.id_perfil = $idPerfil", null, null, "hora_limite, nome")->fetchAll(\PDO::FETCH_OBJ);
  }

  // Método responsável por retornar os grupos de determinado cardápio por id e data
  public static function getCardapioGroupsByIdAndDate($id, $date)
  {
    return (new Database('inutricao', 'item_cardapio ic INNER JOIN grupo_cardapio gc on gc.id = ic.id_grupo'))
      ->select("gc.*", "ic.data_semana = '$date' AND ic.id_cardapio = $id", null, "ic.id_grupo", null)->fetchAll(\PDO::FETCH_OBJ);
  }

  // Método responsável por atualizar o status do cardápio
  public static function updateCardapio($id, $nome, $horaInicio, $horaLimite, $codigo = null)
  {
    return (new Database('inutricao', 'cardapio'))->update('id = ' . $id,  [
      'nome' => $nome,
      'hora_inicio' => $horaInicio,
      'hora_limite' => $horaLimite,
      'codigo_externo' => $codigo
    ]);
  }

  // Método responsável por atualizar o cardápio
  public static function updateStatus($id, $status)
  {
    return (new Database('inutricao', 'cardapio'))->update('id = ' . $id,  [
      'status' => $status,
    ]);
  }

  // Método responsável por deletar os itens cardápios de determinado dia
  public static function deleteGroupById($id)
  {
    return (new Database('inutricao', 'grupo_cardapio'))->delete("id = $id");
  }
  // Método responsável por deletar os perfis relacionados aquele cardápio
  public static function deletePerfisCardapioById($id)
  {
    return (new Database('inutricao', 'perfil_cardapio'))->delete("id_cardapio = $id");
  }
}
