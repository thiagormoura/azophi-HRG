<?php

namespace App\Model\AzophiCC;

use App\Db\SmartPainel;

class AzophiCC
{

  // Método responsável por retornar os convênios por datas específicas
  public static function getConveniosByDate($data_inicial, $data_final)
  {
    $tables = "RCI INNER JOIN CNV ON RCI.RCI_CNV_COD = CNV.CNV_COD";
    $fields = "CNV.CNV_NOME AS nome, CNV.CNV_COD AS code";
    $where = "RCI.RCI_DTHR_INI BETWEEN CONVERT(DATETIME, '$data_inicial 00:00:00', 120) AND CONVERT(DATETIME, '$data_final 23:59:59', 120)";
    $group = "CNV.CNV_NOME, CNV.CNV_COD";
    $order = null;
    $unionQuery = "SELECT CNV.CNV_NOME AS nome, CNV.CNV_COD AS code
    FROM  AGM INNER JOIN CNV ON AGM.AGM_CNV_COD = CNV.CNV_COD
    WHERE AGM.AGM_HINI BETWEEN CONVERT(DATETIME, '$data_inicial 00:00:00', 120) AND CONVERT(DATETIME, '$data_final 23:59:59', 120)
    GROUP BY  CNV.CNV_NOME, CNV.CNV_COD  ORDER BY CNV.CNV_NOME, CNV.CNV_COD";

    return (new SmartPainel($tables))->select($fields, $where, null, $group, $order)->fetchAll(\PDO::FETCH_OBJ);
  }

  // Método responsável por retornar as cirurgias por datas específicas e determinado convênio
  public static function getCountCirurgiasAgendadas($data_inicial, $data_final, $convenio)
  {
    $tables = "agm , smk, psv, LOC, PAC, CNV";
    $fields = "COUNT(*)";
    $where = "
        (agm.agm_tpsmk = smk.smk_tipo) 
    and (agm.agm_smk = smk.smk_cod) 
    and (psv.psv_cod = agm.agm_med) 
    and (agm.agm_hini BETWEEN CONVERT(DATETIME, '$data_inicial 00:00:00', 120) AND CONVERT(DATETIME, '$data_final 23:59:59', 120))
    and (agm.agm_stat <> 'C') 
    and (smk.smk_ind_confirm_agm	= 'S') 
    and (loc.LOC_COD = agm.AGM_LOC)
    and (AGM_PAC	=	pac.PAC_REG)
    and (PAC_CNV =	cnv.cnv_cod)
    and (LOC.LOC_STR =	'CIR')";

    return (new SmartPainel($tables))->select($fields, $where, null, null, null)->fetchColumn();
  }

  // Método responsável por retornar as cirurgias realizadas em uma range de datas e por um determinado convênio
  public static function getCirurgiasRealizadas($data_inicial, $data_final, $convenio)
  {
    $tables = "RCI, RCI R2";
    $fields = "R2.RCI_RCI_SERIE, R2.RCI_RCI_NUM, R2.RCI_PAC_REG";
    $where = "
    RCI.RCI_DTHR_INI BETWEEN CONVERT(DATETIME, '$data_inicial 00:00:00', 120) AND CONVERT(DATETIME, '$data_final 23:59:59', 120)
    AND R2.RCI_RCI_NUM = RCI.RCI_NUM
    AND	R2.RCI_RCI_SERIE = RCI.RCI_SERIE";
    // AND RCI.RCI_CNV_COD IN ($convenio)";
    $group = "R2.RCI_RCI_SERIE, R2.RCI_RCI_NUM, R2.RCI_PAC_REG";
    $order = "R2.RCI_RCI_SERIE, R2.RCI_RCI_NUM";

    return (new SmartPainel($tables))->select($fields, $where, null, $group, $order)->fetchAll(\PDO::FETCH_OBJ);
  }

