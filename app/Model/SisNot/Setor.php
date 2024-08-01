<?php

namespace App\Model\SisNot;

use App\Db\Database;

class Setor
{
    // Método responsável por retornar os setores presentes no sistema
    public static function getSetores()
    {
        return (new Database('sisnot', "setor"))->select('*')->fetchAll(\PDO::FETCH_OBJ);
    }
    // Método responsável por retornar determinado setor pelo código
    public static function getSetorByCodigo(string $codigo)
    {
        return (new Database('sisnot', "setor"))->select('*', "codigo = '$codigo'")->fetchObject();
    }
}
