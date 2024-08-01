<?php

namespace App\Model\Azophi;

use App\Db\SmartPainel;

class Convenio
{

    // Método responsável por retornar os convênios para o filtro de busca
    public static function getConvenios()
    {
        $tables = "RCI INNER JOIN CNV ON RCI.RCI_CNV_COD = CNV.CNV_COD";
        $fields = "CNV.CNV_NOME AS nome, CNV.CNV_COD AS code";
        $where = null;
        $group = "CNV.CNV_NOME, CNV.CNV_COD";
        $order = null;

        return (new SmartPainel($tables))->select($fields, $where, null, $group, $order)->fetchAll(\PDO::FETCH_ASSOC);
    }
}
