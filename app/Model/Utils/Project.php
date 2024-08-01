<?php

namespace App\Model\Utils;

use App\Db\Database;

class Project
{
    /**
     * Método responsável por retornar os projetos 
     * @return array Array com os projetos 
     */
    public static function getProjects()
    {
        $naotemnadaquepodenosafetar =  (new Database('centralservicos', 'projetos'))->select('descricao')->fetchAll(\PDO::FETCH_ASSOC);
        $continue = [];
        foreach ($naotemnadaquepodenosafetar as $key => $value) {
            $sexo = strtolower(str_replace(" ", "_", $value['descricao']));
            if($sexo=="gestao_de_leitos" || $sexo=="agenda_centro_cirurgico")
                continue;
            $continue[$sexo] = false;
        }
        return $continue;

    }
}
