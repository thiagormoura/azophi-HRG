<?php

namespace App\Model\Ouvimed;

use App\Db\Database;
use App\Db\Smart;

class Dashboard
{

    public static function getChart1Data($filtros){

        $fields = "manifestacao.status,
                    count(manifestacao.status) AS quantidade";
    
        $tables = "manifestacao";

        $where = "manifestacao.data BETWEEN '" . $filtros['start_date'] . "' AND '" . $filtros['end_date'] . "'";
        
        $groupby = "manifestacao.status";
  
        return (new Database('ouvimed', $tables))->select($fields, $where, null, $groupby, null)->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Dados para o grafico de manifestações por setor e por status
    public static function getChart2Data($filtros){

        $fields = "setores.id,
                setores.nome,
                manifestacao.status,
                count(manifestacao.status) AS quantidade";
    
        $tables = "manifestacao
                left join identificacao_manifestacao on identificacao_manifestacao.manifestacao_id = manifestacao.id
                left join identificacao_manifestacao_setores on identificacao_manifestacao_setores.identificacao_manifestacao_id = identificacao_manifestacao.id
                left join setores on setores.id = identificacao_manifestacao_setores.setor_id";

        $where = "manifestacao.data BETWEEN '" . $filtros['start_date'] . "' AND '" . $filtros['end_date'] . "'";
        
        if( (isset($filtros['setores_envolvidos'])) && (count($filtros['setores_envolvidos']) > 0) ) 
            $where .= "and (setores.id in ('" . implode("','", $filtros['setores_envolvidos']) . "'))";

        $groupby = "setores.nome, manifestacao.status";
        $orderby = "setores.nome ASC";
    
        return (new Database('ouvimed', $tables))->select($fields, $where, null, $groupby, $orderby)->fetchAll(\PDO::FETCH_ASSOC);

    }

    public static function getChart3Data($filtros){

        $fields = "manifestacao.veiculo_manifestacao,
                 count(manifestacao.veiculo_manifestacao) AS quantidade";
    
        $tables = "manifestacao";

        $where = "manifestacao.data BETWEEN '" . $filtros['start_date'] . "' AND '" . $filtros['end_date'] . "'";
        
        $groupby = "manifestacao.veiculo_manifestacao";
  
        return (new Database('ouvimed', $tables))->select($fields, $where, null, $groupby, null)->fetchAll(\PDO::FETCH_ASSOC);

    }

    public static function getChart4Data($filtros){

        $fields = "setores.id,
                setores.nome,
                 manifestacao.veiculo_manifestacao,
                count(manifestacao.status) AS quantidade";
    
        $tables = "manifestacao
                left join identificacao_manifestacao on identificacao_manifestacao.manifestacao_id = manifestacao.id
                left join identificacao_manifestacao_setores on identificacao_manifestacao_setores.identificacao_manifestacao_id = identificacao_manifestacao.id
                left join setores on setores.id = identificacao_manifestacao_setores.setor_id";

        $where = "manifestacao.data BETWEEN '" . $filtros['start_date'] . "' AND '" . $filtros['end_date'] . "'";
        
        if( (isset($filtros['setores_envolvidos'])) && (count($filtros['setores_envolvidos']) > 0) ) 
            $where .= "and (setores.id in ('" . implode("','", $filtros['setores_envolvidos']) . "'))";

        $groupby = "setores.nome, manifestacao.veiculo_manifestacao";
        $orderby = "setores.nome ASC";
    
        return (new Database('ouvimed', $tables))->select($fields, $where, null, $groupby, $orderby)->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getChart5Data($filtros){
        
        $fields = "identificacao.codigo,
                count(identificacao.codigo) as quantidade,
                identificacao.descricao";
    
        $tables = "manifestacao
                left join identificacao_manifestacao on identificacao_manifestacao.manifestacao_id = manifestacao.id
                left join identificacao on identificacao.id = identificacao_manifestacao.identificacao_id";

        $where = "manifestacao.data BETWEEN '" . $filtros['start_date'] . "' AND '" . $filtros['end_date'] . "'";
        
        $groupby = "identificacao.codigo";
  
        return (new Database('ouvimed', $tables))->select($fields, $where, null, $groupby, null)->fetchAll(\PDO::FETCH_ASSOC);

    }

    public static function getChart6Data($filtros){
            
            $fields = "setores.id,
                    setores.nome,
                    identificacao.codigo,
                    count(identificacao.codigo) AS quantidade";
        
            $tables = "manifestacao
                    left join identificacao_manifestacao on identificacao_manifestacao.manifestacao_id = manifestacao.id
                    left join identificacao_manifestacao_setores on identificacao_manifestacao_setores.identificacao_manifestacao_id = identificacao_manifestacao.id
                    left join setores on setores.id = identificacao_manifestacao_setores.setor_id
                    left join identificacao on identificacao.id = identificacao_manifestacao.identificacao_id";
    
            $where = "manifestacao.data BETWEEN '" . $filtros['start_date'] . "' AND '" . $filtros['end_date'] . "'";
            
            if( (isset($filtros['setores_envolvidos'])) && (count($filtros['setores_envolvidos']) > 0) ) 
                $where .= "and (setores.id in ('" . implode("','", $filtros['setores_envolvidos']) . "'))";
    
            $groupby = "setores.nome, identificacao.codigo";
            $orderby = "setores.nome ASC";
        
            return (new Database('ouvimed', $tables))->select($fields, $where, null, $groupby, $orderby)->fetchAll(\PDO::FETCH_ASSOC);
    }

}
