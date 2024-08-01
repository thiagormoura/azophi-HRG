<?php

namespace App\Model\Entity;

use App\Db\Database;
use App\Db\Smart;

class Paciente
{
  // Método responsável por retornar o paciente no hospital por registro, data de nascimento e cpf
  public static function getPaciente($registro, $dataNascimento, $cpf)
  {
    $tables = "HSP, PAC, PDC";
    $fields = "PAC_REG as registro, PAC_NUMCPF as cpf, PAC_NASC as data_nascimento, PAC_EMAIL as email, HSP_NUM as hsp_num, HSP_STAT as status, pac_nome_social as nome_social, PDC_APELIDO as apelido";
    $where = "HSP_PAC = PAC_REG AND PAC_REG = pdc_pac_reg AND HSP_STAT = 'A' AND PAC_REG = $registro AND PAC_NASC = CONVERT(DATETIME, '$dataNascimento', 102) AND PAC_NUMCPF = '$cpf'";
    $group = null;
    $order = null;

    return (new Smart($tables))->select($fields, $where, null, $group, $order)->fetchObject(self::class);
  }

  public static function getPatientDischangeByHour(int $hours)
  {
    $tables = "HSP, PAC";
    $fields = "	PAC.PAC_NOME as nome_completo, PAC.PAC_REG as registro";
    $where = "	(	pac_nome not like 'TESTE%' OR 
		PAC_NOME NOT LIKE 'PAC %' OR 
		PAC_NOME NOT LIKE 'PACIENTE%') AND
		PAC.PAC_REG = HSP.HSP_PAC AND
    (HSP.HSP_STAT LIKE 'A' OR (DATEDIFF(hour,HSP_DTHRA,GETDATE()) <= $hours AND HSP.HSP_STAT NOT LIKE 'A'))";
    $group = null;
    $order = "PAC.PAC_NOME";

    return (new Smart($tables))->select($fields, $where, null, $group, $order)->fetchAll(\PDO::FETCH_CLASS, self::class);
  }

  public static function getPacienteByRegister(string $register)
  {
    $tables = "PAC";
    $fields = "PAC_REG as registro, PAC_NASC as data_nascimento, PAC_NUMCPF as cpf, PAC_EMAIL as email, PAC_NOME as nome_completo";
    $where = "PAC_REG = $register";
    $group = null;
    $order = null;

    return (new Smart($tables))->select($fields, $where, null, $group, $order)->fetchObject(self::class);
  }
}
