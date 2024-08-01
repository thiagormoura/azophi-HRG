<?php

namespace App\Model\EscalaMedica;

use App\Db\Database;
use App\Db\SmartPainel;

class EscalaMedicaModel
{

    public static function getDoctorsRegistereds($where = null, $limit = null, $order = null)
    {
        return (new Database('medicos_ps', 'medicos m, especialidade e'))->select("id_med,
            crm_med as CRM, 
            nome_med as MEDICO_NOME, 
            UPPER(nome_esp) as 'MEDICO_ESPECIALIDADE',
            (select group_concat(rqe_cod, ' ') FROM RQE as r where m.crm_med = r.rqe_psv_crm) as 'rqe_cod'", 
        "m.fk_id_esp = e.id_esp and status = 0".(is_null($where) ? "" : $where), is_null($limit) ? null : $limit, null, is_null($order) ? null : $order)->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getDoctors()
    {
        return (new SmartPainel('psv, usr'))->select("DISTINCT PSV_CRM as CRM, UPPER (PSV_APEL) as 'NOME'",
            "usr_psv = psv_cod 
            AND PSV_CONSELHO = 'CRM' 
            AND usr_status = 'A'", null, null, "nome")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function insertDoctorInDuty($data)
    {
        return (new Database('GestaoLeitos', 'medicos'))->insert(
            [
                'nome_med' => $data['nome'],
                'crm_med' => $data['crm'],
                "fk_id_esp" => $data['especialidades']
            ]
        );
    }
}
