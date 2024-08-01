<?php

namespace App\Model\GestaoLeitos;

use App\Db\Smart;
use App\Db\Database;

class Paciente
{
	// Retorna nome e registro de todos os pacientes internados no hospital
	public static function getPatients($internationUnits)
	{
		$fields = "pac_reg AS 'PACIENTE_REGISTRO', pac_nome AS 'PACIENTE_NOME'";
		$tables = "LTO, LOC, HSP , CLE, str, PAC, CNV, CLE C2, PSV";
		$where = "HSP_PAC = PAC_REG AND HSP_MDE = PSV_COD AND PAC_CLE_COD *= C2.CLE_COD AND PAC_CNV = CNV_COD AND STR_STATUS = 'A' AND (STR_COD IN (" . $internationUnits . ")) AND LTO_PAC_REG     = HSP_PAC AND LTO_HSP_NUM     = HSP_NUM AND LTO_LOC_COD     = LOC_COD AND ( (LOC_STR     = STR_COD )) AND LOC_CLE_COD     = CLE.CLE_COD AND CLE.CLE_TIPO      = 'L' AND LOC_STATUS  in ('L','O') AND (( GETDATE() BETWEEN LTO_DTHR_INI  AND LTO_DTHR_FIM)  OR ( LTO.LTO_DTHR_INI = LTO.LTO_DTHR_FIM  AND HSP_STAT        = 'A'   AND LTO_DTHR_FIM    < GETDATE()))";
		$unionQuery = " SELECT
		pac_reg AS 'PACIENTE_REGISTRO',
		pac_nome AS 'PACIENTE_NOME'
		FROM
		HSP JOIN PAC ON PAC_REG = HSP_PAC
		LEFT JOIN CNV ON PAC_CNV = CNV_COD
		LEFT JOIN CLE ON PAC_CLE_COD = CLE_COD
		LEFT JOIN PSV ON HSP_MDE = PSV_COD
		WHERE
		HSP_STAT = 'A' AND
		HSP_TRAT_INT = 'T' 
		AND HSP_STR_COD IN ('PAT', 'PSI', 'PSO', 'CIR', 'SUQ') 
		AND ((hsp_trat_int = 't' and hsp_stat = 'A' and datediff(day,hsp_dthre, getdate())  <= 3))
		ORDER BY pac_nome";

		return (new Smart($tables))->unionAll($fields, $where, null, null, null, $unionQuery)->fetchAll(\PDO::FETCH_ASSOC);
	}

	public static function getPatientMySQL($register, $idSolicitacao, $toCheck = false)
	{
		return (new Database('GestaoTeste', 'PACIENTES pac inner join SOLICITACOES solic on pac.REGISTRO = solic.REGISTRO'))
		->select($toCheck ? "pac.REGISTRO, pac.NOME" : 'pac.*, solic.SOLICITACAO_ACOMODACAO as ACOMODACAO, CONVENIO', 
			'pac.REGISTRO = ' . $register.' AND solic.idSOLICITACAO = '.$idSolicitacao)->fetch(\PDO::FETCH_ASSOC);
	}

	public static function getPatient($register)
	{
		$tables = "LTO, LOC, HSP , CLE, str, PAC, CNV, CLE C2, PSV";
		$fields = "pac_reg AS 'PACIENTE_REGISTRO', pac_nome AS 'PACIENTE_NOME', pac_nome_social AS 'PACIENTE_NOMESOCIAL', PAC_SEXO AS 'SEXO', PAC_NASC AS 'DTNASC', CNV_NOME AS 'CONVENIO', C2.CLE_NOME AS 'ACOMODACAO', HSP_DTHRE AS 'DATA_ADMISSAO', PSV_CRM AS 'MEDICO_CRM', PSV_UF AS 'MEDICO_UF', PSV_NOME AS 'MEDICO_ASSISTENTE'";
		$where = "HSP_PAC = PAC_REG
		AND HSP_MDE = PSV_COD
		AND     PAC_CLE_COD *= C2.CLE_COD
		AND     PAC_CNV = CNV_COD
		AND     STR_STATUS = 'A' 
		AND       LTO_PAC_REG     = HSP_PAC 
		AND     LTO_HSP_NUM     = HSP_NUM 
		AND     LTO_LOC_COD     = LOC_COD 
		AND     (LOC_STR     = STR_COD)
		AND       LOC_CLE_COD     = CLE.CLE_COD 
		AND     CLE.CLE_TIPO      = 'L' 
		AND     LOC_STATUS  in ('L','O')
		AND     (( GETDATE() BETWEEN LTO_DTHR_INI  AND LTO_DTHR_FIM) 
							OR ( LTO.LTO_DTHR_INI = LTO.LTO_DTHR_FIM 
								AND HSP_STAT        = 'A'  
								AND LTO_DTHR_FIM    < GETDATE()))
		AND PAC_REG = '$register'";

		$unionQuery = "SELECT
		pac_reg AS 'PACIENTE_REGISTRO',
		pac_nome AS 'PACIENTE_NOME',
		pac_nome_social AS 'PACIENTE_NOMESOCIAL',
		PAC_SEXO AS 'PACIENTE_SEXO',
		PAC_NASC AS 'PACIENTE_DTNASC',
		CNV_NOME AS 'CONVENIO',
		CLE_NOME AS 'ACOMODACAO',
		HSP_DTHRE AS 'DATA_ADMISSAO',
		PSV_CRM AS 'MEDICO_CRM',
		PSV_UF AS 'MEDICO_UF',
		PSV_NOME AS 'MEDICO_ASSISTENTE'
		FROM
		HSP JOIN PAC ON PAC_REG = HSP_PAC
		LEFT JOIN CNV ON PAC_CNV = CNV_COD
		LEFT JOIN CLE ON PAC_CLE_COD = CLE_COD
		LEFT JOIN PSV ON HSP_MDE = PSV_COD
		WHERE
		HSP_STAT = 'A' AND
		HSP_TRAT_INT = 'T' AND
		HSP_STR_COD = 'PAT' AND
		PAC_REG = '$register'";

		return (new Smart($tables))->unionAll($fields, $where, null, null, null, $unionQuery)->fetch(\PDO::FETCH_ASSOC);
	}

