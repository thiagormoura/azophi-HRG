<?php

namespace App\Model\Azophi;

use App\Db\SmartPainel;

class Internacao
{
       // Método responsável por retornar a quantidade de internações no HRG hoje
       public static function getInternacaoHrgHoje($dateBusca, $conveniosFilter = null, $preserveLeito = false)
       {
              $tables = "str";

              $fields = "
              STR_COD,
              CASE
                     WHEN str_cod = 'UIT' THEN '3º Andar'
                     WHEN str_cod = 'UI4' THEN '4º Andar'
                     WHEN str_cod = 'UI5' THEN '5º Andar'
                     WHEN str_cod = 'UI5' THEN '5º Andar'
                     WHEN str_cod = 'UI7' THEN '7º Andar'
                     WHEN str_cod = 'TMO' THEN 'TMO'
                     WHEN str_cod = 'UT7' THEN 'UTI PED I CARDIO'
                     WHEN str_cod = 'UT1' THEN 'UTI 1'
                     WHEN str_cod = 'UT2' THEN 'UTI 2'
                     WHEN str_cod = 'UT5' THEN 'UTI 3'
                     WHEN str_cod = 'UT8' THEN 'UTI 4'
                     WHEN str_cod = '32' THEN 'UTI 4º Andar'
                     WHEN str_cod = 'UCV' THEN 'UTI PNV I'
                     WHEN str_cod = 'UP1' THEN 'UTI PNV II'
                     WHEN str_cod = 'UP3' THEN 'UTI PNV III'
                     WHEN str_cod = 'UPS' THEN 'UTI 5'
                     WHEN str_cod = '2X' THEN 'UI Lobby'
                     WHEN str_cod = '30' THEN 'UI ISO 7º Andar'
              END AS SETOR,
              (SELECT Count (*)
                     FROM   cle,
                            loc
                            ".(empty($conveniosFilter) || $preserveLeito ? '' : ',pac,cnv')."
                     WHERE  loc_cle_cod = cle_cod
                            AND loc_str = str_cod
                            AND ( ( loc_status IN ( 'L', 'R' )
                                   AND loc_str <> '2X' )
                                   OR ( loc_status IN ( 'O' ) ) )
                            AND loc_nome NOT LIKE 'EXTRA%'
                            AND loc.loc_leito_id IS NOT NULL
                            AND ( loc.loc_del_logica = 'N'
                                   OR loc.loc_del_logica IS NULL )
                            ".(empty($conveniosFilter) || $preserveLeito ? "" : 'AND pac_reg = loc_pac AND pac_cnv = cnv_cod AND RTRIM(cnv_cod) in ('.$conveniosFilter.')')."
                            
              ) AS LEITOS,
              (SELECT Count (*)
                     FROM   cle,
                            loc
                     WHERE  loc_cle_cod = cle_cod
                            AND loc_str = str_cod
                            --AND     CLE_TIPO    = 'L' 
                            AND loc_status IN ( 'B' )
                            --AND    LOC.LOC_LEITO_ID IS NOT NULL
                            AND ( loc.loc_del_logica = 'N'
                                   OR loc.loc_del_logica IS NULL )
              ) AS BLOQUEADOS,
              (SELECT Count (*)
                     FROM   cle,
                            loc,
                            pac,
                            cnv
                     WHERE  loc_cle_cod = cle_cod
                            AND loc_str = str_cod
                            and pac_reg = LOC_PAC
				
                            and PAC_CNV = cnv_cod
                            AND loc_status IN ( 'R' )
                            AND ( loc.loc_del_logica = 'N'
                                   OR loc.loc_del_logica IS NULL )
                            ".(empty($conveniosFilter) ? "" : 'AND RTRIM(cnv_cod) in ('.$conveniosFilter.')')."
              ) AS RESERVA,
              (SELECT Count (*)
                     FROM   cle,
                            loc
                     WHERE  loc_cle_cod = cle_cod
                            AND loc_str = str_cod
                            --AND     CLE_TIPO    = 'L' 
                            AND loc_status IN ( 'L', 'O', 'R' )
                            AND loc_nome LIKE 'EXTRA%'
                            --AND    LOC.LOC_LEITO_ID IS NOT NULL
                            AND ( loc.loc_del_logica = 'N'
                                   OR loc.loc_del_logica IS NULL )
              ) AS EXTRA_TOTAL,
              (SELECT Count (*)
                     FROM   cle,
                            loc
                     WHERE  loc_cle_cod = cle_cod
                            AND loc_str = str_cod
                            --AND     CLE_TIPO    = 'L' 
                            AND loc_status IN ( 'O' )
                            AND loc_nome LIKE 'EXTRA%'
                            --AND    LOC.LOC_LEITO_ID IS NOT NULL
                            AND ( loc.loc_del_logica = 'N'
                                   OR loc.loc_del_logica IS NULL )
              ) AS EXTRA_OCUPADO,
              (SELECT Count (*)
                     FROM   lto,
                            loc,
                            hsp,
                            cle,
                            cnv
                     WHERE  lto_pac_reg = hsp_pac
                            AND lto_hsp_num = hsp_num
                            AND lto_loc_cod = loc_cod
                            AND loc_str = str_cod
                            AND loc_cle_cod = cle_cod
                            AND cle_tipo = 'L'
                            AND loc.loc_leito_id IS NOT NULL
                            AND loc_status IN ( 'L', 'O' )
                            AND loc_nome NOT LIKE 'EXTRA%'
                            AND ( ( '".$dateBusca."' BETWEEN lto_dthr_ini AND lto_dthr_fim )
                                   OR ( lto.lto_dthr_ini = lto.lto_dthr_fim
                                          AND hsp_stat = 'A'
                                          AND lto_dthr_fim < '".$dateBusca."' ) )
				AND cnv_cod = hsp_cnv
                            ".(empty($conveniosFilter) ? "" : 'AND RTRIM(cnv_cod) in ('.$conveniosFilter.')')."
              ) AS PACIENTES,
              (SELECT Count (*)
                     FROM   cle,
                            loc
                     WHERE  loc_cle_cod = cle_cod
                            AND loc_str = str_cod
                            AND loc_str = 'TMO'
                            --AND     CLE_TIPO    = 'L' 
                            AND loc_status IN ( 'O' )
                            -- AND    LOC_NOME LIKE 'EXTRA%'
                            AND loc.loc_leito_id IS NULL
                            AND ( loc.loc_del_logica = 'N'
                            OR loc.loc_del_logica IS NULL )
              ) AS VIRTUAL_TMO
              ".($preserveLeito ? "
                     ,(SELECT Count (*)
                            FROM   lto,
                                   loc,
                                   hsp,
                                   cle
                            WHERE  lto_pac_reg = hsp_pac
                                   AND lto_hsp_num = hsp_num
                                   AND lto_loc_cod = loc_cod
                                   AND loc_str = str_cod
                                   AND loc_cle_cod = cle_cod
                                   AND cle_tipo = 'L'
                                   AND loc.loc_leito_id IS NOT NULL
                                   AND loc_status IN ( 'L', 'O' )
                                   AND loc_nome NOT LIKE 'EXTRA%'
                                   AND ( ( GETDATE() BETWEEN lto_dthr_ini AND lto_dthr_fim )
                                          OR ( lto.lto_dthr_ini = lto.lto_dthr_fim
                                                 AND hsp_stat = 'A'
                                                 AND lto_dthr_fim < GETDATE() ) )
                     ) AS PACIENTES_TOTAL,
                     (SELECT Count (*)
                            FROM   cle,
                                   loc,
                                   pac,
                                   cnv
                            WHERE  loc_cle_cod = cle_cod
                                   AND loc_str = str_cod
                                   and pac_reg = LOC_PAC
                                   and PAC_CNV = cnv_cod
                                   AND loc_status IN ( 'R' )
                                   AND ( loc.loc_del_logica = 'N'
                                          OR loc.loc_del_logica IS NULL )
                     ) AS RESERVA_TOTAL
              " : "");

              $where = "str_categ = 'I'
                     AND str_status = 'A'
                     AND str_cod IN ( 'UPS', 'UT1', 'UT2', 'UT5',
                         'UIT', 'UI4', 'UI5', 'UI7',
                         'TMO', 'UT7', 'UT8', 'UCV',
                         'UP1', 'UP3', '2X', '30', '32' )";

              $order = "str_nome";

              return (new SmartPainel($tables))->select($fields, $where, null, null, $order)->fetchAll(\PDO::FETCH_ASSOC);
       }
       // Método responsável por retornar a quantidade de leitos em estado de isolamento no HRG hoje
       public static function getIsolamentoHrgHoje()
       {
              $tables = "cle, loc, blc, str";

              $fields = "
              STR_COD,
              loc_nome,
              Max(blc_dthr_ini)";

              $where = "loc_cle_cod = cle_cod
              AND blc_loc_cod = loc_cod
              AND blc_status = loc_status
              AND loc_obs LIKE '%ISO%'
              AND cle_cod = 'ENF'
              AND loc_nome NOT LIKE 'EXTRA %'
              AND str_cod = loc.loc_str
              AND loc_status IN ( 'B' )
              AND ( loc.loc_del_logica = 'N' OR loc.loc_del_logica IS NULL )";
              $group = "str_cod, loc_nome";
              $order = "loc_nome ";

              return (new SmartPainel($tables))->select($fields, $where, null, $group, $order)->fetchAll(\PDO::FETCH_ASSOC);
       }
       // Método responsável por retornar a quantidade de internações no maternidade hoje
       public static function getInternacaoMaternidadeHoje($dateBusca, $conveniosFilter = null, $preserveLeito = false)
       {
              $tables = "STR";

              $fields = "STR_COD,
              CASE
                     WHEN STR_COD = 'UIT' THEN '3o Andar'
                     WHEN STR_COD = 'UI4' THEN '4o Andar'
                     WHEN STR_COD = 'UI5' THEN '5o Andar'
                     WHEN STR_COD = 'UI5' THEN '5o Andar'
                     WHEN STR_COD = 'UI7' THEN '7o Andar'
                     WHEN STR_COD = 'TMO' THEN 'TMO'
                     WHEN STR_COD = 'UT7' THEN 'UTI PED I CARDIO'
                     WHEN STR_COD = 'UT1' THEN 'UTI 1'
                     WHEN STR_COD = 'UT2' THEN 'UTI 2'
                     WHEN STR_COD = 'UT5' THEN 'UTI 3'
                     WHEN STR_COD = 'UT8' THEN 'UTI 4'
                     WHEN STR_COD = '32' THEN 'UTI 4o Andar'
                     WHEN STR_COD = 'UCV' THEN 'UTI PNV I'
                     WHEN STR_COD = 'UP1' THEN 'UTI PNV II'
                     WHEN STR_COD = 'UP3' THEN 'UTI PNV III'
                     WHEN STR_COD = 'UPS' THEN 'UTI 5'
                     WHEN STR_COD = '2X' THEN 'UI Lobby'
                     WHEN STR_COD = '30' THEN 'UI ISO 7o Andar'
                     WHEN STR_COD = 'UM1' THEN '1º Andar Mat/Inf'
                     WHEN STR_COD = 'UM3' THEN '3º Andar Mat/Inf'
                     WHEN STR_COD = 'PE3' THEN 'UTI NEONATAL'
                     WHEN STR_COD = 'PE2' THEN 'UTI PED II'
              END AS SETOR,
              
              (SELECT COUNT (*)
                     FROM CLE,
                            LOC".(empty($conveniosFilter) || $preserveLeito ? '' : ', PAC, CNV')."
                     WHERE LOC_CLE_COD = CLE_COD
                            AND LOC_STR = STR_COD
                            AND LOC_STATUS in ('L','O','R')
                            AND LOC_NOME NOT LIKE 'EXTRA%'
                            AND LOC.LOC_LEITO_ID IS NOT NULL
                            AND (LOC.LOC_DEL_LOGICA = 'N'
                                   OR LOC.LOC_DEL_LOGICA IS NULL) 
                             
                            ".(empty($conveniosFilter) || $preserveLeito ? "" : 'AND PAC_REG = LOC_PAC AND PAC_CNV = CNV_COD AND RTRIM(cnv_cod) in ('.$conveniosFilter.')')."
              ) AS LEITOS,

              (SELECT COUNT (*)
                     FROM CLE,
                            LOC
                     WHERE LOC_CLE_COD = CLE_COD
                            AND LOC_STR = STR_COD
                            AND LOC_STATUS in ('B')
                            AND (LOC.LOC_DEL_LOGICA = 'N'
                                   OR LOC.LOC_DEL_LOGICA IS NULL)

              ) AS BLOQUEADOS,
              
              (SELECT COUNT (*)
                     FROM CLE,
                            LOC".(empty($conveniosFilter) || $preserveLeito ? '' : ', PAC, CNV')."
                     WHERE 
                            LOC_CLE_COD = CLE_COD
                            AND LOC_STR = STR_COD 
                            AND LOC_STATUS in ('R') 
                            AND (LOC.LOC_DEL_LOGICA = 'N'
                                   OR LOC.LOC_DEL_LOGICA IS NULL) 
                            ".(empty($conveniosFilter) || $preserveLeito ? "" : 'AND PAC_REG = LOC_PAC AND CNV_COD = PAC_CNV AND RTRIM(cnv_cod) in ('.$conveniosFilter.')')."
              ) AS RESERVA,
              
              (SELECT COUNT (*)
                     FROM CLE,
                            LOC
                     WHERE LOC_CLE_COD = CLE_COD
                            AND LOC_STR = STR_COD
                            AND LOC_STATUS in ('L','O','R')
                            AND LOC_NOME LIKE 'EXTRA%'
                            AND (LOC.LOC_DEL_LOGICA = 'N'
                                   OR LOC.LOC_DEL_LOGICA IS NULL) 
              ) AS EXTRA_TOTAL,
              
              (SELECT COUNT (*)
                     FROM CLE,
                            LOC
                     WHERE LOC_CLE_COD = CLE_COD
                            AND LOC_STR = STR_COD
                            
                            AND LOC_STATUS in ('O')
                            AND LOC_NOME LIKE 'EXTRA%'
                            
                            AND (LOC.LOC_DEL_LOGICA = 'N'
                                   OR LOC.LOC_DEL_LOGICA IS NULL) 
              ) AS EXTRA_OCUPADO,
              
              (SELECT COUNT (*)
                     FROM LTO,
                            LOC,
                            HSP,
                            CLE
                            ".(empty($conveniosFilter) ? '' : ',CNV')."
                            
                     WHERE LTO_PAC_REG = HSP_PAC
                            AND LTO_HSP_NUM = HSP_NUM
                            AND LTO_LOC_COD = LOC_COD
                            AND LOC_STR = STR_COD
                            AND LOC_CLE_COD = CLE_COD
                            AND CLE_TIPO = 'L'
                            AND LOC.LOC_LEITO_ID IS NOT NULL
                            AND LOC_STATUS in ('L', 'O')
                            AND LOC_NOME NOT LIKE 'EXTRA%'
                            AND (('$dateBusca' BETWEEN LTO_DTHR_INI AND LTO_DTHR_FIM)
                                   OR (LTO.LTO_DTHR_INI = LTO.LTO_DTHR_FIM
                                          AND HSP_STAT = 'A'
                                          AND LTO_DTHR_FIM < '$dateBusca'))
                            
                            ".(empty($conveniosFilter) ? "" : 'AND cnv_cod = hsp_cnv AND RTRIM(cnv_cod) in ('.$conveniosFilter.')')."
              ) AS PACIENTES,
              
              (SELECT COUNT (*)
                     FROM CLE,
                            LOC
                     WHERE LOC_CLE_COD = CLE_COD
                            AND LOC_STR = STR_COD
                            AND LOC_STR = 'TMO'
                            
                            AND LOC_STATUS in ('O')
                            
                            AND LOC.LOC_LEITO_ID IS NULL
                            AND (LOC.LOC_DEL_LOGICA = 'N'
                                   OR LOC.LOC_DEL_LOGICA IS NULL) 
              ) AS VIRTUAL_TMO
              ".($preserveLeito ? "
                     ,(SELECT COUNT (*)
                            FROM LTO,
                                   LOC,
                                   HSP,
                                   CLE
                                   
                            WHERE LTO_PAC_REG = HSP_PAC
                                   AND LTO_HSP_NUM = HSP_NUM
                                   AND LTO_LOC_COD = LOC_COD
                                   AND LOC_STR = STR_COD
                                   AND LOC_CLE_COD = CLE_COD
                                   AND CLE_TIPO = 'L'
                                   AND LOC.LOC_LEITO_ID IS NOT NULL
                                   AND LOC_STATUS in ('L', 'O')
                                   AND LOC_NOME NOT LIKE 'EXTRA%'
                                   AND (('$dateBusca' BETWEEN LTO_DTHR_INI AND LTO_DTHR_FIM)
                                          OR (LTO.LTO_DTHR_INI = LTO.LTO_DTHR_FIM
                                                 AND HSP_STAT = 'A'
                                                 AND LTO_DTHR_FIM < '$dateBusca'))
                     ) AS PACIENTES_TOTAL,
                     (SELECT COUNT (*)
                            FROM CLE,
                                   LOC
                            WHERE 
                                   LOC_CLE_COD = CLE_COD
                                   AND LOC_STR = STR_COD 
                                   AND LOC_STATUS in ('R') 
                                   AND (LOC.LOC_DEL_LOGICA = 'N'
                                          OR LOC.LOC_DEL_LOGICA IS NULL)
                     ) AS RESERVA_TOTAL
              " : "");

              $where = "STR_CATEG = 'I'
              AND STR_STATUS = 'A'
              AND STR_COD IN ('UM1','UM3','PE3','PE2')";
              $group = null;
              $order = "STR_NOME";

              return (new SmartPainel($tables))->select($fields, $where, null, $group, $order)->fetchAll(\PDO::FETCH_ASSOC);
       }
       // Método responsável por retornar a quantidade de leitos em estado de isolamento no maternidade hoje
       public static function getIsolamentoMaternidadeHoje()
       {
              $tables = "CLE, LOC, BLC, str";

              $fields = "STR_COD, loc_nome, MAX(blc_dthr_ini)";
              $where = "
              LOC_CLE_COD = CLE_COD  and
              blc_loc_cod = loc_cod and
              BLC_STATUS = LOC_STATUS and
              LOC_OBS LIKE '%ISO%' and
              CLE_COD = 'ENF' and 
              LOC_NOME not like 'EXTRA %' AND
              str_cod = loc.loc_str
              AND     LOC_STATUS  in ('B')
              AND     ( LOC.LOC_DEL_LOGICA = 'N' OR LOC.LOC_DEL_LOGICA IS NULL )";
              $group = "STR_COD, loc_nome";
              $order = "STR_COD, loc_nome";

              return (new SmartPainel($tables))->select($fields, $where, null, $group, $order)->fetchAll(\PDO::FETCH_ASSOC);
       }
       // Método responsável por retornar a quantidade de altas nas últimas 24 horas
       public static function getAltas24h($conveniosFilter = null)
       {
              $tables = "hsp, cnv";
              $fields = "count(*) as quantidade ";
              $where = "HSP_DTHRA between getdate() - 1 and getdate() and hsp_trat_int = 'i'
                     AND hsp_cnv = cnv_cod
                     ".(empty($conveniosFilter) ? "" : 'AND cnv_cod = hsp_cnv AND RTRIM(cnv_cod) in ('.$conveniosFilter.')');
              $group = null;
              $order = null;
              return (new SmartPainel($tables))->select($fields, $where, null, $group, $order)->fetchColumn();
       }

