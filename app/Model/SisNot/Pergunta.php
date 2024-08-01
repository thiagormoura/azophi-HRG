<?php

namespace App\Model\SisNot;

use App\Db\Database;

class Pergunta
{
  public static function getActiveQuestions()
  {
    return (new Database('sisnot', 'pergunta'))->select('*', "status = 1 AND subpergunta = 0", null, null, "ordem asc")->fetchAll(\PDO::FETCH_CLASS, self::class);
  }

  public static function getQuestionsByIncident($incidentId)
  {
    return (new Database('sisnot', 'pergunta p, pergunta_incidente pi'))->select('p.*', "status = 1 AND pi.id_incidente = $incidentId AND pi.id_pergunta = p.id", null, null, "ordem asc")->fetchAll(\PDO::FETCH_CLASS, self::class);
  }

  public static function getQuestionByAnswer($answerId)
  {
    return (new Database('sisnot', 'pergunta p, subpergunta sp'))->select('p.*', "sp.id_resposta = $answerId AND p.id = sp.id_pergunta")->fetchObject(self::class);
  }

  public static function getQuestionsByAnswer($answerId)
  {
    return (new Database('sisnot', 'pergunta p, subpergunta sp'))->select('p.*', "sp.id_resposta = $answerId AND p.id = sp.id_pergunta")->fetchAll(\PDO::FETCH_CLASS, self::class);
  }

  public static function getDetailsQuestions()
  {
    return (new Database('sisnot', 'pergunta'))->select('*', "status = 1 AND detalhe_incidente = 1 AND subpergunta = 0")->fetchAll(\PDO::FETCH_CLASS, self::class);
  }
}
