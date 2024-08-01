<?php

namespace App\Model\Monps;

use App\Db\SmartPainel;

class Paciente
{
  // Método responsável por retornar todos os pacientes que estão em atendimento no momento
  public static function getPacientes()
  {
    $tables = "
    fle 
        left join pac on FLE_PAC_REG = pac_reg
        LEFT JOIN fle f_totem on 
                fle.FLE_BIP = f_totem.FLE_BIP 
              and fle.FLE_PAC_REG = f_totem.FLE_PAC_REG 
              and f_totem.FLE_PSV_COD in (900250,900197)
              and datediff(hour,f_totem.FLE_DTHR_CHEGADA,getdate()) < 24
        LEFT JOIN PSV p_resp on fle.FLE_PSV_RESP = p_resp.PSV_COD
        LEFT JOIN HSP on fle.FLE_PAC_REG = HSP_PAC and hsp_trat_int = 't' and hsp_str_cod = 'PAT' and HSP_STAT = 'A', psv";
    $fields = "
    'A' as query,
    fle.fle_psv_cod,
    case	when fle.fle_psv_cod = 900288 then 'CAR' 
        when fle.fle_psv_cod = 900290 then 'CLG'
        when fle.fle_psv_cod = 900250 then 'TRIA'
        when fle.fle_psv_cod = 900197 then 'REC'
        when fle.fle_psv_cod = 900289 then 'ORT'
    END AS PSV_FILA_SIGLA,
    psv.psv_FILA_nome,
    fle.FLE_DTHR_CHEGADA,
    isnull((select TOP 1 'S' from rcl where  RCL_PAC = pac_reg 
              and RCL_DTHR between  MIN(f_totem.FLE_DTHR_CHEGADA) and getdate() 
              and rcl_cod in ('00010022','07012268','21010006') 
              and rcl_MED <> '101010'
              and RCL_STAT IN ('L','I')
              AND RCL_OSM_SERIE IS NOT NULL AND RCL_OSM IS NOT NULL),'N') as reavaliacao,
    fle.fle_status,
    CASE 
      WHEN fle.FLE_STATUS = 'A' THEN 'Aguardando'
      WHEN fle.FLE_STATUS = 'E' THEN 'Em atendimento'
      else 'Em procedimento' end as fle_status_nome,
        DATEDIFF(MINUTE,fle.fle_dthr_chegada,getdate()) as tempo_na_fila,
        CASE 
            WHEN PAC_REG = 0 
                THEN DATEDIFF(MINUTE,FLE.FLE_DTHR_CHEGADA,getdate()) 
                ELSE isnull(DATEDIFF(MINUTE,MIN(f_totem.FLE_DTHR_CHEGADA),getdate()), DATEDIFF(MINUTE,MIN(fle.FLE_DTHR_CHEGADA),getdate())) 
        END		as tempo_hospitalar,
      isnull(fle.FLE_BIP,'-') as FLE_BIP,
      pac_reg,
      pac_nome,
    fle.FLE_PSV_COD,
    isnull(p_resp.PSV_TRAT + ' ' + p_resp.PSV_APEL,'') as medico_responsavel,
    isnull(MIN(f_totem.FLE_DTHR_CHEGADA), fle.FLE_DTHR_CHEGADA) as h_dthr_chegada, 
    (SELECT count(*) FROM APL WHERE APL_PSC_PAC = FLE.FLE_PAC_REG AND APL_PSC_HSP = HSP_NUM) as med,
    (SELECT count(*) FROM APL WHERE APL_STATUS = 'A'  and APL_PSC_PAC = FLE.FLE_PAC_REG AND APL_PSC_HSP = HSP_NUM) as apl,
    isnull((SELECT TOP 1 CR_COR_NOME FROM CR_CLS, CR_COR WHERE cr_cls_cor_cod = CR_COR_COD and CR_CLS_PAC_REG = FLE.FLE_PAC_REG AND (CR_CLS_DTHR_REG > MIN(f_totem.FLE_DTHR_CHEGADA) or DATEDIFF(HOUR,CR_CLS_DTHR_REG,FLE.FLE_DTHR_CHEGADA) < 4 ) AND cr_cls_del_logica = 'N' ORDER BY CR_CLS_DTHR_REG DESC),'') AS CR";

    $where = "    
    fle.FLE_PSV_COD in (900289,900197,900250,900288,900289,900290,901509)
    and fle.fle_psv_cod = psv.psv_cod
    and fle.FLE_DTHR_CHEGADA > getdate() - 2
    and  ( fle.FLE_STATUS  in ('A') or (fle.FLE_STATUS  in ('E','P') 
    and EXISTS (SELECT 1 FROM FLE FLE2 WHERE FLE2.FLE_PAC_REG = FLE.FLE_PAC_REG AND FLE2.FLE_BIP = FLE.FLE_BIP AND FLE2.FLE_STATUS IN ('E'))))";

    $group = "      
    fle.fle_psv_cod,
    psv.psv_FILA_nome,
    fle.FLE_DTHR_CHEGADA,
    fle.FLE_DTHR_HORA_AGUARDO,
    fle.fle_status,
    fle.FLE_BIP,
    pac.PAC_REG,pac.PAC_NOME,fle.FLE_PSV_RESP,p_resp.PSV_TRAT,p_resp.PSV_APEL,fle.FLE_PAC_REG,HSP.HSP_NUM";

    $order = null;

    $union = "
    select
    'B' as query,
    fle.fle_psv_cod,
    case	when fle.fle_psv_cod = 900253 then 'TOM' 
        when fle.fle_psv_cod = 127208 then 'ULT'
        when fle.fle_psv_cod = 900252 then 'R-X'
        when fle.fle_psv_cod = 900255 then 'LAB'
        when fle.fle_psv_cod = 901306 then 'ENF'
    END,
    psv.psv_FILA_nome,
    fle.FLE_DTHR_CHEGADA,
    
    isnull((select TOP 1 'S' from rcl where  RCL_PAC = pac_reg 
              and RCL_DTHR between  MIN(f_totem.FLE_DTHR_CHEGADA) and getdate() 
              and rcl_cod in ('00010022','07012268','21010006') 
              and rcl_MED <> '101010'
              and RCL_STAT IN ('L','I')
              AND RCL_OSM_SERIE IS NOT NULL AND RCL_OSM IS NOT NULL),'N') as reavaliacao,
    fle.fle_status,
    CASE 
      WHEN fle.FLE_STATUS = 'A' THEN 'Aguardando'
      WHEN fle.FLE_STATUS = 'E' THEN 'Em atendimento'
      else 'Em procedimento' end as fle_status_nome,
        DATEDIFF(MINUTE,fle.fle_dthr_chegada,getdate()) as tempo_na_fila,
        isnull(DATEDIFF(MINUTE,MIN(f_totem.FLE_DTHR_CHEGADA),getdate()), DATEDIFF(MINUTE,MIN(fle.FLE_DTHR_CHEGADA),getdate()))  as tempo_hospitalar,
      isnull(fle.FLE_BIP,'-') as FLE_BIP,
      pac_reg,
      pac_nome,
    fle.FLE_PSV_COD,
    isnull(p_resp.PSV_TRAT + ' ' + p_resp.PSV_APEL,''),
    isnull(MIN(f_totem.FLE_DTHR_CHEGADA), fle.fle_dthr_chegada) as h_dthr_chegada,
    (SELECT count(*) FROM APL WHERE APL_PSC_PAC = FLE.FLE_PAC_REG AND APL_PSC_HSP = HSP_NUM),
    (SELECT count(*) FROM APL WHERE APL_STATUS = 'A'  and APL_PSC_PAC = FLE.FLE_PAC_REG AND APL_PSC_HSP = HSP_NUM),
    isnull((SELECT TOP 1 CR_COR_NOME FROM CR_CLS, CR_COR WHERE cr_cls_cor_cod = CR_COR_COD and CR_CLS_PAC_REG = FLE.FLE_PAC_REG AND (CR_CLS_DTHR_REG > MIN(f_totem.FLE_DTHR_CHEGADA) or DATEDIFF(HOUR,CR_CLS_DTHR_REG,FLE.FLE_DTHR_CHEGADA) < 4 ) AND cr_cls_del_logica = 'N' ORDER BY CR_CLS_DTHR_REG DESC),'') AS CR
    from fle 
        left join pac on FLE_PAC_REG = pac_reg
        LEFT JOIN fle f_totem on 
                fle.FLE_BIP = f_totem.FLE_BIP 
              and fle.FLE_PAC_REG = f_totem.FLE_PAC_REG 
              and f_totem.FLE_PSV_COD in (900250,900197)
              and datediff(hour,f_totem.FLE_DTHR_CHEGADA,getdate()) < 24
        LEFT JOIN PSV p_resp on fle.FLE_PSV_RESP = p_resp.PSV_COD, 
    psv, 
    HSP where 
    fle.FLE_PSV_COD in (901306,901509,900252,900253,127208,900255)
    and fle.fle_status in ('A','E')
    and fle.fle_psv_cod = psv.psv_cod
    and fle.FLE_DTHR_CHEGADA > getdate() - 2
    and hsp_pac = fle.FLE_PAC_REG
    and hsp_trat_int = 't'
    and hsp_str_cod = 'PAT'		
    and HSP_STAT = 'A' 
    
    group by 
      fle.fle_psv_cod,
      psv.psv_FILA_nome,
      fle.FLE_DTHR_CHEGADA,
      fle.FLE_DTHR_HORA_AGUARDO,
      fle.fle_status,
      fle.FLE_BIP,
      pac.PAC_REG,pac.PAC_NOME,fle.FLE_PSV_RESP,p_resp.PSV_TRAT,p_resp.PSV_APEL,fle.FLE_PAC_REG,HSP.HSP_NUM
    order by tempo_hospitalar desc, tempo_na_fila desc, fle_status_nome
    ";

    return (new SmartPainel($tables))->unionAll($fields, $where, null, $group, $order, $union)->fetchAll(\PDO::FETCH_ASSOC);
  }

