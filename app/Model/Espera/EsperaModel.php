<?php

namespace App\Model\Espera;

use App\Db\Database;
use App\Db\SmartPainel;

class EsperaModel
{
    public static function getTriagem()
    {
        $select = "DISTINCT 'TEMPO TRIAGEM', F1.FLE_PAC_REG,
            PAC.PAC_NOME,
            F1.FLE_BIP AS SENHA,
            P1.PSV_COD AS FILA_COD_CLASSIFICACAO,
            P1.PSV_NOME AS FILA_CLASSIFICACAO,
            F1.FLE_STATUS AS STATUS_CLASSIFICACAO,
            F1.FLE_DTHR_CHEGADA AS DTHR_CHEGADA_CLASSIFICACAO,
            F1.FLE_DTHR_ATENDIMENTO AS DTHR_ATENDIMENTO_CLASSIFICACAO,
            DATEDIFF(MINUTE,
                    MIN(F1.FLE_DTHR_CHEGADA),
                    ISNULL(F1.FLE_DTHR_ATENDIMENTO, GETDATE())) AS ESPERA_CLASSIFICACAO,
            P2.PSV_COD AS FILA_COD_RECEPCAO,
            P2.PSV_NOME AS FILA_NOME,
            F2.FLE_STATUS AS STATUS_RECEPCAO,
            F2.FLE_DTHR_CHEGADA AS DTHR_CHEGADA_RECEPCAO,
            F2.FLE_DTHR_ATENDIMENTO AS DTHR_ATENDIMENTO_RECEPCAO,
            DATEDIFF(MINUTE,
                    MIN(F1.FLE_DTHR_CHEGADA),
                    ISNULL(F2.FLE_DTHR_ATENDIMENTO, GETDATE())) AS ESPERA_RECEPCAO";
    
        $from = "FLE F1
                LEFT JOIN
            FLE f2 ON f1.FLE_BIP = F2.FLE_BIP
                AND F1.FLE_PAC_REG = F2.FLE_PAC_REG
                AND F1.FLE_DTHR_ATENDIMENTO < F2.FLE_DTHR_CHEGADA
            and DATEDIFF(HOUR, F1.FLE_DTHR_ATENDIMENTO, F2.FLE_DTHR_CHEGADA) < 48
                AND F2.FLE_PSV_COD IN ('900197')

                LEFT JOIN
            PSV P2 ON F2.FLE_PSV_COD = P2.PSV_COD,
            PSV P1,
            PAC";


        $where = "F1.FLE_DTHR_CHEGADA >= getdate() -1
            AND f1.FLE_PSV_COD = P1.PSV_COD
            AND F1.FLE_PAC_REG = PAC.PAC_REG
            AND P1.PSV_COD IN ('900250')
            AND (F1.FLE_STATUS = 'A'
            OR EXISTS(SELECT 1
                    FROM
                        FLE F3
                    WHERE
                        F1.FLE_BIP = F3.FLE_BIP AND
                        F2.FLE_STATUS = 'A' AND 
                        F1.FLE_PAC_REG = F3.FLE_PAC_REG
                        AND F3.FLE_DTHR_CHEGADA > F1.FLE_DTHR_ATENDIMENTO
                        AND F3.FLE_DTHR_CHEGADA >= '2020-06-04 00:00:00'))";

        $group = "F1.FLE_PAC_REG, 
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
    
        return (new SmartPainel($from))->select($select, $where, null, $group, "F1.FLE_DTHR_CHEGADA")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getPrimeiraAvaliacao()
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

        $where = "F1.FLE_DTHR_CHEGADA >  '2020-05-28 00:00:00' and
            f1.FLE_PSV_COD		= P1.PSV_COD AND
            F1.FLE_PAC_REG = PAC.PAC_REG AND 
            P1.PSV_COD			IN ( '900290', '900289', '900288') AND
            f2.FLE_PSV_COD		= P2.PSV_COD AND
            F2.FLE_PAC_REG		= F1.FLE_PAC_REG AND
            F2.FLE_BIP		=	F1.FLE_BIP AND
            F2.FLE_PSV_COD IN ( '900197', '900250' ) AND
            F2.FLE_DTHR_CHEGADA >  '2020-05-28 00:00:00' and
            F1.FLE_STATUS  = 'A'	AND
            NOT EXISTS (SELECT 1 FROM RCL WHERE RCL_PAC = f1.FLE_PAC_REG AND RCL_DTHR > F2.FLE_DTHR_CHEGADA AND RCL_COD IN('00010022','21010006') AND RCL_MED <> 101010 AND  RCL_STAT <> 'C')";

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

    public static function getReavaliacao()
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

        $where = "F1.FLE_DTHR_CHEGADA >  '2020-05-28 00:00:00' and
            f1.FLE_PSV_COD		= P1.PSV_COD AND
            F1.FLE_PAC_REG = PAC.PAC_REG AND 
            P1.PSV_COD			IN ( '900290', '900289', '900288') AND
            f2.FLE_PSV_COD		= P2.PSV_COD AND
            F2.FLE_PAC_REG		= F1.FLE_PAC_REG AND
            F2.FLE_BIP		=	F1.FLE_BIP AND
            F2.FLE_PSV_COD IN ( '900197', '900250' ) AND
            F2.FLE_DTHR_CHEGADA >  '2020-05-28 00:00:00' and
            F1.FLE_STATUS  = 'A'	AND
		    EXISTS (SELECT 1 FROM RCL WHERE RCL_PAC = f1.FLE_PAC_REG AND RCL_DTHR > F2.FLE_DTHR_CHEGADA AND RCL_COD IN('00010022','21010006') AND RCL_MED <> 101010 AND RCL_STAT <> 'C')";

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
    
    public static function getTriagemPedValues()
    {
        $select = "DISTINCT
            'TRIAGEM_PED' AS TIPO_FILA,
            F1.FLE_PAC_REG,
            PAC.PAC_NOME,
            f1.FLE_BIP AS SENHA,
            MIN(F1.FLE_DTHR_CHEGADA) as DTHR_CHEGADA_HRG,
            MIN(F1.FLE_DTHR_ATENDIMENTO) as DTHR_CHEGADA_CLASSIFICACAO,
            DATEDIFF(MINUTE,MIN(F1.FLE_DTHR_CHEGADA), MIN(F1.FLE_DTHR_ATENDIMENTO)) as tempo_espera_classificacao,
            P1.PSV_COD as FILA_COD,
            P1.PSV_NOME AS FILA_NOME,
            CASE 
                WHEN f1.FLE_BIP IS NULL 
                        THEN DATEDIFF(MINUTE,MAX(F1.FLE_DTHR_CHEGADA), isnull(F1.FLE_DTHR_ATENDIMENTO,GETDATE()))
                ELSE DATEDIFF(MINUTE,MIN(F1.FLE_DTHR_CHEGADA), isnull(F1.FLE_DTHR_ATENDIMENTO,GETDATE())) END as tempo_espera_total";

        $where = "F1.FLE_DTHR_CHEGADA >  getdate() - 1  and
            f1.FLE_PSV_COD		= P1.PSV_COD AND
            F1.FLE_PAC_REG = PAC.PAC_REG AND 
            F1.FLE_PSV_COD		IN ( '903330' ) AND
            F1.FLE_DTHR_CHEGADA >  getdate() - 1  and
            F1.FLE_STATUS  = 'A'";

        $group = "P1.PSV_COD,
            F1.FLE_BIP,
            F1.FLE_PAC_REG,
            PAC.PAC_NOME,
            P1.PSV_NOME,
            F1.FLE_DTHR_CHEGADA,
            F1.FLE_DTHR_ATENDIMENTO,
            F1.FLE_STATUS";

        return (new SmartPainel("FLE f1, PSV P1, PAC"))->select($select, $where, null, $group)->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getRecepcaoPedValues()
    {
        $select = "DISTINCT
		'CONS_PED' AS TIPO_FILA,
		F1.FLE_PAC_REG,
		PAC.PAC_NOME,
		f1.FLE_BIP AS SENHA,
		MIN(F2.FLE_DTHR_CHEGADA) as DTHR_CHEGADA_HRG,
		MIN(F2.FLE_DTHR_ATENDIMENTO) as DTHR_CHEGADA_CLASSIFICACAO,
		DATEDIFF(MINUTE,MIN(F2.FLE_DTHR_CHEGADA), MIN(F2.FLE_DTHR_ATENDIMENTO)) as tempo_espera_classificacao,
		P1.PSV_COD as FILA_COD,
		P1.PSV_NOME AS FILA_NOME,
		CASE 
			WHEN f1.FLE_BIP IS NULL 
					THEN DATEDIFF(MINUTE,MAX(F2.FLE_DTHR_CHEGADA), isnull(F1.FLE_DTHR_ATENDIMENTO,GETDATE()))
			ELSE DATEDIFF(MINUTE,MIN(F2.FLE_DTHR_CHEGADA), isnull(F1.FLE_DTHR_ATENDIMENTO,GETDATE())) END as tempo_espera_total,
		F1.FLE_STATUS,'A' AS atendimento";

        $where = "F1.FLE_DTHR_CHEGADA >  getdate() - 5  and
            f1.FLE_PSV_COD		= P1.PSV_COD AND
            F1.FLE_PAC_REG = PAC.PAC_REG AND 
            P1.PSV_COD			IN ( '903341') AND
            f2.FLE_PSV_COD		= P2.PSV_COD AND
            F2.FLE_PAC_REG		= F1.FLE_PAC_REG AND
            F2.FLE_PSV_COD		IN ( '903330' ) AND
            F2.FLE_DTHR_CHEGADA	>  getdate() - 5  and
            F1.FLE_STATUS		= 'A'";

        $group = "P1.PSV_COD,
            F1.FLE_BIP,
            F1.FLE_PAC_REG,
            PAC.PAC_NOME,
            P1.PSV_NOME,
            F1.FLE_DTHR_CHEGADA,
            F1.FLE_DTHR_ATENDIMENTO,
            F1.FLE_STATUS
            HAVING (SELECT	1 FROM	RCL WHERE	RCL_PAC = f1.FLE_PAC_REG AND RCL_DTHR > MIN(F2.FLE_DTHR_CHEGADA) AND RCL_COD IN('CONSGINE') AND RCL_MED <> 101010 AND RCL_STAT <> 'C') IS NULL";

        $unionAll = "select  DISTINCT
                    'CONS_PED' AS TIPO_FILA,
                    F1.FLE_PAC_REG,
                    PAC.PAC_NOME,
                    f1.FLE_BIP AS SENHA,
                    MIN(F2.FLE_DTHR_CHEGADA) as DTHR_CHEGADA_HRG,
                    MIN(F2.FLE_DTHR_ATENDIMENTO) as DTHR_CHEGADA_CLASSIFICACAO,
                    DATEDIFF(MINUTE,MIN(F2.FLE_DTHR_CHEGADA), MIN(F2.FLE_DTHR_ATENDIMENTO)) as tempo_espera_classificacao,
                    P1.PSV_COD as FILA_COD,
                    P1.PSV_NOME AS FILA_NOME,
                    CASE 
                        WHEN f1.FLE_BIP IS NULL 
                                THEN DATEDIFF(MINUTE,MAX(F2.FLE_DTHR_CHEGADA), isnull(F1.FLE_DTHR_ATENDIMENTO,GETDATE()))
                        ELSE DATEDIFF(MINUTE,MIN(F2.FLE_DTHR_CHEGADA), isnull(F1.FLE_DTHR_ATENDIMENTO,GETDATE())) END as tempo_espera_total,
                    F1.FLE_STATUS,'R' AS atendimento
            from	FLE f1, PSV P1, FLE f2, PSV P2, PAC
            where	F1.FLE_DTHR_CHEGADA >  getdate() - 5  and
                    f1.FLE_PSV_COD		= P1.PSV_COD AND
                    F1.FLE_PAC_REG = PAC.PAC_REG AND 
                    P1.PSV_COD			IN ( '903341') AND
                    f2.FLE_PSV_COD		= P2.PSV_COD AND
                    F2.FLE_PAC_REG		= F1.FLE_PAC_REG AND
                    F2.FLE_PSV_COD		IN ( '903330' ) AND
                    F2.FLE_DTHR_CHEGADA >  getdate() - 5  and
                    F1.FLE_STATUS  = 'A'
            GROUP BY	
                    P1.PSV_COD,
                    F1.FLE_BIP,
                    F1.FLE_PAC_REG,
                    PAC.PAC_NOME,
                    P1.PSV_NOME,
                    F1.FLE_DTHR_CHEGADA,
                    F1.FLE_DTHR_ATENDIMENTO,
                    F1.FLE_STATUS
            HAVING (SELECT 1 FROM RCL WHERE	RCL_PAC = f1.FLE_PAC_REG AND RCL_DTHR > MIN(F2.FLE_DTHR_CHEGADA) AND RCL_COD IN('CONSGINE') AND RCL_MED <> 101010 AND RCL_STAT <> 'C') IS NOT NULL";

        return (new SmartPainel("FLE f1, PSV P1, FLE f2, PSV P2, PAC"))->unionAll($select, $where, null, $group, null, $unionAll)->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getConsultorioValues()
    {
        $select = "DISTINCT
            'RECEP_PED' AS TIPO_FILA,
            F1.FLE_PAC_REG,
            PAC.PAC_NOME,
            f1.FLE_BIP AS SENHA,
            MIN(F2.FLE_DTHR_CHEGADA) as DTHR_CHEGADA_HRG,
            MIN(F2.FLE_DTHR_ATENDIMENTO) as DTHR_CHEGADA_CLASSIFICACAO,
            DATEDIFF(MINUTE,MIN(F2.FLE_DTHR_CHEGADA), MIN(F2.FLE_DTHR_ATENDIMENTO)) as tempo_espera_classificacao,
            P1.PSV_COD as FILA_COD,
            P1.PSV_NOME AS FILA_NOME,
            CASE 
                WHEN f1.FLE_BIP IS NULL 
                        THEN DATEDIFF(MINUTE,MAX(F2.FLE_DTHR_CHEGADA), isnull(F1.FLE_DTHR_ATENDIMENTO,GETDATE()))
                ELSE DATEDIFF(MINUTE,MIN(F2.FLE_DTHR_CHEGADA), isnull(F1.FLE_DTHR_ATENDIMENTO,GETDATE())) END as tempo_espera_total,
            F1.FLE_STATUS,'A' AS atendimento";
        
        $where = "F1.FLE_DTHR_CHEGADA >  getdate() - 1  and
            f1.FLE_PSV_COD		= P1.PSV_COD AND
            F1.FLE_PAC_REG = PAC.PAC_REG AND 
            P1.PSV_COD			IN ( '903328') AND
            f2.FLE_PSV_COD		= P2.PSV_COD AND
            F2.FLE_PAC_REG		= F1.FLE_PAC_REG AND
            F2.FLE_PSV_COD IN ( '903330', '903341' ) AND
            F2.FLE_DTHR_CHEGADA >  getdate() - 1  and
            F1.FLE_STATUS  = 'A'";
        $group = "P1.PSV_COD,
            F1.FLE_BIP,
            F1.FLE_PAC_REG,
            PAC.PAC_NOME,
            P1.PSV_NOME,
            F1.FLE_DTHR_CHEGADA,
            F1.FLE_DTHR_ATENDIMENTO,
            F1.FLE_STATUS
            HAVING (SELECT	1 FROM	RCL WHERE	RCL_PAC = f1.FLE_PAC_REG AND RCL_DTHR > MIN(F2.FLE_DTHR_CHEGADA) AND RCL_COD IN('00010023') AND RCL_MED <> 101010 AND RCL_STAT <> 'C') IS NULL";

        $unionAll = "select  DISTINCT
                'RECEP_PED' AS TIPO_FILA,
                F1.FLE_PAC_REG,
                PAC.PAC_NOME,
                f1.FLE_BIP AS SENHA,
                MIN(F2.FLE_DTHR_CHEGADA) as DTHR_CHEGADA_HRG,
                MIN(F2.FLE_DTHR_ATENDIMENTO) as DTHR_CHEGADA_CLASSIFICACAO,
                DATEDIFF(MINUTE,MIN(F2.FLE_DTHR_CHEGADA), MIN(F2.FLE_DTHR_ATENDIMENTO)) as tempo_espera_classificacao,
                P1.PSV_COD as FILA_COD,
                P1.PSV_NOME AS FILA_NOME,
                CASE 
                    WHEN f1.FLE_BIP IS NULL 
                            THEN DATEDIFF(MINUTE,MAX(F2.FLE_DTHR_CHEGADA), isnull(F1.FLE_DTHR_ATENDIMENTO,GETDATE()))
                    ELSE DATEDIFF(MINUTE,MIN(F2.FLE_DTHR_CHEGADA), isnull(F1.FLE_DTHR_ATENDIMENTO,GETDATE())) END as tempo_espera_total,
                F1.FLE_STATUS,'R' AS atendimento
        from	FLE f1, PSV P1, FLE f2, PSV P2, PAC
        where	F1.FLE_DTHR_CHEGADA >  getdate() - 1  and
                f1.FLE_PSV_COD		= P1.PSV_COD AND
                F1.FLE_PAC_REG = PAC.PAC_REG AND 
                P1.PSV_COD			IN ( '903328') AND
                f2.FLE_PSV_COD		= P2.PSV_COD AND
                F2.FLE_PAC_REG		= F1.FLE_PAC_REG AND
                F2.FLE_PSV_COD IN ( '903330', '903341' ) AND
                F2.FLE_DTHR_CHEGADA >  getdate() - 1  and
                F1.FLE_STATUS  = 'A'
        GROUP BY	
                P1.PSV_COD,
                F1.FLE_BIP,
                F1.FLE_PAC_REG,
                PAC.PAC_NOME,
                P1.PSV_NOME,
                F1.FLE_DTHR_CHEGADA,
                F1.FLE_DTHR_ATENDIMENTO,
                F1.FLE_STATUS
        HAVING (SELECT 1 FROM RCL WHERE	RCL_PAC = f1.FLE_PAC_REG AND RCL_DTHR > MIN(F2.FLE_DTHR_CHEGADA) AND RCL_COD IN('00010023') AND RCL_MED <> 101010 AND RCL_STAT <> 'C') IS NOT NULL";
        
        return (new SmartPainel("FLE f1, PSV P1, FLE f2, PSV P2, PAC"))->unionAll($select, $where, null, $group, null, $unionAll)->fetchAll(\PDO::FETCH_ASSOC);
    }
}