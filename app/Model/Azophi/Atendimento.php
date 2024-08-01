<?php

namespace App\Model\Azophi;

use App\Db\SmartPainel;

class Atendimento
{
    // Método responsável por retornar a quantidade de atendimentos em $dias a partir de hoje no pronto atendimento
    // do HRG
    public static function getPAHrgAtendimento(int $initialDays, int $finalDays, $conveniosFilter = null)
    {
        $tables = "HSP, SMM".(empty($conveniosFilter) ? '' : ',CNV');
        $fields = "CASE 
            WHEN SMM_COD = '00010022'
                THEN 'Clinico Geral' 
            WHEN SMM_COD = '21010006' 
                THEN 'Cardiologia' 
            WHEN SMM_COD = '07012268' 
                THEN 'Ortopedia' 
            ELSE 'Outros Atend.' 
            END AS CONSULTA,
            COUNT(*) AS QUANTIDADE,
            CONVERT(DATE, HSP_DTHRE) as DATA";
        $where = "HSP_TRAT_INT = 'T'
            AND     HSP_DTHRE BETWEEN CONVERT(DATETIME, CONVERT(VARCHAR(10), DATEADD(DAY, $initialDays, GETDATE()), 103) + ' 00:00:00', 103) AND CONVERT(DATETIME, CONVERT(VARCHAR(10), DATEADD(DAY, $finalDays, GETDATE()), 103) + ' 23:59:59', 103)
            AND 	SMM.SMM_PAC_REG =* HSP_PAC
            AND 	SMM.SMM_HSP_NUM	=*	HSP_NUM
            AND	    HSP_STR_COD = 'PAT'  
            AND	    SMM_EXEC <> 'C'			
            AND 	SMM.SMM_COD IN ('00010022','21010006','07012268')
            AND	    SMM.SMM_TPCOD ='S'
            ".(empty($conveniosFilter) ? "" : 'AND cnv_cod = hsp_cnv AND RTRIM(cnv_cod) in ('.$conveniosFilter.')');
        $group = "SMM_COD, CONVERT(DATE, HSP_DTHRE)";
        $order = "CONSULTA, DATA";

        return (new SmartPainel($tables))->select($fields, $where, null, $group, $order)->fetchAll(\PDO::FETCH_ASSOC);
    }
    // Método responsável por retornar a quantidade de atendimentos hoje em $dias no pronto atendimento
    // da maternidade
    public static function getPAMaternidadeAtendimento(int $initialDays, int $finalDays, $conveniosFilter = null)
    {
        $tables = "HSP, SMM".(empty($conveniosFilter) ? '' : ',CNV');
        $fields = "CASE 
        WHEN SMM_COD = '00010023' THEN 'Pediatria'
        WHEN SMM_COD = '00010024' THEN 'Obstetricia'
        ELSE 'Outros Atend.' 
        END AS CONSULTA,
        COUNT(*) AS QUANTIDADE,
        CONVERT(DATE, HSP_DTHRE) AS DATA";
        $where = "HSP_TRAT_INT = 'T'
            AND     HSP_DTHRE BETWEEN CONVERT(DATETIME, CONVERT(VARCHAR(10), DATEADD(DAY, $initialDays, GETDATE()), 103) + ' 00:00:00', 103) AND CONVERT(DATETIME, CONVERT(VARCHAR(10), DATEADD(DAY, $finalDays, GETDATE()), 103) + ' 23:59:59', 103)
            AND 	SMM.SMM_PAC_REG =* HSP_PAC
            AND 	SMM.SMM_HSP_NUM	=*	HSP_NUM
            AND	    HSP_STR_COD  IN ('PE4','PE5')  
            AND	    SMM_EXEC <> 'C'			
            AND 	SMM.SMM_COD IN ('00010023','00010024')
            AND	    SMM.SMM_TPCOD ='S'
            ".(empty($conveniosFilter) ? "" : 'AND cnv_cod = hsp_cnv AND RTRIM(cnv_cod) in ('.$conveniosFilter.')');
        $group = "SMM_COD, CONVERT(DATE, HSP_DTHRE)";
        $order = "CONSULTA, DATA";

        return (new SmartPainel($tables))->select($fields, $where, null, $group, $order)->fetchAll(\PDO::FETCH_ASSOC);
    }
    // Método responsável por retornar a quantidade o tempo dos atendimentos em $dias no pronto atendimento
    // do HRG
    public static function getPAHrgTempo(int $dias)
    {
        $tables = "HSP, SMM";
        $fields = "HSP.HSP_PAC,HSP.HSP_DTHRE,
                    CASE 
                        WHEN SMM_COD = '00010022' THEN 'Clinico Geral'
                        WHEN SMM_COD = '21010006' THEN 'Cardiologia' 
                        WHEN SMM_COD = '07012268' THEN 'Ortopedia' 
                        ELSE 'Outros Atend.'
                    END AS CONSULTA,
                    CASE 
                        WHEN SMM_COD = '00010022' THEN DATEDIFF(MINUTE, isnull((SELECT MAX (FLE.FLE_DTHR_CHEGADA) FROM FLE WHERE FLE.FLE_PAC_REG = HSP.HSP_PAC AND FLE.FLE_DTHR_CHEGADA < HSP.HSP_DTHRE AND FLE.FLE_PSV_COD IN (900197,900198)),0) , isnull((SELECT MAX (FLE.FLE_DTHR_ATENDIMENTO) FROM FLE WHERE FLE.FLE_PAC_REG = HSP.HSP_PAC AND FLE.FLE_DTHR_ATENDIMENTO > HSP.HSP_DTHRE AND FLE.FLE_PSV_COD IN (900290)),0))
                        WHEN SMM_COD = '21010006' THEN DATEDIFF(MINUTE, isnull((SELECT MAX (FLE.FLE_DTHR_CHEGADA) FROM FLE WHERE FLE.FLE_PAC_REG = HSP.HSP_PAC AND FLE.FLE_DTHR_CHEGADA < HSP.HSP_DTHRE AND FLE.FLE_PSV_COD IN (900197,900198)),0) , isnull((SELECT MAX (FLE.FLE_DTHR_ATENDIMENTO) FROM FLE WHERE FLE.FLE_PAC_REG = HSP.HSP_PAC AND FLE.FLE_DTHR_ATENDIMENTO > HSP.HSP_DTHRE AND FLE.FLE_PSV_COD IN (900288)),0))
                        WHEN SMM_COD = '07012268' THEN DATEDIFF(MINUTE, isnull((SELECT MAX (FLE.FLE_DTHR_CHEGADA) FROM FLE WHERE FLE.FLE_PAC_REG = HSP.HSP_PAC AND FLE.FLE_DTHR_CHEGADA < HSP.HSP_DTHRE AND FLE.FLE_PSV_COD IN (900197,900198)),0) , isnull((SELECT MAX (FLE.FLE_DTHR_ATENDIMENTO) FROM FLE WHERE FLE.FLE_PAC_REG = HSP.HSP_PAC AND FLE.FLE_DTHR_ATENDIMENTO > HSP.HSP_DTHRE AND FLE.FLE_PSV_COD IN (900289)),0))
                        else 0
                    END AS TEMPO";
        $where = "HSP_TRAT_INT = 'T'
                    AND     HSP_DTHRE BETWEEN CONVERT(DATETIME, CONVERT(VARCHAR(10), DATEADD(DAY, $dias, GETDATE()), 103) + ' 00:00:00', 103) AND CONVERT(DATETIME, CONVERT(VARCHAR(10), DATEADD(DAY, $dias, GETDATE()), 103) + ' 23:59:59', 103)
                    AND 	SMM.SMM_PAC_REG =* HSP_PAC
                    AND 	SMM.SMM_HSP_NUM	=*	HSP_NUM
                    AND	    HSP_STR_COD = 'PAT'  
                    AND	    SMM_EXEC <> 'C'			
                    AND 	SMM.SMM_COD IN ('00010022','21010006','07012268')
                    AND	    SMM.SMM_TPCOD ='S'";
        $group = null;
        $order = "CONSULTA";

        return (new SmartPainel($tables))->select($fields, $where, null, $group, $order)->fetchAll(\PDO::FETCH_ASSOC);
    }
    // Método responsável por retornar a quantidade o tempo dos atendimentos em $dias no pronto atendimento
    // da maternidade
    public static function getPAMaternidadeTempo(int $dias)
    {
        $tables = "HSP, SMM";
        $fields = "HSP.HSP_PAC,HSP.HSP_DTHRE,
                    CASE 
                        WHEN SMM_COD = '00010023' THEN 'Pediatria'
                        WHEN SMM_COD = '00010024' THEN 'Obstetricia'
                        ELSE 'Outros Atend.'
                    END AS CONSULTA,
                    CASE 
                        WHEN SMM_COD = '00010023' THEN DATEDIFF(MINUTE, isnull((SELECT MAX (FLE.FLE_DTHR_CHEGADA) FROM FLE WHERE FLE.FLE_PAC_REG = HSP.HSP_PAC AND FLE.FLE_DTHR_CHEGADA < HSP.HSP_DTHRE AND FLE.FLE_PSV_COD IN (903330)),0) , isnull((SELECT MAX (FLE.FLE_DTHR_ATENDIMENTO) FROM FLE WHERE FLE.FLE_PAC_REG = HSP.HSP_PAC AND FLE.FLE_DTHR_ATENDIMENTO > HSP.HSP_DTHRE AND FLE.FLE_PSV_COD IN (903328)),0))
                        WHEN SMM_COD = '00010024' THEN DATEDIFF(MINUTE, isnull((SELECT MAX (FLE.FLE_DTHR_CHEGADA) FROM FLE WHERE FLE.FLE_PAC_REG = HSP.HSP_PAC AND FLE.FLE_DTHR_CHEGADA < HSP.HSP_DTHRE AND FLE.FLE_PSV_COD IN (903340)),0) , isnull((SELECT MAX (FLE.FLE_DTHR_ATENDIMENTO) FROM FLE WHERE FLE.FLE_PAC_REG = HSP.HSP_PAC AND FLE.FLE_DTHR_ATENDIMENTO > HSP.HSP_DTHRE AND FLE.FLE_PSV_COD IN (903329)),0))
                        else 0
                    END AS TEMPO";
        $where = "HSP_TRAT_INT = 'T'
                    AND     HSP_DTHRE BETWEEN CONVERT(DATETIME, CONVERT(VARCHAR(10), DATEADD(DAY, $dias, GETDATE()), 103) + ' 00:00:00', 103) AND CONVERT(DATETIME, CONVERT(VARCHAR(10), DATEADD(DAY, $dias, GETDATE()), 103) + ' 23:59:59', 103)
                    AND 	SMM.SMM_PAC_REG =* HSP_PAC
                    AND 	SMM.SMM_HSP_NUM	=*	HSP_NUM
                    AND	    HSP_STR_COD  IN ('PE4','PE5')  
                    AND	    SMM_EXEC <> 'C'			
                    AND 	SMM.SMM_COD IN ('00010023','00010024')
                    AND	    SMM.SMM_TPCOD ='S'";
        $group = null;
        $order = "CONSULTA";

        return (new SmartPainel($tables))->select($fields, $where, null, $group, $order)->fetchAll(\PDO::FETCH_ASSOC);
    }
    // Método responsável por retornar os convênios dos pacientes que foram atendidos em $dias no pronto atendimento
    // do HRG
    public static function getPAHrgConvenios(int $dias, string $convenios)
    {
        $tables = "HSP, CNV";
        $fields = "CNV_NOME AS NOME, COUNT(*) AS QUANTIDADE";
        $where = "HSP_TRAT_INT = 'T'
        AND HSP_STR_COD in ('PAT')
        AND CNV_COD IN (" . $convenios . ")
        AND CNV_COD = HSP_CNV
        AND HSP_DTHRE BETWEEN CONVERT(DATETIME, CONVERT(VARCHAR(10), DATEADD(DAY, $dias, GETDATE()), 103) + ' 00:00:00', 103) AND CONVERT(DATETIME, CONVERT(VARCHAR(10), DATEADD(DAY, $dias, GETDATE()), 103) + ' 23:59:59', 103)";
        $group = "CNV_NOME";
        $order = "QUANTIDADE DESC";

        return (new SmartPainel($tables))->select($fields, $where, null, $group, $order)->fetchAll(\PDO::FETCH_ASSOC);
    }
    // Método responsável por retornar os convênios dos pacientes que foram atendidos em $dias no pronto atendimento
    // da maternidade
    public static function getPAMaternidadeConvenios(int $dias, string $convenios)
    {
        $tables = "HSP, CNV";
        $fields = "CNV_NOME AS NOME, COUNT(*) AS QUANTIDADE";
        $where = "HSP_TRAT_INT = 'T'
        AND HSP_STR_COD  IN ('PE4','PE5')
        AND CNV_COD IN (" . $convenios . ")
        AND CNV_COD = HSP_CNV
        AND HSP_DTHRE BETWEEN CONVERT(DATETIME, CONVERT(VARCHAR(10), DATEADD(DAY, $dias, GETDATE()), 103) + ' 00:00:00', 103) AND CONVERT(DATETIME, CONVERT(VARCHAR(10), DATEADD(DAY, $dias, GETDATE()), 103) + ' 23:59:59', 103)";
        $group = "CNV_NOME";
        $order = "QUANTIDADE DESC";

        return (new SmartPainel($tables))->select($fields, $where, null, $group, $order)->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Método responsável por retornar os pacientes que no pronto atendimento
    // do HRG
    public static function getPAPacientesHrg()
    {
        $tables = "HSP, SMM";
        $fields = "CONVERT(VARCHAR(10), HSP_DTHRE, 23) AS DIA, SUM(1) as QUANTIDADE";
        $where = "HSP_TRAT_INT = 'T'
        AND 	HSP_DTHRE BETWEEN CONVERT(DATETIME, CONVERT(VARCHAR(10), DATEADD(DAY, -7, GETDATE()), 103) + ' 00:00:00', 103) AND CONVERT(DATETIME, CONVERT(VARCHAR(10), DATEADD(DAY, 0, GETDATE()), 103) + ' 23:59:59', 103)
        AND 	SMM.SMM_PAC_REG =* HSP_PAC
        AND 	SMM.SMM_HSP_NUM	=*	HSP_NUM
        AND	    HSP_STR_COD = 'PAT'  
        AND	    SMM_EXEC <> 'C'			
        AND 	SMM.SMM_COD IN ('00010022','21010006','07012268')
        AND	    SMM.SMM_TPCOD ='S' AND DATEPART(YEAR, HSP_DTHRE) = YEAR(getdate())";
        $group = "CONVERT(VARCHAR(10), HSP_DTHRE, 23)";
        $order = "DIA";

        return (new SmartPainel($tables))->select($fields, $where, null, $group, $order)->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Método responsável por retornar os pacientes que no pronto atendimento
    // do maternidade
    public static function getPAPacientesMaternidade()
    {
        $tables = "HSP, SMM";
        $fields = "CONVERT(VARCHAR(10), HSP_DTHRE, 23) AS DIA, SUM(1) as QUANTIDADE";
        $where = "HSP_TRAT_INT = 'T'
        AND 	HSP_DTHRE BETWEEN CONVERT(DATETIME, CONVERT(VARCHAR(10), DATEADD(DAY, -7, GETDATE()), 103) + ' 00:00:00', 103) AND CONVERT(DATETIME, CONVERT(VARCHAR(10), DATEADD(DAY, 0, GETDATE()), 103) + ' 23:59:59', 103)
        AND 	SMM.SMM_PAC_REG =* HSP_PAC
        AND 	SMM.SMM_HSP_NUM	=*	HSP_NUM
        AND	    HSP_STR_COD IN ('PE5', 'PE4') 
        AND	    SMM_EXEC <> 'C'			
        AND 	SMM.SMM_COD IN ('00010022','21010006','07012268')
        AND	    SMM.SMM_TPCOD ='S' AND DATEPART(YEAR, HSP_DTHRE) = YEAR(getdate())";
        $group = "CONVERT(VARCHAR(10), HSP_DTHRE, 23)";
        $order = "DIA";

        return (new SmartPainel($tables))->select($fields, $where, null, $group, $order)->fetchAll(\PDO::FETCH_ASSOC);
    }
}
