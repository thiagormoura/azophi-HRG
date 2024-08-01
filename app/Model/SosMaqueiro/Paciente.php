<?php

namespace App\Model\SosMaqueiro;

use App\Db\SmartPainel;

class Paciente
{
  public static function getPacientes()
  {
    $fields = "
    pac_reg AS pac_registro,
    pac_pront AS pac_prontuario,
    PAC_NOME AS pac_nome,
      pac_sexo,
      pac_nasc,
      pac_peso,
      loc_cod AS pac_local_codigo,
      loc_nome AS pac_local_nome,
    case 
      when 	setor.str_cod 	 = 'UIT' then '3º Andar'
      when 	setor.str_cod 	 = 'UI4' then '4º Andar'
      when 	setor.str_cod 	 = 'UI5' then '5º ANDAR'
      when 	setor.str_cod 	 = 'UI7' then '7º ANDAR'
      when 	setor.str_cod 	 = 'CIR' then 'CENTRO CIRURGICO'
      when 	setor.str_cod 	 = 'HEM' then 'HEMODINAMICA'
      when 	setor.str_cod 	 = 'TMO' then 'TRANSP MEDULA OSSEA'
      when 	setor.str_cod 	 = 'UT7' then 'UTI - PEDIÃTRICA'
      when 	setor.str_cod 	 = '32' then 'UTI CIRÚRGICO 4º ANDAR'
      when 	setor.str_cod 	 = 'UT1' then 'UTI I'
      when 	setor.str_cod 	 = 'UT2' then 'UTI II'
      when 	setor.str_cod 	 = 'UT5' then 'UTI III'
      when 	setor.str_cod 	 = 'UT8' then 'UTI IV' 
      else    setor.str_nome 
    end AS pac_unidade_nome,
    setor.str_cod  AS pac_unidade_codigo";
    $tables = "hsp, loc, str AS setor, pac";
    $where = "HSP_STAT = 'A' AND HSP_LOC = LOC_COD AND LOC_STR = STR_COD AND pac_reg = HSP_PAC order by pac_nome";

    return (new SmartPainel($tables))->select($fields, $where, null, null, null)->fetchAll(\PDO::FETCH_ASSOC);
  }

  public static function getPacienteByRegistro($registro)
  {
    $fields = "
    pac_reg AS pac_registro,
    pac_pront AS pac_prontuario,
    PAC_NOME AS pac_nome,
      pac_sexo,
      pac_nasc,
      pac_peso,
      loc_cod AS pac_local_codigo,
      loc_nome AS pac_local_nome,
    case 
      when 	setor.str_cod 	 = 'UIT' then '3º Andar'
      when 	setor.str_cod 	 = 'UI4' then '4º Andar'
      when 	setor.str_cod 	 = 'UI5' then '5º ANDAR'
      when 	setor.str_cod 	 = 'UI7' then '7º ANDAR'
      when 	setor.str_cod 	 = 'CIR' then 'CENTRO CIRURGICO'
      when 	setor.str_cod 	 = 'HEM' then 'HEMODINAMICA'
      when 	setor.str_cod 	 = 'TMO' then 'TRANSP MEDULA OSSEA'
      when 	setor.str_cod 	 = 'UT7' then 'UTI - PEDIÃTRICA'
      when 	setor.str_cod 	 = '32' then 'UTI CIRÚRGICO 4º ANDAR'
      when 	setor.str_cod 	 = 'UT1' then 'UTI I'
      when 	setor.str_cod 	 = 'UT2' then 'UTI II'
      when 	setor.str_cod 	 = 'UT5' then 'UTI III'
      when 	setor.str_cod 	 = 'UT8' then 'UTI IV' 
      else    setor.str_nome 
    end AS pac_unidade_nome,
    setor.str_cod  AS pac_unidade_codigo";
    $tables = "hsp, loc, str AS setor, pac";
    $where = "HSP_LOC = LOC_COD AND LOC_STR = STR_COD AND pac_reg = HSP_PAC AND pac_reg = " . $registro . " order by pac_nome";

    return (new SmartPainel($tables))->select($fields, $where, null, null, null)->fetch(\PDO::FETCH_ASSOC);
  }
}