       public static function getAltasUltimosDias($dias, $conveniosFilter = null)
       {
              $tables = "hsp".(empty($conveniosFilter) ? '' : ',CNV');
              $fields = "count(*) as QUANTIDADE, CONVERT(DATE, HSP_DTHRA) as DIA, CASE 
              WHEN DATEPART(HOUR, HSP_DTHRA) BETWEEN 19 AND 23 OR DATEPART(HOUR, HSP_DTHRA) BETWEEN 0 AND 6
              THEN 'Noite'
              WHEN DATEPART(HOUR, HSP_DTHRA) BETWEEN 7 AND 12
              THEN 'Manhã'
              WHEN DATEPART(HOUR, HSP_DTHRA) BETWEEN 13 AND 18
              THEN 'Tarde'
              END AS TURNO";
              $where = "HSP_DTHRA between getdate() - $dias and getdate() and hsp_trat_int = 'i'
                     ".(empty($conveniosFilter) ? "" : 'AND cnv_cod = hsp_cnv AND RTRIM(cnv_cod) in ('.$conveniosFilter.')');

              $group = "CONVERT(DATE, HSP_DTHRA), CASE 
                            WHEN DATEPART(HOUR, HSP_DTHRA) BETWEEN 19 AND 23 OR DATEPART(HOUR, HSP_DTHRA) BETWEEN 0 AND 6
                                   THEN 'Noite'
                            WHEN DATEPART(HOUR, HSP_DTHRA) BETWEEN 7 AND 12
                                   THEN 'Manhã'
                            WHEN DATEPART(HOUR, HSP_DTHRA) BETWEEN 13 AND 18
                                   THEN 'Tarde'
                            END";
              $order = "DIA";

              return (new SmartPainel($tables))->select($fields, $where, null, $group, $order)->fetchAll(\PDO::FETCH_ASSOC);
       }

