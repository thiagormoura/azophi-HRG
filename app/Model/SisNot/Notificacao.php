<?php

namespace App\Model\SisNot;

use App\Db\Database;

class Notificacao
{

    // Método responsável por cadastrar um novo usuário
    public function create()
    {
        $this->id = (new Database('sisnot', 'notificacao'))->insert(
            [
                'id_incidente' => $this->id_incidente,
                'data_hora' => $this->data_hora,
                'registro_paciente' => $this->registro_paciente,
                'setor_origem' => $this->setor_origem,
                'setor_notificador' => $this->setor_notificador,
            ]
        );
    }

    // Método responsável para inserir uma resposta de uma notificação
    public static function createNotificationAnswer(int $idNotification, int $idQuestion, int $idAnswer, string $value)
    {
        return (new Database('sisnot', 'notificacao_resposta'))->insert(
            [
                'id_notificacao' => $idNotification,
                'id_pergunta' => $idQuestion,
                'id_resposta' => $idAnswer,
                'valor' => $value,
            ]
        );
    }

    public static function getNotificationById(int $id)
    {
        return (new Database('sisnot', 'notificacao'))->select('*', "id = $id")->fetchObject(self::class);
    }

    public static function getNotificationByDates($startDate, $endDate)
    {
        return (new Database('sisnot', "notificacao_resposta np right join notificacao n on np.id_notificacao = n.id"))->select('*', "n.data_criacao BETWEEN '$startDate 00:00:00' AND '$endDate 23:59:59'", null, 'n.data_criacao')->fetchAll(\PDO::FETCH_CLASS, self::class);
    }

    // function to get the list of all notifications by date and in some ids of incidents
    public static function getNotificationByDatesAndIncidents($startDate, $endDate, $incidents)
    {
        return (new Database('sisnot', "notificacao_resposta np right join notificacao n on np.id_notificacao = n.id"))->select('*', "n.data_criacao BETWEEN '$startDate 00:00:00' AND '$endDate 23:59:59' and n.id_incidente in ($incidents)")->fetchAll(\PDO::FETCH_CLASS, self::class);
    }

    public static function getLastYearId($ano)
    {
        return (new Database('sisnot', "notificacao"))->select('max(id) as lastId', "year(data_criacao) = ".$ano)->fetchAll(\PDO::FETCH_ASSOC);
    }
}