  // Método responsável por retornar um determinado paciente que está em atendimento no momento com enfâse nas filas
  public static function getPacienteByRegistroBip($registro, $fleBip)
  {
    $tables = "
    fle 
        left join pac on FLE_PAC_REG = pac_reg
        LEFT JOIN fle f_totem on 
                fle.FLE_BIP = f_totem.FLE_BIP 
              and fle.FLE_PAC_REG = f_totem.FLE_PAC_REG 
              and f_totem.FLE_PSV_COD in (900250,900197)
              and datediff(hour,f_totem.FLE_DTHR_CHEGADA,getdate()) < 24
        LEFT JOIN PSV p_resp on fle.FLE_PSV_RESP = p_resp.PSV_COD
        LEFT JOIN HSP on fle.FLE_PAC_REG = HSP_PAC and hsp_trat_int = 't' and hsp_str_cod = 'PAT' and HSP_STAT = 'A', psv";

    $fields = "
    'A' as query,
    fle.fle_psv_cod,
    case	when fle.fle_psv_cod = 900288 then 'CAR' 
        when fle.fle_psv_cod = 900290 then 'CLG'
        when fle.fle_psv_cod = 900250 then 'TRIA'
        when fle.fle_psv_cod = 900197 then 'REC'
        when fle.fle_psv_cod = 900289 then 'ORT'
    END AS PSV_FILA_SIGLA,
    psv.psv_FILA_nome,
    fle.FLE_DTHR_CHEGADA,
    isnull((select TOP 1 'S' from rcl where  RCL_PAC = pac_reg 
              and RCL_DTHR between  MIN(f_totem.FLE_DTHR_CHEGADA) and getdate() 
              and rcl_cod in ('00010022','07012268','21010006') 
              and rcl_MED <> '101010'
              and RCL_STAT IN ('L','I')
              AND RCL_OSM_SERIE IS NOT NULL AND RCL_OSM IS NOT NULL),'N') as reavaliacao,
    fle.fle_status,
    CASE 
      WHEN fle.FLE_STATUS = 'A' THEN 'Aguardando'
      WHEN fle.FLE_STATUS = 'E' THEN 'Em atendimento'
      else 'Em procedimento' end as fle_status_nome,
        DATEDIFF(MINUTE,fle.fle_dthr_chegada,getdate()) as tempo_na_fila,
        CASE 
            WHEN PAC_REG = 0 
                THEN DATEDIFF(MINUTE,FLE.FLE_DTHR_CHEGADA,getdate()) 
                ELSE isnull(DATEDIFF(MINUTE,MIN(f_totem.FLE_DTHR_CHEGADA),getdate()), DATEDIFF(MINUTE,MIN(fle.FLE_DTHR_CHEGADA),getdate())) 
        END		as tempo_hospitalar,
      isnull(fle.FLE_BIP,'-') as FLE_BIP,
      pac_reg,
      pac_nome,
    fle.FLE_PSV_COD,
    isnull(p_resp.PSV_TRAT + ' ' + p_resp.PSV_APEL,'') as medico_responsavel,
    isnull(MIN(f_totem.FLE_DTHR_CHEGADA), fle.FLE_DTHR_CHEGADA) as h_dthr_chegada, 
    (SELECT count(*) FROM APL WHERE APL_PSC_PAC = FLE.FLE_PAC_REG AND APL_PSC_HSP = HSP_NUM) as med,
    (SELECT count(*) FROM APL WHERE APL_STATUS = 'A'  and APL_PSC_PAC = FLE.FLE_PAC_REG AND APL_PSC_HSP = HSP_NUM) as apl,
    isnull((SELECT TOP 1 CR_COR_NOME FROM CR_CLS, CR_COR WHERE cr_cls_cor_cod = CR_COR_COD and CR_CLS_PAC_REG = FLE.FLE_PAC_REG AND (CR_CLS_DTHR_REG > MIN(f_totem.FLE_DTHR_CHEGADA) or DATEDIFF(HOUR,CR_CLS_DTHR_REG,FLE.FLE_DTHR_CHEGADA) < 4 ) AND cr_cls_del_logica = 'N' ORDER BY CR_CLS_DTHR_REG DESC),'') AS CR";

    $where = "    
    Fle.fle_pac_reg = " . $registro . "
    and (fle.fle_bip = '" . $fleBip . "' OR fle.fle_bip IS NULL)
    and fle.FLE_PSV_COD in (900289,900197,900250,900288,900289,900290,901509)
    and fle.fle_psv_cod = psv.psv_cod
    and fle.FLE_DTHR_CHEGADA > getdate() - 2
    and  ( fle.FLE_STATUS  in ('A') or (fle.FLE_STATUS  in ('E','P') 
    and EXISTS (SELECT 1 FROM FLE FLE2 WHERE FLE2.FLE_PAC_REG = FLE.FLE_PAC_REG AND FLE2.FLE_BIP = FLE.FLE_BIP AND FLE2.FLE_STATUS IN ('E'))))";

    $group = "      
    fle.fle_psv_cod,
    psv.psv_FILA_nome,
    fle.FLE_DTHR_CHEGADA,
    fle.FLE_DTHR_HORA_AGUARDO,
    fle.fle_status,
    fle.FLE_BIP,
    pac.PAC_REG,pac.PAC_NOME,fle.FLE_PSV_RESP,p_resp.PSV_TRAT,p_resp.PSV_APEL,fle.FLE_PAC_REG,HSP.HSP_NUM";

    $order = null;

    $union = "
    select
    'B' as query,
    fle.fle_psv_cod,
    case	when fle.fle_psv_cod = 900253 then 'TOM' 
        when fle.fle_psv_cod = 127208 then 'ULT'
        when fle.fle_psv_cod = 900252 then 'R-X'
        when fle.fle_psv_cod = 900255 then 'LAB'
        when fle.fle_psv_cod = 901306 then 'ENF'
    END,
    psv.psv_FILA_nome,
    fle.FLE_DTHR_CHEGADA,
    
    isnull((select TOP 1 'S' from rcl where  RCL_PAC = pac_reg 
              and RCL_DTHR between  MIN(f_totem.FLE_DTHR_CHEGADA) and getdate() 
              and rcl_cod in ('00010022','07012268','21010006') 
              and rcl_MED <> '101010'
              and RCL_STAT IN ('L','I')
              AND RCL_OSM_SERIE IS NOT NULL AND RCL_OSM IS NOT NULL),'N') as reavaliacao,
    fle.fle_status,
    CASE 
      WHEN fle.FLE_STATUS = 'A' THEN 'Aguardando'
      WHEN fle.FLE_STATUS = 'E' THEN 'Em atendimento'
      else 'Em procedimento' end as fle_status_nome,
        DATEDIFF(MINUTE,fle.fle_dthr_chegada,getdate()) as tempo_na_fila,
        isnull(DATEDIFF(MINUTE,MIN(f_totem.FLE_DTHR_CHEGADA),getdate()), DATEDIFF(MINUTE,MIN(fle.FLE_DTHR_CHEGADA),getdate()))  as tempo_hospitalar,
      isnull(fle.FLE_BIP,'-') as FLE_BIP,
      pac_reg,
      pac_nome,
    fle.FLE_PSV_COD,
    isnull(p_resp.PSV_TRAT + ' ' + p_resp.PSV_APEL,''),
    isnull(MIN(f_totem.FLE_DTHR_CHEGADA), fle.fle_dthr_chegada) as h_dthr_chegada,
    (SELECT count(*) FROM APL WHERE APL_PSC_PAC = FLE.FLE_PAC_REG AND APL_PSC_HSP = HSP_NUM),
    (SELECT count(*) FROM APL WHERE APL_STATUS = 'A'  and APL_PSC_PAC = FLE.FLE_PAC_REG AND APL_PSC_HSP = HSP_NUM),
    isnull((SELECT TOP 1 CR_COR_NOME FROM CR_CLS, CR_COR WHERE cr_cls_cor_cod = CR_COR_COD and CR_CLS_PAC_REG = FLE.FLE_PAC_REG AND (CR_CLS_DTHR_REG > MIN(f_totem.FLE_DTHR_CHEGADA) or DATEDIFF(HOUR,CR_CLS_DTHR_REG,FLE.FLE_DTHR_CHEGADA) < 4 ) AND cr_cls_del_logica = 'N' ORDER BY CR_CLS_DTHR_REG DESC),'') AS CR
    from fle 
        left join pac on FLE_PAC_REG = pac_reg
        LEFT JOIN fle f_totem on 
                fle.FLE_BIP = f_totem.FLE_BIP 
              and fle.FLE_PAC_REG = f_totem.FLE_PAC_REG 
              and f_totem.FLE_PSV_COD in (900250,900197)
              and datediff(hour,f_totem.FLE_DTHR_CHEGADA,getdate()) < 24
        LEFT JOIN PSV p_resp on fle.FLE_PSV_RESP = p_resp.PSV_COD, 
    psv, 
    HSP where 
    fle.FLE_PSV_COD in (901306,901509,900252,900253,127208,900255)
    and fle.fle_status in ('A','E')
    and fle.fle_psv_cod = psv.psv_cod
    and fle.FLE_DTHR_CHEGADA > getdate() - 2
    and hsp_pac = fle.FLE_PAC_REG
    and hsp_trat_int = 't'
    and hsp_str_cod = 'PAT'		
    and HSP_STAT = 'A' 
    and Fle.fle_pac_reg = " . $registro . " 
    and (fle.fle_bip = '" . $fleBip . "' OR fle.fle_bip IS NULL)
    
    group by 
      fle.fle_psv_cod,
      psv.psv_FILA_nome,
      fle.FLE_DTHR_CHEGADA,
      fle.FLE_DTHR_HORA_AGUARDO,
      fle.fle_status,
      fle.FLE_BIP,
      pac.PAC_REG,pac.PAC_NOME,fle.FLE_PSV_RESP,p_resp.PSV_TRAT,p_resp.PSV_APEL,fle.FLE_PAC_REG,HSP.HSP_NUM
    order by tempo_hospitalar desc, tempo_na_fila desc, fle_status_nome
    ";
    return (new SmartPainel($tables))->unionAll($fields, $where, null, $group, $order, $union)->fetchAll(\PDO::FETCH_ASSOC);
  }