       public static function getAdmissaoUltimosDias($dias, $conveniosFilter = null)
       {
              $tables = "hsp".(empty($conveniosFilter) ? '' : ',CNV');
              $fields = "count(*) as QUANTIDADE, CONVERT(DATE, HSP_DTHRE) as DIA, CASE 
              WHEN DATEPART(HOUR, HSP_DTHRE) BETWEEN 19 AND 23 OR DATEPART(HOUR, HSP_DTHRE) BETWEEN 0 AND 6
              THEN 'Noite'
              WHEN DATEPART(HOUR, HSP_DTHRE) BETWEEN 7 AND 12
              THEN 'Manhã'
              WHEN DATEPART(HOUR, HSP_DTHRE) BETWEEN 13 AND 18
              THEN 'Tarde'
              END AS TURNO";
              $where = "HSP_DTHRE between getdate() - $dias and getdate() and hsp_trat_int = 'i'
                     ".(empty($conveniosFilter) ? "" : 'AND cnv_cod = hsp_cnv AND RTRIM(cnv_cod) in ('.$conveniosFilter.')');
              $group = "CONVERT(DATE, HSP_DTHRE), CASE 
                            WHEN DATEPART(HOUR, HSP_DTHRE) BETWEEN 19 AND 23 OR DATEPART(HOUR, HSP_DTHRE) BETWEEN 0 AND 6
                                   THEN 'Noite'
                            WHEN DATEPART(HOUR, HSP_DTHRE) BETWEEN 7 AND 12
                                   THEN 'Manhã'
                            WHEN DATEPART(HOUR, HSP_DTHRE) BETWEEN 13 AND 18
                                   THEN 'Tarde'
                            END";
              $order = "DIA";

              return (new SmartPainel($tables))->select($fields, $where, null, $group, $order)->fetchAll(\PDO::FETCH_ASSOC);
       }

