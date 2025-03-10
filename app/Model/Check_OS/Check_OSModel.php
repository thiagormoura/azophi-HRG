<?php

namespace App\Model\Check_OS;

use App\Db\Database;
use App\Db\SmartPainel;

class Check_OSModel
{

    public static function updateAcess($user){
   
        date_default_timezone_set('America/Fortaleza');

        return (new Database("centralservicos", "usuario_acesso"))->insert([ 
            "usuario_nome" => $user->nome, 
            "usuario_cpf" => $user->cpf,
            "sistema" => 'Check OS (Produção)',
            "dthr_login" => date("Y-m-d H:i:s")
        ]);
    }

    public static function getSectors($dateFirst, $dateSecond)
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

        $fields = "distinct STR_COD AS SETOR_COD, STR_NOME AS SETOR";

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
            EXM_SMK_TIPO = SMK_TIPO AND
            SMK_CTF = CTF_COD AND CTF_CTF_COD in (2800,2806,9134)
            ";

        $group = "STR_NOME, STR_COD";
        $order = "STR_NOME";

        return (new SmartPainel($tables))->select($fields, $where, null, $group, $order)->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getAllMinimalExamesByDatesAndSector($order=null, $filter=null)
    {
        $with = "WITH CountExm as 
        (
            SELECT
                DISTINCT
                    OSM_SERIE as OS_SERIE,  
                    OSM_NUM as OS_NUMERO,
                    OSM_DTHR as LANCAMENTO,
                    OSM_ATEND as OS_TIPO,
                    pac_reg as paciente_registro,
                    pac_nome as paciente_nome,
                    str.str_cod as setor_codigo,
                    str.str_nome as setor,
                    pex.PEX_DTHR as pedido_data,
                    SUM(case WHEN smm_exec <> 'L' AND smm_exec <> 'X' THEN 1 ELSE 0 END) 'ABERTO',
                    SUM(case WHEN smm_exec = 'X' THEN 1 ELSE 0 END) 'EXECUCAO',
                    SUM(case WHEN smm_exec = 'L' THEN 1 ELSE 0 END) 'LIBERADO'
            FROM 
                SMM
                    left join
                        pex on smm.SMM_OSM_SERIE = pex.PEX_OSM_SERIE and smm.SMM_OSM = pex.PEX_OSM_NUM
                    left join
                        amo on smm.smm_amo_cod = amo.amo_cod 
                    left join
                        hsp as hsp1 on SMM_HSP_NUM = hsp1.hsp_num and SMM_PAC_REG = hsp1.HSP_PAC and hsp1.hsp_trat_int = 'T'
                    left join
                        STR AS STR1 ON HSP1.HSP_STR_COD = STR1.STR_COD
                    left join
                        hsp as hsp2 on SMM_HSP_NUM = hsp2.hsp_num and SMM_PAC_REG = hsp2.HSP_PAC and hsp2.hsp_trat_int = 'I'
                    left join
                        loc as loc on hsp2.HSP_LOC = loc_cod
                    left join
                        str as str2 on LOC_STR = str2.str_cod,
                    OSM left join
                        pac on osm.OSM_PAC = pac_reg
                    left join
                        cnv on OSM_CNV = cnv_cod
                    left join
                        psv on OSM_MREQ = psv.psv_cod,
                    STR,
                    SMK
            WHERE
                ".(!empty($filter['first']) ? $filter['first'].' AND ' : "")."
                OSM.OSM_STR = str.STR_COD AND
                SMM_OSM = OSM_NUM AND
                SMM_OSM_SERIE = OSM_SERIE AND
                OSM_STR = str.str_cod AND
                smm_exec <> 'C' AND
                ".(!empty($filter['third']) ? $filter['third'].' AND ' : "")."
                SMM.smm_cod = SMK.smk_cod AND 
                EXISTS ( SELECT 1 FROM SMK, CTF WHERE SMM_COD = SMK_COD AND SMM_TPCOD = SMK_TIPO AND SMK_CTF = CTF_COD AND CTF_CTF_COD in (9134,2800,2806))
            GROUP BY 
                OSM_SERIE,  
                OSM_NUM,
                str.str_cod,
                str.str_nome,
                OSM_DTHR,
                pac_reg,
                pac_nome,
                PEX_DTHR,
                OSM_ATEND
        ), Integracao AS
        ( 
            SELECT 
                CountExm.*,
                ((CountExm.ABERTO + CountExm.EXECUCAO) + CountExm.LIBERADO) as QTD, 
                case 
                    when (CountExm.ABERTO > 0) then 'A' 
                    else 'false'
                end as status_aberto,
                case 
                    when (CountExm.EXECUCAO > 0) then 'X'
                    else 'false'
                end as status_execucao,
                case 
                    when (CountExm.LIBERADO > 0) then 'L'
                    else 'false'
                end as status_liberacao
            FROM CountExm
        ), Pagination AS
        ( 
            SELECT 
                Integracao.*,
                CASE WHEN lwl_num_origem like '%INCLUSAO ERRO = N%' THEN 'I' ELSE 'NI' END as TIPO_INTEGRACAO
            FROM 
                Integracao 
                    left join osm on (osm.osm_serie = Integracao.OS_SERIE AND osm.osm_num = Integracao.OS_NUMERO)
                    left join lwl on (osm.osm_serie = lwl.LWL_OSM_SERIE and osm.osm_num = lwl.LWL_OSM_NUM AND lwl_num_origem like '%INCLUSAO ERRO =%')
        )";

        return (new SmartPainel("Pagination"))->with("Pagination.*, ROW_NUMBER() OVER (ORDER BY ".$order.") AS RowNum", $filter['second'], null, null, null, $with)->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Método responsável por retornar todos os exames dentro de um range de datas e por um determinado setor
    public static function getAllExamesByDatesAndSector($dateFirst, $dateSecond, $sector = null)
    {
        $sectorSql = "OSM_STR like '$sector%' AND ";

        $tables = "SMM
        left join
        pex on smm.SMM_OSM_SERIE = pex.PEX_OSM_SERIE and smm.SMM_OSM = pex.PEX_OSM_NUM
        left join
        hsp as hsp1 on SMM_HSP_NUM = hsp1.hsp_num and SMM_PAC_REG = hsp1.HSP_PAC and hsp1.hsp_trat_int = 'T'
        left join
        STR AS STR1 ON HSP1.HSP_STR_COD = STR1.STR_COD
        left join
        hsp as hsp2 on SMM_HSP_NUM = hsp2.hsp_num and SMM_PAC_REG = hsp2.HSP_PAC and hsp2.hsp_trat_int = 'I'
        left join
        loc as loc on hsp2.HSP_LOC = loc_cod
        left join
        str as str2 on LOC_STR = str2.str_cod,
        OSM left join
        pac on osm.OSM_PAC = pac_reg
        left join
        cnv on OSM_CNV = cnv_cod
        left join
        psv on OSM_MREQ = psv.psv_cod,
        STR";

        $fields = "DISTINCT
        OSM_SERIE as OS_SERIE,  
            OSM_NUM as OS_NUMERO,
        str.str_cod as setor_codigo,
        str.str_nome as setor,
        pac_reg as paciente_registro,
        pac_nome as paciente_nome,
        PAC_PRONT as paciente_prontuario,
        PAC_NASC as paciente_dtnasc,
        PAC_SEXO as paciente_sexo,
        PAC_NUMCPF as paciente_cpf,
        PAC_NUMRG as paciente_rg,
        OSM_CNV as convenio_codigo,
        cnv_nome as convenio_nome,
        case when loc.loc_nome IS NOT NULL THEN STR2.STR_NOME ELSE STR1.STR_NOME END SETOR,
        LOC_COD AS LOCAL_CODIGO,
        LOC_NOME AS LOCAL_NOME,
        psv_cod as solicitante_codigo,
        psv_nome as solicitante_nome,
        psv_crm as solicitante_registro,
        SMM_NUM,
        SMM_QT AS QTD,
        OSM_DTHR as LANCAMENTO,
        smm_exec AS ST,
        pex.PEX_DTHR as pedido_data,
        pex.PEX_CID as pedido_cid,
        CAST(pex.PEX_OBS AS VARCHAR(MAX)) as pedido_indicacao";

        $where = "OSM_DTHR BETWEEN '" . $dateFirst . "' and '" . $dateSecond . "' AND 
        OSM.OSM_STR = str.STR_COD AND
        SMM_OSM = OSM_NUM AND
        SMM_OSM_SERIE = OSM_SERIE AND
        OSM_STR = str.str_cod AND
        smm_exec <> 'C' AND
        ".(!empty($sector) ? $sectorSql : '')."
          EXISTS ( SELECT 1 FROM SMK, CTF WHERE SMM_COD = SMK_COD AND SMM_TPCOD = SMK_TIPO AND SMK_CTF = CTF_COD AND CTF_CTF_COD in (2800,2806,9134))";

        $group = null;
        $order = "OS_SERIE, OS_NUMERO, str.STR_NOME, SMM_NUM";

        return (new SmartPainel($tables))->select($fields, $where, null, $group, $order)->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getExameByOS($os_serie, $os_numero, $smm_num = null)
    {

        $tables = "SMM
            left join
                pex on smm.SMM_OSM_SERIE = pex.PEX_OSM_SERIE and smm.SMM_OSM = pex.PEX_OSM_NUM
            left join
                amo on smm.smm_amo_cod = amo.amo_cod 
            left join
                hsp as hsp1 on SMM_HSP_NUM = hsp1.hsp_num and SMM_PAC_REG = hsp1.HSP_PAC and hsp1.hsp_trat_int = 'T'
            left join
                STR AS STR1 ON HSP1.HSP_STR_COD = STR1.STR_COD
            left join
                hsp as hsp2 on SMM_HSP_NUM = hsp2.hsp_num and SMM_PAC_REG = hsp2.HSP_PAC and hsp2.hsp_trat_int = 'I'
            left join
                loc as loc on hsp2.HSP_LOC = loc_cod
            left join
                str as str2 on LOC_STR = str2.str_cod,
            OSM left join
                pac on osm.OSM_PAC = pac_reg
            left join
                cnv on OSM_CNV = cnv_cod
            left join
                psv on OSM_MREQ = psv.psv_cod,
            STR,
            SMK";

        $fields = "DISTINCT
            OSM_SERIE as OS_SERIE,
            OSM_ATEND as tipo_os,  
            OSM_NUM as OS_NUMERO,
            str.str_cod as setor_codigo,
            str.str_nome as setor,
            pac_reg as paciente_registro,
            pac_nome as paciente_nome,
            PAC_PRONT as paciente_prontuario,
            PAC_NASC as paciente_dtnasc,
            PAC_SEXO as paciente_sexo,
            PAC_NUMCPF as paciente_cpf,
            PAC_NUMRG as paciente_rg,
            OSM_CNV as convenio_codigo,
            cnv_nome as convenio_nome,
            case when loc.loc_nome IS NOT NULL THEN STR2.STR_NOME ELSE STR1.STR_NOME END SETOR,
            LOC_COD AS LOCAL_CODIGO,
            LOC_NOME AS LOCAL_NOME,
            psv_cod as solicitante_codigo,
            psv_nome as solicitante_nome,
            psv_crm as solicitante_registro,
            SMM_NUM,
            SMM_QT AS QTD,
            SMM_PRE_CCV as codigo_exame,
            SMK_NOME AS EXAME,
            OSM_DTHR as LANCAMENTO,
            smm_exec AS ST,
            pex.PEX_DTHR as pedido_data,
            pex.PEX_CID as pedido_cid,
            CAST(pex.PEX_OBS AS VARCHAR(MAX)) as pedido_indicacao,
            AMO.AMO_QLF_ROT as amostra";

        $where = "
            OSM.OSM_STR = str.STR_COD AND
            SMM_OSM = OSM_NUM AND
            SMM_OSM_SERIE = OSM_SERIE AND
            OSM_STR = str.str_cod AND
            smm_exec <> 'C' AND
            SMM.smm_cod = SMK.smk_cod AND 
            OSM_SERIE = ".$os_serie." AND
            OSM_NUM = ".$os_numero." AND
            ".(empty($smm_num) ? "" : 'SMM_NUM = '.$smm_num.' AND ')."
            EXISTS ( SELECT 1 FROM SMK, CTF WHERE SMM_COD = SMK_COD AND SMM_TPCOD = SMK_TIPO AND SMK_CTF = CTF_COD AND CTF_CTF_COD in ('2806','2800', '3100', '9134'))";

        $group = null;
        $order = "OS_SERIE, OS_NUMERO, str.STR_NOME, SMM_NUM";

        return (new SmartPainel($tables))->select($fields, $where, null, $group, $order)->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function verifyOS($serie, $numero, $user, $obs)
    {
        $datetime = new \DateTime();
        $datetime->setTimezone(new \DateTimeZone('America/Fortaleza'));

        return (new Database("CheckOS", "OSVERIFIED"))->insert([
            "OS_SERIE" => $serie,
            "OS_NUMERO" => $numero,
            "DATA_RECEBIMENTO" => $datetime->format("Y-m-d H:i:s"),
            "STATUS" => 'V',
            "USER" => $user,
            "COMENTARIO" => $obs
        ]);
    }

    public static function verifyIntegration($serie, $numero)
    {
        return (new SmartPainel("osm left join lwl on osm_serie = LWL_OSM_SERIE and osm_num = LWL_OSM_NUM"))
            ->select("osm_serie, osm_num, CASE WHEN lwl_num_origem like '%INCLUSAO ERRO = N%' THEN 'I' ELSE 'NI' END as STATUS", 
            "osm_serie = '".$serie."' AND osm_num = '".$numero."'")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getStatus($os_serie, $os_numero)
    {
        return (new Database("CheckOS", "OSVERIFIED"))->select("id, STATUS, COMENTARIO", "OS_SERIE = ".$os_serie." AND OS_NUMERO = ".$os_numero)->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getAllVerifiedOS()
    {
        return (new Database("CheckOS", "OSVERIFIED"))->select("*")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getObsByExame($os_serie, $os_numero, $os_exame_numero)
    {
        return (new Database("CheckOS", "OSEXAMEOBS"))->select("id, observacao, justificativa", "os_serie = ".$os_serie." AND os_numero = ".$os_numero." AND os_exame_num = ".$os_exame_numero)->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getJustificativas()
    {
        return (new Database("CheckOS", "JUSTIFICATIVAS"))->select("*")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function addObsInExame($os_serie, $os_numero, $os_exame_numero, $observacao, $justificativa)
    {
        return (new Database("CheckOS", "OSEXAMEOBS"))->insert([
            "os_serie" => $os_serie, 
            "os_numero" => $os_numero, 
            "os_exame_num" => $os_exame_numero,
            "observacao" => $observacao,
            "justificativa" => $justificativa
        ]);
    }

    public static function updateObsInExame($idObservacao, $observacao, $justificativa)
    {
        return (new Database("CheckOS", "OSEXAMEOBS"))->update("id = ".$idObservacao, [
            "observacao" => $observacao,
            "justificativa" => $justificativa
        ]);
    }
}