  // Método responsável por retornar o paciente com ênfase nos dados do paciente
  public static function getPacienteByRegistro($registro)
  {
    $tables = "hsp 
    LEFT JOIN 
      RCL on 
      HSP_PAC = RCL_PAC AND 
      HSP_NUM = RCL_HSP AND 
      RCL_COD IN ('00010022','07012268','21010006') AND 
      RCL_MED <> 101010 AND 
      RCL_STAT <> 'C'
    LEFT JOIN
      psv on PSV_COD = RCL_MED, pac";
    $fields = "PAC_REG AS 'registro',
    HSP_NUM as 'numInternacao',
    (SELECT TOP 1 cast(FLE_BIP as varchar) + ';' + 
        cast(DATEDIFF(MINUTE,FLE_DTHR_CHEGADA_INICIAL,GETDATE()) as varchar)+' min.' + ';' +
        cast (CONVERT(VARCHAR(30), FLE_DTHR_CHEGADA_INICIAL,103) + ' ' + CONVERT(VARCHAR(5), FLE_DTHR_CHEGADA_INICIAL, 114) as varchar)
        FROM FLE WHERE FLE_PSV_COD = '900250' AND FLE_STATUS <> 'C' AND FLE_PAC_REG = PAC_REG AND FLE_DTHR_CHEGADA_INICIAL BETWEEN GETDATE() -1 AND GETDATE() ORDER BY FLE_DTHR_CHEGADA_INICIAL DESC) AS bip,
    '' AS 'reavaliacao',
    (SELECT TOP 1 CR_COR_NOME FROM cr_cor,CR_CLS
      WHERE  ( cr_cls.cr_cls_pac_reg = pac_reg )
         AND ( cr_cls.cr_cls_dthr between  getdate() - 1 and getdate())
         AND ( cr_cls.cr_cls_del_logica = 'N' )
         AND ( cr_cls.cr_cls_hsp_num IS NULL
                OR cr_cls.cr_cls_hsp_num = hsp_num )
         AND ( cr_cls.cr_cls_cor_cod = cr_cor.cr_cor_cod )) AS 'cr',
    (SELECT count(*) FROM APL WHERE APL_PSC_PAC = hsp.hsp_pac AND APL_PSC_HSP = HSP_NUM) AS 'med',
    (SELECT count(*) FROM APL WHERE APL_STATUS = 'A'  and APL_PSC_PAC = hsp.hsp_pac AND APL_PSC_HSP = HSP_NUM) AS 'apl',
    PAC_NOME AS 'nome',
    PSV_APEL AS 'medico'";
    $where = "hsp_pac = pac_reg and
    hsp_stat  = 'A' AND
    HSP_STR_COD = 'PAT' AND
    PAC_REG = $registro";
    $group = null;
    $order = null;

    return (new SmartPainel($tables))->select($fields, $where, null, $group, $order)->fetch(\PDO::FETCH_OBJ);
  }