       // Método responsável por retornar a quantidade de altas nas últimas 12 horas
       public static function getAltas12h($conveniosFilter = null)
       {
              $tables = "hsp, cnv";
              $fields = "count(*) as quantidade ";
              $where = "HSP_DTHRA between dateadd(hour, -12, getdate()) and getdate() and hsp_trat_int = 'i'
                     AND hsp_cnv = cnv_cod
                     ".(empty($conveniosFilter) ? "" : 'AND cnv_cod = hsp_cnv AND RTRIM(cnv_cod) in ('.$conveniosFilter.')');
              $group = null;
              $order = null;
              return (new SmartPainel($tables))->select($fields, $where, null, $group, $order)->fetchColumn();
       }
       // Método responsável por retornar os convênios dos pacientes que estão em internação
       public static function getInternacaoConvenios(string $convenios)
       {
              $tables = "LTO, LOC, HSP, CNV";
              $fields = "CNV_NOME AS NOME, 'A' AS TIPO, COUNT (*) AS QUANTIDADE";

              // AND CNV_COD IN (" . $convenios . ");
              $where = "LTO_PAC_REG = HSP_PAC
              AND LTO_HSP_NUM = HSP_NUM
              AND HSP_CNV = CNV_COD
              
              AND LTO_LOC_COD = LOC_COD
              AND ((GETDATE() BETWEEN LTO_DTHR_INI AND LTO_DTHR_FIM)
                   OR (LTO.LTO_DTHR_INI = LTO.LTO_DTHR_FIM
                       AND HSP_STAT = 'A'
                       AND LTO_DTHR_FIM < GETDATE()))";
              $group = "CNV_NOME";
              $order = null;
              $unionQuery = "SELECT CNV_NOME AS CONVENIO,
                                   CLE_COD,
                                   COUNT (*) AS QTD
                            FROM LTO,
                            LOC,
                            HSP,
                            CNV,
                            CLE
                            WHERE LTO_PAC_REG = HSP_PAC
                            AND LTO_HSP_NUM = HSP_NUM
                            AND HSP_CNV = CNV_COD
                            
                            AND LTO_LOC_COD = LOC_COD
                            AND LOC_CLE_COD = CLE_COD
                            AND ((GETDATE() BETWEEN LTO_DTHR_INI AND LTO_DTHR_FIM)
                                   OR (LTO.LTO_DTHR_INI = LTO.LTO_DTHR_FIM
                                   AND HSP_STAT = 'A'
                                   AND LTO_DTHR_FIM < GETDATE()))
                            GROUP BY CNV_NOME,
                                   CLE_COD
                            ORDER BY TIPO,
                                   COUNT (*) DESC";
                                   // AND CNV_COD IN (" . $convenios . ");
              return (new SmartPainel($tables))->unionAll($fields, $where, null, $group, $order, $unionQuery)->fetchAll(\PDO::FETCH_ASSOC);
       }

