<?php

namespace App\Model\PaFilas;

use App\Db\Smart;

class Pediatria
{
	public static function getTriagemPediatrica()
	{
		$fields = "DISTINCT
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
                    ELSE DATEDIFF(MINUTE,MIN(F1.FLE_DTHR_CHEGADA), isnull(F1.FLE_DTHR_ATENDIMENTO,GETDATE())) END as tempo_espera_total
        ";

		$tables = "FLE f1, PSV P1, PAC";

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

		return (new Smart($tables))->select($fields, $where, null, $group, null)->fetchAll(\PDO::FETCH_ASSOC);
	}

	public static function getTriagemMaterna()
	{
		$fields = "DISTINCT
        'TRIAGEM_MAT' AS TIPO_FILA,
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

		$tables = "FLE f1, PSV P1, PAC";

		$where = "F1.FLE_DTHR_CHEGADA >  getdate() - 1  and
        		f1.FLE_PSV_COD		= P1.PSV_COD AND
        		F1.FLE_PAC_REG = PAC.PAC_REG AND 
        		F1.FLE_PSV_COD		IN ( '903340' ) AND
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

		return (new Smart($tables))->select($fields, $where, null, $group, null)->fetchAll(\PDO::FETCH_ASSOC);
	}

	public static function getRecepcaoPediatrica()
	{

		$fields = "DISTINCT
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

		$tables = "FLE f1, PSV P1, FLE f2, PSV P2, PAC";
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
        		F1.FLE_STATUS HAVING (SELECT 1 FROM	RCL WHERE	RCL_PAC = f1.FLE_PAC_REG AND RCL_DTHR > MIN(F2.FLE_DTHR_CHEGADA) AND RCL_COD IN('CONSGINE') AND RCL_MED <> 101010 AND RCL_STAT <> 'C') IS NULL";

		$unionQuery = "select DISTINCT
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
		return (new Smart($tables))->unionAll($fields, $where, null, $group, null, $unionQuery)->fetchAll(\PDO::FETCH_ASSOC);
	}

	public static function getRecepcaoMaterna()
	{
		$fields = "DISTINCT
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

		$tables = "FLE f1, PSV P1, FLE f2, PSV P2, PAC";

		$where = "F1.FLE_DTHR_CHEGADA >  getdate() - 5  and
		f1.FLE_PSV_COD		= P1.PSV_COD AND
		F1.FLE_PAC_REG = PAC.PAC_REG AND 
		P1.PSV_COD			IN ( '903342') AND
		f2.FLE_PSV_COD		= P2.PSV_COD AND
		F2.FLE_PAC_REG		= F1.FLE_PAC_REG AND
		F2.FLE_PSV_COD IN ( '903340' ) AND
		F2.FLE_DTHR_CHEGADA >  getdate() - 5  and
		F1.FLE_STATUS  = 'A'";

		$group = "P1.PSV_COD,
		F1.FLE_BIP,
		F1.FLE_PAC_REG,
		PAC.PAC_NOME,
		P1.PSV_NOME,
		F1.FLE_DTHR_CHEGADA,
		F1.FLE_DTHR_ATENDIMENTO,
		F1.FLE_STATUS
        HAVING (SELECT	1 FROM	RCL WHERE	RCL_PAC = f1.FLE_PAC_REG AND RCL_DTHR > MIN(F2.FLE_DTHR_CHEGADA) AND RCL_COD IN('CONSGINE') AND RCL_MED <> 101010 AND RCL_STAT <> 'C') IS NULL";

		$unionQuery = "select  DISTINCT
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
                P1.PSV_COD			IN ( '903342') AND
                f2.FLE_PSV_COD		= P2.PSV_COD AND
                F2.FLE_PAC_REG		= F1.FLE_PAC_REG AND
                F2.FLE_PSV_COD IN ( '903340' ) AND
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
		return (new Smart($tables))->unionAll($fields, $where, null, $group, null, $unionQuery)->fetchAll(\PDO::FETCH_ASSOC);
	}

	public static function getConsultorioPediatrico()
	{
		$fields = "DISTINCT
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
		$tables = "FLE f1, PSV P1, FLE f2, PSV P2, PAC";
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

		$unionQuery = "select  DISTINCT
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
		return (new Smart($tables))->unionAll($fields, $where, null, $group, null, $unionQuery)->fetchAll(\PDO::FETCH_ASSOC);
	}

	public static function getConsultorioMaterno()
	{
		$fields = "DISTINCT
		'CONS_GO' AS TIPO_FILA,
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
		$tables = "FLE f1, PSV P1, FLE f2, PSV P2, PAC";
		$where = "F1.FLE_DTHR_CHEGADA >  getdate() - 5  and
		f1.FLE_PSV_COD		= P1.PSV_COD AND
		F1.FLE_PAC_REG = PAC.PAC_REG AND 
		P1.PSV_COD			IN ( '903329') AND
		f2.FLE_PSV_COD		= P2.PSV_COD AND
		F2.FLE_PAC_REG		= F1.FLE_PAC_REG AND
		F2.FLE_PSV_COD IN ( '903340', '903342' ) AND
		F2.FLE_DTHR_CHEGADA >  getdate() - 5  and
		F1.FLE_STATUS  = 'A'";

		$group = "P1.PSV_COD,
		F1.FLE_BIP,
		F1.FLE_PAC_REG,
		PAC.PAC_NOME,
		P1.PSV_NOME,
		F1.FLE_DTHR_CHEGADA,
		F1.FLE_DTHR_ATENDIMENTO,
		F1.FLE_STATUS
		HAVING (SELECT	1 FROM	RCL WHERE	RCL_PAC = f1.FLE_PAC_REG AND RCL_DTHR > MIN(F2.FLE_DTHR_CHEGADA) AND RCL_COD IN('CONSGINE') AND RCL_MED <> 101010 AND RCL_STAT <> 'C') IS NULL";

		$unionQuery = "select  DISTINCT
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
		where	F1.FLE_DTHR_CHEGADA >  getdate() - 1  and
				f1.FLE_PSV_COD		= P1.PSV_COD AND
				F1.FLE_PAC_REG = PAC.PAC_REG AND 
				P1.PSV_COD			IN ( '903329') AND
				f2.FLE_PSV_COD		= P2.PSV_COD AND
				F2.FLE_PAC_REG		= F1.FLE_PAC_REG AND
				F2.FLE_PSV_COD IN ( '903340', '903342' ) AND
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
		HAVING (SELECT 1 FROM RCL WHERE	RCL_PAC = f1.FLE_PAC_REG AND RCL_DTHR > MIN(F2.FLE_DTHR_CHEGADA) AND RCL_COD IN('CONSGINE') AND RCL_MED <> 101010 AND RCL_STAT <> 'C') IS NOT NULL";
		return (new Smart($tables))->unionAll($fields, $where, null, $group, null, $unionQuery)->fetchAll(\PDO::FETCH_ASSOC);
	}
}
