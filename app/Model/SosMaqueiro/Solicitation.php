<?php

namespace App\Model\SosMaqueiro;

use App\Db\Database;

class Solicitation
{
  public static function insertSolicitation($paciente, $setor_solicitante, $destino, $transporte, $id_user_solicitante, $observacao)
  {
    return (new Database('sosmaqueiro', 'solicitacao'))->insert([
      'paciente' => $paciente,
      'setor_solicitante' => $setor_solicitante,
      'destino' => $destino,
      'id_transporte' => $transporte,
      'id_user_solicitante' => $id_user_solicitante,
      'observacao' => $observacao,
      'status' => 'aberto',
    ]);
  }

  public static function insertResource($id_solicitacao, $id_recurso)
  {
    return (new Database('sosmaqueiro', 'solicitacao_recurso'))->insert([
      'id_solicitacao' => $id_solicitacao,
      'id_recurso' => $id_recurso,
    ]);
  }

  // Método responsável por atualizar o chamado
  public static function insertTransferencia($idSolicitacao, $id_antigo_atendente, $id_novo_atendente, $status = 'atribuicao')
  {
    return (new Database('sosmaqueiro', 'solicitacao_transferencia'))->insert([
      'id_solicitacao' => $idSolicitacao,
      'id_antigo_atendente' => $id_antigo_atendente,
      'id_novo_atendente' => $id_novo_atendente,
      'id_novo_atendente' => $id_novo_atendente,
      'status' => $status,
    ]);
  }

  public static function insertPausaChamado($id_solicitacao, $motivo_pausa)
  {
    return (new Database('sosmaqueiro', 'solicitacao_pausa'))->insert([
      'id_solicitacao' => $id_solicitacao,
      'motivo_pausa' => $motivo_pausa,
    ]);
  }

  public static function getMotivoPausa($id_solicitacao)
  {
    return (new Database('sosmaqueiro', 'solicitacao_pausa'))->select('motivo_pausa', 'id_solicitacao = ' . $id_solicitacao . " and `status` = 'pausado'")->fetchColumn();
  }

  public static function getSolicitationsBySolicitant($idSolicitant)
  {
    return (new Database(
      'sosmaqueiro',
      '
      solicitacao s left join `centralservicos`.usuario atendente on atendente.id = s.id_user_atendente 
      left join `centralservicos`.usuario solicitante on solicitante.id = s.id_user_solicitante,
      transporte t,
      sosmaqueiro.setor setor_solicitante
      '
    ))->select(
      '
        s.id,
        s.paciente,
        s.destino,
        s.`status`,
        s.observacao,
        s.dthr_solicitacao,
        s.dthr_atualizacao,
        atendente.id as id_usuario_atendente,
        atendente.nome as usuario_atendente,
        solicitante.nome as usuario_solicitante,
        solicitante.id as id_usuario_solicitante,
        setor_solicitante.nome as setor_solicitante,
        t.nome as transporte
      ',
      "
      s.id_transporte = t.id AND
      s.setor_solicitante = setor_solicitante.codigo AND
      id_user_solicitante = " . $idSolicitant . " AND
      dthr_solicitacao BETWEEN DATE_SUB(NOW(), INTERVAL 36 HOUR) AND NOW()",
      null,
      null,
      's.dthr_atualizacao asc'
    )->fetchAll(\PDO::FETCH_CLASS, self::class);
  }
  public static function getChamadoByAtendente($id_atendente)
  {
    return (new Database(
      'sosmaqueiro',
      '
        solicitacao s,
        transporte t,
        sosmaqueiro.setor setor_solicitante,
        centralservicos.usuario usuario_solicitante,
        centralservicos.usuario usuario_atendente,
      '
    ))->select(
      '
        s.id,
        s.paciente,
        usuario_solicitante.nome as usuario_solicitante,
        usuario_atendente.id as id_usuario_atendente,
        usuario_atendente.nome as usuario_atendente,
        setor_solicitante.nome as setor_solicitante,
        s.destino,
        t.nome as transporte,
        s.`status`,
        s.observacao,
        s.dthr_atualizacao
      ',
      "
        s.id_transporte = t.id AND
        s.setor_solicitante = setor_solicitante.codigo AND
        s.id_user_atendente = usuario_atendente.id AND
        s.id_user_solicitante = usuario_solicitante.id AND
        s.id_user_atendente = " . $id_atendente,
      null,
      null,
      's.dthr_atualizacao asc'
    )->fetchObject(self::class);
  }

