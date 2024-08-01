<?php

namespace App\Model\Utils;

use App\Db\Database;

class Spy {

    public static function updateAcess($user, $project_id, $project_name){

        date_default_timezone_set('America/Fortaleza');

        // token de acesso atual
  
        // ultimo acesso do usuario nesse sistema      
        if($_SESSION['ailton'][$project_name] == false){
            $_SESSION['ailton'][$project_name] = true;
            return (new Database("centralservicos", "usuario_acesso"))->insert([
                "usuario_nome" => $user->nome . " " . $user->sobrenome,
                "usuario_cpf" => $user->cpf,
                "sistema" => $project_id,
                "dthr_login" => date("Y-m-d H:i:s")
            ]);
        }
        
    }

}