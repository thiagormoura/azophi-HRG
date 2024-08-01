<?php

namespace App\Model\Inutri;

use App\Db\Smart;

class Paciente
{
  // Método responsável por retornar um determinado paciente que está em atendimento no momento com enfâse nas filas
public static function getDietaPaciente($registro)
  {
    $tables = "	
    PSC left join DTA on dta_pac_reg = PSC_PAC AND dta_hsp_num = PSC_HSP AND dta_psc_num = PSC_NUM,
    ADP,
    HSP,
    STR,
    LOC,
    pac";

    $fields = "	
    STR_COD as setor_code,
    STR_NOME as setor_nome,
    loc_nome,
    PSC_PAC as prescricao,
    pac_reg as paciente_registro,
    pac_nome as paciente,
    PSC_HSP as psc_hsp,
    max(PSC_DHINI) AS data_prescricao,
    PSC_STAT as status_presc,
    ADP_COD as dieta_code,
    ADP_NOME as dieta_nome,
    PSC_DHINI";

    $where = "
    pac_reg = $registro AND
    ADP_COD = PSC_ADP AND
    HSP_PAC = PSC_PAC AND
    HSP_NUM = PSC_HSP AND
    HSP_TRAT_INT = 'I' AND
    HSP_LOC = loc_cod and
    str_cod = LOC_STR and
    PSC_PAC = pac_reg
    AND PSC_TIP = 'D' AND
    PSC_DHINI = (SELECT MAX(PSC_DHINI) FROM PSC, ADP WHERE PSC_PAC = HSP_PAC AND PSC_HSP = HSP_NUM AND ADP_COD = PSC_ADP AND  adp.adp_tipo = 'D')";

    $group = "
    STR_COD,
    STR_NOME,
    loc_nome,
    PSC_PAC,
    pac_reg,
    pac_nome,
    PSC_HSP,
    PSC_STAT,
    ADP_COD,
    ADP_NOME,
    PSC_DHINI";

    $order = null;

    $union = "
    SELECT 
      STR_COD,
      STR_NOME,
      '',
      PSC_PAC,
      pac_reg,
      pac_nome,
      PSC_HSP,
      max(PSC_DHINI),
      PSC_STAT,
      ADP_COD,
      ADP_NOME,
      PSC_DHINI
    FROM 
      PSC,
      DTA,
      ADP,
      HSP,
      STR,
      pac
    WHERE 
      pac_reg = $registro AND
      dta_pac_reg = PSC_PAC AND
      dta_hsp_num = PSC_HSP AND
      dta_psc_num = PSC_NUM AND 
      ADP_COD = PSC_ADP AND
      HSP_PAC = PSC_PAC AND
      HSP_NUM = PSC_HSP AND
      HSP_TRAT_INT = 'T' AND 
      HSP_STR_COD = STR_COD and 
      PSC_PAC = pac_reg AND
      PSC_DHINI = (SELECT MAX(PSC_DHINI ) FROM PSC, ADP WHERE PSC_PAC = HSP_PAC AND PSC_HSP = HSP_NUM AND ADP_COD = PSC_ADP AND  adp.adp_tipo = 'D' )
    group by 
      STR_COD,
      STR_NOME,
      PSC_PAC,
      pac_reg,
      pac_nome,
      PSC_HSP,
      PSC_STAT,
      ADP_COD,
      ADP_NOME,
      PSC_DHINI
    ORDER BY 
      PSC_DHINI DESC";
    return (new Smart($tables))->unionAll($fields, $where, null, $group, $order, $union)->fetch(\PDO::FETCH_OBJ);
  }

