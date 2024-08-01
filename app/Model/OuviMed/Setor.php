<?php

namespace App\Model\Ouvimed;

use App\Db\Database;
use App\Db\Smart;

class Setor{

    // Retorna o codigo e o nome de todos os setores no smart
    public static function getAllSectors(){

        return (new Database('ouvimed', 'setores'))->select('*', null, null, null, "nome ASC")->fetchAll(\PDO::FETCH_ASSOC);

        //return (new Smart('STR'))->select("STR.STR_COD, STR_NOME", "STR_COD != '999'", null, null, "STR_NOME ASC")->fetchAll(\PDO::FETCH_ASSOC);

    }

}