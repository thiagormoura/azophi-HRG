<?php

namespace App\Model\Azophi;

use App\Db\SmartPainel;

class Paciente
{
       // Método responsável por retornar os pacientes nos últimos 7 dias
       public static function getPacientesUltimos7Dias($conveniosFilter = null)
       {
              $tables = "LTO, LOC, HSP, STR, PSV, PAC, CNV";
              $fields = "
              CASE 
                     WHEN STR_COD = 'UIT' THEN '3º Andar' 
                     WHEN STR_COD = 'UI4' THEN '4º Andar' 
                     WHEN STR_COD = 'UI5' THEN '5º Andar' 
                     WHEN STR_COD = 'UI7' THEN '7º Andar' 
                     WHEN STR_COD = 'TMO' THEN 'TMO' 
                     WHEN STR_COD = 'UT7' THEN 'UTI P.' 
                     WHEN STR_COD = 'UT1' THEN 'UTI 1' 
                     WHEN STR_COD = 'UT2' THEN 'UTI 2' 
                     WHEN STR_COD = 'UT5' THEN 'UTI 3' 
                     WHEN STR_COD = 'UT8' THEN 'UTI 4'
                     WHEN STR_COD = 'UCV' THEN 'UTI PNV I'
                     WHEN STR_COD = 'UP1' THEN 'UTI PNV II'
                     WHEN STR_COD = 'UP3' THEN 'UTI PNV III'
                     WHEN STR_COD = 'UPS' THEN 'UTI 5'
                     WHEN STR_COD = '32' THEN 'UTI Cir 4º Andar'
                     WHEN STR_COD = 'UM1' THEN ' 1º Andar Mat/Inf'
                     WHEN STR_COD = 'UM3' THEN ' 3º Andar Mat/Inf'
                     WHEN STR_COD = 'PE3' THEN 'UTI NEONATAL'
                     WHEN STR_COD = 'PE2' THEN 'UTI PEDIATRICA II'
                     WHEN STR_COD = '1FU' THEN 'Sala de infusão'
                     WHEN STR_COD = '41' THEN 'Berçario'
                     ELSE STR_COD
              END AS SETOR,
              COUNT(*) as QUANTIDADE";
              $where = "LTO_DTHR_INI = LTO_DTHR_FIM
                        AND LTO_LOC_COD = LOC_COD
                        AND LTO_HSP_NUM = HSP_NUM
                        AND LTO_PAC_REG = HSP_PAC
                        AND LOC_STR = STR_COD
                        AND HSP_STAT = 'A'
                        AND HSP_MDE = PSV_COD
                        AND PAC.PAC_REG = HSP_PAC
                        AND CNV.CNV_COD = HSP_CNV
                        AND DATEDIFF(DAY,LTO_DTHR_INI, GETDATE()) >= 7
                        ".(empty($conveniosFilter) ? "" : 'AND PAC_REG = LOC_PAC AND PAC_CNV = CNV_COD AND RTRIM(cnv_cod) in ('.$conveniosFilter.')');
              $group = "STR.STR_COD";
              $order = "SETOR";
              return (new SmartPainel($tables))->select($fields, $where, null, $group, $order)->fetchAll(\PDO::FETCH_ASSOC);
       }

       // Método responsável por retornar os convênios dos pacientes dos últimos 7 dias
       public static function getPacientesUltimos7DiasConvenios(string $convenios)
       {
              $tables = "LTO, LOC, HSP, STR, PSV, PAC, CNV";
              $fields = "cnv.cnv_nome as CONVENIO, COUNT(*) as QUANTIDADE";
              $where = "LTO_DTHR_INI    = LTO_DTHR_FIM
                          AND LTO_LOC_COD     = LOC_COD
                          AND LTO_HSP_NUM     = HSP_NUM
                          AND LTO_PAC_REG     = HSP_PAC
                          AND LOC_STR         = STR_COD
                          AND HSP_STAT        = 'A'
                          AND HSP_MDE         = PSV_COD
                          AND PAC.PAC_REG     = HSP_PAC
                          AND CNV.CNV_COD     = HSP_CNV
                          AND CNV.CNV_COD IN (" . $convenios . ")
                          AND DATEDIFF(DAY,LTO_DTHR_INI, GETDATE()) >= 7";
              $group = "CNV.CNV_NOME";
              $order = "QUANTIDADE desc";
              return (new SmartPainel($tables))->select($fields, $where, null, $group, $order)->fetchAll(\PDO::FETCH_ASSOC);
       }
}
