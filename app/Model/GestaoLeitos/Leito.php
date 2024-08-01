<?php

namespace App\Model\GestaoLeitos;

use App\Db\Database;
use App\Db\Smart;

class Leito
{
    public static function insertLockBed(string $code, string $operation, string $userId)
    {
        return (new Database('GestaoLeitos', 'BLOQUEIO_LEITOS'))->insert([
            'codigo_leito' => $code,
            'usuario_operacao' => $userId,
            'operacao' => $operation,
        ]);
    }

    public static function getBedByCode($code)
    {
        return (new Smart('LOC'))->select("LOC_NOME as nome, LOC_COD as codigo, LOC_STATUS as status, LOC_STR as setor_codigo, LOC_PAC", "LOC_COD = '$code'")->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Método responsável por retornar todos os leitos (virtuais e não virtuais) por setor
     * @param string $unit Código da unidade
     * @return array|false
     */
    public static function getAllBedsByUnit($unit)
    {
        return (new Smart('LOC, CLE'))->select("LOC_NOME as nome, LOC_COD as codigo, CLE_COD as acomodacao, CASE WHEN LOC_LEITO_ID is null THEN 'virtual' ELSE 'normal' END AS tipo, LOC_STATUS as status, LOC_STR as setor_codigo", "LOC_DEL_LOGICA = 'N' AND CLE_COD = LOC_CLE_COD AND LOC_STR IN ('$unit') AND LOC_NOME NOT LIKE '%berço%'")->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Método responsável por retornar todos os leitos (virtuais e não virtuais) por setor e status
     * @param string $unit Código da unidade
     * @param string $status Status do leito
     * @return array|false
     */
    public static function getAllBedsByStatusAndUnit($unit, $status)
    {
        return (new Smart('LOC, CLE'))->select("LOC_NOME as nome, LOC_COD as codigo, CLE_COD as acomodacao, CASE WHEN LOC_LEITO_ID is null THEN 'virtual' ELSE 'normal' END AS tipo, LOC_STATUS as status, LOC_STR as setor_codigo", "LOC_DEL_LOGICA='N' AND LOC_STR IN ('$unit') AND LOC_CLE_COD=CLE_COD AND LOC_STATUS = '$status' AND LOC_NOME NOT LIKE '%berço%'", null, "LOC_COD, CLE_COD")->fetchAll(\PDO::FETCH_ASSOC);
    }


    public static function getBedsByUnitAccAndGender($unit, $accommodation, $gender)
    {
        $tables = "LOC 
        LEFT JOIN PAC	ON (LOC.LOC_PAC = PAC.PAC_REG)
        LEFT JOIN CLE	ON (CLE.CLE_COD = LOC.LOC_CLE_COD )
        INNER JOIN STR	ON (STR.STR_COD  = LOC.LOC_STR),
        LOC AS L2";

        $fields = "STR.STR_COD as setor_codigo,
        STR.STR_NOME as setor_nome,
        L2.LOC_COD as leito_codigo,
        L2.LOC_NOME as leito_nome";

        $where = "
        LOC.LOC_DEL_LOGICA = 'N'
        AND		LOC.LOC_LEITO_ID IS NOT NULL
        AND		(((LOC.LOC_STATUS IN ('B') AND LOC.LOC_OBS LIKE '%(S:$gender)%')) OR ((LOC.LOC_STATUS IN ('R') AND PAC_SEXO LIKE '%$gender%')))
        AND		CLE_ACOMODACAO = '$accommodation'
        AND		STR_COD = '$unit'
        AND		( LOC.LOC_RAMAL2 not like '%CVD%' OR LOC.LOC_RAMAL2 IS NULL )
        AND 	(LOC.LOC_NOME NOT LIKE '%PED%')		
        AND		(L2.LOC_NOME LIKE '' + SUBSTRING( LOC.LOC_NOME, 1, CHARINDEX(' ', LOC.LOC_NOME, 0 ) - 1) + '%' AND L2.LOC_STATUS = 'L' AND L2.LOC_DEL_LOGICA = 'N')";

        $group = "STR.STR_COD,
        STR.STR_NOME,
        L2.LOC_COD,
        L2.LOC_NOME,
        L2.LOC_CLE_COD,
        L2.LOC_STR,
        PAC_SEXO";

        $unionQuery = "SELECT		
        STR.STR_COD as setor_codigo,
        STR.STR_NOME as setor_nome,
        LOC_COD as leito_codigo,
        LOC.LOC_NOME as leito_nome
        FROM 
            LOC 
            LEFT JOIN PAC	ON (LOC.LOC_PAC = PAC.PAC_REG)
            LEFT JOIN CLE	ON (CLE.CLE_COD = LOC.LOC_CLE_COD )
            INNER JOIN STR	ON (STR.STR_COD  = LOC.LOC_STR)
        WHERE 
            LOC_DEL_LOGICA = 'N'
            AND		LOC_LEITO_ID IS NOT NULL
            AND		LOC_STATUS = 'L'
            AND		CLE_ACOMODACAO = '$accommodation'
            AND		STR_COD = '$unit'
            AND		( LOC_RAMAL2 not like '%CVD%' OR LOC_RAMAL2 IS NULL )
            AND 	(LOC_NOME NOT LIKE '%PED%')		
        GROUP BY
            STR.STR_COD,
            STR.STR_NOME,
            LOC_COD,
            LOC.LOC_NOME,
            LOC.LOC_CLE_COD,
            LOC.LOC_STR,
            PAC_SEXO
        HAVING 
            ( (SELECT COUNT(*) 
            FROM	LOC L, PAC P 
            WHERE	L.LOC_STR = LOC.LOC_STR 
            AND		L.LOC_CLE_COD = LOC.LOC_CLE_COD 
            AND		L.LOC_DEL_LOGICA = 'N'
            AND		L.LOC_PAC = P.PAC_REG
            AND		L.LOC_STATUS = 'O' AND	P.PAC_SEXO = '$gender'
            AND 	(L.LOC_NOME NOT LIKE '%PED%')	
            AND		L.LOC_NOME LIKE '' + SUBSTRING( LOC.LOC_NOME, 1, CHARINDEX(' ', LOC.LOC_NOME, 0 ) - 1) + '%') ) > 0
        OR
            (SELECT COUNT(*) 
            FROM	LOC L 
            WHERE	L.LOC_STR = LOC.LOC_STR 
            AND		L.LOC_CLE_COD = LOC.LOC_CLE_COD 
            AND		L.LOC_DEL_LOGICA = 'N'
            AND		L.LOC_NOME LIKE '' + SUBSTRING( LOC.LOC_NOME, 1, CHARINDEX(' ', LOC.LOC_NOME, 0 ) - 1) + '%') = 
            (SELECT COUNT(*) 
            FROM	LOC L 
            WHERE	L.LOC_STR = LOC.LOC_STR 
            AND		L.LOC_CLE_COD = LOC.LOC_CLE_COD 
            AND		L.LOC_DEL_LOGICA = 'N' 
            AND		L.LOC_STATUS = 'L'
            AND		L.LOC_NOME LIKE '' + SUBSTRING( LOC.LOC_NOME, 1, CHARINDEX(' ', LOC.LOC_NOME, 0 ) - 1) + '%')
        ORDER BY 
            STR.STR_NOME";

        return (new Smart($tables))->unionAll($fields, $where, null, $group, null, $unionQuery)->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getBedsByUnitAndAcc($unit, $accommodation)
    {
        return (new Smart('LOC, CLE, STR'))->select("STR.STR_COD AS setor_codigo, STR_NOME as setor_nome, LOC_COD as leito_codigo, LOC_NOME as leito_nome", "LOC_DEL_LOGICA = 'N' AND ( LOC_RAMAL2 not like '%CVD%' OR LOC_RAMAL2 IS NULL ) AND LOC_CLE_COD = '$accommodation' AND CLE_COD = LOC_CLE_COD AND STR_COD = LOC_STR AND STR_COD = '$unit' AND LOC_STATUS = 'L' AND (LOC_NOME NOT LIKE '%PED%' OR LOC_NOME NOT LIKE '%NEO%')")->fetchAll(\PDO::FETCH_ASSOC);
    }

    // leito covid
    public static function getCovidBedsByUnitAndAcc($unit, $accommodation)
    {
        return (new Smart('LOC, CLE, STR'))->select("STR_COD as setor_codigo, STR_NOME as setor_nome, LOC_COD as leito_codigo, LOC_NOME as leito_nome", "LOC_DEL_LOGICA = 'N' AND LOC_RAMAL2 LIKE '%CVD' AND LOC_RAMAL2 IS NOT NULL AND LOC_CLE_COD = '$accommodation' AND LOC_STATUS = 'L' AND CLE_COD = LOC_CLE_COD AND STR_COD = LOC_STR AND STR_COD = '$unit'")->fetchAll(\PDO::FETCH_ASSOC);
    }

    // leito não covid e pediatrico
    public static function getPediatricBedsByUnitAndAcc($unit, $accommodation)
    {
        return (new Smart('LOC, CLE, STR'))->select("STR_COD as setor_codigo, STR_NOME as setor_NOme, LOC_COD as leito_codigo, LOC_NOME as leito_nome", "LOC_DEL_LOGICA = 'N' AND ( LOC_RAMAL2 not like '%CVD%' OR LOC_RAMAL2 IS NULL ) AND LOC_CLE_COD = '$accommodation' AND CLE_COD = LOC_CLE_COD AND STR_COD = LOC_STR AND STR_COD = '$unit' AND LOC_STATUS = 'L' AND (LOC_NOME LIKE '%PED%' OR LOC_NOME LIKE '%NEO%')")->fetchAll(\PDO::FETCH_ASSOC);
    }


    public static function getVirtualBeds($units)
    {
        return (new Smart('LOC, CLE'))->select("LOC_STR as setor_codigo, LOC_NOME as leito_nome, LOC_COD as leito_codigo, CLE_COD as acomodacao, LOC_STATUS as status", "LOC_DEL_LOGICA='N' AND (LOC_STR IN ($units)) AND LOC_CLE_COD=CLE_COD AND LOC_LEITO_ID is null AND LOC_NOME NOT LIKE '%berço%'")->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Método responsável por retornar todos os leitos virtuais por unidade
     * @param string $unit Código da unidade
     * @return array|false
     */
    public static function getVirtualBedsByUnit(string $unit)
    {
        return (new Smart('LOC, CLE'))->select("LOC_STR as setor_codigo, LOC_NOME as leito_nome, LOC_COD as leito_codigo, CLE_COD as acomodacao, LOC_STATUS as status", "LOC_DEL_LOGICA='N' AND LOC_STR='$unit' AND LOC_CLE_COD=CLE_COD AND LOC_LEITO_ID is null AND LOC_NOME NOT LIKE '%berço%'")->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Método responsável por retornar todos os leitos normais por unidade
     * @param string $unit Código da unidade
     * @return array|false
     */
    public static function getBedsByUnit(string $unit)
    {
        return (new Smart('LOC, CLE'))->select("LOC_STR as setor_codigo, LOC_NOME as leito_nome, LOC_COD as leito_codigo, CLE_COD as acomodacao, LOC_STATUS as status", "LOC_DEL_LOGICA='N' AND LOC_STR='$unit' AND LOC_CLE_COD=CLE_COD AND LOC_LEITO_ID is not null AND LOC_NOME NOT LIKE '%berço%'")->fetchAll(\PDO::FETCH_ASSOC);
    }
    /**
     * Método responsável por retornar a operação do leito (bloqueado ou desbloqueado)
     * @param string $unit Código da unidade
     * @return array|false
     */
    public static function getBlockBedOperation(string $bedCode)
    {
        return (new Database('GestaoLeitos', 'BLOQUEIO_LEITOS'))->select('operacao', "codigo_leito = '$bedCode'", "1", null, 'dthr_operacao desc')->fetch(\PDO::FETCH_ASSOC);
    }

    public static function getBlockedBeds()
    {
        return (new Database('GestaoLeitos', 'BLOQUEIO_LEITOS bl'))->select('DISTINCT codigo_leito, operacao, dthr_operacao', "operacao like 'bloqueio' AND dthr_operacao >= (SELECT dthr_operacao FROM BLOQUEIO_LEITOS bl2 WHERE bl.codigo_leito = bl2.codigo_leito AND operacao like 'desbloqueio' ORDER BY dthr_operacao DESC LIMIT 1)", null, "codigo_leito", 'dthr_operacao desc')->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getBeds($units)
    {
        return (new Smart('LOC, CLE'))->select("LOC_STR as setor_codigo, LOC_NOME as leito_nome, LOC_COD as leito_codigo, CLE_COD as acomodacao, LOC_STATUS as status", "LOC_DEL_LOGICA='N' AND (LOC_STR IN ($units)) AND LOC_CLE_COD=CLE_COD AND LOC_LEITO_ID is not null AND LOC_NOME NOT LIKE '%berço%'")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getBedsByStatus(string $units, string $status)
    {
        return (new Smart('LOC, CLE'))->select("LOC_COD as leito_codigo, CLE_COD as acomodacao", "LOC_DEL_LOGICA='N' AND (LOC_STR IN ('$units')) AND LOC_CLE_COD=CLE_COD AND LOC_STATUS = '$status' AND LOC_LEITO_ID is not null AND LOC_NOME NOT LIKE '%berço%'", null, "LOC_COD, CLE_COD")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getBedsByStatusAndUnit($unit, $status)
    {
        return (new Smart('LOC, CLE'))->select("LOC_COD as leito_codigo, LOC_NOME as leito_nome, CLE_COD as acomodacao", "LOC_DEL_LOGICA='N' AND (LOC_STR = '$unit') AND LOC_CLE_COD=CLE_COD AND LOC_STATUS = '$status' AND LOC_LEITO_ID is not null AND LOC_NOME NOT LIKE '%berço%'", null, "LOC_NOME, LOC_COD, CLE_COD", "LOC_NOME asc")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getBedsByAccomodation($units, $type)
    {
        return (new Smart('LOC, CLE'))->select("LOC_COD as leito_codigo, CLE_COD as acomodacao", "LOC_DEL_LOGICA='N' AND (LOC_STR IN (" . $units . ")) AND LOC_CLE_COD=CLE_COD AND LOC_STATUS='L' AND CLE_COD = '$type' AND LOC_LEITO_ID is not null AND LOC_NOME NOT LIKE '%berço%'", null, "LOC_COD, CLE_COD")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getOccupationBedDate(string $bedCode, string $patientRegister)
    {
        return (new Smart('LTO'))->select("LTO_DTHR_INI AS data_ocupacao", "LTO_LOC_COD LIKE '$bedCode' AND LTO_PAC_REG LIKE '$patientRegister' AND LTO_DTHR_FIM = LTO_DTHR_INI")->fetchColumn();
    }

    public static function getReservationBedDate(string $bedCode, string $patientRegister)
    {
        return (new Smart('RLT'))->select("RLT_DTHR as data_reserva", "RLT_LOC_COD = '$bedCode' AND RLT_STATUS LIKE 'R' AND RLT_PAC_REG LIKE '$patientRegister'")->fetchColumn();
    }

    public static function getEmptyBedDate(string $bedCode)
    {
        return (new Smart('BLC, LTO'))->select("TOP 1 
        CASE WHEN ISNULL(BLC_DTHR_FIM, BLC_DTHR_INI) > LTO_DTHR_FIM THEN ISNULL(BLC_DTHR_FIM, BLC_DTHR_INI) ELSE LTO_DTHR_FIM END AS data", "BLC_LOC_COD = '$bedCode'", null, null, "ISNULL(BLC_DTHR_FIM, BLC_DTHR_INI) DESC")->fetchColumn();
    }

    public static function getBlockBedInfo(string $bedCode)
    {
        return (new Smart('LOC LEFT JOIN BLC ON LOC_COD = BLC_LOC_COD'))->select("BLC_DTHR_INI AS 'data', BLC_OBS AS motivo", "LOC_COD = '$bedCode' AND BLC_DTHR_FIM IS NULL AND LOC_STATUS = 'B'")->fetch(\PDO::FETCH_ASSOC);
    }

    public static function verifySMARTBed($code)
    {
        return (new Smart('BLC'))->select("*", "( blc_loc_cod = '$code' ) AND ( blc_status ='B' ) AND ( blc_dthr_fim is null )")->fetch(\PDO::FETCH_ASSOC);
    }

    public static function CloseBlockSMARTBed($code)
    {
        return (new Smart('BLC'))->update("( blc_loc_cod = '$code' ) AND ( blc_status ='B' ) AND ( blc_dthr_fim is null )", ['blc_dthr_fim' => date('Y-m-d H:i:s')]);
    }

    public static function openNewFreeBlockSMARTBed(array $data)
    {
        $query = "INSERT INTO {{table}} (blc_loc_cod, blc_dthr_ini, blc_status, blc_obs, blc_usr_login, blc_mot_tipo, blc_mot_cod, blc_dthr_prev)
        VALUES ('" . $data['bedCode'] . "', " . $data['dthr_initial'] . ", '" . $data['status'] . "', '" . $data['observation'] . "', '" . $data['user'] . "', '" . $data['reasonType'] . "', '" . $data['reasonCode'] . "', '" . $data['dthr_prev'] . "')";
        return (new Smart('BLC'))->insertRaw($query);
    }

    public static function closeNewFreeBlockSMARTBed(array $data)
    {
        $query = "INSERT INTO {{table}} (blc_loc_cod, blc_dthr_ini, blc_status, blc_obs, blc_usr_login, blc_mot_tipo, blc_mot_cod, blc_dthr_prev)
        VALUES ('" . $data['bedCode'] . "', " . $data['dthr_initial'] . ", '" . $data['status'] . "', '" . $data['observation'] . "', '" . $data['user'] . "', '" . $data['reasonType'] . "', '" . $data['reasonCode'] . "', '" . $data['dthr_prev'] . "')";
        return (new Smart('BLC'))->insertRaw($query);
    }

    public static function finishBlockActiveOfBed($code)
    {
        // return (new Smart('blc'))->select("*", "( blc_loc_cod = '".$code."') AND ( blc_status ='L' ) AND ( blc_dthr_fim is null )")->fetchAll(\PDO::FETCH_ASSOC);
        return (new Smart('blc'))->update("( blc_loc_cod = '$code') AND ( blc_status ='L' ) AND ( blc_dthr_fim is null )", [
            "blc_dthr_fim" => date("Y-m-d H:i:s")
        ]);
    }

    public static function startBlockActiveOfBed($code)
    {
        return (new Smart('blc'))->update("( blc_loc_cod = '$code') AND ( blc_status ='B' ) AND ( blc_dthr_fim is null )", [
            "blc_dthr_fim" => date("Y-m-d H:i:s")
        ]);
    }

    public static function setBedToBlockSMART($idSolicitation, $codeBed, $register, $gender)
    {
        return (new Smart('loc'))->update("( loc_cod = '$codeBed' ) AND ( loc_pac is null ) AND ( loc_status ='L' )", [
            "loc_status" => 'B',
            "loc_obs" => "PREPARAÇÃO ID: " . $idSolicitation . " REGISTRO: " . $register . " (S:" . $gender . ")",
            "loc_trak_status_leito" => null
        ]);
    }

    public static function setBedToFreeSMART($code)
    {
        // return (new Smart('loc'))->update("( loc_cod = '$code' ) AND ( loc_pac is null ) AND ( loc_status ='B' )", [
        //     "loc_status" => "L",
        //     "loc_obs" => null,
        //     "loc_trak_status_leito" => null
        // ]);

        $query = "UPDATE loc SET loc_status = 'L', loc_obs = null, loc_trak_status_leito = null WHERE ( loc_cod ='".$code."' ) AND ( loc_status ='B' ) AND ( loc_pac is null )";
        return (new Smart('loc'))->updateRaw($query);
    }

    public static function reserveBed($code)
    {
        // return (new Smart('loc'))->update("( loc_cod ='".$code."' ) AND ( loc_status ='L' )", [
        //     "loc_status" => 'R',
        //     "loc_trak_status_leito" => null
        // ]);

        $query = "UPDATE loc SET loc_status = 'R', loc_trak_status_leito = null WHERE ( loc_cod ='".$code."' ) AND ( loc_status ='L' )";
        return (new Smart('loc'))->updateRaw($query);
    }

    public static function createReserveBedInRLT(array $data)
    {
        $query = "INSERT INTO {{table}} ( rlt_str_cod, rlt_num, rlt_tipo, rlt_status, rlt_dthr, rlt_usr_login, rlt_pac_reg, rlt_dt_prev_entrada, rlt_loc_cod, rlt_ind_leito_qualquer, rlt_dias_prev ) 
            VALUES ( '" . $data['sector'] . "', '" . $data['contagem'] . "', '9', 'R', GETDATE(), 'GLEITOS', 0, GETDATE(), '" . $data['bedCode'] . "', 'N', 1 )";
        return (new Smart('RLT'))->insertRaw($query);
    }

    public static function putPatientInNewReservedBed($register, $bedCode)
    {
        return (new Smart('loc'))->update("( loc_cod = '" . $bedCode . "' ) AND ( loc_status in ( 'H' , 'R' ) )", [
            "loc_pac" => $register
        ]);
    }

    public static function putPatientInNewRLTBed($register, $sectorCode, $contagem)
    {
        // return "UPDATE RLT SET rlt_pac_reg = '" . $register . "' WHERE rlt_str_cod = '" . $sectorCode . "' AND rlt_num = " . $contagem . " AND rlt_tipo = '9'";
        return (new Smart('rlt'))->updateRaw("UPDATE RLT SET rlt_pac_reg = '" . $register . "' WHERE rlt_str_cod = '" . $sectorCode . "' AND rlt_num = " . $contagem . " AND rlt_tipo = '9'");
    }

    public static function updateReserve($newBedReserved)
    {
        return (new Smart('rlt'))->updateRaw("UPDATE RLT SET RLT_STR_COD = '".$newBedReserved['newSector']."', RLT_LOC_COD = '".$newBedReserved['newBed']."' WHERE RLT_PAC_REG = '".$newBedReserved['registro']."' AND RLT_STATUS = 'R' AND RLT_STR_COD = '".$newBedReserved['oldSector']."' AND RLT_LOC_COD = '".$newBedReserved['oldBed']."'");
    }

    public static function updateBedStatus($oldBed)
    {
        return (new Smart('rlt'))->updateRaw("UPDATE LOC SET LOC_STATUS='L', LOC_PAC=NULL WHERE LOC_COD = '".$oldBed."'");
    }

    public static function updateNewBedChanged($registro, $newBedCode)
    {
        return (new Smart('LOC'))->updateRaw("UPDATE LOC SET LOC_STATUS='R', LOC_PAC= '".$registro."' WHERE LOC_COD = '".$newBedCode."'");
    }

    public static function cancelReserve($registro, $reserveCode)
    {
        return (new Smart('RLT'))->updateRaw("UPDATE RLT SET RLT_STATUS = 'C', RLT_DTHR_CANC = GETDATE(), RLT_USR_LOGIN_CANC = 'GLEITOS' WHERE RLT_PAC_REG = '".$registro."' AND RLT_NUM = '".$reserveCode."' AND RLT_STATUS = 'R'");
    }




    public static function getContRLT()
    {
        return (new Smart('cnt'))->select("cnt_num", "( cnt_tipo ='RLT' ) AND ( cnt_serie =0 )")->fetch(\PDO::FETCH_ASSOC);
    }

    public static function updateContRLT($newCont)
    {
        return (new Smart('cnt'))->update("( cnt_tipo ='RLT' ) AND ( cnt_serie =0 ) AND ( cnt_num = " . ($newCont - 1) . " )", [
            "cnt_num" => $newCont
        ]);
    }
}
