<?php

namespace App\Model\GestaoLeitos;

use App\Db\Database;
use App\Db\Smart;

class Solicitacao
{
    public static function getRisksAndPrecautionsInSolicitation(string $id)
    {
        return (new Database('GestaoTeste', 'PRECAUCOES'))->select("PRECAUCAO_VALOR", "idSOLICITACAO = ".$id)->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function setRisksAndPrecautionsInSolicitation(string $data, string $id)
    {
        return (new Database('GestaoTeste', 'PRECAUCOES'))->insert(
            [
                'idSOLICITACAO' => $id,
                'PRECAUCAO_VALOR' => $data
            ]
        );
    }

    public static function getSolicitation(string $id)
    {
        $innerJoin = "SOLICITACOES s
            left join (SELECT idSolicitacao, sectorLiberate, bedLiberate 
                    FROM HISTORICOS 
                    WHERE sectorLiberate is not null 
                    GROUP by idSolicitacao order by dthr_action desc) as hist_unit 
                ON s.idSOLICITACAO = hist_unit.idSolicitacao
            left join (SELECT * FROM HISTORICOS WHERE statusChange = 'A') as hist_registro 
                ON s.idSOLICITACAO = hist_registro.idSolicitacao
            left join `centralservicos`.usuario as user_registro on user_registro.id = hist_registro.idUser
            left join (SELECT * FROM HISTORICOS WHERE statusChange = 'P' order by dthr_action desc) as hist_atendimento 
                ON s.idSOLICITACAO = hist_atendimento.idSolicitacao
            left join `centralservicos`.usuario as user_atendimento on user_atendimento.id = hist_atendimento.idUser
            left join (SELECT * FROM HISTORICOS WHERE statusChange = 'L') as hist_liberacao
                ON s.idSOLICITACAO = hist_liberacao.idSolicitacao
            left join `centralservicos`.usuario as user_liberacao on user_liberacao.id = hist_liberacao.idUser
            left join PACIENTES pac on pac.REGISTRO = s.REGISTRO";

        return (new Database('GestaoTeste', $innerJoin))->select(
            's.*, 
            pac.*,
            hist_registro.dthr_action as SOLICITACAO_DTHR_REGISTRO,
            hist_atendimento.dthr_action as SOLICITACAO_DTHR_ATENDIMENTO,
            hist_liberacao.dthr_action as SOLICITACAO_DTHR_RESERVADO,
            concat(user_registro.nome," ", user_registro.sobrenome) as SOLICITACAO_SOLICITANTE,
            concat(user_atendimento.nome," ", user_atendimento.sobrenome) as USUARIO_ATENDIMENTO,
            concat(user_liberacao.nome," ", user_liberacao.sobrenome) as USUARIO_LIBERACAO,
            hist_unit.sectorLiberate as setorLiberado,
            hist_unit.bedLiberate as leitoLiberado', 

            's.idSOLICITACAO = ' . $id
        )->fetch(\PDO::FETCH_ASSOC);
    }

    public static function getSolicitationCreator($id)
    {
        return (new Database('GestaoTeste', 'HISTORICOS'))->select('idUser as creator', 'idSolicitacao = ' . $id)->fetch(\PDO::FETCH_ASSOC)['creator'];
    }

    public static function getPrecautions(string $id)
    {
        return (new Database('GestaoTeste', 'PRECAUCOES'))->select('PRECAUCAO_VALOR', 'idSOLICITACAO = ' . $id, null, null, "PRECAUCAO_VALOR asc")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getSolicitations(string $limit = null, string $search = null, bool $isCount = false)
    {
        $innerJoin = "SOLICITACOES s
            left join (SELECT idSolicitacao, sectorLiberate 
                    FROM HISTORICOS 
                    WHERE sectorLiberate is not null 
                    GROUP by idSolicitacao order by dthr_action desc) as hist_unit 
                ON s.idSOLICITACAO = hist_unit.idSolicitacao
            left join (SELECT * FROM HISTORICOS WHERE statusChange = 'A') as hist_registro 
                ON s.idSOLICITACAO = hist_registro.idSolicitacao
            left join PACIENTES pac on pac.REGISTRO = s.REGISTRO";

        return (new Database('GestaoTeste', $innerJoin))->select(
            $isCount ? "count(s.idSOLICITACAO) as qtd" : "s.idSOLICITACAO, pac.REGISTRO,
            pac.NOME as PACIENTE_NOME, hist_registro.dthr_action as SOLICITACAO_DTHR_REGISTRO, 
            SOLICITACAO_ACOMODACAO, pac.SEXO as PACIENTE_SEXO,
            SOLICITACAO_STATUS, SOLICITACAO_SETOR, 
            ISCOVID, SOLICITACAO_PED,
            hist_unit.sectorLiberate as UNIDADE_LIBERADA",
            $search,
            $limit,
            null,
            $isCount ? "" : "SOLICITACAO_DTHR_REGISTRO desc"
        )->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getGroupOfSolicitations(string $select, string $group, string $search = null)
    {
        $innerJoin = "SOLICITACOES s
            left join (SELECT idSolicitacao, sectorLiberate 
                    FROM HISTORICOS 
                    WHERE sectorLiberate is not null 
                    GROUP by idSolicitacao order by dthr_action desc) as hist_unit 
                ON s.idSOLICITACAO = hist_unit.idSolicitacao
            left join (SELECT * FROM HISTORICOS WHERE statusChange = 'A') as hist_registro 
                ON s.idSOLICITACAO = hist_registro.idSolicitacao
            left join PACIENTES pac on pac.REGISTRO = s.REGISTRO";

        return (new Database('GestaoTeste', $innerJoin))->select(
            $select,
            $search,
            null,
            $group
        )->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getFiltersHome()
    {
        $innerJoin = "SOLICITACOES s left join (SELECT idSolicitacao, sectorLiberate, dthr_action 
				FROM HISTORICOS 
				WHERE sectorLiberate is not null 
				GROUP by idSolicitacao order by dthr_action desc) as hist_unit 
			ON s.idSOLICITACAO = hist_unit.idSolicitacao";

        return (new Database('GestaoTeste', $innerJoin))->select(
            "s.idSOLICITACAO,
            SOLICITACAO_ACOMODACAO,
            SOLICITACAO_STATUS, 
            SOLICITACAO_SETOR",
            null,
            null,
            null,
            "hist_unit.dthr_action desc"
        )->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getAllSolicitationsWithoutHistoric()
    {
        return (new Database('GestaoTeste', "SOLICITACOES"))->select(
            "SOLICITACOES.idSOLICITACAO as id, 
            SOLICITACAO_PERFIL as perfil, 
            SOLICITACAO_ACOMODACAO as acomodacao, 
            SOLICITACAO_STATUS as status, 
            SOLICITACAO_SETOR as setor")->fetchAll(\PDO::FETCH_ASSOC);
    }


    public static function getAllSolicitations()
    {
        $unionAll = "
                (SELECT 
                SOLICITACOES.idSOLICITACAO as id, 
                SOLICITACAO_PERFIL as perfil, 
                SOLICITACAO_ACOMODACAO as acomodacao, 
                SOLICITACAO_STATUS as status, 
                SOLICITACAO_SETOR as setor,
                hist_registro.dthr_action as data_mudanca,
                hist_registro.statusChange as status_mudanca
            FROM SOLICITACOES
                left join HISTORICOS hist_registro on hist_registro.idSolicitacao = SOLICITACOES.idSOLICITACAO
            WHERE hist_registro.statusChange = 'A'

            UNION ALL
            SELECT 
                SOLICITACOES.idSOLICITACAO as id, 
                SOLICITACAO_PERFIL as perfil, 
                SOLICITACAO_ACOMODACAO as acomodacao, 
                SOLICITACAO_STATUS as status, 
                SOLICITACAO_SETOR as setor,
                hist_atendimento.dthr_action as data_mudanca,
                hist_atendimento.statusChange as status_mudanca
            FROM SOLICITACOES 
                left join (SELECT * FROM HISTORICOS order by dthr_action desc) hist_atendimento 
                    on hist_atendimento.idSolicitacao = SOLICITACOES.idSOLICITACAO
            WHERE hist_atendimento.statusChange = 'P'

            UNION ALL

            SELECT 
                SOLICITACOES.idSOLICITACAO as id, 
                SOLICITACAO_PERFIL as perfil, 
                SOLICITACAO_ACOMODACAO as acomodacao, 
                SOLICITACAO_STATUS as status, 
                SOLICITACAO_SETOR as setor,
                hist_liberacao.dthr_action as data_mudanca,
                hist_liberacao.statusChange as status_mudanca
            FROM SOLICITACOES
                left join HISTORICOS hist_liberacao on hist_liberacao.idSolicitacao = SOLICITACOES.idSOLICITACAO
            WHERE
                hist_liberacao.statusChange = 'L') as historicos
        ";

        return (new Database('GestaoTeste', $unionAll))->select("*", null, null, null, 'historicos.id asc')->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getSolicitationsFromSearchHome(?string $date_inicial, ?string $date_final, ?string $namePerson = null)
    {
        $select = "idSOLICITACAO,
            REGISTRO,
            pac.NOME, 
            SOLICITACAO_DTHR_REGISTRO, 
            SOLICITACAO_SETOR, 
            SOLICITACAO_ACOMODACAO, 
            SOLICITACAO_STATUS, 
            hist_unit.sectorLiberate";

        $dateWhere = "";
        if (isset($date_inicial) && isset($date_final))
            $dateWhere = " AND hist_registro.dthr_action BETWEEN '" . $date_inicial . "' AND '" . $date_final . "'";
        if (!is_null($namePerson))
            $dateWhere .= " AND pac.NOME like '%" . $namePerson . "%'";

        $innerJoin = "SOLICITACOES s
            left join PACIENTES pac on pac.REGISTRO = s.REGISTRO
            left join (SELECT idSolicitacao, sectorLiberate 
                    FROM HISTORICOS 
                    WHERE sectorLiberate is not null 
                    GROUP by idSolicitacao order by dthr_action desc) as hist_unit
                ON s.idSOLICITACAO = hist_unit.idSolicitacao
            left join (SELECT * from HISTORICOS where statusChange = 'A') hist_registro
                ON s.idSOLICITACAO = hist_registro.idSolicitacao";
        
        return (new Database('GestaoTeste', $innerJoin))->select($select, "s.SOLICITACAO_STATUS in ('A', 'P', 'L')" . $dateWhere, null, null, "hist_registro.dthr_action desc")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getSolicitationByStatus(string $status)
    {
        return (new Database('GestaoTeste', 'SOLICITACOES'))->select("*", "SOLICITACAO_STATUS = '" . $status . "'", null, null, "SOLICITACAO_ACOMODACAO desc")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getHistoricoAtendimentoFromNewSolicitation(string $id)
    {
        return (new Database('GestaoTeste', 'HISTORICOS'))->select("idUser as usuario, statusChange, isUserSMART, SMARTUsername, bedLiberate as leito, sectorLiberate as setor, dthr_action as dateAction", "idSolicitacao=$id", null, null, "dthr_action desc")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function createPatient(array $data)
    {
        return (new Database('GestaoTeste', 'PACIENTES'))->insert([
            'REGISTRO' => $data['patientRegistration'],
            'NOME' => $data['patientName'],
            'SEXO' => (strtoupper($data['patientGender']) == "MASCULINO" ? "M" : (strtoupper($data['patientGender']) == "FEMININO" ? "F" : "O")),
            'DTNASC' => $data['patientBirth'],
            'CONVENIO' => $data['patientHealthInsurance'],
            'ACOMODACAO' => $data['patientAccommodation'],
            'DIAGNOSTICO' => $data['patientDiagnosis'],
            "ORIGEM" => 'I'
        ]);
    }

    public static function createSolicitation(array $data)
    {
        return (new Database('GestaoTeste', 'SOLICITACOES'))->insert([
            'idPACIENTE' => $data['idPatient'],
            'SOLICITACAO_PERFIL' => $data['solicitationProfile'],
            'SOLICITACAO_ACOMODACAO' => $data['solicitationAccommodation'],
            'SOLICITACAO_DTHR_ADMISSAO' => $data['solicitationAdmissionDate'],
            'SOLICITACAO_ISOLAMENTO' => strval($data['solicitationIsolation']),
            'SOLICITACAO_MOTIVO' => $data['solicitationReason'],
            'SOLICITACAO_MEDICO_SOLIC' => $data['solicitationDoctor'],
            'SOLICITACAO_PRIORIDADE' => $data['solicitationPriority'],
            'SOLICITACAO_SETOR' => $data['solicitationUnit'],
            'SOLICITACAO_STATUS' => 'A',
            'SOLICITACAO_PED' => $data['solicitationPediatric'],
            'ISCOVID' => strval($data['isCovid']),
            'COVID_SUSPEITO' => $data['covidSuspect'],
            'COVID_OBSERVACAO' => $data['covidObservation'],
            'OUTRAS_INFOS' => $data['otherInformation']
        ]);
    }

    public static function createSolicitationPrecaution(string $id, array $precaution)
    {
        return (new Database('GestaoTeste', 'PRECAUCOES'))->insert([
            'idSOLICITACAO' => $id,
            'PRECAUCAO_VALOR' => $precaution
        ]);
    }

    public static function createSolicitationRisk(string $id, array $risk)
    {
        return (new Database('GestaoTeste', 'PRECAUCOES'))->insert([
            'idSOLICITACAO' => $id,
            'PRECAUCAO_VALOR' => $risk
        ]);
    }

    public static function GeneralInfoAboutSolicitations($date_inicial, $date_final)
    {
        $innerJoin = "SOLICITACOES 
            left join (SELECT * from HISTORICOS where statusChange = 'A') hist_registro 
            on hist_registro.idSolicitacao = SOLICITACOES.idSOLICITACAO";
            
        return (new Database('GestaoTeste', $innerJoin))->select("COUNT(*) AS Total, COUNT(CASE WHEN SOLICITACAO_STATUS = 'A' THEN 1 END) AS EmAberto, COUNT(CASE WHEN SOLICITACAO_STATUS = 'P' THEN 1 END) AS EmAtendimento, COUNT(CASE WHEN SOLICITACAO_STATUS = 'C' OR SOLICITACAO_STATUS = 'E' OR SOLICITACAO_STATUS = 'RC' OR SOLICITACAO_STATUS = 'RC2' THEN 1 END) AS TotalCanceladas, COUNT(CASE WHEN SOLICITACAO_STATUS = 'L' THEN 1 END) AS Liberadas, COUNT(CASE WHEN SOLICITACAO_STATUS = 'RU' OR SOLICITACAO_STATUS = 'RU2' THEN 1 END) AS TotalAdmitidas", "SOLICITACAO_STATUS is not null AND (hist_registro.dthr_action BETWEEN '$date_inicial' AND '$date_final')")->fetch(\PDO::FETCH_ASSOC);
    }

    public static function getContagemTotalSolicitation($date_inicial, $date_final)
    {
        $innerJoin = "SOLICITACOES 
            left join (SELECT * from HISTORICOS where statusChange = 'A') hist_registro 
            on hist_registro.idSolicitacao = SOLICITACOES.idSOLICITACAO";

        return (new Database('GestaoTeste', $innerJoin))->select("COUNT(*) AS CONTAGEM", "SOLICITACAO_STATUS is not null AND SOLICITACAO_ACOMODACAO is not null AND SOLICITACAO_SETOR is not null AND SOLICITACAO_PERFIL is not null AND (hist_registro.dthr_action BETWEEN '$date_inicial' AND '$date_final')")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getContagemByAcomodacaoSolicitation($date_inicial, $date_final)
    {
        $innerJoin = "SOLICITACOES 
            left join (SELECT * from HISTORICOS where statusChange = 'A') hist_registro 
            on hist_registro.idSolicitacao = SOLICITACOES.idSOLICITACAO";

        return (new Database('GestaoTeste', $innerJoin))->select("SOLICITACAO_ACOMODACAO, COUNT(*) AS CONTAGEM", "SOLICITACAO_STATUS is not null AND SOLICITACAO_ACOMODACAO is not null AND (hist_registro.dthr_action BETWEEN '$date_inicial' AND '$date_final')", null, "SOLICITACAO_ACOMODACAO")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getContagemBySetorSolicitation($date_inicial, $date_final)
    {
        $innerJoin = "SOLICITACOES 
            left join (SELECT * from HISTORICOS where statusChange = 'A') hist_registro 
            on hist_registro.idSolicitacao = SOLICITACOES.idSOLICITACAO";

        return (new Database('GestaoTeste', $innerJoin))->select("SOLICITACAO_SETOR, COUNT(*) AS CONTAGEM", "SOLICITACAO_STATUS is not null AND SOLICITACAO_SETOR is not null AND (hist_registro.dthr_action BETWEEN '$date_inicial' AND '$date_final')", null, "SOLICITACAO_SETOR")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getContagemByPerfilSolicitation($date_inicial, $date_final)
    {
        $innerJoin = "SOLICITACOES 
            left join (SELECT * from HISTORICOS where statusChange = 'A') hist_registro 
            on hist_registro.idSolicitacao = SOLICITACOES.idSOLICITACAO";

        return (new Database('GestaoTeste', $innerJoin))->select("SOLICITACAO_PERFIL, COUNT(*) AS CONTAGEM", "SOLICITACAO_STATUS is not null AND SOLICITACAO_PERFIL is not null AND (hist_registro.dthr_action BETWEEN '$date_inicial' AND '$date_final')", null, "SOLICITACAO_PERFIL")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getSolicitacoesByHora($date_inicial, $date_final)
    {
        $select = "COUNT(CASE WHEN HOUR(hist_registro.dthr_action) >= 0 AND HOUR(hist_registro.dthr_action) < 1 THEN 1 END) AS '0title',
        COUNT(CASE WHEN HOUR(hist_registro.dthr_action) >= 1 AND HOUR(hist_registro.dthr_action) < 2 THEN 1 END) AS '1title',
        COUNT(CASE WHEN HOUR(hist_registro.dthr_action) >= 2 AND HOUR(hist_registro.dthr_action) < 3 THEN 1 END) AS '2title',
        COUNT(CASE WHEN HOUR(hist_registro.dthr_action) >= 3 AND HOUR(hist_registro.dthr_action) < 4 THEN 1 END) AS '3title',
        COUNT(CASE WHEN HOUR(hist_registro.dthr_action) >= 4 AND HOUR(hist_registro.dthr_action) < 5 THEN 1 END) AS '4title',
        COUNT(CASE WHEN HOUR(hist_registro.dthr_action) >= 5 AND HOUR(hist_registro.dthr_action) < 6 THEN 1 END) AS '5title',
        COUNT(CASE WHEN HOUR(hist_registro.dthr_action) >= 6 AND HOUR(hist_registro.dthr_action) < 7 THEN 1 END) AS '6title',
        COUNT(CASE WHEN HOUR(hist_registro.dthr_action) >= 7 AND HOUR(hist_registro.dthr_action) < 8 THEN 1 END) AS '7title',
        COUNT(CASE WHEN HOUR(hist_registro.dthr_action) >= 8 AND HOUR(hist_registro.dthr_action) < 9 THEN 1 END) AS '8title',
        COUNT(CASE WHEN HOUR(hist_registro.dthr_action) >= 9 AND HOUR(hist_registro.dthr_action) < 10 THEN 1 END) AS '9title',
        COUNT(CASE WHEN HOUR(hist_registro.dthr_action) >= 10 AND HOUR(hist_registro.dthr_action) < 11 THEN 1 END) AS '10title',
        COUNT(CASE WHEN HOUR(hist_registro.dthr_action) >= 11 AND HOUR(hist_registro.dthr_action) < 12 THEN 1 END) AS '11title',
        COUNT(CASE WHEN HOUR(hist_registro.dthr_action) >= 12 AND HOUR(hist_registro.dthr_action) < 13 THEN 1 END) AS '12title',
        COUNT(CASE WHEN HOUR(hist_registro.dthr_action) >= 13 AND HOUR(hist_registro.dthr_action) < 14 THEN 1 END) AS '13title',
        COUNT(CASE WHEN HOUR(hist_registro.dthr_action) >= 14 AND HOUR(hist_registro.dthr_action) < 15 THEN 1 END) AS '14title',
        COUNT(CASE WHEN HOUR(hist_registro.dthr_action) >= 15 AND HOUR(hist_registro.dthr_action) < 16 THEN 1 END) AS '15title',
        COUNT(CASE WHEN HOUR(hist_registro.dthr_action) >= 16 AND HOUR(hist_registro.dthr_action) < 17 THEN 1 END) AS '16title',
        COUNT(CASE WHEN HOUR(hist_registro.dthr_action) >= 17 AND HOUR(hist_registro.dthr_action) < 18 THEN 1 END) AS '17title',
        COUNT(CASE WHEN HOUR(hist_registro.dthr_action) >= 18 AND HOUR(hist_registro.dthr_action) < 19 THEN 1 END) AS '18title',
        COUNT(CASE WHEN HOUR(hist_registro.dthr_action) >= 19 AND HOUR(hist_registro.dthr_action) < 20 THEN 1 END) AS '19title',
        COUNT(CASE WHEN HOUR(hist_registro.dthr_action) >= 20 AND HOUR(hist_registro.dthr_action) < 21 THEN 1 END) AS '20title',
        COUNT(CASE WHEN HOUR(hist_registro.dthr_action) >= 21 AND HOUR(hist_registro.dthr_action) < 22 THEN 1 END) AS '21title',
        COUNT(CASE WHEN HOUR(hist_registro.dthr_action) >= 22 AND HOUR(hist_registro.dthr_action) < 23 THEN 1 END) AS '22title',
        COUNT(CASE WHEN HOUR(hist_registro.dthr_action) >= 23 THEN 1 END) AS '23title'";

        $innerJoin = "SOLICITACOES 
            left join (SELECT * from HISTORICOS where statusChange = 'A') hist_registro 
            on hist_registro.idSolicitacao = SOLICITACOES.idSOLICITACAO";

        return (new Database('GestaoTeste', $innerJoin))->select($select, "SOLICITACAO_STATUS is not null AND (hist_registro.dthr_action BETWEEN '$date_inicial' AND '$date_final')")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getSolicitacoesByHoraAndSetor($date_inicial, $date_final)
    {
        $select = "SOLICITACAO_SETOR AS 'Setor', 
        COUNT(CASE WHEN HOUR(hist_registro.dthr_action) >= 0 AND HOUR(hist_registro.dthr_action) < 1 THEN 1 END) AS '0title',
        COUNT(CASE WHEN HOUR(hist_registro.dthr_action) >= 1 AND HOUR(hist_registro.dthr_action) < 2 THEN 1 END) AS '1title',
        COUNT(CASE WHEN HOUR(hist_registro.dthr_action) >= 2 AND HOUR(hist_registro.dthr_action) < 3 THEN 1 END) AS '2title',
        COUNT(CASE WHEN HOUR(hist_registro.dthr_action) >= 3 AND HOUR(hist_registro.dthr_action) < 4 THEN 1 END) AS '3title',
        COUNT(CASE WHEN HOUR(hist_registro.dthr_action) >= 4 AND HOUR(hist_registro.dthr_action) < 5 THEN 1 END) AS '4title',
        COUNT(CASE WHEN HOUR(hist_registro.dthr_action) >= 5 AND HOUR(hist_registro.dthr_action) < 6 THEN 1 END) AS '5title',
        COUNT(CASE WHEN HOUR(hist_registro.dthr_action) >= 6 AND HOUR(hist_registro.dthr_action) < 7 THEN 1 END) AS '6title',
        COUNT(CASE WHEN HOUR(hist_registro.dthr_action) >= 7 AND HOUR(hist_registro.dthr_action) < 8 THEN 1 END) AS '7title',
        COUNT(CASE WHEN HOUR(hist_registro.dthr_action) >= 8 AND HOUR(hist_registro.dthr_action) < 9 THEN 1 END) AS '8title',
        COUNT(CASE WHEN HOUR(hist_registro.dthr_action) >= 9 AND HOUR(hist_registro.dthr_action) < 10 THEN 1 END) AS '9title',
        COUNT(CASE WHEN HOUR(hist_registro.dthr_action) >= 10 AND HOUR(hist_registro.dthr_action) < 11 THEN 1 END) AS '10title',
        COUNT(CASE WHEN HOUR(hist_registro.dthr_action) >= 11 AND HOUR(hist_registro.dthr_action) < 12 THEN 1 END) AS '11title',
        COUNT(CASE WHEN HOUR(hist_registro.dthr_action) >= 12 AND HOUR(hist_registro.dthr_action) < 13 THEN 1 END) AS '12title',
        COUNT(CASE WHEN HOUR(hist_registro.dthr_action) >= 13 AND HOUR(hist_registro.dthr_action) < 14 THEN 1 END) AS '13title',
        COUNT(CASE WHEN HOUR(hist_registro.dthr_action) >= 14 AND HOUR(hist_registro.dthr_action) < 15 THEN 1 END) AS '14title',
        COUNT(CASE WHEN HOUR(hist_registro.dthr_action) >= 15 AND HOUR(hist_registro.dthr_action) < 16 THEN 1 END) AS '15title',
        COUNT(CASE WHEN HOUR(hist_registro.dthr_action) >= 16 AND HOUR(hist_registro.dthr_action) < 17 THEN 1 END) AS '16title',
        COUNT(CASE WHEN HOUR(hist_registro.dthr_action) >= 17 AND HOUR(hist_registro.dthr_action) < 18 THEN 1 END) AS '17title',
        COUNT(CASE WHEN HOUR(hist_registro.dthr_action) >= 18 AND HOUR(hist_registro.dthr_action) < 19 THEN 1 END) AS '18title',
        COUNT(CASE WHEN HOUR(hist_registro.dthr_action) >= 19 AND HOUR(hist_registro.dthr_action) < 20 THEN 1 END) AS '19title',
        COUNT(CASE WHEN HOUR(hist_registro.dthr_action) >= 20 AND HOUR(hist_registro.dthr_action) < 21 THEN 1 END) AS '20title',
        COUNT(CASE WHEN HOUR(hist_registro.dthr_action) >= 21 AND HOUR(hist_registro.dthr_action) < 22 THEN 1 END) AS '21title',
        COUNT(CASE WHEN HOUR(hist_registro.dthr_action) >= 22 AND HOUR(hist_registro.dthr_action) < 23 THEN 1 END) AS '22title',
        COUNT(CASE WHEN HOUR(hist_registro.dthr_action) >= 23 THEN 1 END) AS '23title'";

        $innerJoin = "SOLICITACOES 
            left join (SELECT * from HISTORICOS where statusChange = 'A') hist_registro 
            on hist_registro.idSolicitacao = SOLICITACOES.idSOLICITACAO";

        return (new Database('GestaoTeste', $innerJoin))->select($select, "SOLICITACAO_STATUS is not null AND (hist_registro.dthr_action BETWEEN '$date_inicial' AND '$date_final')", null, "SOLICITACAO_SETOR")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getSolicitacoesByTempoMedioAtendimentoInSectors($date_inicial, $date_final)
    {
        $select = "SOLICITACAO_SETOR, 
            AVG(TIME_TO_SEC(TIMEDIFF(hist_atendimento.dthr_action, SOLICITACAO_DTHR_REGISTRO)))/60 AS TempoMedioAtd";

        $innerJoin = "SOLICITACOES 
            left join (SELECT * from HISTORICOS where statusChange = 'A') hist_registro on hist_registro.idSolicitacao = SOLICITACOES.idSOLICITACAO
            left join (SELECT * FROM HISTORICOS where statusChange = 'P' order by dthr_action desc) hist_atendimento on hist_atendimento.idSolicitacao = SOLICITACOES.idSOLICITACAO";

        return (new Database('GestaoTeste', $innerJoin))->select($select, "(hist_registro.dthr_action BETWEEN '" . $date_inicial . "' AND '" . $date_final . "') AND hist_registro.dthr_action IS NOT NULL AND hist_atendimento.dthr_action >= hist_registro.dthr_action", null, "SOLICITACAO_SETOR")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getSolicitacoesByTempoMedioAtendimentoByPerfil($date_inicial, $date_final)
    {
        $select = "SOLICITACAO_SETOR, 
            SOLICITACAO_PERFIL, 
            AVG(TIME_TO_SEC(TIMEDIFF(hist_atendimento.dthr_action, hist_registro.dthr_action)))/60 AS TempoMedioAtd";

        $innerJoin = "SOLICITACOES 
            left join (SELECT * from HISTORICOS where statusChange = 'A') hist_registro on hist_registro.idSolicitacao = SOLICITACOES.idSOLICITACAO
            left join (SELECT * FROM HISTORICOS order by dthr_action desc) hist_atendimento on hist_atendimento.idSolicitacao = SOLICITACOES.idSOLICITACAO";

        return (new Database('GestaoTeste', $innerJoin))->select($select, "(hist_registro.dthr_action BETWEEN '" . $date_inicial . "' AND '" . $date_final . "') AND hist_registro.dthr_action IS NOT NULL AND hist_atendimento.dthr_action >= hist_registro.dthr_action", null, "SOLICITACAO_SETOR, SOLICITACAO_PERFIL")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getSolicitacoesByTempoMedioAtendimentoByAcommodations($date_inicial, $date_final)
    {
        $select = "SOLICITACAO_SETOR, 
            SOLICITACAO_ACOMODACAO, AVG(TIME_TO_SEC(TIMEDIFF(SOLICITACAO_DTHR_ATENDIMENTO, SOLICITACAO_DTHR_REGISTRO)))/60 AS TempoMedioAtd";

        $innerJoin = "SOLICITACOES 
            left join (SELECT * from HISTORICOS where statusChange = 'A') hist_registro on hist_registro.idSolicitacao = SOLICITACOES.idSOLICITACAO
            left join (SELECT * FROM HISTORICOS order by dthr_action desc) hist_atendimento on hist_atendimento.idSolicitacao = SOLICITACOES.idSOLICITACAO";

        return (new Database('GestaoTeste', $innerJoin))->select($select, "(hist_registro.dthr_action BETWEEN '" . $date_inicial . "' AND '" . $date_final . "') AND hist_registro.dthr_action IS NOT NULL AND hist_atendimento.dthr_action >= hist_registro.dthr_action", null, "SOLICITACAO_SETOR, SOLICITACAO_ACOMODACAO")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getSolicitacoesByTempoMedioLiberacaoInSectors($date_inicial, $date_final)
    {
        $select = "SOLICITACAO_SETOR, 
            AVG(TIME_TO_SEC(TIMEDIFF(hist_liberacao.dthr_action, hist_atendimento.dthr_action)))/60 AS TempoMedioLibComPreparo";

        $innerJoin = "SOLICITACOES 
            left join (SELECT * from HISTORICOS where statusChange = 'A') hist_registro on hist_registro.idSolicitacao = SOLICITACOES.idSOLICITACAO
            left join (SELECT * FROM HISTORICOS order by dthr_action desc) hist_atendimento on hist_atendimento.idSolicitacao = SOLICITACOES.idSOLICITACAO
            left join (SELECT * from HISTORICOS where statusChange = 'L') hist_liberacao on hist_liberacao.idSolicitacao = SOLICITACOES.idSOLICITACAO";


        return (new Database('GestaoTeste', $innerJoin))->select($select, "(hist_registro.idSolicitacao BETWEEN '" . $date_inicial . "' AND '" . $date_final . "') AND hist_registro.idSolicitacao IS NOT NULL AND hist_liberacao.idSolicitacao >= hist_atendimento.dthr_action", null, "SOLICITACAO_SETOR")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getSolicitacoesByTempoMedioLiberacaoWithoutSectors($date_inicial, $date_final)
    {
        $select = "SOLICITACAO_SETOR, 
        AVG(TIME_TO_SEC(TIMEDIFF(hist_liberacao.dthr_action, hist_registro.dthr_action)))/60 AS TempoMedioLibSemPreparo";

        $innerJoin = "SOLICITACOES 
            left join (SELECT * from HISTORICOS where statusChange = 'A') hist_registro on hist_registro.idSolicitacao = SOLICITACOES.idSOLICITACAO
            left join (SELECT * from HISTORICOS where statusChange = 'L') hist_liberacao on hist_liberacao.idSolicitacao = SOLICITACOES.idSOLICITACAO";

        return (new Database('GestaoTeste', $innerJoin))->select($select, "(hist_registro.dthr_action BETWEEN '" . $date_inicial . "' AND '" . $date_final . "') AND hist_registro.dthr_action IS NOT NULL AND hist_liberacao.idSolicitacao >= hist_registro.dthr_action", null, "SOLICITACAO_SETOR")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getSolicitacoesByTempoMedioLiberacaoByPerfil($date_inicial, $date_final)
    {
        $select = "SOLICITACAO_SETOR, 
        SOLICITACAO_PERFIL, 
        AVG(TIME_TO_SEC(TIMEDIFF(hist_liberacao.dthr_action, hist_registro.dthr_action)))/60 AS TempoMedioLib";

        $innerJoin = "SOLICITACOES 
            left join (SELECT * from HISTORICOS where statusChange = 'A') hist_registro on hist_registro.idSolicitacao = SOLICITACOES.idSOLICITACAO
            left join (SELECT * from HISTORICOS where statusChange = 'L') hist_liberacao on hist_liberacao.idSolicitacao = SOLICITACOES.idSOLICITACAO";

        return (new Database('GestaoTeste', $innerJoin))->select($select, "(hist_registro.dthr_action BETWEEN '" . $date_inicial . "' AND '" . $date_final . "') AND hist_registro.dthr_action IS NOT NULL AND hist_liberacao.dthr_action >= hist_registro.dthr_action", null, "SOLICITACAO_SETOR, SOLICITACAO_PERFIL")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getSolicitacoesByTempoMedioLiberacaoByAcommodations($date_inicial, $date_final)
    {
        $select = "SOLICITACAO_SETOR, 
        SOLICITACAO_ACOMODACAO, 
        AVG(TIME_TO_SEC(TIMEDIFF(hist_liberacao.dthr_action, hist_registro.dthr_action)))/60 AS TempoMedioLib";

        $innerJoin = "SOLICITACOES 
            left join (SELECT * from HISTORICOS where statusChange = 'A') hist_registro on hist_registro.idSolicitacao = SOLICITACOES.idSOLICITACAO
            left join (SELECT * from HISTORICOS where statusChange = 'L') hist_liberacao on hist_liberacao.idSolicitacao = SOLICITACOES.idSOLICITACAO";

        return (new Database('GestaoTeste', $innerJoin))->select($select, "(hist_registro.dthr_action BETWEEN '" . $date_inicial . "' AND '" . $date_final . "') AND hist_registro.dthr_action IS NOT NULL AND hist_liberacao.dthr_action >= hist_registro.dthr_action", null, "SOLICITACAO_SETOR, SOLICITACAO_ACOMODACAO")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function prepareBed(array $data)
    {
        return (new Database('GestaoTeste', 'SOLICITACOES'))->update("idSOLICITACAO = '" . $data['id'] . "'", [
            "SOLICITACAO_STATUS" => $data['status']
        ]);
    }

    public static function liberateBed(array $data)
    {
        return (new Database('GestaoTeste', 'SOLICITACOES'))->update("idSOLICITACAO = '" . $data['id'] . "'", [
            "SOLICITACAO_STATUS" => $data['status']
        ]);
    }

    public static function cancelSolicitation(array $data)
    {
        return (new Database('GestaoTeste', 'SOLICITACOES'))->update("idSOLICITACAO = '" . $data['id'] . "'", [
            "SOLICITACAO_STATUS" => $data['status']
        ]);
    }

    public static function getCodigoReserva($idSOLICITACAO)
    {
        return (new Database('GestaoTeste', 'HISTORICOS'))->select("contagemReserva", "idSolicitacao = " . $idSOLICITACAO, null, null, "dthr_action desc")->fetch(\PDO::FETCH_ASSOC);
    }

    public static function cancelReserveInMySQL($idSolicitation)
    {
        return (new Database('GestaoTeste', 'SOLICITACOES'))->update("idSOLICITACAO = '" . $idSolicitation . "'", [
            "SOLICITACAO_STATUS" => 'E'
        ]);
    }

    public static function cancelPreparation($idSolicitation)
    {
        return (new Database('GestaoTeste', 'SOLICITACOES'))->update("idSOLICITACAO = '" . $idSolicitation . "'", [
            "SOLICITACAO_STATUS" => 'A'
        ]);
    }

    public static function editSolicitation(array $data)
    {
        return (new Database('GestaoTeste', 'SOLICITACOES'))->update("idSOLICITACAO = " . $data['id'], [
            'SOLICITACAO_PERFIL' => $data['solicitationProfile'],
            'SOLICITACAO_ACOMODACAO' => $data['solicitationAccommodation'],
            'SOLICITACAO_DTHR_ADMISSAO' => $data['solicitationAdmissionDate'],
            'SOLICITACAO_ISOLAMENTO' => strval($data['solicitationIsolation']),
            'SOLICITACAO_MOTIVO' => $data['solicitationReason'],
            'SOLICITACAO_MEDICO_SOLIC' => $data['solicitationDoctor'],
            'SOLICITACAO_PRIORIDADE' => $data['solicitationPriority'],
            'SOLICITACAO_SETOR' => $data['solicitationUnit'],
            'SOLICITACAO_SOLICITANTE' => $data['solicitationRequester'],
            'ISCOVID' => strval($data['isCovid']),
            'COVID_SUSPEITO' => $data['covidSuspect'],
            'COVID_OBSERVACAO' => $data['covidObservation'],
            'OUTRAS_INFOS' => $data['otherInformation'],
            'SOLICITACAO_PED' => $data['solicitationPediatric']
        ]);
    }

    public static function addAlteration($idSolicitation, $idUser, $dthrRegistro, $status, $isSMART, $bedCode = null, $sectorCode = null, $contagem = null)
    {
        return (new Database('GestaoTeste', 'HISTORICOS'))->insert([
            'idSolicitacao' => $idSolicitation,
            'idUser' => $idUser,
            'dthr_action' => $dthrRegistro,
            'statusChange' => $status,
            'isUserSMART' => $isSMART,
            'bedLiberate' => $bedCode,
            'sectorLiberate' => $sectorCode,
            'contagemReserva' => $contagem
        ]);
    }

    public static function getBedAndSectorLiberateFromSolicitation(string $idSOLICITACAO, ?string $status = null)
    {
        $status_check = "";
        if (isset($status)) {
            if (!in_array($status, array("C", "E", "RC")))
                $status_check = " AND statusChange = '" . $status . "'";
        }

        return (new Database('GestaoTeste', 'HISTORICOS'))->select("bedLiberate as codigo_leito, sectorLiberate as codigo_setor", "idSolicitacao = " . $idSOLICITACAO . " AND sectorLiberate is not null" . $status_check, 1, null, "dthr_action desc")->fetch(\PDO::FETCH_ASSOC);
    }

    public static function getLabelsFromHistoric($idSolicitation)
    {
        return (new Database('GestaoTeste', 'HISTORICOS'))->select("DATE(dthr_action) as datesActions", "idSolicitacao = " . $idSolicitation, null, "DATE(dthr_action)", "dthr_action desc")->fetchAll(\PDO::FETCH_ASSOC);
    }
}
