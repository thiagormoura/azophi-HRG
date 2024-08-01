<?php

namespace App\Model\Allog;

use App\Db\Database;
use App\Db\SmartPainel;

class AllogModel{

    public static function getAllog($data, $limit = null, $search = null){
     
        $fields = "
            usr.id as ID,
            UPPER(usr.nome) as USER_NAME,
            usr.email as 'EMAIL',
            usr.cpf as 'CPF',
            'Gestão de Leitos' as 'SISTEMA',
            usr_login.dthr_login as 'DTHR_LOGIN'
        ";

        $tables = 'usr, usr_login';

        $where = "(dthr_login BETWEEN '".$data['dataInicio']['date'].' '.$data['dataInicio']['time']."' and '".$data['dataFim']['date'].' '.$data['dataFim']['time']."')";
        $where = $where." and (nome like '%".$search."%' or email like '%".$search."%' or cpf like '%".$search."%')";
        $where = $where." and (usr.id = usr_login.id_usr)";

        $order = 'dthr_login DESC';

        return (new Database("GestaoLeitosOld", $tables))->select($fields, $where, $limit, null, $order)->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getSystems()
    {
        return (new Database("centralservicos", 'projetos'))->select('id, descricao', "id not in (18, 16, 15, 14, 10, 9, 8, 7, 4, 3, 2, 1)")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getSystem($systemId)
    {
        return (new Database("centralservicos", 'projetos'))->select('id, descricao', "id = ".$systemId)->fetch(\PDO::FETCH_ASSOC);
    }

    public static function getSistemsInWeek(){
        $fields = "DATE(ua.dthr_login) as dt, projetos.id, projetos.descricao";
        $tables = "usuario_acesso ua inner join projetos on ua.sistema = projetos.id";
        $where = "DATE(ua.dthr_login) BETWEEN DATE(NOW()) - INTERVAL 7 DAY AND DATE(NOW())";
        $order = "dt";
        

        return (new Database("centralservicos", $tables))->select($fields, $where, null, null, $order)->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getTotalAccess(){

        $fields = "projetos.descricao as label, count(projetos.descricao) as y";
        $tables = "usuario_acesso ua inner join projetos on ua.sistema = projetos.id";
        $group = "projetos.descricao";

        return (new Database("centralservicos", $tables))->select($fields, null, null, $group, "count(projetos.descricao) asc")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getUsersAccessBySystem($systemId)
    {
        $fields = "usuario_nome as label, count(usuario_nome) as y";
        $tables = "usuario_acesso ua inner join projetos on ua.sistema = projetos.id";
        $where = "usuario_nome not like 'TI%' AND projetos.id = ".$systemId;
        $group = "usuario_nome";

        return (new Database("centralservicos", $tables))->select($fields, $where, "20", $group, "count(usuario_nome) desc")->fetchAll(\PDO::FETCH_ASSOC);
    }
}