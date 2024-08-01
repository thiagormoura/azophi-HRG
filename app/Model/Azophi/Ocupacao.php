<?php

namespace App\Model\Azophi;

use App\Db\SmartPainel;

class Ocupacao
{
       // Método responsável por buscar a quantidade de pacientes que estão atualmente no hospital
       public static function getPacientesOcupacao(int $dias, $conveniosFilter = null)
       {
              $fields = "CONVERT(VARCHAR(10), DATEADD(DAY, $dias, GETDATE()), 23) AS DIA, COUNT(*) AS PACIENTES";
              $tables = "LTO, LOC, HSP".(empty($conveniosFilter) ? '' : ',CNV');
              $where = "LTO_PAC_REG = HSP_PAC
                            AND LTO_HSP_NUM = HSP_NUM
                            AND LOC_LEITO_ID IS NOT NULL
                            AND LTO_LOC_COD = LOC_COD
                            AND LOC_STR IN ('UT1', 'UT2', 'UT5', 'UIT', 'UI4', 'UI5', 'UI7', 'TMO', 'UT7', 'UT8', 'UCV', 'UP1', 'UP3', '2X', '30', '32', 'ups', 'UM1', 'UM3', 'PE3', 'PE2')
                            AND ((((CONVERT(DATETIME, CONVERT(VARCHAR(10), DATEADD(DAY, $dias, GETDATE()), 103) + ' 23:59:59', 103)) BETWEEN LTO_DTHR_INI AND LTO_DTHR_FIM)
                                   OR (LTO.LTO_DTHR_INI = LTO.LTO_DTHR_FIM
                                          AND ((CONVERT(DATETIME, CONVERT(VARCHAR(10), DATEADD(DAY, $dias, GETDATE()), 103) + ' 23:59:59', 103)) BETWEEN LTO.LTO_DTHR_INI AND HSP_DTHRA))
                                   OR (LTO.LTO_DTHR_INI = LTO.LTO_DTHR_FIM
                                          AND HSP_STAT = 'A'
                                          AND LTO.LTO_DTHR_INI <= (CONVERT(DATETIME, CONVERT(VARCHAR(10), DATEADD(DAY, $dias, GETDATE()), 103) + ' 23:59:59', 103)))))
                            ".(empty($conveniosFilter) ? "" : 'AND cnv_cod = hsp_cnv AND RTRIM(cnv_cod) in ('.$conveniosFilter.')');
              $group = null;
              $order = null;
              return (new SmartPainel($tables))->select($fields, $where, null, $group, $order)->fetch(\PDO::FETCH_ASSOC);
       }
       // Método responsável por realizar a busca das entradas e saidas dos pacientes
       public static function getEntradaESaidaPacientes(int $dias, $conveniosFilter = null)
       {

              $fields = "'ALTA' as 'status', HSP_DTHRA as datahora";
              $tables = "HSP".(empty($conveniosFilter) ? '' : ',CNV');
              $where = "HSP_TRAT_INT = 'I' AND
                     HSP_DTHRA BETWEEN 
                     CONVERT(DATETIME, CONVERT(VARCHAR(10), DATEADD(DAY, $dias, GETDATE()), 103) + ' 00:00:00', 103) AND CONVERT(DATETIME, CONVERT(VARCHAR(10), DATEADD(DAY, $dias, GETDATE()), 103) + ' 23:59:59', 103)
                     ".(empty($conveniosFilter) ? "" : 'AND cnv_cod = hsp_cnv AND RTRIM(cnv_cod) in ('.$conveniosFilter.')');
              $group = null;
              $order = null;

              $unionQuery = "SELECT 'ADMISSAO', HSP_DTHRE FROM HSP ".(empty($conveniosFilter) ? '' : ',CNV')."
                            WHERE 
                                   HSP_TRAT_INT = 'I' AND
                                   HSP_DTHRE BETWEEN 
                                   CONVERT(DATETIME, CONVERT(VARCHAR(10), DATEADD(DAY, $dias, GETDATE()), 103) + ' 00:00:00', 103) AND CONVERT(DATETIME, CONVERT(VARCHAR(10), DATEADD(DAY, $dias, GETDATE()), 103) + ' 23:59:59', 103)
                                   ".(empty($conveniosFilter) ? "" : 'AND cnv_cod = hsp_cnv AND RTRIM(cnv_cod) in ('.$conveniosFilter.')')."
                            order by datahora desc";

              return (new SmartPainel($tables))->unionAll($fields, $where, null, $group, $order, $unionQuery)->fetchAll(\PDO::FETCH_ASSOC);
       }
}
