<?php

namespace App\Model\SisNot;

use App\Db\Database;

class Resposta
{
  public static function getAnswersByQuestion($questionId)
  {
    return (new Database('sisnot', 'resposta'))->select('*', "id_pergunta = $questionId AND status = 1")->fetchAll(\PDO::FETCH_CLASS, self::class);
  }

  public static function getAnswersNotificationByIdQuestion(int $idNotification, int $idQuestion)
  {

    return (new Database('sisnot', "(SELECT 
    TRUE as respondido, r.*, np.valor as resposta
    FROM
    notificacao_resposta np, resposta r
    WHERE
    np.id_notificacao = $idNotification AND np.id_pergunta = $idQuestion AND np.id_resposta = r.id
    UNION 
    SELECT
      FALSE as respondido,
      r.*,
      null
    FROM
      resposta r, pergunta p 
    WHERE
    p.id = $idQuestion AND
    r.id NOT IN (SELECT id_resposta FROM notificacao_resposta WHERE id_pergunta = $idQuestion AND id_notificacao = $idNotification) AND r.id_pergunta = p.id) tabela_resposta"))->select('*', null, null, null, "tabela_resposta.valor")->fetchAll(\PDO::FETCH_CLASS, self::class);
  }
}
