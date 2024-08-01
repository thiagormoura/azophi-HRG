<?php

namespace App\Model\Inutri;

use App\Db\Database;
use DateTime;

class Pedido
{
  // Método responsavel por inserir um novo pedido
  public static function insertPedido($solicitante, $cardapio, $tipo, $destinatario, $unidade, $leito, $observacao, $itemAdicional, $dataSolicitada, $registro = null)
  {
    return (new Database('inutricao', 'pedido'))->insert([
      'id_solicitante' =>  $solicitante,
      'id_cardapio' => $cardapio,
      'tipo_pedido' =>  $tipo,
      'destinatario' =>  $destinatario,
      'registro_paciente' => $registro,
      'unidade' => $unidade,
      'leito' => $leito,
      'observacao' => $observacao,
      'item_adicional' => $itemAdicional,
      'situacao' => 'pendente',
      'data_pendente' => $dataSolicitada,
    ]);
  }

  // Método responsavel por inserir as comidas de determinado pedido
  public static function insertComidaPedido($idPedido, $idComida, $porcao)
  {
    return (new Database('inutricao', 'comida_pedido'))->insert([
      'id_pedido' =>  $idPedido,
      'id_comida  ' => $idComida,
      'porcao_comida' => $porcao,
    ]);
  }

  // Método responsável por retornar os pedidos nas últimas 12hrs por determinada situação
  public static function getAllPedidosByDate($dataInicio, $dataFim)
  {
    return (new Database('inutricao', 'pedido'))->select("*", "data_pendente BETWEEN '$dataInicio' AND '$dataFim'", null, null, 'data_pendente ASC')->fetchAll(\PDO::FETCH_OBJ);
  }

  // Método responsável por retornar os pedidos nas últimas 12hrs por determinada situação
  public static function getCurrentPedidos($situation)
  {
    return (new Database('inutricao', 'pedido p INNER JOIN cardapio c ON c.id = p.id_cardapio'))->select("p.*, c.nome as nome_cardapio", "data_$situation > DATE_SUB(NOW(), INTERVAL 12 HOUR) AND situacao = '$situation'", null, null, 'data_pendente DESC')->fetchAll(\PDO::FETCH_OBJ);
  }

  public static function getPedidosBySituationAndDays($situation, $days)
  {
    return (new Database('inutricao', 'pedido p INNER JOIN cardapio c ON c.id = p.id_cardapio'))->select("p.*, c.nome as nome_cardapio", "data_$situation > DATE_SUB(NOW(), INTERVAL $days DAY) AND situacao = '$situation'", null, null, 'data_pendente DESC')->fetchAll(\PDO::FETCH_OBJ);
  }

  // Método responsável por retornar os pedidos por usuário
  public static function getPedidoBySolicitanteId($idSolicitante)
  {
    return (new Database('inutricao', 'pedido p INNER JOIN cardapio c ON c.id = p.id_cardapio'))->select("p.*, c.nome as nome_cardapio", "p.id_solicitante = $idSolicitante", null, null, "data_pendente DESC")->fetchAll(\PDO::FETCH_OBJ);
  }

  // Método responsável por retornar os pedidos por determinada situação
  public static function getPedidosBySituation($situation)
  {
    return (new Database('inutricao', 'pedido p INNER JOIN cardapio c ON c.id = p.id_cardapio'))->select("p.*, c.nome as nome_cardapio", "situacao = '$situation'", null, null, "data_$situation DESC")->fetchAll(\PDO::FETCH_OBJ);
  }

  // Método responsável por retornar o pedido por id
  public static function getPedidoById($id)
  {
    return (new Database('inutricao', 'pedido'))->select('*', 'id = ' . $id)->fetch(\PDO::FETCH_OBJ);
  }

  // Método responsável por retornar o pedido por registro do paciente
  public static function getPedidosPacienteByRegistro($registro)
  {
    return (new Database('inutricao', 'pedido'))->select('*', "registro_paciente = $registro")->fetchAll(\PDO::FETCH_OBJ);
  }

  // Método responsável por retornar o pedido por registro do paciente
  public static function getLastPacientePedido($registro)
  {
    return (new Database('inutricao', 'pedido p INNER JOIN cardapio c ON c.id = p.id_cardapio'))->select('p.*, c.nome as nome_cardapio', "data_pendente = (select MAX(data_pendente) from pedido WHERE registro_paciente = $registro)")->fetch(\PDO::FETCH_OBJ);
  }

  // Método responsável por retornar os pedidos entre duas datas e por uma determinada situação
  public static function getPedidosByRange($situation, $dates)
  {
    return (new Database('inutricao', 'pedido p INNER JOIN cardapio c ON c.id = p.id_cardapio'))->select("p.*, c.nome as nome_cardapio, data_$situation as data", "situacao = '" . $situation . "' AND data_" . $situation . " BETWEEN '" . $dates[0] . " 00:00:00' AND '" . $dates[1] . " 23:59:59'")->fetchAll(\PDO::FETCH_OBJ);
  }

  // Método responsável por atualizar a situação de determinado pedido
  public static function updatePedido($id, $atendente, $situation, $date_field, $date)
  {
    return (new Database('inutricao', 'pedido'))->update('id = ' . $id,  [
      'id_atendente' => $atendente,
      'situacao' => $situation,
      $date_field => $date
    ]);
  }

  // Método responsável por cancelar determinado pedido justificando o motivo e o cancelador
  public static function cancelPedido($id, $motivo, $cancelador)
  {
    setlocale(LC_ALL, 'pt_BR.UTF-8');
    date_default_timezone_set('America/Fortaleza');

    return (new Database('inutricao', 'pedido'))->update('id = ' . $id,  [
      'situacao' => 'cancelado',
      'data_cancelado' => date('Y-m-d H:i:s'),
      'id_atendente' => $cancelador,
      'motivo_cancel' => $motivo
    ]);
  }
}
