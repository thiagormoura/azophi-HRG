<?php

namespace App\Model\Papem;

use App\Db\Database;
use App\Db\Smart;
use App\Db\SmartPainel;

class PapemModel
{
    public static function getFila()
    {
        $select = "DISTINCT
            F1.FLE_PAC_REG,
            PAC.PAC_NOME,
            f1.FLE_BIP AS SENHA,
            MIN(F2.FLE_DTHR_CHEGADA) as DTHR_CHEGADA_HRG,
            MIN(F2.FLE_DTHR_ATENDIMENTO) as DTHR_CHEGADA_CLASSIFICACAO,
            DATEDIFF(MINUTE,MIN(F2.FLE_DTHR_CHEGADA), MIN(F2.FLE_DTHR_ATENDIMENTO)) as tempo_espera_classificacao,
            P1.PSV_COD as FILA_COD,
            P1.PSV_NOME AS FILA_NOME,
            DATEDIFF(MINUTE,MIN(F2.FLE_DTHR_CHEGADA), isnull(F1.FLE_DTHR_ATENDIMENTO,GETDATE())) as tempo_espera_total,
            F1.FLE_STATUS";

        $where = "F1.FLE_DTHR_CHEGADA >  GETDATE() - 1 and
            f1.FLE_PSV_COD		= P1.PSV_COD AND
            F1.FLE_PAC_REG = PAC.PAC_REG AND 
            P1.PSV_COD			IN ( '900290', '900289', '900288') AND
            f2.FLE_PSV_COD		= P2.PSV_COD AND
            F2.FLE_PAC_REG		= F1.FLE_PAC_REG AND
            F2.FLE_BIP		=	F1.FLE_BIP AND
            F2.FLE_PSV_COD IN ( '900197', '900250' ) AND
            F2.FLE_DTHR_CHEGADA >  GETDATE() - 1 and
            F1.FLE_STATUS  = 'A'";
        
        $group = "P1.PSV_COD,
            F1.FLE_BIP,
            F1.FLE_PAC_REG,
            PAC.PAC_NOME,
            P1.PSV_NOME,
            F1.FLE_DTHR_CHEGADA,
            F1.FLE_DTHR_ATENDIMENTO,
            F1.FLE_STATUS";

        return (new SmartPainel("FLE f1, PSV P1, FLE f2, PSV P2, PAC"))->select($select, $where, null, $group, "p1.psv_nome, tempo_espera_total desc")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getClinicaPapemRecep()
    {
        $select = "DISTINCT
            F1.FLE_PAC_REG,
            PAC.PAC_NOME,
            f1.FLE_BIP AS SENHA,
            MIN(F2.FLE_DTHR_CHEGADA) as DTHR_CHEGADA_HRG,
            MIN(F2.FLE_DTHR_ATENDIMENTO) as DTHR_CHEGADA_CLASSIFICACAO,
            DATEDIFF(MINUTE,MIN(F2.FLE_DTHR_CHEGADA), MIN(F2.FLE_DTHR_ATENDIMENTO)) as tempo_espera_classificacao,
            P1.PSV_COD as FILA_COD,
            P1.PSV_NOME AS FILA_NOME,
            DATEDIFF(MINUTE,MIN(F2.FLE_DTHR_CHEGADA), 
                        CASE WHEN F1.FLE_STATUS = 'A' THEN GETDATE()
                            ELSE F1.FLE_DTHR_ATENDIMENTO END) as tempo_espera_total,
            F1.FLE_STATUS";

        $where = "F1.FLE_DTHR_CHEGADA > GETDATE () - 1 and
            f1.FLE_PSV_COD		= P1.PSV_COD AND
            F1.FLE_PAC_REG = PAC.PAC_REG AND 
            P1.PSV_COD			IN ( '900290') AND
            f2.FLE_PSV_COD		= P2.PSV_COD AND
            F2.FLE_PAC_REG		= F1.FLE_PAC_REG AND
            F2.FLE_BIP		=	F1.FLE_BIP AND
            F2.FLE_PSV_COD IN ( '900197', '900250' ) AND
            F2.FLE_DTHR_CHEGADA > GETDATE () - 1 and
            F1.FLE_STATUS  = 'A'	AND
            NOT EXISTS (SELECT 1 FROM RCL WHERE RCL_PAC = f1.FLE_PAC_REG AND RCL_DTHR > F2.FLE_DTHR_CHEGADA AND RCL_COD IN('00010022','21010006') AND RCL_MED <> 101010 AND RCL_STAT <> 'C')";
        
        $group = "P1.PSV_COD,
            F1.FLE_BIP,
            F1.FLE_PAC_REG,
            PAC.PAC_NOME,
            P1.PSV_NOME,
            F1.FLE_DTHR_CHEGADA,
            F1.FLE_DTHR_ATENDIMENTO,
            F1.FLE_STATUS";

        return (new SmartPainel("FLE f1, PSV P1, FLE f2, PSV P2, PAC"))->select($select, $where, null, $group, "p1.psv_nome, tempo_espera_total desc")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getClassificaoPapemRecep()
    {
        $select = "DISTINCT
            F1.FLE_PAC_REG,
            PAC.PAC_NOME,
            f1.FLE_BIP AS SENHA,
            MIN(F1.FLE_DTHR_CHEGADA) as DTHR_CHEGADA_HRG,
            MIN(F1.FLE_DTHR_ATENDIMENTO) as DTHR_CHEGADA_CLASSIFICACAO,
            DATEDIFF(MINUTE,MIN(F1.FLE_DTHR_CHEGADA), MIN(F1.FLE_DTHR_ATENDIMENTO)) as tempo_espera_classificacao,
            P1.PSV_COD as FILA_COD,
            P1.PSV_NOME AS FILA_NOME,
            DATEDIFF(MINUTE,MIN(F1.FLE_DTHR_CHEGADA), isnull(F1.FLE_DTHR_ATENDIMENTO,GETDATE())) as tempo_espera_total,
            F1.FLE_STATUS";

        $where = "F1.FLE_DTHR_CHEGADA >  '2020-06-20 00:00:00' and
            f1.FLE_PSV_COD		= P1.PSV_COD AND
            F1.FLE_PAC_REG = PAC.PAC_REG AND 
            P1.PSV_COD			IN ( '900250') and
            f1.FLE_STATUS = 'A'";
            
        $group = "P1.PSV_COD,
		F1.FLE_BIP,
		F1.FLE_PAC_REG,
		PAC.PAC_NOME,
		P1.PSV_NOME,
		F1.FLE_DTHR_CHEGADA,
		F1.FLE_DTHR_ATENDIMENTO,
		F1.FLE_STATUS";

        return (new SmartPainel("FLE f1, PSV P1, PAC"))->select($select, $where, null, $group, "p1.psv_nome, tempo_espera_total desc")->fetchAll(\PDO::FETCH_ASSOC);
    }
}