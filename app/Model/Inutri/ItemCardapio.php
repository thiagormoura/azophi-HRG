<?php

namespace App\Model\Inutri;

use App\Db\Database;

class ItemCardapio
{

  // Método responsavel por inserir um novo item do cardápio
  public static function insertComida($nome, $categoria)
  {
    return (new Database('inutricao', 'comida'))->insert([
      'nome' => $nome,
      'categoria_id' => $categoria,
      'status' => 1
    ]);
  }

  // Método responsavel por inserir um novo item do cardápio
  public static function insertItemCardapio($idGrupo, $id, $opcoes, $porcoes, $dataSemana)
  {
    return (new Database('inutricao', 'item_cardapio'))->insert([
      'id_grupo' => $idGrupo,
      'id_cardapio' => $id,
      'id_comida' => $opcoes,
      'porcao_comida' => $porcoes,
      'data_semana' => $dataSemana,
    ]);
  }

  // Método responsável por retornar os itens de determinado pedido
  public static function getItensByPedido($idPedido)
  {
    return (new Database('inutricao', 'comida_pedido cp INNER JOIN comida c ON c.id = cp.id_comida'))->select("c.*, cp.porcao_comida as porcao", "id_pedido = $idPedido", null, null, null)->fetchAll(\PDO::FETCH_OBJ);
  }

  // Método responsável por retornar os itens dos pedidos
  public static function getItensPedido()
  {

    return (new Database('inutricao', 'comida co INNER JOIN categoria ca on co.categoria_id = ca.id'))->select("co.*, ca.descricao as categoria_descricao", null, null, null, "co.nome")->fetchAll(\PDO::FETCH_OBJ);
  }

  // Método responsável por retornar os itens dos pedidos
  public static function getActivedItensCardapio()
  {
    return (new Database('inutricao', 'comida co INNER JOIN categoria ca on co.categoria_id = ca.id'))->select("co.*, ca.descricao as categoria_descricao", "status = 1", null, null, "co.nome")->fetchAll(\PDO::FETCH_OBJ);
  }

  // Método responsável por retornar as categorias dos itens
  public static function getCategorias()
  {
    return (new Database('inutricao', 'categoria'))->select("*", null, null, null, "descricao")->fetchAll(\PDO::FETCH_OBJ);
  }

  // Método responsável por retornar o item do cardápio por id
  public static function getItemById($id)
  {

    return (new Database('inutricao', 'comida'))->select("*", "id = $id", null, null, null)->fetch(\PDO::FETCH_OBJ);
  }

  // Método responsável por retornar o item do cardápio por id do cardapio, id do perfil e determinado dia
  public static function getItemByDateAndIds($idCardapio, $idPerfil, $date)
  {
    return (new Database('inutricao', 'item_cardapio ic 
    INNER JOIN perfil_cardapio pc ON pc.id_cardapio = ic.id_cardapio'))->select("*", "pc.id_perfil = $idPerfil and ic.data_semana = '$date'
    AND ic.id_cardapio = $idCardapio", null, null, null)->fetchAll(\PDO::FETCH_OBJ);
  }

  // Método responsavel por inserir uma nova relação de cardápio e perfil
  public static function getComidaItemByDateAndCardapio($date, $idCardapio, $idComida)
  {
    return (new Database('inutricao', 'item_cardapio'))->select('*', "data_semana = '$date' and id_cardapio = $idCardapio and id_comida = $idComida", null)->fetchColumn();
  }

  // Método responsável por retornar os itens do cardápio por id do grupo
  public static function geItemComidaByIdGroup($idGroup)
  {
    return (new Database('inutricao', 'item_cardapio ic INNER JOIN comida c ON c.id = ic.id_comida'))->select("ic.*, c.nome", "ic.id_grupo = $idGroup", null, null, null)->fetchAll(\PDO::FETCH_OBJ);
  }

  // Método responsável por atualizar o status do item do cardápio
  public static function updateStatus($id, $status)
  {
    return (new Database('inutricao', 'comida'))->update('id = ' . $id,  [
      'status' => $status,
    ]);
  }

  // Método responsável por atualizar os itens cardápios
  public static function updateItemCardapio($id, $nome, $categoria)
  {
    return (new Database('inutricao', 'comida'))->update('id = ' . $id,  [
      'nome' => $nome,
      'categoria_id' => $categoria
    ]);
  }

  // Método responsável por deletar os itens cardápios de determinado dia
  public static function deleteItemCardapio($idCardapio, $selectedDate)
  {
    return (new Database('inutricao', 'item_cardapio'))->delete("id_cardapio = $idCardapio AND data_semana = '$selectedDate'");
  }
}
