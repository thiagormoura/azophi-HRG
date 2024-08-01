<?php

namespace App\Model\PainelAGM;

use App\Db\Database;
use App\Db\Smart;
use App\Db\SmartPainel;
use DateTime;
use DateTimeZone;

class PainelAGMModel
{
    public static function getCirurgias($limit = null)
    {
        $select = "'CIR' as TIPO,
            AGM_ATEND			AS	'UNIDADE',
            CONVERT(NVARCHAR(16),AGM_HINI, 126) AS HORA_INICIO,
            AGM_PAC				AS	REGISTRO_PACIENTE,
            AGM_SMK				AS	SERVICO_CODIGO,
            AGM_OBS				AS	OBSERVACAO,
            AGM_OBS_CLINICA			AS	OBSERVACAO_CLINICA,
            LOC_NOME			AS	SALA,
            SMK_NOME			AS	SERVICO_NOME,
            PAC_NOME			AS	NOME_PACIENTE,
            CNV_NOME			AS	CONVENIO,
            '' 				AS EQP_MEDICO,
            PSV_APEL			AS  MEDICO,
            PSV_CRM				AS  CRM,
            AGM_IND_OPME		AS OPME,
            (SELECT TOP 1 'S' FROM agm_prx WHERE agm_p_id = agm_agm_prx_id)  AS SANGUE,
            CASE  WHEN (SELECT 1 FROM AGM_PRX WHERE AGM.AGM_AGM_PRX_ID = AGM_PRX.AGM_P_ID AND
                    AGM_PRX.AGM_P_PRX_COD = 29 ) = 1 THEN 'S' ELSE 'N' END AS INTIMG,
            agm_cir_ind_uti     AS INTERNACAO,
            DATEDIFF(MINUTE,GETDATE(), AGM_HINI)  AS TEMPO,
            DATEDIFF(MINUTE, AGM_HINI,AGM_HFIM) / 60   AS TEMPO_PREVISTO,
            (ISNULL((SELECT TOP 1  RTRIM(str_nome)
                FROM 
                    HSP, 
                    str
                WHERE 
                    HSP_PAC = AGM_PAC AND 
                    HSP_STAT <> 'F' and 
                    HSP_TRAT_INT = 't' and
                    HSP_STAT = 'A' and 
                    HSP_STR_COD = str_cod order by HSP_DTHRE desc)
                ,'-')) as TAM,
            (ISNULL((SELECT TOP 1 RTRIM(str_nome) +  ' - ' + RTRIM(LOC_NOME)
                FROM 
                    HSP, 
                    str, loc
                WHERE 
                    HSP_PAC = AGM_PAC AND 
                    HSP_STAT <> 'F' and 
                    HSP_TRAT_INT = 'i' and
                    HSP_STAT = 'A' and 
                    HSP_LOC= loc_cod and
                    LOC_STR = str_cod
                order by HSP_DTHRE desc),'-')
                ) AS INT";
        
        $tables = "agm, smk, psv, usr, LOC, PAC, CNV";

        $where = "( agm.agm_usr_login *= usr.usr_login) 
            and		( agm.agm_tpsmk = smk.smk_tipo ) 
            and		( agm.agm_smk = smk.smk_cod ) 
            and		( psv.psv_cod = agm.agm_med ) 
            and     ( agm.agm_hini BETWEEN (CONVERT(datetime, CONVERT(VARCHAR(2), DATEPART(DD, GETDATE()))+'/' +  CONVERT(VARCHAR(2), DATEPART(MM, GETDATE())) +'/'+ CONVERT(VARCHAR(4), DATEPART(YYYY, GETDATE()))+' 00:00:00', 103)) AND (CONVERT(datetime, CONVERT(VARCHAR(2), DATEPART(DD, GETDATE()))+'/' +  CONVERT(VARCHAR(2), DATEPART(MM, GETDATE())) +'/'+ CONVERT(VARCHAR(4), DATEPART(YYYY, GETDATE()))+' 23:59:59', 103)))


            and		( agm.agm_stat				<> 'C' ) 
            and		( smk.smk_ind_confirm_agm	= 'S' ) 
            and		( loc.LOC_COD				=     agm.AGM_LOC )
            and		( AGM_PAC					=	pac.PAC_REG )
            and		( PAC_CNV				    =	cnv.cnv_cod )
            and		( LOC.LOC_STR				=	'CIR')";

        return (new SmartPainel($tables))->select($select, $where, $limit, null, "agm_hini asc")->fetchAll(\PDO::FETCH_ASSOC);
    }
}