  // Método responsável por retornar os procedimentos realizados em um range de datas e por determinado convênio
  public static function getProcedimentos($data_inicial, $data_final, $convenio)
  {
    $tables = "RCI, RCI R2";
    $fields = "
    R2.RCI_RCI_SERIE, 
    R2.RCI_RCI_NUM, 
    R2.RCI_PAC_REG,
    R2.RCI_SMK_COD";
    $where = "
    RCI.RCI_DTHR_INI BETWEEN CONVERT(DATETIME, '$data_inicial 00:00:00', 120) AND CONVERT(DATETIME, '$data_final 23:59:59', 120)
    AND R2.RCI_RCI_NUM = RCI.RCI_NUM
    AND R2.RCI_RCI_SERIE = RCI.RCI_SERIE";
    // AND RCI.RCI_CNV_COD IN ($convenio)";
    $group = null;
    $order = "
    R2.RCI_RCI_SERIE, 
    R2.RCI_RCI_NUM";

    return (new SmartPainel($tables))->select($fields, $where, null, $group, $order)->fetchAll(\PDO::FETCH_OBJ);
  }
  // Método responsável por retornar as cirurgias suspensas em um range de datas
  public static function getCountCirurgiasSuspensas($data_inicial, $data_final)
  {
    $tables = "PAC, AGM, USR UC, PSV, STR SL, LOC , MOT";
    $fields = "COUNT(*) as qtd";
    $where = "
    ( AGM.AGM_PAC = PAC.PAC_REG ) AND 
    ( AGM.AGM_MED = PSV.PSV_COD ) AND 
    ( AGM.AGM_LOC = LOC.LOC_COD ) AND 
    (  SL.STR_COD = LOC.LOC_STR  ) AND 
    ( AGM.AGM_CANC_MOT_COD = MOT.MOT_COD OR AGM.AGM_CANC_MOT_COD IS NULL) AND
    (  AGM.AGM_CANC_USR_LOGIN = UC.USR_LOGIN  )  AND 
    ( agm.agm_hini BETWEEN CONVERT(DATETIME, '$data_inicial 00:00:00', 120) AND CONVERT(DATETIME, '$data_final 23:59:59', 120)) and
    ( SL.STR_NOME like 'centro%' ) AND
    ( AGM.agm_canc_mot_cod in ('AUT','ENC','EQM','FHD','FMT','MD','OPME','PCS','PD','PIM'))";
    $group = null;
    $order = null;

    return (new SmartPainel($tables))->select($fields, $where, null, $group, $order)->fetch(\PDO::FETCH_OBJ);
  }

  // Método responsável por retornar a quantidade de pacientes cirurgiados em um range de datas e por um determinado convênio
  public static function getCountPacientesCirurgiados($data_inicial, $data_final, $convenio)
  {
    $tables = "RCI";
    $fields = "COUNT(DISTINCT RCI_PAC_REG) as qtd_pacientes";
    $where = "RCI.RCI_DTHR_INI BETWEEN CONVERT(DATETIME, '$data_inicial 00:00:00', 120) AND CONVERT(DATETIME, '$data_final 23:59:59', 120) AND RCI.RCI_CNV_COD IN ($convenio)";
    $group = null;
    $order = null;

    return (new SmartPainel($tables))->select($fields, $where, null, $group, $order)->fetch(\PDO::FETCH_OBJ);
  }

  // Método responsável por retornar as cirurgias por meses em um range de datas epor um determinado convênio
  public static function getCirurgiasByMonths($data_inicial, $data_final, $convenio)
  {
    $tables = "RCI , RCI R2";
    $fields = "CASE 
    WHEN DATEPART(MM,R2.RCI_DTHR_INI) = 1 THEN 'Jan'
    WHEN DATEPART(MM,R2.RCI_DTHR_INI) = 2 THEN 'Fev'
    WHEN DATEPART(MM,R2.RCI_DTHR_INI) = 3 THEN 'Mar'
    WHEN DATEPART(MM,R2.RCI_DTHR_INI) = 4 THEN 'Abr'
    WHEN DATEPART(MM,R2.RCI_DTHR_INI) = 5 THEN 'Mai'
    WHEN DATEPART(MM,R2.RCI_DTHR_INI) = 6 THEN 'Jun'
    WHEN DATEPART(MM,R2.RCI_DTHR_INI) = 7 THEN 'Jul'
    WHEN DATEPART(MM,R2.RCI_DTHR_INI) = 8 THEN 'Ago'
    WHEN DATEPART(MM,R2.RCI_DTHR_INI) = 9 THEN 'Set'
    WHEN DATEPART(MM,R2.RCI_DTHR_INI) = 10 THEN 'Out'
    WHEN DATEPART(MM,R2.RCI_DTHR_INI) = 11 THEN 'Nov'
    WHEN DATEPART(MM,R2.RCI_DTHR_INI) = 12 THEN 'Dez'
    END AS mes,
    COUNT($) AS qtd";
    $where = "RCI.RCI_DTHR_INI BETWEEN CONVERT(DATETIME, '$data_inicial 00:00:00', 120) AND CONVERT(DATETIME, '$data_final 23:59:59', 120)
    AND    R2.RCI_RCI_NUM = RCI.RCI_NUM
    AND	   R2.RCI_RCI_SERIE = RCI.RCI_SERIE
    AND RCI.RCI_CNV_COD IN ($convenio)";
    $group = "DATEPART(MM,R2.RCI_DTHR_INI)";
    $order = "DATEPART(MM,R2.RCI_DTHR_INI)";

    return (new SmartPainel($tables))->select($fields, $where, null, $group, $order)->fetchAll(\PDO::FETCH_OBJ);
  }

