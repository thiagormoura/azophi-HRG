<?php

namespace App\Model\GestaoLeitos;

use App\Db\Database;
use App\Db\Smart;

class Setor
{
    public static function getUnitByCode($code)
    {
        return (new Database('GestaoLeitos', 'unidades'))->select('codigo, nome', "codigo = '$code'")->fetch(\PDO::FETCH_ASSOC);
    }

    public static function getHospitalizationUnits()
    {
        return (new Database('GestaoLeitos', 'unidades'))->select('codigo, nome', 'internacao is true', null, null, 'nome asc')->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getSolicitationUnits()
    {
        return (new Database('GestaoLeitos', 'unidades'))->select('codigo, nome', 'solicitante is true', null, null, 'nome asc')->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getUnits()
    {
        return (new Database('GestaoLeitos', 'unidades'))->select('codigo, nome', null, null, null, 'nome asc')->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getLeitosWithoutDifference($andar)
    {
        return (new Smart('LOC'))->select("LOC_COD, LOC_NOME, LOC_STATUS", "LOC_STR = '$andar' AND LOC_DEL_LOGICA = 'N' AND LOC_STATUS = 'L' AND LOC_LEITO_ID is not null", null, null, "LOC_NOME")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getSectorsCovid($accommodation)
    {
        return (new Smart('LOC, CLE, STR'))->select("STR_COD as setor_codigo, STR_NOME as setor_nome, LOC_COD as leito_codigo, LOC_NOME as leito_nome, LOC_STATUS", "LOC_DEL_LOGICA = 'N' AND LOC_RAMAL2 LIKE '%CVD%' AND LOC_RAMAL2 IS NOT NULL AND LOC_CLE_COD = '$accommodation' AND LOC_STATUS = 'L' AND CLE_COD = LOC_CLE_COD AND STR_COD = LOC_STR", null, null, "STR_NOME asc")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getSectorsPediatricos($accommodation)
    {
        return (new Smart('LOC, CLE, STR'))->select("STR_COD as setor_codigo, STR_NOME as setor_nome, LOC_COD as leito_codigo, LOC_NOME as leito_nome, LOC_STATUS", "LOC_DEL_LOGICA = 'N' AND ( LOC_RAMAL2 not like '%CVD%' OR LOC_RAMAL2 IS NULL ) AND LOC_CLE_COD = '$accommodation' AND CLE_COD = LOC_CLE_COD AND STR_COD IN ('UIT','UI4','UI5','UI7', 'UT1', 'UT2', 'UT5', 'UT8') AND STR_COD = LOC_STR AND LOC_STATUS = 'L' AND (LOC_NOME LIKE '%PED%' OR LOC_NOME LIKE '%NEO%')", null, null, "STR_NOME asc")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getSectorsEnfermaria($accommodation, $sexo)
    {
        $tables = "LOC 
        LEFT JOIN PAC	ON (LOC.LOC_PAC = PAC.PAC_REG)
        LEFT JOIN CLE	ON (CLE.CLE_COD = LOC.LOC_CLE_COD )
        INNER JOIN STR	ON (STR.STR_COD  = LOC.LOC_STR),
        LOC as L2";

        $where = "LOC.LOC_DEL_LOGICA = 'N'
        AND		LOC.LOC_LEITO_ID IS NOT NULL
        AND		(((LOC.LOC_STATUS IN ('B') AND LOC.LOC_OBS LIKE '%(S:$sexo)%')) OR ((LOC.LOC_STATUS IN ('R') AND PAC_SEXO LIKE '%$sexo%')))
        AND		CLE_ACOMODACAO = '$accommodation'
        AND		STR_COD IN ('UIT','UI4','UI5','UI7')
        AND		( LOC.LOC_RAMAL2 not like '%CVD%' OR LOC.LOC_RAMAL2 IS NULL )
        AND 	(LOC.LOC_NOME NOT LIKE '%PED%')	
        AND    (L2.loc_nome LIKE ''+ Substring( loc.loc_nome, 1, Charindex(' ',loc.loc_nome, 0) - 1) + '%' and l2.LOC_STATUS = 'L' AND L2.loc_del_logica = 'N')";

        $groupBy = "STR.STR_COD,
        STR.STR_NOME,
        L2.LOC_COD,
        L2.LOC_NOME,
        L2.LOC_STATUS,
        L2.LOC_RAMAL2,
        L2.LOC_CLE_COD,
        L2.LOC_STR,
        PAC_SEXO";

        $unionQuery = "SELECT		
            STR.STR_COD,
            STR.STR_NOME,
            LOC_COD,
            LOC.LOC_NOME,
            LOC.LOC_STATUS
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
            AND		STR_COD IN ('UIT','UI4','UI5','UI7')
            AND		( LOC_RAMAL2 not like '%CVD%' OR LOC_RAMAL2 IS NULL )
            AND 	(LOC_NOME NOT LIKE '%PED%')		
        GROUP BY
            STR.STR_COD,
            STR.STR_NOME,
            LOC_COD,
            LOC.LOC_NOME,
            LOC.LOC_STATUS,
            LOC.LOC_RAMAL2,
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
            AND		L.LOC_STATUS = 'O' AND	P.PAC_SEXO = '$sexo'
            AND 	(L.LOC_NOME NOT LIKE '%PED%')	
            AND		L.LOC_NOME LIKE '' + SUBSTRING( LOC.LOC_NOME, 1, CHARINDEX(' ', LOC.LOC_NOME, 0 ) - 1) + '%') ) > 0 
            
            OR 
            
            (SELECT COUNT(*) 
            FROM	LOC L 
            WHERE	L.LOC_STR = LOC.LOC_STR 
            AND		L.LOC_CLE_COD = LOC.LOC_CLE_COD 
            AND		L.LOC_DEL_LOGICA = 'N'
            AND		L.LOC_NOME LIKE '' + SUBSTRING( LOC.LOC_NOME, 1, CHARINDEX(' ', LOC.LOC_NOME, 0 ) - 1) + '%') = (SELECT COUNT(*) 
            FROM	LOC L 
            WHERE	L.LOC_STR = LOC.LOC_STR 
            AND		L.LOC_CLE_COD = LOC.LOC_CLE_COD 
            AND		L.LOC_DEL_LOGICA = 'N' 
            AND		L.LOC_STATUS = 'L'
            AND		L.LOC_NOME LIKE '' + SUBSTRING( LOC.LOC_NOME, 1, CHARINDEX(' ', LOC.LOC_NOME, 0 ) - 1) + '%')
                    
            ORDER BY 
                    STR.STR_NOME";



        return (new Smart($tables))->unionAll("STR.STR_COD as setor_codigo, STR.STR_NOME as setor_nome, L2.LOC_COD as leito_codigo, L2.LOC_NOME as leito_nome, L2.LOC_STATUS", $where, null, $groupBy, null, $unionQuery)->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getSectorsNormals($type)
    {
        return (new Smart('LOC, CLE, STR'))->select("STR.STR_COD as setor_codigo, STR_NOME as setor_nome, LOC_COD as leito_codigo, LOC_NOME as leito_nome, LOC_STATUS", "LOC_DEL_LOGICA = 'N'  AND ( LOC_RAMAL2 not like '%CVD%' OR LOC_RAMAL2 IS NULL ) AND LOC_CLE_COD = '$type' AND CLE_COD = LOC_CLE_COD AND STR_COD IN ('UIT','UI4','UI5','UI7', 'UT1', 'UT2', 'UT5', 'UT8') AND STR_COD = LOC_STR AND LOC_STATUS = 'L'", null, null, "STR_NOME asc")->fetchAll(\PDO::FETCH_ASSOC);
    }

    // diferencaAllUnidades
    public static function getAllSectorsWithoutDifference()
    {
        return (new Smart('STR'))->select("STR_COD, STR_NOME", "STR_STATUS = 'A' AND STR_CATEG = 'I' AND exists(SELECT LOC_NOME FROM LOC WHERE LOC_STR = STR_COD AND LOC_STATUS = 'L' AND LOC_LEITO_ID is not null)", null, null, "STR_NOME")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getAllBedsWithoutDifference($sector)
    {
        return (new Smart('LOC'))->select("LOC_COD, LOC_NOME, LOC_STATUS", "LOC_STR = '$sector' AND LOC_DEL_LOGICA = 'N' AND LOC_STATUS = 'L' AND LOC_LEITO_ID is not null", null, null, "LOC_NOME")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getSetores()
    {
        return (new Smart('CLE, loc, str'))->select("str.str_nome, str.str_cod", "( str.str_cod = loc.loc_str ) and ( CLE.CLE_cod = loc.loc_CLE_cod ) and ( ( CLE.CLE_tipo = 'L' ) and ( str.str_status <> 'I' ) and  ( loc.loc_del_logica = 'N' or  ( loc.loc_del_logica is null )  ) and  ( str.str_tipo_atende is null or ( str.str_tipo_atende in ( 'I', 'T', 'H', 'R' ) ) ) ) and (loc.LOC_LEITO_ID is not null)", null, "str.str_nome, str.str_cod , str.str_str_cod , str.str_categ", "str.str_nome ASC")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getTotalSectorsNotVirtual($setores_sql_string)
    {
        return (new Smart('LOC, CLE'))->select("COUNT(LOC_COD) AS QUANT_LEITOS_TOTAL", "LOC_DEL_LOGICA='N' AND (LOC_STR IN ($setores_sql_string)) AND LOC_CLE_COD=CLE_COD AND LOC_LEITO_ID is not null")->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Método responsável por retornar o 
     * total de leitos (virtuais e não virtuais), porcentagem de ocupação, quantidade de ocupados, reservados, bloqueados, 
     * vagos (total, uti, apt e enf) de um determinado setor
     * @param string $unit
     * @return array|false
     */
    public static function getInfoUnit(string $unit)
    {
        $fields = "LOC_STR as setor_codigo, 
                   COUNT(LOC_STR) AS total, 
                   ROUND(100*(CAST(COUNT(case LOC_STATUS when 'O' then 1 else null end) AS FLOAT)/CAST(COUNT(LOC_STR) AS FLOAT)), 1) AS ocupacao, 
                   COUNT(case LOC_STATUS when 'O' then 1 else null end) AS leitos_ocupados, 
                   COUNT(case LOC_STATUS when 'R' then 1 else null end) AS leitos_reservados, 
                   COUNT(case LOC_STATUS when 'B' then 1 else null end) AS leitos_bloqueados, 
                   COUNT(case LOC_STATUS when 'L' then 1 else null end) AS leitos_vagos,
                   COUNT(case when LOC_STATUS = 'L' and CLE_COD = 'UTI' then 1 else null end) AS leitos_vagos_uti,
                   COUNT(case when LOC_STATUS = 'L' and CLE_COD = 'APT' then 1 else null end) AS leitos_vagos_apt,
                   COUNT(case when LOC_STATUS = 'L' and CLE_COD = 'ENF' then 1 else null end) AS leitos_vagos_enf";
        $tables = "LOC, CLE";
        $where = "LOC_DEL_LOGICA='N' AND LOC_CLE_COD=CLE_COD AND LOC_STR = '$unit' AND LOC_NOME NOT LIKE '%berço%'";
        $group = "LOC_STR";
        return (new Smart($tables))->select($fields, $where, null, $group)->fetch(\PDO::FETCH_ASSOC);
    }

    public static function getHSPFromLeito($registro)
    {
        return (new Smart('HSP'))->select("TOP 1 HSP_NUM", "HSP_PAC = '$registro' AND HSP_TRAT_INT = 'I'", null, null, "HSP_DTHRE DESC")->fetchColumn();
    }

    public static function getInfosFromOcuppiedLeito($codigo_leito, $hsp_num, $registro)
    {
        return (new Smart('LOC, PAC, LTO, STR'))->select("LOC_STATUS, LOC_PAC AS Registro, PAC_SEXO AS Sexo, (CONVERT(int,CONVERT(char(8),GETDATE(),112))-CONVERT(char(8),PAC_NASC,112))/10000 AS Idade, DATEDIFF(day, LTO_DTHR_INI, GETDATE()) AS TempoOcupacaoDias, LOC_NOME AS NomeLeito, STR_NOME AS SetorLeito", "LOC_COD = '$codigo_leito' AND LOC_PAC = '$registro' AND LTO_PAC_REG = PAC_REG AND LTO_LOC_COD = LOC_COD AND LTO_DTHR_FIM=LTO_DTHR_INI AND LTO_HSP_NUM = '$hsp_num' AND STR_COD = LOC_STR")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getInfosFromReservedLeito($codigo_leito, $registro)
    {
        return (new Smart('LOC, PAC, RLT, STR'))->select("LOC_STATUS, LOC_PAC AS Registro, PAC_SEXO AS Sexo, (CONVERT(int,CONVERT(char(8),GETDATE(),112))-CONVERT(char(8),PAC_NASC,112))/10000 AS Idade, DATEDIFF(hour, RLT_DTHR, GETDATE()) AS TempoReservaHoras, LOC_NOME AS NomeLeito, STR_NOME AS SetorLeito", "LOC_COD = '$codigo_leito' AND LOC_PAC = '$registro' AND RLT_PAC_REG = PAC_REG AND RLT_LOC_COD = LOC_COD AND RLT_STATUS='R' AND STR_COD = LOC_STR")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getInfosFromBlockedLeito($codigo_leito)
    {
        return (new Smart('LOC LEFT JOIN BLC ON LOC_COD = BLC_LOC_COD, STR'))->select("LOC_STATUS, DATEDIFF(hour, BLC_DTHR_INI, GETDATE()) AS TempoBloqueioHoras, LOC_NOME AS NomeLeito, STR_NOME AS SetorLeito, BLC_OBS AS MotivoBloqueio", "LOC_COD = '$codigo_leito' AND BLC_DTHR_FIM IS NULL AND LOC_STATUS = 'B' AND STR_COD = LOC_STR")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getDaysOffLeito($codigo_leito)
    {
        return (new Smart('BLC, LTO'))->select("TOP 1 CASE WHEN ISNULL(BLC_DTHR_FIM, BLC_DTHR_INI) > LTO_DTHR_FIM THEN DATEDIFF(day, ISNULL(BLC_DTHR_FIM, BLC_DTHR_INI), GETDATE()) ELSE DATEDIFF(day, LTO_DTHR_FIM, GETDATE()) END AS DiasLivres", "BLC_LOC_COD = '$codigo_leito'", null, null, "ISNULL(BLC_DTHR_FIM, BLC_DTHR_INI) DESC")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getInfosFromFreeLeito($codigo_leito)
    {
        return (new Smart('LOC, STR'))->select("LOC_STATUS, LOC_NOME AS NomeLeito, STR_NOME AS SetorLeito", "LOC_COD = '$codigo_leito' AND LOC_STATUS = 'L' AND STR_COD = LOC_STR")->fetchAll(\PDO::FETCH_ASSOC);
    }
}