  // Método responsável por retornar as filas de um determinado paciente
  public static function getFilas($registro)
  {
    $tables = "FLE, PSV";
    $fields = "PSV_FILA_NOME as fila,
    case when FLE_STATUS = 'A' then 'Aguardando'
    when FLE_STATUS = 'E' then 'Em atendimento'
    when FLE_STATUS = 'P' then 'Em Procedimento'
    end status_fila,
    DATEDIFF(MINute,FLE_DTHR_CHEGADA, GETDATE()) as tempo";
    $where = "FLE_PAC_REG = $registro AND  -- variavel registro
    FLE_STATUS IN ('A','E','P') AND 
    FLE_PSV_COD = PSV_COD  AND 
    FLE_DTHR_CHEGADA > GETDATE() - 1";
    $group = null;
    $order = "FLE_DTHR_CHEGADA_INICIAL";

    return (new SmartPainel($tables))->select($fields, $where, null, $group, $order)->fetchAll(\PDO::FETCH_ASSOC);
  }

  public static function getPacientesPAtendimento(){
    $tables = "FLE f1, PSV P1, FLE f2, PSV P2, PAC";
    $fields = "DISTINCT
		F1.FLE_PAC_REG,
		PAC.PAC_NOME,
		f1.FLE_BIP AS SENHA,
		MIN(F2.FLE_DTHR_CHEGADA) as DTHR_CHEGADA_HRG,
		MIN(F2.FLE_DTHR_ATENDIMENTO) as DTHR_CHEGADA_CLASSIFICACAO,
		DATEDIFF(MINUTE,MIN(F2.FLE_DTHR_CHEGADA), MIN(F2.FLE_DTHR_ATENDIMENTO)) as tempo_espera_classificacao,
		P1.PSV_COD as FILA_COD,
		P1.PSV_NOME AS FILA_NOME,
		DATEDIFF(MINUTE,MIN(F2.FLE_DTHR_CHEGADA), 
		CASE 
      WHEN F1.FLE_STATUS = 'A' 
        THEN GETDATE()
    ELSE F1.FLE_DTHR_ATENDIMENTO END) as tempo_espera_total,
		F1.FLE_STATUS";
    $where = "
    F1.FLE_DTHR_CHEGADA > DATEADD(day, -1, GETDATE()) and
		f1.FLE_PSV_COD = P1.PSV_COD AND
		F1.FLE_PAC_REG = PAC.PAC_REG AND 
		P1.PSV_COD IN ('900290','900289','900288') AND
		f2.FLE_PSV_COD = P2.PSV_COD AND
		F2.FLE_PAC_REG = F1.FLE_PAC_REG AND
		F2.FLE_BIP =	F1.FLE_BIP AND
		F2.FLE_PSV_COD IN ( '900197', '900250' ) AND
		F2.FLE_DTHR_CHEGADA >  DATEADD(day, -1, GETDATE()) and
		F1.FLE_STATUS  = 'A'	AND
		NOT EXISTS (SELECT 1 FROM RCL WHERE RCL_PAC = f1.FLE_PAC_REG AND RCL_DTHR > F2.FLE_DTHR_CHEGADA AND RCL_COD IN('00010022','21010006') AND RCL_MED <> 101010 AND  RCL_STAT <> 'C')
    ";
    $group = "
    P1.PSV_COD,
		F1.FLE_BIP,
		F1.FLE_PAC_REG,
		PAC.PAC_NOME,
		P1.PSV_NOME,
		F1.FLE_DTHR_CHEGADA,
		F1.FLE_DTHR_ATENDIMENTO,
		F1.FLE_STATUS";
    $order = "
    p1.psv_nome,
		tempo_espera_total desc";

    return (new SmartPainel($tables))->select($fields, $where, null, $group, $order)->fetchAll(\PDO::FETCH_ASSOC);
  }
  public static function getPacientesReavaliacao(){
    $tables = "FLE f1, PSV P1, FLE f2, PSV P2, PAC";
    $fields = "	
    DISTINCT
		F1.FLE_PAC_REG,
		PAC.PAC_NOME,
		f1.FLE_BIP AS SENHA,
		MIN(F2.FLE_DTHR_CHEGADA) as DTHR_CHEGADA_HRG,
		MIN(F2.FLE_DTHR_ATENDIMENTO) as DTHR_CHEGADA_CLASSIFICACAO,
		DATEDIFF(MINUTE,MIN(F2.FLE_DTHR_CHEGADA), MIN(F2.FLE_DTHR_ATENDIMENTO)) as tempo_espera_classificacao,
		P1.PSV_COD as FILA_COD,
		P1.PSV_NOME AS FILA_NOME,
		DATEDIFF(MINUTE,MIN(F2.FLE_DTHR_CHEGADA), 
    CASE 
      WHEN F1.FLE_STATUS = 'A' THEN GETDATE()
    ELSE F1.FLE_DTHR_ATENDIMENTO END) as tempo_espera_total,
		F1.FLE_STATUS";
    $where = "
    F1.FLE_DTHR_CHEGADA > DATEADD(day, -1, GETDATE()) and
		f1.FLE_PSV_COD	= P1.PSV_COD AND
		F1.FLE_PAC_REG = PAC.PAC_REG AND 
		P1.PSV_COD	IN ( '900290', '900289', '900288') AND
		f2.FLE_PSV_COD	= P2.PSV_COD AND
		F2.FLE_PAC_REG	= F1.FLE_PAC_REG AND
		F2.FLE_BIP	=	F1.FLE_BIP AND
		F2.FLE_PSV_COD IN ( '900197', '900250' ) AND
		F2.FLE_DTHR_CHEGADA > DATEADD(day, -1, GETDATE()) and
		F1.FLE_STATUS  = 'A'	AND
		EXISTS (SELECT 1 FROM RCL WHERE RCL_PAC = f1.FLE_PAC_REG AND RCL_DTHR > F2.FLE_DTHR_CHEGADA AND RCL_COD IN('00010022','21010006') AND RCL_MED <> 101010 AND RCL_STAT <> 'C')
    ";
    $group = "
		P1.PSV_COD,
		F1.FLE_BIP,
		F1.FLE_PAC_REG,
		PAC.PAC_NOME,
		P1.PSV_NOME,
		F1.FLE_DTHR_CHEGADA,
		F1.FLE_DTHR_ATENDIMENTO,
		F1.FLE_STATUS";
    $order = "
		p1.psv_nome,
		tempo_espera_total desc";

    return (new SmartPainel($tables))->select($fields, $where, null, $group, $order)->fetchAll(\PDO::FETCH_ASSOC);
  }