  // Método responsável por retornar os X melhores meses de cirurgias em um range de dadas e de um determinado convênio 
  public static function getBestMonths($count, $data_inicial, $data_final, $convenio)
  {
    $tables = "RCI , RCI R2";
    $fields = "top $count
    CASE 
        WHEN DATEPART(MM,R2.RCI_DTHR_INI) = 1 THEN 'Janeiro'
        WHEN DATEPART(MM,R2.RCI_DTHR_INI) = 2 THEN 'Fevereiro'
        WHEN DATEPART(MM,R2.RCI_DTHR_INI) = 3 THEN 'Março'
        WHEN DATEPART(MM,R2.RCI_DTHR_INI) = 4 THEN 'Abril'
        WHEN DATEPART(MM,R2.RCI_DTHR_INI) = 5 THEN 'Maio'
        WHEN DATEPART(MM,R2.RCI_DTHR_INI) = 6 THEN 'Junho'
        WHEN DATEPART(MM,R2.RCI_DTHR_INI) = 7 THEN 'Julho'
        WHEN DATEPART(MM,R2.RCI_DTHR_INI) = 8 THEN 'Agosto'
        WHEN DATEPART(MM,R2.RCI_DTHR_INI) = 9 THEN 'Setembro'
        WHEN DATEPART(MM,R2.RCI_DTHR_INI) = 10 THEN 'Outubro'
        WHEN DATEPART(MM,R2.RCI_DTHR_INI) = 11 THEN 'Novembro'
        WHEN DATEPART(MM,R2.RCI_DTHR_INI) = 12 THEN 'Dezembro'
        END AS mes,
        COUNT(*) AS qtd";
    $where = "RCI.RCI_DTHR_INI BETWEEN CONVERT(DATETIME, '$data_inicial 00:00:00', 120) AND CONVERT(DATETIME, '$data_final 23:59:59', 120)
    AND    R2.RCI_RCI_NUM = RCI.RCI_NUM
    AND	   R2.RCI_RCI_SERIE = RCI.RCI_SERIE
    AND RCI.RCI_CNV_COD IN ($convenio)";
    $group = "DATEPART(MM,R2.RCI_DTHR_INI)";
    $order = "COUNT(*) desc";

    return (new SmartPainel($tables))->select($fields, $where, null, $group, $order)->fetchAll(\PDO::FETCH_OBJ);
  }

