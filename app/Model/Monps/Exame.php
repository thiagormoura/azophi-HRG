<?php

namespace App\Model\Monps;

use App\Db\SmartPainel;

class Exame
{
  // Método responsável por retornar os exames do laboratório
  public static function getExameLab($horaChegada, $registro)
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
    lwl,
    pex,
    EXM,
    ctf";

    $fields = "distinct
    'lab' as modalidade,
    smm_exec,
    PEX_DTHR AS OS_DTHR,
    OSM_SERIE as OS_SERIE,   
    OSM_NUM as OS_NUMERO,
    SMM_NUM AS ITEM_NUM,
    SMM_COD AS CODIGO,
    SMK_NOME AS EXAME,
    STR_NOME AS SETOR,
    OSM_DTHR as LANCAMENTO,
    SMM_DTHR_COLETA ,
    SMM_DTHR_LANC ,
    lwl.LWL_DTHR as ENVIO,
    max(ALD_ENVIO.ALD_DTHR) AS RECEBIMENTO,
    ALD_RESULTADO.ALD_DTHR AS RESULTADO";

    $where = "
    OSM_DTHR BETWEEN DATEADD(MINUTE, " . -$horaChegada . ", GETDATE()) and GETDATE() AND
    OSM_PAC = " . $registro . " AND
    OSM_STR = str_cod and
    pex.PEX_OSM_SERIE = osm.osm_serie and
    pex.PEX_OSM_NUM = osm.osm_num and
    SMM_OSM_SERIE = OSM_SERIE AND
    SMM_OSM = OSM_NUM AND
    SMK_COD = SMM_COD AND
    osm_str = 'pat' and
    SMM_EXEC <> 'c' and
    EXM_SMK_COD = SMK_COD AND
    (OSM_SERIE	= lwl.lwl_osm_serie and  
    osm_num		= lwl.lwl_osm_num AND
    LWL_B2B_PADRAO = 'NDLab' and lwl_num_origem like '%INCLUSAO ERRO = N%') and
    SMK_CTF = CTF_COD AND CTF_CTF_COD = 2800";

    $group = "
    PEX_DTHR,
    OSM_SERIE,   
    OSM_NUM,
    SMM_NUM,
    SMM_COD,
    SMK_NOME,
    STR_NOME,
    smm_exec,
    SMM_DTHR_COLETA ,
    SMM_DTHR_LANC ,
    lwl.LWL_DTHR,
    OSM_DTHR,
    ALD_RESULTADO.ALD_DTHR";

    $order = "osm_dthr,os_serie,os_numero,item_num, STR_NOME";

