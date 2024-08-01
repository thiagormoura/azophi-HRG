<?php

namespace App\Model\Monexm;

use App\Db\SmartPainel;

class Exame
{
  // Método responsável por retornar todos os exames dentro de um range de datas
  public static function getAllExamesByDates($dateFirst, $dateSecond)
  {
    $tables = "
    osm,
    str,
    SMM LEFT JOIN ALD ALD_ENVIO 
        ON SMM_OSM_SERIE = ALD_ENVIO.ALD_OSM_SERIE 
        AND SMM_OSM = ALD_ENVIO.ALD_OSM_NUM 
        AND SMM_NUM = ALD_ENVIO.ALD_SMM_NUM 
        AND ALD_STAT = 'F'
        LEFT JOIN ALD ALD_RESULTADO
        ON SMM_OSM_SERIE = ALD_RESULTADO.ALD_OSM_SERIE 
        AND SMM_OSM = ALD_RESULTADO.ALD_OSM_NUM 
        AND SMM_NUM = ALD_RESULTADO.ALD_SMM_NUM 
        AND ALD_RESULTADO.ALD_STAT = 'L' 
        and ALD_RESULTADO.ALD_OBS like '%INTEGRALAB%',
    SMK,
    pex,
    EXM,
    ctf";

    $fields = "
    distinct
    'lab' as modalidade,
    OSM_DTHR AS OS_DTHR,
    OSM_SERIE as OS_SERIE,   
    OSM_NUM as OS_NUMERO,
    OSM_STR as SETOR_COD,
    SMM_NUM AS ITEM_NUM,
    SMM_COD AS CODIGO,
    smm_qt as QTD,
    case when MAX(ALD_RESULTADO.ALD_DTHR) is null then 0 else 1 end as RESULTADOS,
    SMK_NOME AS EXAME,
    STR_NOME AS SETOR,
    OSM_DTHR as LANCAMENTO,
    SMM_DTHR_COLETA AS COLETA,
    smm_exec AS ST,
    MIN(ALD_RESULTADO.ALD_DTHR) AS RESULTADO_INICAL,
    MAX(ALD_RESULTADO.ALD_DTHR) AS RESULTADO_ULTIMO";

    $where = "
    OSM_DTHR BETWEEN '" . $dateFirst . "' and '" . $dateSecond . "' AND
    OSM_STR = str_cod and
    pex.PEX_OSM_SERIE = osm.osm_serie and
    pex.PEX_OSM_NUM = osm.osm_num and
    SMM_OSM_SERIE = OSM_SERIE AND
    SMM_OSM = OSM_NUM AND
    SMK_COD = SMM_COD AND
    SMM_EXEC <> 'c' and
    EXM_SMK_COD = SMK_COD AND
    SMK_CTF = CTF_COD AND CTF_CTF_COD = 2800
    ";

    $group = "
    PEX_DTHR,
    OSM_SERIE,   
    OSM_NUM,
    OSM_STR,
    SMM_NUM,
    SMM_COD,
    smm_qt,
    SMK_NOME,
    STR_NOME,
    smm_exec,
    SMM_DTHR_COLETA ,
    SMM_DTHR_LANC ,
    OSM_DTHR";
    $order = "osm_dthr, os_serie, os_numero, item_num, STR_NOME";

    return (new SmartPainel($tables))->select($fields, $where, null, $group, $order)->fetchAll(\PDO::FETCH_ASSOC);
  }

  // Método responsável por retornar todos os exames dentro de um range de datas e por um determinado setor
  public static function getAllExamesByDatesAndSector($dateFirst, $dateSecond, $sector)
  {
    $tables = "  
    SMM,
    OSM,
    STR";

    $fields = "DISTINCT
    OSM_SERIE as OS_SERIE,   
    OSM_NUM as OS_NUMERO,
    STR_NOME AS SETOR,
    SMM_NUM,
    SMM_QT AS QTD,
    OSM_DTHR as LANCAMENTO,
    smm_exec AS ST";

    $where = "
    OSM.OSM_STR = STR_COD AND
    SMM_OSM = OSM_NUM AND
    SMM_OSM_SERIE = OSM_SERIE AND
    OSM_DTHR BETWEEN CONVERT(DATETIME, '$dateFirst', 102) and CONVERT(DATETIME, '$dateSecond', 102) AND
    OSM_STR = str_cod AND
    smm_exec <> 'C' AND
    OSM_STR like '$sector%' AND
    EXISTS ( SELECT 1 
				FROM 
					SMK,
					CTF 
				WHERE 
					SMM_COD = SMK_COD 
				AND SMM_TPCOD = SMK_TIPO 
				AND SMK_CTF = CTF_COD 
				AND CTF_CTF_COD = 2800)";

    $group = null;
    $order = "OS_SERIE, OS_NUMERO, STR_NOME";

    return (new SmartPainel($tables))->select($fields, $where, null, $group, $order)->fetchAll(\PDO::FETCH_ASSOC);
  }
}
