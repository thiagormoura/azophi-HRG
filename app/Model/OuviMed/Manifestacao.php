<?php

namespace App\Model\Ouvimed;

use App\Db\Database;
use App\Db\Smart;

class Manifestacao
{

    public static function createManifestacao($dados){

        return (new Database('ouvimed', 'manifestacao'))->insert([
            "nome_autor" => $dados['nome_autor'],
            "nome_paciente" => $dados['nome_paciente'],
            "telefone" => $dados['telefone_paciente'],
            "rgo" => $dados['registro_paciente'],
            "data" => (new \DateTime($dados['data_hora_manifestacao'], new \DateTimeZone('America/Fortaleza')))->format('Y-m-d H:i:s'),
            "veiculo_manifestacao" => $dados['veiculo_manifestacao'],
            "veiculo_descricao" => $dados['veiculo_descricao'],
            "status" => "A",
            "created_at" => (new \DateTime('now', new \DateTimeZone('America/Fortaleza')))->format('Y-m-d H:i:s'),
        ]);

    }

    public static function setSetorEnvolvidoOnCreteManifestacao($id, $setor){
            
        $db = new Database('ouvimed', 'identificacao_manifestacao_setores');

        return $db->insert([
            "identificacao_manifestacao_id" => $id,
            "setor_id" => $setor
        ]);
    
    }

    

    public static function setIdentificacaoAndDescricaoOnCreateManifestacao($id, $identificacao_codigo, $descricao){
        
        // get id from identificacao table
        $identificacao_id = (new Database('ouvimed', 'identificacao'))->select('id', 'codigo = "'.$identificacao_codigo.'"')->fetchObject()->id;

        $db = new Database('ouvimed', 'identificacao_manifestacao');

        return $db->insert([
            "manifestacao_id" => $id,
            "identificacao_id" => $identificacao_id,
            "descricao" => $descricao
        ]);
    
    }

    public static function deleteSetoresEnvolvidos($id){
                
            $db = new Database('ouvimed', 'setores_envolvidos');
    
            return $db->delete('manifestacao_id = '.$id);
        
    }

    public static function deleteIdentificacoes($id){
                
        $db = new Database('ouvimed', 'identificacao_manifestacao');

        return $db->delete('manifestacao_id = '.$id);
    
    }

    public static function getCountOfAllManifestacoesEmAberto(){
            
        return (new Database('ouvimed', 'manifestacao'))->select('count(*) AS quantidade', 'status = "A"')->fetchAll(\PDO::FETCH_ASSOC);
    
    }

    public static function getCountOfAllManifestacoesEmAbertoByIdentificacao(){

        $tables = "manifestacao 
                left join identificacao_manifestacao on manifestacao.id = identificacao_manifestacao.manifestacao_id
                left join identificacao on identificacao.id = identificacao_manifestacao.identificacao_id";

        $fields = "identificacao.codigo as identificacao,
                identificacao.descricao,
                count(*) AS quantidade";

        $groupby = "identificacao_manifestacao.identificacao_id";

        return (new Database('ouvimed', $tables))->select($fields, 'status = "A"', null, $groupby, null)->fetchAll(\PDO::FETCH_ASSOC);

    }

    public static function getCountOfAllManifestacoesEmProcessamento(){
            
        return (new Database('ouvimed', 'manifestacao'))->select('count(*) AS quantidade', 'status = "EA"')->fetchAll(\PDO::FETCH_ASSOC);
    
    }

    public static function getCountOfAllManifestacoesEmProcessamentoByIdentificacao(){
            
        $tables = "manifestacao 
                left join identificacao_manifestacao on manifestacao.id = identificacao_manifestacao.manifestacao_id
                left join identificacao on identificacao.id = identificacao_manifestacao.identificacao_id";

        $fields = "identificacao.codigo as identificacao,
                identificacao.descricao,
                count(*) AS quantidade";

        $groupby = "identificacao_manifestacao.identificacao_id";

        return (new Database('ouvimed', $tables))->select($fields, 'status = "EA"', null, $groupby, null)->fetchAll(\PDO::FETCH_ASSOC);
    
    }