	public static function getPatientByBed(string $bed)
	{
		$fields = "PAC.PAC_REG as registro, PAC.PAC_NOME as nome_completo, PAC.PAC_NASC as data_nascimento, PAC.PAC_SEXO as sexo";
		$tables = "PAC, LOC";
		$where = "LOC_PAC = PAC_REG AND LOC_COD LIKE '$bed'";

		return (new Smart($tables))->select($fields, $where)->fetch(\PDO::FETCH_ASSOC);
	}

	public static function getPatientReservation($register)
	{
		$fields = "rlt_dthr, pac_nome";
		$tables = "rlt, pac";
		$where = "pac_reg = rlt_pac_reg and ( rlt_pac_reg = '" . $register . "' ) AND ( rlt_status ='R' ) ";
		$order = "rlt_dthr desc";

		return (new Smart($tables))->select($fields, $where, null, null, null, $order)->fetchAll(\PDO::FETCH_ASSOC);
	}

	public static function getPatientPrecautions($register)
	{
		$fields = "distinct tap_cod as 'codigo', tap_descr as 'precaucao'";
		$tables = "app, tap, pac";
		$where = "app_tap_cod IN ('22', '23', '24', '25', '26') AND app_tap_cod = tap_cod AND app_pac_reg = pac_reg AND app_status = 'A' and pac_reg = '$register'";
		$order = "pac_nome";
		return (new Smart($tables))->select($fields, $where, null, null, null, $order)->fetchAll(\PDO::FETCH_ASSOC);
	}

	public static function getPatientRisks($register)
	{
		$fields = "distinct tap_cod as 'codigo', tap_descr as 'risco'";
		$tables = "app, tap";
		$where = "app_tap_cod IN ('39', '37', '38') AND app_tap_cod = tap_cod AND app_status = 'A' AND app_pac_reg = '$register'";

		return (new Smart($tables))->select($fields, $where, null, null, null, null)->fetchAll(\PDO::FETCH_ASSOC);
	}

	public static function getPacienteReservation($registro)
	{
		$fields = "STR_COD, STR_NOME, LOC_COD, LOC_NOME, RLT_PAC_REG, USR_NOME, RLT_DTHR, RLT_NUM";
		$where = "RLT_USR_LOGIN = usr_login AND RLT_STATUS = 'R' AND RLT_LOC_COD = LOC_COD AND LOC_STR = STR_COD AND RLT_PAC_REG = '" . $registro . "'";

		return (new Smart('RLT, LOC, STR, usr'))->select($fields, $where)->fetch(\PDO::FETCH_ASSOC);
	}

	public static function verifyReservation($registro, $reserveCode = null)
	{
		$where = "RLT_PAC_REG = '$registro' AND RLT_STATUS = 'R'";

		if ($reserveCode != null)
			$where .= " AND RLT_NUM = '$reserveCode'";

		return (new Smart('RLT'))->select("RLT_LOC_COD, RLT_STR_COD, RLT_NUM", $where)->fetchAll(\PDO::FETCH_ASSOC);
	}

	public static function getPatientRegisterById($id)
	{
		return (new Database('GestaoTeste', 'PACIENTES'))->select('REGISTRO, NOME', 'idPACIENTE = '.$id)->fetch(\PDO::FETCH_ASSOC);
	}
}
