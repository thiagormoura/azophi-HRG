<?php

namespace App\Model\Inutri;

use App\Db\Database;

class Usuario
{

    public static function updateAccess($user){
            
        date_default_timezone_set('America/Fortaleza');

        return (new Database("centralservicos", "usuario_acesso"))->insert([
            "usuario_nome" => $user->nome,
            "usuario_cpf" => $user->cpf,
            "sistema" => "Inutri (Produção)",
            "dthr_login" => date("Y-m-d H:i:s")
        ]);
    }

}