    public static function getCountOfAllManifestacoesFinalizadas(){   
        return (new Database('ouvimed', 'manifestacao'))->select('count(*) AS quantidade', 'status = "F"')->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getCountOfAllManifestacoesCanceladas(){
        return (new Database('ouvimed', 'manifestacao'))->select('count(*) AS quantidade', 'status = "C"')->fetchAll(\PDO::FETCH_ASSOC);
    }

    

    // Adquire dados das manifestações para montar a tabela na homepage
    public static function getManifestacoes($filter_values, $limit){

        // Formata horas para yyyy-mm-dd hh:mm:ss
        $ymdStartDate = explode(" ", $filter_values['startDate'])[0];
        $horaStartDate = explode(" ", $filter_values['startDate'])[1];
        $ymdFormatedStartDate = implode('-', array_reverse(explode('/', $ymdStartDate)));

        $ymdEndDate = explode(" ", $filter_values['endDate'])[0];
        $horaEndDate = explode(" ", $filter_values['endDate'])[1];
        $ymdFormatedEndDate = implode('-', array_reverse(explode('/', $ymdEndDate)));

        $filter_values['startDate'] = (new \DateTime($ymdFormatedStartDate.' '.$horaStartDate, new \DateTimeZone('America/Fortaleza')))->format('Y-m-d H:i:s');
        $filter_values['endDate'] = (new \DateTime($ymdFormatedEndDate.' '.$horaEndDate, new \DateTimeZone('America/Fortaleza')))->format('Y-m-d H:i:s');

        $tables = "manifestacao 
                left join identificacao_manifestacao on identificacao_manifestacao.manifestacao_id = manifestacao.id
                left join identificacao on identificacao.id = identificacao_manifestacao.identificacao_id
                left join identificacao_manifestacao_setores on identificacao_manifestacao_setores.identificacao_manifestacao_id = identificacao_manifestacao.id
                left join setores on setores.id = identificacao_manifestacao_setores.setor_id";

        $fields = "manifestacao.id as ID, 
                manifestacao.nome_autor as NOME_AUTOR,
                manifestacao.nome_paciente as NOME_PACIENTE,
                manifestacao.rgo as REGISTRO_PACIENTE,
                manifestacao.data as DTHR_MANIFESTACAO,
                manifestacao.veiculo_manifestacao as VEICULO,
                manifestacao.status as STATUS,
                group_concat(DISTINCT setores.nome) as SETORES_ENVOLVIDOS";

        $where = "(manifestacao.data between '".$filter_values['startDate']."' and '".$filter_values['endDate']."')";

        if($filter_values['setoresEnvolvidos'] != [])
            $where .= "and (setores.id in ('" . implode("','", $filter_values['setoresEnvolvidos']) . "'))";

        if($filter_values['identificacoes'] != [])
            $where .= "and (identificacao.codigo in ('" . implode("','", $filter_values['identificacoes']) . "'))";

        if($filter_values['veiculos'] != [])
            $where .= "and (manifestacao.veiculo_manifestacao in ('" . implode("','", $filter_values['veiculos']) . "'))";

        if($filter_values['status'] != [])
            $where .= "and (manifestacao.status in ('" . implode("','", $filter_values['status']) . "'))";

        if($filter_values['search']['value'] != '')
            $where .= "and (manifestacao.nome_autor like '%".$filter_values['search']['value']."%' 
                or manifestacao.nome_paciente like '%".$filter_values['search']['value']."%' 
                or manifestacao.rgo like '%".$filter_values['search']['value']."%')";

        $groupby = "manifestacao.id";
        $orderby = "manifestacao.data ASC";

        return (new Database('ouvimed', $tables))->select($fields, $where, $limit, $groupby, $orderby)->fetchAll(\PDO::FETCH_ASSOC);

    }

    // Adquire o protocolo de uma manifestação
    public static function getProtocolo($manifestacao){
        
        $ano = date('Y', strtotime($manifestacao['DTHR_MANIFESTACAO']));

        $where = "data between '".$ano."-01-01 00:00:00' and '".$ano."-12-31 23:59:59'";
        $manifestacoes_do_ano =  (new Database('ouvimed', 'manifestacao'))->select("id", $where, null, null, "manifestacao.data ASC")->fetchAll(\PDO::FETCH_ASSOC);

        $protocolo = 1;
        foreach($manifestacoes_do_ano as $manifestacao_atual){
            if($manifestacao['ID'] != $manifestacao_atual['id'])
                $protocolo++;
            else
                break;
        }

        return $protocolo.'/'.$ano;

    }

