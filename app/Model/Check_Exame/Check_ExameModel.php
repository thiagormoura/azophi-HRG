<?php

namespace App\Model\Check_Exame;

use App\Db\Database;
use App\Db\Smart;

class Check_ExameModel
{
    public static function getPacientes($where, $order, $join = null)
    {
        $with = "
            WITH PACIENTES as (
                SELECT PAC_NOME as NOME, IMG_RCL_RCL_PAC as REGISTRO 
                FROM IMG_RCL INNER JOIN PAC on PAC_REG = IMG_RCL_RCL_PAC
                    INNER JOIN SMK on IMG_RCL_RCL_COD = smk_cod
                    INNER JOIN CTF on CTF.CTF_COD = SMK_CTF
                WHERE SMK_STATUS = 'A' AND SMK_TIPO = 'S' AND CTF_CTF_COD = '2800'".
                    (count($where) == 1 ? " AND ".$where[0] : (count($where) > 1 ? " AND ".implode(' AND ', $where) : ''))."
                GROUP BY PAC_NOME, IMG_RCL_RCL_PAC
            )";

        return (new Smart("PACIENTES"))->with("PACIENTES.*, ROW_NUMBER() OVER (ORDER BY ".$order.") AS RowNum", null, null, null, null, $with)->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getExamesName()
    {
        $tables = "SMK 
            INNER JOIN EXM on EXM.EXM_SMK_COD = SMK_COD
            INNER JOIN CTF on CTF.CTF_COD = SMK_CTF";
        
        $where = "SMK_STATUS = 'A' AND SMK_TIPO = 'S' AND CTF_CTF_COD = '2800'";

        return (new Smart($tables))->select("LTRIM(SMK_NOME) as NOME, SMK_COD as CODIGO", $where, null, "LTRIM(SMK_NOME), SMK_COD", "LTRIM(SMK_NOME)")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getPacienteExames($registro, $dataInterval, $examesFilter = null, $hasFileBinary = false)
    {
        $fields = "
            IMG_RCL_IND as id,
            IMG_RCL_RCL_COD as codigo, 
            IMG_RCL_EXTENSAO as extensao, 
            smk.SMK_NOME as nomeExame,
            IMG_RCL_RCL_DTHR as dthr".(!$hasFileBinary ? '' : ', IMG_RCL_IMG as arquivo, PAC_NOME as nomePaciente');

        $tables = "IMG_RCL INNER JOIN SMK on IMG_RCL_RCL_COD = smk_cod
            INNER JOIN CTF on CTF.CTF_COD = SMK_CTF
            INNER JOIN PAC on IMG_RCL_RCL_PAC = PAC_REG";

        $where = $dataInterval.(empty($examesFilter) ? '' : " AND ".$examesFilter)." AND SMK_STATUS = 'A' AND SMK_TIPO = 'S' AND CTF_CTF_COD = '2800' AND IMG_RCL_RCL_PAC = ".$registro;

        return (new Smart($tables))->select($fields, $where, null, null, 'IMG_RCL_RCL_DTHR desc')->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getFile($exame, $registro)
    {
        $where = "IMG_RCL_RCL_PAC = ".$registro." AND IMG_RCL_IND = ".$exame;
        return (new Smart('IMG_RCL'))->select('IMG_RCL_IMG as arquivo', $where)->fetch(\PDO::FETCH_ASSOC)['arquivo'];
    }

    public static function getExamesFiltered($registros, $dataInterval)
    {
        $where = "IMG_RCL_RCL_PAC in (".$registros.") AND ".$dataInterval;
        $tables = "IMG_RCL 
            INNER JOIN SMK on IMG_RCL_RCL_COD = smk_cod";

        $group = "SMK_NOME, SMK_COD";

        return (new Smart($tables))->select('DISTINCT SMK_NOME as text, SMK_COD as value', $where, null, $group)->fetchAll(\PDO::FETCH_ASSOC);
    }
}