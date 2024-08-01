<?php

namespace App\Model\MonLogin;

use App\Db\SmartPainel;

class User
{
    // Método responsável por retornar todos os exames dentro de um range de datas
    public static function getUsersLogin(string $userLogin)
    {
        $tables = "GR_SES, USR";


        $fields = "GR_SES_APL_COD as modulo, 
        GR_SES_DTHR_INI AS data_hora_login,  
        USR_LOGIN AS login, 
        USR_NOME AS nome, 
        USR_NOME_COMPLETO AS nome_completo,  
        GR_SES_EQP_NOME AS equipamento,
        CASE 
            WHEN 
            GR_SES_DTHR_FIM IS NULL 
            THEN 'Ativa' 
            ELSE 'Inativa' 
        end sessao";

        $where = "GR_USR_LOGIN LIKE '$userLogin%'
        AND (GR_SES_DTHR_INI < GETDATE() AND GR_SES_DTHR_FIM IS NULL)
        AND GR_SES_DTHR_FIM IS NULL
        AND GR_USR_LOGIN = USR_LOGIN";

        $group = null;
        $order = "data_hora_login desc";

        return (new SmartPainel($tables))->select($fields, $where, null, $group, $order)->fetchAll(\PDO::FETCH_CLASS, self::class);
    }
}