  // Método responsável por retornar as salas mais utilizadas em um range de datas e por um determinado convênio
  public static function getSalasMaisUtilizadas($data_inicial, $data_final, $convenio)
  {
    $tables = "RCI , RCI R2, LOC";
    $fields = "
    DISTINCT
    CASE 
      WHEN LOC_NOME LIKE '%A' THEN 'A'
      WHEN LOC_NOME LIKE '%B' THEN 'B'
      WHEN LOC_NOME LIKE '%C' THEN 'C'
      WHEN LOC_NOME LIKE '%D' THEN 'D'
      WHEN LOC_NOME LIKE '%E' THEN 'E'
      WHEN LOC_NOME LIKE '%F' THEN 'F'
    END as nome,
    RCI.RCI_DTHR_INI,
    RCI.RCI_DTHR_FIM,
    RCI.RCI_SERIE,
    RCI.RCI_NUM,
    RCI.RCI_RCI_SERIE,
    RCI.RCI_RCI_NUM,
    DATEDIFF(MINUTE, RCI.RCI_DTHR_INI, RCI.RCI_DTHR_FIM) AS uso,
	  DATEDIFF(MINUTE, CONVERT(DATETIME, '$data_inicial 00:00:00', 120), CONVERT(DATETIME, '$data_final 23:59:59', 120)) as tempo";
    $where = " 
    RCI.RCI_DTHR_INI BETWEEN CONVERT(DATETIME, '$data_inicial 00:00:00', 120) AND CONVERT(DATETIME, '$data_final 23:59:59', 120)
    AND RCI.RCI_CNV_COD IN ($convenio)
		AND    R2.RCI_RCI_NUM = RCI.RCI_NUM
		AND	   R2.RCI_RCI_SERIE = RCI.RCI_SERIE
		AND	   R2.RCI_LOC_COD = LOC_COD
		AND	   LOC_STR = 'CIR'
		AND	   LOC_COD != 'STM'
		AND    LOC_NOME NOT LIKE 'Pr%'
		AND	   LOC_COD NOT IN ('LA','LB','LC')";
    $group = "
    LOC_NOME, 	
    RCI.RCI_DTHR_INI,
    RCI.RCI_DTHR_FIM,
    RCI.RCI_SERIE,
    RCI.RCI_NUM,
    RCI.RCI_RCI_SERIE,
    RCI.RCI_RCI_NUM
    ";
    $order = "nome, RCI.RCI_DTHR_INI";
    return (new SmartPainel($tables))->select($fields, $where, null, $group, $order)->fetchAll(\PDO::FETCH_OBJ);
  }

  // Método responsável por retornar os horários de cirurgias em um range de datas e por um determinado convênio
  public static function getHorariosCirurgias($data_inicial, $data_final, $convenio)
  {
    $tables = "RCI, LOC";
    $fields = "
      DISTINCT
      CONVERT(VARCHAR(5),RCI_DTHR_INI,114) as 'no-repeat',
      DATEPART(HH, RCI.RCI_DTHR_INI) AS h_inicio,
      DATEPART(HH, RCI.RCI_DTHR_FIM) AS h_fim,
      DATEPART(MINUTE, RCI.RCI_DTHR_INI) AS m_inicio,
      DATEPART(MINUTE, RCI.RCI_DTHR_FIM) AS m_fim";
    $where = "RCI.RCI_DTHR_INI BETWEEN CONVERT(DATETIME, '$data_inicial 00:00:00', 120) AND CONVERT(DATETIME, '$data_final 23:59:59', 120) AND RCI.RCI_CNV_COD IN ($convenio)";
    $group = NULL;
    $order = "CONVERT(VARCHAR(5),RCI_DTHR_INI,114)";

    return (new SmartPainel($tables))->select($fields, $where, null, $group, $order)->fetchAll(\PDO::FETCH_OBJ);
  }

  // Método responsável por retornar os procedimentos por porte em um range de datas e um determinado convênio
  public static function getProcedimentosByPorte($data_inicial, $data_final, $convenio)
  {
    $tables = "RCI, LOC, SMK";
    $fields = "
    RCI_PORTE AS porte,
    COUNT(*) AS qtd";
    $where = "RCI.RCI_DTHR_INI BETWEEN CONVERT(DATETIME, '$data_inicial 00:00:00', 120) AND CONVERT(DATETIME, '$data_final 23:59:59', 120)
    AND RCI.RCI_CNV_COD IN ($convenio)
    AND	   RCI.RCI_LOC_COD = LOC.LOC_COD
    AND	SMK_COD = RCI.RCI_SMK_COD
    AND SMK_TIPO = 'S'";
    $group = "RCI_PORTE";
    $order = "RCI_PORTE";

    return (new SmartPainel($tables))->select($fields, $where, null, $group, $order)->fetchAll(\PDO::FETCH_OBJ);
  }