       public static function getPatientBySector(string $sectorCode, string $convenios)
       {
              $fields = "
                     CASE 
                            WHEN STR_COD = 'UIT' THEN '3o Andar'
                            WHEN STR_COD = 'UI4' THEN '4o Andar' 
                            WHEN STR_COD = 'UI5' THEN '5o Andar' 
                            WHEN STR_COD = 'TMO' THEN 'TMO' 
                            WHEN STR_COD = 'UT7' THEN 'UTI P.' 
                            WHEN STR_COD = 'UT1' THEN 'UTI 1' 
                            WHEN STR_COD = 'UT2' THEN 'UTI 2' 
                            WHEN STR_COD = 'UT5' THEN 'UTI 3' 
                            ELSE STR_NOME 
                     END AS setor,
                     LOC_NOME as leito,
                     LOC_LEITO_ID as leito_id,
                     LOC_STATUS as leito_status,
                     (SELECT PSV_NOME FROM HSP,PSV WHERE HSP_TRAT_INT = 'I' AND HSP_PAC = LOC_PAC AND HSP_STAT = 'A' AND PSV_COD=HSP_MDE) as medico_responsavel,
                     (SELECT DATEDIFF(DD,HSP_DTHRE,GETDATE()) FROM HSP,PSV WHERE HSP_TRAT_INT = 'I' AND HSP_PAC = LOC_PAC AND HSP_STAT = 'A' AND PSV_COD=HSP_MDE) dias_internados,
                     (SELECT CNV_NOME FROM HSP, CNV WHERE HSP_TRAT_INT = 'I' AND HSP_PAC = LOC_PAC AND HSP_STAT = 'A' AND HSP_CNV=CNV_COD) as convenio,
                     (SELECT TOP 1 BLC_OBS FROM BLC WHERE BLC_LOC_COD = LOC_COD AND BLC_STATUS = 'B' AND BLC_DTHR_FIM IS NULL ORDER BY BLC_DTHR_INI DESC) as leito_bloqueado,
                     (SELECT TOP 1 CONVERT(VARCHAR, BLC_DTHR_INI, 23) FROM BLC WHERE BLC_LOC_COD = LOC_COD AND BLC_STATUS = 'B' AND BLC_DTHR_FIM IS NULL ORDER BY BLC_DTHR_INI DESC) as dthr_bloqueio,
                     PAC_NOME as paciente_nome,
                     DATEDIFF(YY,PAC_NASC, GETDATE()) as idade";
              $tables = "LOC, STR, PAC";
                     //cnv
              $where = "LOC_STR = STR_COD AND
                     LOC_PAC*=PAC_REG AND
                     STR_STATUS = 'A' AND
                     LOC_DEL_LOGICA = 'N' AND
                     LOC_STATUS <> 'I' AND
                     STR_CATEG	= 'I' AND
                     STR_COD = '$sectorCode'";
                     
                     // AND 
                     // pac_reg = loc_pac AND 
                     // pac_cnv = cnv_cod ";
                     // AND RTRIM(cnv_cod) in (".$convenios.")";
              $order = "STR_NOME, LOC_NOME";

              return (new SmartPainel($tables))->select($fields, $where, null, null, $order)->fetchAll(\PDO::FETCH_ASSOC);
       }
       public static function getRecemNascidos($conveniosFilter = null)
       {
              $fields = "count(*) as 'RN'";
              $tables = "LOC, PAC, cnv";
              $where = "LOC_LEITO_ID is null and
              LOC_STATUS = 'O' AND
              LOC_STR = 'UM3' AND
              LOC_PAC = PAC_REG AND
              LOC_NOME like '%berço%'
		and PAC_CNV = cnv_cod
              ".(empty($conveniosFilter) ? "" : 'AND RTRIM(cnv_cod) in ('.$conveniosFilter.')');

              return (new SmartPainel($tables))->select($fields, $where)->fetchColumn();
       }
}