  // Método responsável por retornar todas as unidades que possuem paciente
  public static function getUnidades()
  {
    $tables = "LTO, LOC, HSP , CLE, STR, PAC, CNV, CLE C2, PSV";

    $fields = "
    STR_COD as code, STR_NOME as nome
    ";

    $where = "
    HSP_PAC = PAC_REG
    AND HSP_MDE = PSV_COD
    AND PAC_CLE_COD = C2.CLE_COD
    AND PAC_CNV = CNV_COD
    AND STR_STATUS = 'A' 
    AND STR_COD IN ('HEM','CIR','PAT','UT1','UT2','UT5','UIT','UI4','UI5','UI7', 'TMO','UT7','UT8','UCV','UP1','UP3','2X','30','32')
    AND LTO_PAC_REG     = HSP_PAC 
    AND LTO_HSP_NUM     = HSP_NUM 
    AND LTO_LOC_COD     = LOC_COD 
    AND ( (LOC_STR     = STR_COD ))
    AND LOC_CLE_COD     = CLE.CLE_COD 
    AND CLE.CLE_TIPO = 'L' 
    AND LOC_STATUS  in ('L','O')
    AND (( GETDATE() BETWEEN LTO_DTHR_INI  AND LTO_DTHR_FIM) OR ( LTO.LTO_DTHR_INI = LTO.LTO_DTHR_FIM 
    AND HSP_STAT = 'A'  
    AND LTO_DTHR_FIM < GETDATE()))";

    $group = "STR_COD, STR_NOME";

    $order = null;

    $union = "
    SELECT
      STR_COD,
      STR_NOME
    FROM
      HSP JOIN PAC ON PAC_REG = HSP_PAC
      LEFT JOIN CNV ON PAC_CNV = CNV_COD
      LEFT JOIN CLE ON PAC_CLE_COD = CLE_COD
      LEFT JOIN PSV ON HSP_MDE = PSV_COD,
      STR
    WHERE
      HSP_STAT = 'A' AND
      HSP_TRAT_INT = 'T' AND
      HSP_STR_COD = 'PAT' AND
      HSP_STR_COD = STR_COD
    GROUP BY STR_COD, STR_NOME
    ORDER BY STR_NOME";
    return (new Smart($tables))->unionAll($fields, $where, null, $group, $order, $union)->fetchAll(\PDO::FETCH_OBJ);
  }

  public static function getPacienteByUnidade($unidade)
  {
    $tables = "LTO, LOC, HSP , CLE, STR, PAC, CNV, CLE C2, PSV";

    $fields = "
    pac_reg as registro,
    pac_nome as nome,
    pac_nome_social as nome_social,
    LOC_NOME as local,
    LOC_COD as local_code,
    STR_COD as unidade_code,
    STR_NOME as unidade_nome
    ";

    $where = "
    HSP_PAC = PAC_REG
    AND HSP_MDE = PSV_COD
    AND PAC_CLE_COD = C2.CLE_COD
    AND PAC_CNV = CNV_COD
    AND STR_STATUS = 'A' 
    AND STR_COD IN ('HEM','CIR','PAT','UT1','UT2','UT5','UIT','UI4','UI5','UI7', 'TMO','UT7','UT8','UCV','UP1','UP3','2X','30','32')
    AND STR_COD LIKE '$unidade'
    AND LTO_PAC_REG     = HSP_PAC 
    AND LTO_HSP_NUM     = HSP_NUM 
    AND LTO_LOC_COD     = LOC_COD 
    AND ( (LOC_STR     = STR_COD ))
    AND LOC_CLE_COD     = CLE.CLE_COD 
    AND CLE.CLE_TIPO = 'L' 
    AND LOC_STATUS  in ('L','O')
    AND (( GETDATE() BETWEEN LTO_DTHR_INI  AND LTO_DTHR_FIM) OR ( LTO.LTO_DTHR_INI = LTO.LTO_DTHR_FIM 
    AND HSP_STAT = 'A'  
    AND LTO_DTHR_FIM < GETDATE()))";

    $group = null;

    $order = null;

    $union = "
    SELECT
      pac_reg as registro,
      pac_nome as nome,
      pac_nome_social as nome_social,
      '',
      '',
      STR_COD as unidade_code,
      STR_NOME as unidade_nome
    FROM
      HSP JOIN PAC ON PAC_REG = HSP_PAC
      LEFT JOIN CNV ON PAC_CNV = CNV_COD
      LEFT JOIN CLE ON PAC_CLE_COD = CLE_COD
      LEFT JOIN PSV ON HSP_MDE = PSV_COD,
      STR
    WHERE
      HSP_STAT = 'A' AND
      HSP_TRAT_INT = 'T' AND
      HSP_STR_COD = 'PAT' AND
      HSP_STR_COD = STR_COD AND
      STR_COD LIKE '$unidade'
    ORDER BY pac_nome";
    return (new Smart($tables))->unionAll($fields, $where, null, $group, $order, $union)->fetchAll(\PDO::FETCH_OBJ);
  }
}