  // Método responsável por retornar os procedimentos em um range de data e por um determinado convênio
  public static function getProcedimentosByConvenio($data_inicial, $data_final, $convenio)
  {
    $tables = "RCI, cnv";
    $fields = "	CNV_NOME as convenio, COUNT(*) AS qtd";
    $where = "RCI.RCI_DTHR_INI BETWEEN CONVERT(DATETIME, '$data_inicial 00:00:00', 120) AND CONVERT(DATETIME, '$data_final 23:59:59', 120)
    AND RCI.RCI_CNV_COD IN ($convenio)
    AND	CNV_COD = RCI_CNV_COD";
    $group = "CNV_NOME";
    $order = "QTD DESC";

    return (new SmartPainel($tables))->select($fields, $where, null, $group, $order)->fetchAll(\PDO::FETCH_OBJ);
  }

  public static function getCirurgiasAgendadas($data_inicial, $data_final)
  {
    $fields = "
    'CIR' as TIPO,
    AGM_ATEND AS 'UNIDADE',
    CONVERT(NVARCHAR(16),AGM_HINI, 126) AS HORA_INICIO,
    AGM_PAC AS REGISTRO_PACIENTE,
    AGM_SMK AS SERVICO_CODIGO,
    AGM_OBS AS OBSERVACAO,
    AGM_OBS_CLINICA AS OBSERVACAO_CLINICA,
    LOC_NOME AS SALA,
    SMK_NOME AS	SERVICO_NOME,
    PAC_NOME AS	NOME_PACIENTE,
    CNV_NOME AS	CONVENIO,
    '' AS EQP_MEDICO,
    PSV_APEL AS  MEDICO,
    PSV_CRM AS  CRM,
    AGM_IND_OPME AS OPME,
    (SELECT TOP 1 'S' FROM agm_prx WHERE agm_p_id = agm_agm_prx_id) AS SANGUE,
    CASE  
      WHEN 
        (SELECT 1 FROM AGM_PRX WHERE AGM.AGM_AGM_PRX_ID = AGM_PRX.AGM_P_ID AND AGM_PRX.AGM_P_PRX_COD = 29) = 1 
      THEN 'S' 
      ELSE 'N' 
    END AS INTIMG,
    agm_cir_ind_uti AS INTERNACAO,
    DATEDIFF(MINUTE, GETDATE(), AGM_HINI) AS TEMPO,
    DATEDIFF(SECOND, AGM_HINI, AGM_HFIM) AS TEMPO_PREVISTO,
    (SELECT TOP 1  RTRIM(str_nome)	FROM HSP, str	WHERE HSP_PAC = AGM_PAC AND HSP_STAT <> 'F' and HSP_TRAT_INT = 't' and	HSP_STAT = 'A' and 	HSP_STR_COD = str_cod order by HSP_DTHRE desc) as TAM,
    (SELECT TOP 1 RTRIM(str_nome) +  ' - ' + RTRIM(LOC_NOME) FROM HSP, str, loc	WHERE HSP_PAC = AGM_PAC AND HSP_STAT <> 'F' and HSP_TRAT_INT = 'i' and HSP_STAT = 'A' and HSP_LOC= loc_cod and LOC_STR = str_cod order by HSP_DTHRE desc) AS INTERNACAO
    ";

    $tables = "agm, smk, psv, usr, LOC, PAC, CNV";
    $where = "
    (agm.agm_usr_login *= usr.usr_login) 
    and ( agm.agm_tpsmk = smk.smk_tipo ) 
    and ( agm.agm_smk = smk.smk_cod ) 
    and ( psv.psv_cod = agm.agm_med ) 
    and ( agm.agm_hini BETWEEN CONVERT(DATETIME, '$data_inicial 00:00:00', 120) AND CONVERT(DATETIME, '$data_final 23:59:59', 120))
    and ( agm.agm_stat <> 'C' ) 
    and ( smk.smk_ind_confirm_agm = 'S' ) 
    and ( loc.LOC_COD =     agm.AGM_LOC )
    and ( AGM_PAC =	pac.PAC_REG )
    and ( PAC_CNV =	cnv.cnv_cod )
    and ( LOC.LOC_STR =	'CIR')";
    $order = "agm_hini asc";

    return (new SmartPainel($tables))->select($fields, $where, null, null, $order)->fetchAll(\PDO::FETCH_OBJ);
  }
}