  // Método responsavel retornar os chamados por status
  public static function getChamadosByStatus($status)
  {
    return (new Database('sosmaqueiro', '
      solicitacao s, 
      transporte t,
      sosmaqueiro.setor setor_solicitante
    '))->select(
      '
      s.id, s.paciente, 
      s.id_user_solicitante as id_usuario_solicitante, 
      s.id_user_atendente as id_usuario_atendente, 
      setor_solicitante.nome as setor_solicitante, 
      s.motivo_cancelamento,
      s.destino, 
      `local`.nome as local_paciente, 
      t.nome as transporte, s.`status`, 
      s.observacao, s.dthr_atualizacao, s.dthr_solicitacao 
      ',
      "
	    s.id_transporte = t.id AND 
	    s.setor_solicitante = setor_solicitante.codigo AND
      s.`status` = '" . $status . "'"
    )->fetchAll(\PDO::FETCH_CLASS, self::class);
  }
  // Método responsavel retornar todos os chamados
  public static function getAllSolicitations(string $limit = null, string $search = null)
  {
    return (new Database(
      'sosmaqueiro',
      '
      solicitacao s left join `centralservicos`.usuario atendente on atendente.id = s.id_user_atendente 
      left join `centralservicos`.usuario solicitante on solicitante.id = s.id_user_solicitante
      left join `centralservicos`.usuario encerrador on encerrador.id = s.id_user_encerrador,
      transporte t,
      sosmaqueiro.setor setor_solicitante
      '
    ))->select(
      '
        s.id,
        s.paciente,
        s.destino,
        s.`status`,
        s.observacao,
        s.dthr_solicitacao,
        s.dthr_atualizacao,
        atendente.id as id_usuario_atendente,
        atendente.nome as usuario_atendente,
        solicitante.nome as usuario_solicitante,
        solicitante.id as id_usuario_solicitante,
        concat(encerrador.nome, " ", encerrador.sobrenome) as usuario_encerrador,
        encerrador.id as id_usuario_encerrador,
        setor_solicitante.nome as setor_solicitante,
        t.nome as transporte
      ',
      "
        s.id_transporte = t.id AND
        s.setor_solicitante = setor_solicitante.codigo
      ",
      $limit,
      null,
      's.dthr_atualizacao asc'
    )->fetchAll(\PDO::FETCH_CLASS, self::class);
  }
  // Método responsavel retornar todos os chamados
  public static function getChamadoById($id)
  {
    return (new Database('sosmaqueiro', '
      solicitacao s, 
      transporte t,
      sosmaqueiro.setor setor_solicitante
    '))->select(
      '
      s.id, s.paciente, 
      s.id_user_solicitante as id_usuario_solicitante, 
      s.id_user_atendente as id_usuario_atendente, 
      setor_solicitante.nome as setor_solicitante, 
      s.motivo_cancelamento,
      s.destino,
      t.nome as transporte, s.`status`, 
      s.observacao, s.dthr_atualizacao, s.dthr_solicitacao 
      ',
      "
	    s.id_transporte = t.id AND 
	    s.setor_solicitante = setor_solicitante.codigo AND
      s.id = " . $id,
      null,
      null,
      's.dthr_atualizacao asc'
    )->fetchObject(self::class);
  }
  // Método responsável por retornar o histórico de uma solicitação
  public static function getHistoricoBySolicitacao($id_solicitacao)
  {
    return (new Database('sosmaqueiro', 'solicitacao_historico'))->select('*', 'id_solicitacao = ' . $id_solicitacao)->fetchAll(\PDO::FETCH_OBJ);
  }

  // Método responsável por atualizar o chamado
  public static function updateChamado($idSolicitacao, $idUser, $status, $isFinish = false)
  {
    return (new Database('sosmaqueiro', 'solicitacao'))->update('id = ' . $idSolicitacao,  [
      'status' => $status,
      ($isFinish ? 'id_user_encerrador' :'id_user_atendente') => $idUser
    ]);
  }
  // Método responsável por atualizar o chamado
  public static function transferirChamado($idSolicitacao, $idUser)
  {
    return (new Database('sosmaqueiro', 'solicitacao'))->update('id = ' . $idSolicitacao,  [
      'id_user_atendente' => $idUser,
    ]);
  }

  // Método responsável por atualizar o chamado
  public static function continuarChamado($id_solicitacao)
  {
    return (new Database('sosmaqueiro', 'solicitacao_pausa'))->update('id_solicitacao = ' . $id_solicitacao,  [
      'status' => 'continuado',
    ]);
  }

  // Método responsável por atualizar o chamado
  public static function cancelarChamado($id_solicitacao, $id_user_atendente, $motivo_cancelamento)
  {
    return (new Database('sosmaqueiro', 'solicitacao'))->update('id = ' . $id_solicitacao,  [
      'status' => 'cancelado',
      'id_user_atendente' => $id_user_atendente,
      'motivo_cancelamento' => $motivo_cancelamento,
    ]);
  }


  public static function getChamadosHoje()
  {
    return (new Database('sos_maqueiro', 'chmd'))->select(
      "status, 
      count(*) as qtd,
      round(sum( CASE
        WHEN data_hora_atendimento IS NULL THEN timestampdiff(MINUTE, data_hora_chamado, NOW())
        ELSE TIMESTAMPDIFF(MINUTE, data_hora_chamado, data_hora_atendimento)
      END ) / count(*) , 0) AS tempo_a,
      round(sum(CASE
        WHEN data_hora_encerramento IS NULL THEN timestampdiff(MINUTE, data_hora_chamado, NOW())
        ELSE TIMESTAMPDIFF(MINUTE, data_hora_chamado, data_hora_encerramento)
      END ), 0) AS tempo_b",
      "(chmd.data_hora_chamado between  DATE_FORMAT(NOW() ,'%Y-%m-%d 00:00:00') AND NOW())",
      null,
      "status"
    )->fetchAll(\PDO::FETCH_ASSOC);
  }

  public static function getChamados7Dias()
  {
    return (new Database('sos_maqueiro', 'chmd'))->select(
      "count(*) as qtd,
      round(sum( CASE
        WHEN data_hora_atendimento IS NULL THEN timestampdiff(MINUTE, data_hora_chamado, NOW())
        ELSE TIMESTAMPDIFF(MINUTE, data_hora_chamado, data_hora_atendimento)
      END ) / count(*) , 0) AS tempo_a",
      "chmd.data_hora_chamado BETWEEN NOW() - INTERVAL 7 DAY AND NOW()"
    )->fetch(\PDO::FETCH_ASSOC);
  }

  public static function getChamadosPorHora()
  {
    return (new Database('sos_maqueiro', 'chmd'))->select(
      "HOUR(data_hora_chamado) as hora,
      count(*) as qtd",
      "chmd.status <> 'C' AND (chmd.data_hora_chamado BETWEEN NOW() - INTERVAL 15 DAY AND NOW())",
      null,
      "HOUR(data_hora_chamado)",
      "hora asc"
    )->fetchAll(\PDO::FETCH_ASSOC);
  }

  public static function getChamadosPorDiasDaSemana()
  {
    return (new Database('sos_maqueiro', 'chmd'))->select(
      "CASE 
        WHEN dayofweek(data_hora_chamado) = 1 THEN 'Dom'
        WHEN dayofweek(data_hora_chamado) = 2 THEN 'Seg'
        WHEN dayofweek(data_hora_chamado) = 3 THEN 'Ter'
        WHEN dayofweek(data_hora_chamado) = 4 THEN 'Qua'
        WHEN dayofweek(data_hora_chamado) = 5 THEN 'Qui'
        WHEN dayofweek(data_hora_chamado) = 6 THEN 'Sex'
        WHEN dayofweek(data_hora_chamado) = 7 THEN 'Sab'
      end as dia,
      count(*) as qtd",
      "chmd.status <> 'C' AND (chmd.data_hora_chamado BETWEEN NOW() - INTERVAL 15 DAY AND NOW())",
      null,
      "dayname(data_hora_chamado)",
      "dayofweek(data_hora_chamado)"
    )->fetchAll(\PDO::FETCH_ASSOC);
  }
}