    return (new SmartPainel($tables))->select($fields, $where, null, $group, $order)->fetchAll(\PDO::FETCH_ASSOC);
  }

  // Método responsável por retornar os exames img
  public static function getExameImg($horaChegada, $registro)
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

    $fields = "distinct
    'IMG' as modalidade,
        PEX_DTHR AS OS_DTHR,
    OSM_SERIE as OS_SERIE,   
        OSM_NUM as OS_NUMERO,
    SMM_NUM AS ITEM_NUM,
    SMM_COD AS CODIGO,
    SMK_NOME AS EXAME,
    STR_NOME AS SETOR,
    OSM_DTHR as LANCAMENTO,
    SMM_DTHR_COLETA ,
    SMM_DTHR_LANC ,
    smm_exec,
    max(ALD_ENVIO.ALD_DTHR) AS RECEBIMENTO,
    ALD_RESULTADO.ALD_DTHR AS RESULTADO";

    $where = "
    OSM_DTHR BETWEEN DATEADD(MINUTE, " . -$horaChegada . ", GETDATE()) and GETDATE() AND
    OSM_PAC = " . $registro . " AND
    OSM_STR = str_cod and
    pex.PEX_OSM_SERIE = osm.osm_serie and
    pex.PEX_OSM_NUM = osm.osm_num and
    SMM_OSM_SERIE = OSM_SERIE AND
    SMM_OSM = OSM_NUM AND
    SMK_COD = SMM_COD AND
    osm_str = 'pat' and
    SMM_EXEC <> 'c' and
    EXM_SMK_COD = SMK_COD AND
    SMK_CTF = ctf_cod and
    ctf_ctf_cod in (3200,3300,3400)";

    $group = "
    PEX_DTHR,
    OSM_SERIE,   
    OSM_NUM,
    SMM_NUM,
    SMM_COD,
    SMK_NOME,
    STR_NOME,
    smm_exec,
    SMM_DTHR_COLETA ,
    SMM_DTHR_LANC ,
    OSM_DTHR,
    ALD_RESULTADO.ALD_DTHR";

    $order = "osm_dthr,os_serie,os_numero,item_num, STR_NOME";

    return (new SmartPainel($tables))->select($fields, $where, null, $group, $order)->fetchAll(\PDO::FETCH_ASSOC);
  }

  // Método responsável por retornar os exames do laboratório
  public static function getExameLabForPaciente($registro, $hspNum)
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
    lwl,
    pex,
    EXM,
    ctf";

    $fields = "distinct
    'lab' as modalidade,
    smm_exec,
    PEX_DTHR AS OS_DTHR,
    OSM_SERIE as OS_SERIE,   
    OSM_NUM as OS_NUMERO,
    SMM_NUM AS ITEM_NUM,
    SMM_COD AS CODIGO,
    SMK_NOME AS EXAME,
    STR_NOME AS SETOR,
    OSM_DTHR as LANCAMENTO,
    SMM_DTHR_COLETA ,
    SMM_DTHR_LANC ,
    lwl.LWL_DTHR as ENVIO,
    max(ALD_ENVIO.ALD_DTHR) AS RECEBIMENTO,
    ALD_RESULTADO.ALD_DTHR AS RESULTADO";

    $where = "
    OSM_PAC = " . $registro . " AND
    OSM_STR = str_cod and
    pex.PEX_OSM_SERIE = osm.osm_serie and
    pex.PEX_OSM_NUM = osm.osm_num and
    SMM_OSM_SERIE = OSM_SERIE AND
    SMM_OSM = OSM_NUM AND
    SMM_PAC_REG = $registro AND
    SMM_HSP_NUM = $hspNum AND
    SMK_COD = SMM_COD AND
    osm_str = 'pat' and
    SMM_EXEC <> 'c' and
    EXM_SMK_COD = SMK_COD AND
    (OSM_SERIE	= lwl.lwl_osm_serie and  
    osm_num		= lwl.lwl_osm_num AND
    LWL_B2B_PADRAO = 'NDLab' and lwl_num_origem like '%INCLUSAO ERRO = N%') and
    SMK_CTF = CTF_COD AND CTF_CTF_COD = 2800";

    $group = "
    PEX_DTHR,
    OSM_SERIE,   
    OSM_NUM,
    SMM_NUM,
    SMM_COD,
    SMK_NOME,
    STR_NOME,
    smm_exec,
    SMM_DTHR_COLETA ,
    SMM_DTHR_LANC ,
    lwl.LWL_DTHR,
    OSM_DTHR,
    ALD_RESULTADO.ALD_DTHR";

    $order = "osm_dthr,os_serie,os_numero,item_num, STR_NOME";

    return (new SmartPainel($tables))->select($fields, $where, null, $group, $order)->fetchAll(\PDO::FETCH_ASSOC);
  }

  // Método responsável por retornar os exames img
  public static function getExameImgForPaciente($registro, $hspNum)
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

    $fields = "distinct
    'IMG' as modalidade,
        PEX_DTHR AS OS_DTHR,
    OSM_SERIE as OS_SERIE,   
        OSM_NUM as OS_NUMERO,
    SMM_NUM AS ITEM_NUM,
    SMM_COD AS CODIGO,
    SMK_NOME AS EXAME,
    STR_NOME AS SETOR,
    OSM_DTHR as LANCAMENTO,
    SMM_DTHR_COLETA ,
    SMM_DTHR_LANC ,
    smm_exec,
    max(ALD_ENVIO.ALD_DTHR) AS RECEBIMENTO,
    ALD_RESULTADO.ALD_DTHR AS RESULTADO";

    $where = "
    OSM_PAC = " . $registro . " AND
    OSM_STR = str_cod and
    pex.PEX_OSM_SERIE = osm.osm_serie and
    pex.PEX_OSM_NUM = osm.osm_num and
    SMM_OSM_SERIE = OSM_SERIE AND
    SMM_OSM = OSM_NUM AND
    SMM_PAC_REG = $registro AND
    SMM_HSP_NUM = $hspNum AND
    SMK_COD = SMM_COD AND
    osm_str = 'pat' and
    SMM_EXEC <> 'c' and
    EXM_SMK_COD = SMK_COD AND
    SMK_CTF = ctf_cod and
    ctf_ctf_cod in (3200,3300,3400)";

    $group = "
    PEX_DTHR,
    OSM_SERIE,   
    OSM_NUM,
    SMM_NUM,
    SMM_COD,
    SMK_NOME,
    STR_NOME,
    smm_exec,
    SMM_DTHR_COLETA ,
    SMM_DTHR_LANC ,
    OSM_DTHR,
    ALD_RESULTADO.ALD_DTHR";

    $order = "osm_dthr,os_serie,os_numero,item_num, STR_NOME";

    return (new SmartPainel($tables))->select($fields, $where, null, $group, $order)->fetchAll(\PDO::FETCH_ASSOC);
  }
}
