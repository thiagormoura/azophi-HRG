<?php

namespace App\Model\SisNot;

use App\Db\Database;

class Incidente
{

  public static function getActivedIncidentes()
  {
    return (new Database('sisnot', 'incidente'))->select('*', "status = 1", null, null, "descricao asc")->fetchAll(\PDO::FETCH_CLASS, self::class);
  }

  public static function getIncidents()
  {
    return (new Database('sisnot', 'incidente'))->select('*')->fetchAll(\PDO::FETCH_CLASS, self::class);
  }

  public static function getIncidentById($incidentId)
  {
    return (new Database('sisnot', 'incidente'))->select('*', "id = $incidentId")->fetchObject(Incidente::class);
  }

  public static function getIncidentesByDate($startDate, $endDate, $incidentes = null)
  {
    if (!empty($incidentes))
      $whereIn = " and i.id in ($incidentes)";

    return (new Database('sisnot', "notificacao_resposta np, incidente i, notificacao n"))->select('i.*', "i.id = n.id_incidente AND n.id = np.id_notificacao AND n.data_criacao BETWEEN '$startDate 00:00:00' AND '$endDate 23:59:59' $whereIn")->fetchAll(\PDO::FETCH_ASSOC);
  }
}
