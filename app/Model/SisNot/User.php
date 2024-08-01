<?php

namespace App\Model\SisNot;

use App\Db\Database;

class User
{
    public static function updateAccess($user){
        
        date_default_timezone_set('America/Fortaleza');

        return (new Database("centralservicos", "usuario_acesso"))->insert([
            "usuario_nome" => $user->nome,
            "usuario_cpf" => $user->cpf,
            "sistema" => "Sisnot (Produção)",
            "dthr_login" => date("Y-m-d H:i:s")
        ]);
    }
}