  public static function getPacientesRecepcaoTriagem(){
    $tables = "
    FLE F1 LEFT JOIN FLE f2 ON f1.FLE_BIP = F2.FLE_BIP
      AND F1.FLE_PAC_REG = F2.FLE_PAC_REG
      AND F1.FLE_DTHR_ATENDIMENTO < F2.FLE_DTHR_CHEGADA
	    AND DATEDIFF(HOUR, F1.FLE_DTHR_ATENDIMENTO, F2.FLE_DTHR_CHEGADA) < 48
      AND F2.FLE_PSV_COD IN ('900197')
    LEFT JOIN
      PSV P2 ON F2.FLE_PSV_COD = P2.PSV_COD,
      PSV P1,
      PAC";
    $fields = "	
    DISTINCT
    F1.FLE_PAC_REG,
    PAC.PAC_NOME,
    F1.FLE_BIP AS SENHA,
    P1.PSV_COD AS FILA_COD_CLASSIFICACAO,
    P1.PSV_NOME AS FILA_CLASSIFICACAO,
    F1.FLE_STATUS AS STATUS_CLASSIFICACAO,
    F1.FLE_DTHR_CHEGADA AS DTHR_CHEGADA_CLASSIFICACAO,
    F1.FLE_DTHR_ATENDIMENTO AS DTHR_ATENDIMENTO_CLASSIFICACAO,
    DATEDIFF(MINUTE, MIN(F1.FLE_DTHR_CHEGADA), ISNULL(F1.FLE_DTHR_ATENDIMENTO, GETDATE())) AS ESPERA_CLASSIFICACAO,
    P2.PSV_COD AS FILA_COD_RECEPCAO,
    P2.PSV_NOME AS FILA_NOME,
    F2.FLE_STATUS AS STATUS_RECEPCAO,
    F2.FLE_DTHR_CHEGADA AS DTHR_CHEGADA_RECEPCAO,
    F2.FLE_DTHR_ATENDIMENTO AS DTHR_ATENDIMENTO_RECEPCAO,
    DATEDIFF(MINUTE, MIN(F1.FLE_DTHR_CHEGADA), ISNULL(F2.FLE_DTHR_ATENDIMENTO, GETDATE())) AS ESPERA_RECEPCAO";
    $where = "
    F1.FLE_DTHR_CHEGADA >= getdate() -1
    AND f1.FLE_PSV_COD = P1.PSV_COD
    AND F1.FLE_PAC_REG = PAC.PAC_REG
    AND P1.PSV_COD IN ('900250')
    AND (F1.FLE_STATUS = 'A' OR EXISTS(SELECT 1 FROM FLE F3 WHERE F1.FLE_BIP = F3.FLE_BIP AND F2.FLE_STATUS = 'A' AND F1.FLE_PAC_REG = F3.FLE_PAC_REG AND F3.FLE_DTHR_CHEGADA > F1.FLE_DTHR_ATENDIMENTO AND F3.FLE_DTHR_CHEGADA >= DATEADD(day, -1, GETDATE())))";
    $group = "
		F1.FLE_PAC_REG, 
    PAC.PAC_NOME, 
    F1.FLE_BIP, 
    P1.PSV_COD, 
    P1.PSV_NOME, 
    F1.FLE_STATUS, 
    F1.FLE_DTHR_ATENDIMENTO, 
    F1.FLE_DTHR_CHEGADA, 
    P2.PSV_COD,  
    F2.FLE_STATUS, 
    P2.PSV_NOME, 
    F2.FLE_DTHR_CHEGADA, 
    F2.FLE_DTHR_ATENDIMENTO";
    $order = "F1.FLE_DTHR_CHEGADA";

    return (new SmartPainel($tables))->select($fields, $where, null, $group, $order)->fetchAll(\PDO::FETCH_ASSOC);
  }
}