    // Adquire os dados de uma manifestação para montar a pagina de visualização
    public static function getManifestacao($id){
            
            $tables = "manifestacao 
            left join identificacao_manifestacao on identificacao_manifestacao.manifestacao_id = manifestacao.id
            left join identificacao on identificacao.id = identificacao_manifestacao.identificacao_id
            left join identificacao_manifestacao_setores on identificacao_manifestacao_setores.identificacao_manifestacao_id = identificacao_manifestacao.id
            left join setores on setores.id = identificacao_manifestacao_setores.setor_id";
            
            $fields = "manifestacao.id as ID, 
            manifestacao.nome_autor as NOME_AUTOR,
            manifestacao.nome_paciente as NOME_PACIENTE,
            manifestacao.telefone as TELEFONE,
            manifestacao.rgo as REGISTRO_PACIENTE,
            manifestacao.data as DTHR_MANIFESTACAO,
            manifestacao.veiculo_manifestacao as VEICULO,
            manifestacao.veiculo_descricao as VEICULO_DESCRICAO,
            group_concat(DISTINCT identificacao.codigo ORDER BY identificacao.id) as IDENTIFICACAO_CODIGO,
            group_concat(DISTINCT identificacao.descricao ORDER BY identificacao.id) as IDENTIFICACAO_NOME,
            group_concat(DISTINCT identificacao_manifestacao.descricao ORDER BY identificacao.id SEPARATOR '---') as IDENTIFICACAO_DESCRICAO,
            manifestacao.status as STATUS,
            group_concat(DISTINCT setores.nome) as SETORES_ENVOLVIDOS,
            group_concat(DISTINCT setores.id) as SETORES_ENVOLVIDOS_ID";
    
            $where = "manifestacao.id = ".$id;
            $groupby = "identificacao_manifestacao.id";
    
            return (new Database('ouvimed', $tables))->select($fields, $where, null, $groupby, null)->fetchAll(\PDO::FETCH_ASSOC);
    
    }

    public static function getAcoesTomadas($id){
        return (new Database('ouvimed', 'acoes_tomadas'))->select("*", "manifestacao_id = ".$id, null, null, "dthr_registro ASC")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getAllIdentificacoes(){
        return (new Database('ouvimed', 'identificacao'))->select("*", null, null, null, null)->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public static function cancelarManifestacao($id){
        return (new Database('ouvimed', 'manifestacao'))->update("id = '" . $id . "'", [
            "status" => 'C'
        ]);
    }

    public static function editarManifestacao($dados, $id){
    
        return  (new Database('ouvimed', 'manifestacao'))->update("id = '" . $id . "'", [
            "nome_autor" => $dados['nome_autor'],
            "nome_paciente" => $dados['nome_paciente'],
            "telefone" => $dados['telefone_paciente'],
            "rgo" => $dados['registro_paciente'],
            "data" => (new \DateTime($dados['data_hora_manifestacao'], new \DateTimeZone('America/Fortaleza')))->format('Y-m-d H:i:s'),
            "veiculo_manifestacao" => $dados['veiculo_manifestacao'],
            "veiculo_descricao" => $dados['veiculo_descricao'],
        ]);

    }

    public static function processarManifestacao($id){
        return (new Database('ouvimed', 'manifestacao'))->update("id = '" . $id . "'", [
            "status" => 'EA'
        ]);
    }

    public static function cancelarProcessamento($id){
        return (new Database('ouvimed', 'manifestacao'))->update("id = '" . $id . "'", [
            "status" => 'A'
        ]);
    }

    public static function atualizarAcao($id, $acoesTomadas, $dthr_acoes, $id_usuario){
        
        // delete
        (new Database('ouvimed', 'acoes_tomadas'))->delete("manifestacao_id = '" . $id . "' and usuario = '" . $id_usuario . "'");
        
        // insert
        foreach($acoesTomadas as $key => $acao){
            (new Database('ouvimed', 'acoes_tomadas'))->insert([
                "manifestacao_id" => $id,
                "acao" => $acao,
                "dthr_registro" => (new \DateTime($dthr_acoes[$key], new \DateTimeZone('America/Fortaleza')))->format('Y-m-d H:i:s'),
                "usuario" => $id_usuario
            ]);
        }

    }

    public static function finalizarProcessamento($id){
        return (new Database('ouvimed', 'manifestacao'))->update("id = '" . $id . "'", [
            "status" => 'F'
        ]);
    }
}
