<?php

namespace App\Model\Avasis;

use App\Db\Database;

date_default_timezone_set("America/Fortaleza");

class Questionario
{
    
    public static function getPerguntasToSelect()
    {
        return (new Database('avasis', 'perguntas p left join categorias c on p.id_categoria = c.id'))->select("p.id, concat(p.pergunta, ' (',c.nome, ')') as pergunta, p.status","p.status = 1", null, null, "p.pergunta")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function puxarCategorias($no_limite = null){
        if($no_limite != null) return (new Database('avasis', 'categorias'))->select("*","status = 1", null, "nome", "nome")->fetchAll(\PDO::FETCH_ASSOC);
        return (new Database('avasis', 'categorias c left join perguntas p on p.id_categoria = c.id'))->select("c.*","c.status = 1 and p.status = 1", 5, "c.nome")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function puxarPergunta($id){
        return (new Database('avasis', 'perguntas'))->select("*","id_categoria = ".$id." and status = 1", 1, null, "RAND()")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function enviarRelato($post){
        return (new Database('avasis', 'relatos'))->insert([
            "nome" => $post['nome'], 
            "contato1" => $post['cont_1'], 
            "contato2" => $post["cont_2"], 
            "observacao" => $post['obs']
        ]);
    }

    public static function enviarResp($rep, $id_relato, $id_questionario){
        return (new Database('avasis', 'respostas'))->insert([
            "id_questionario" => $id_questionario,
            "id_pergunta" => $rep['id_pergunta'], 
            "id_relato" => $id_relato, 
            "resposta" => $rep['resposta_valor'], 
            "dthr" => date('Y-m-d H:i:s')
        ]);
    }

    public static function puxarNPS(){
        return (new Database('avasis', 'questionario'))->select(
            "*",
            "tipo = 'N' and status = 1 and (Select count(*) from perguntas, perguntas_questionario where perguntas.id = id_pergunta and questionario.id = id_questionario) != 0"
        )->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function puxarPerguntasAllByQuestionario($id){

        return (new Database('avasis', 'perguntas p 
            left join perguntas_questionario pq on pq.id_pergunta = p.id 
            left join questionario q on q.id = pq.id_questionario
            left join categorias c on c.id = p.id_categoria'))->select(
                "p.*, c.nome as nome_categoria, q.nome as nome_questionario",
                "pq.id_questionario = ".$id,
                null, null, "c.nome, pergunta"
            )->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public static function puxarQuestionarioInfos($id)
    {
        return (new Database('avasis', 'questionario'))->select("nome","status = 1 and id = '$id'")->fetch(\PDO::FETCH_ASSOC);
    }

    public static function puxarUnidadesAllActive(){
        return (new Database('avasis', 'unidades'))->select("*",'status = 1')->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function puxarCategoriasAll(){
        return (new Database('avasis', 'categorias'))->select("*")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function puxarPerguntasAll(){
        return (new Database('avasis', 'perguntas'))->select("*")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function puxarPerguntasAllActive($perguntasSelected){

        $where = "perguntas.status = 1";
        if(!empty($perguntasSelected)){
            $where .= " AND perguntas.id not in (".implode(',', $perguntasSelected).")"; 
        }

        return (new Database('avasis', 'perguntas left join categorias on categorias.id = perguntas.id_categoria'))->select("perguntas.*, categorias.nome as categoria_nome", $where)->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function puxarQuestionariosAll()
    {
        return (new Database('avasis', 'questionario'))->select("*")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function puxarQuestionariosAllActive()
    {
        return (new Database('avasis', 'questionario, (SELECT p.*, c.nome, pq.id_questionario from perguntas_questionario pq right join perguntas p on pq.id_pergunta = p.id left join categorias c on c.id = p.id_categoria) perg'))->select("questionario.*", "questionario.`status` = 1 and questionario.id = perg.id_questionario", null, "id")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function puxarCategoriasAllActive()
    {
        return (new Database('avasis', 'categorias'))->select("*", "status = 1", null, null, 'nome')->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public static function puxarUnidadesAll()
    {
        return (new Database('avasis', 'unidades'))->select("*")->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public static function changeStatus($tabela, $id, $status){
        return (new Database('avasis', $tabela))->update('id = '.$id, [
            'status' => $status
        ]);
    }
    
    public static function puxarDateMaxAndMinRespostas()
    {
        return (new Database('avasis', 'respostas'))->select("Date(min(dthr)) as min, Date(max(dthr)) as max")->fetchAll(\PDO::FETCH_ASSOC)[0];
    }
    
    public static function puxarTiposRelatorios()
    {
        return (new Database('avasis', 'tipos_relatorios'))->select("*", null, null, null, "nome asc")->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    //MEXENDO AQUI PELO AMOR DE DEUS ALGUÉM ME AJUDA 

    public static function puxarTopTen(){
        return (new Database('avasis', 'respostas r
        inner join unidades u
        on r.id_unidade = u.id'))->select("u.nome as label, avg(r.resposta) as y", null, 10, 'u.nome', "y desc")->fetchAll(\PDO::FETCH_ASSOC);
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public static function addCategoria($nome_categoria)
    {
        return (new Database('avasis', 'categorias'))->insert([
            "nome" => $nome_categoria, 
            "status" => 1
        ]);
    }

    public static function addPergunta($nome_pergunta, $id_categoria)
    {
        return (new Database('avasis', 'perguntas'))->insert([
            "pergunta" => $nome_pergunta,
            "id_categoria" => $id_categoria,
            "status" => 1
        ]);
    }

    public static function addUnidade($nome_unidade)
    {
        return (new Database('avasis', 'unidades'))->insert([
            "nome" => $nome_unidade,
            "status" => 1
        ]);
    }

    public static function editCategoriaOrUnidade($tabela, $nome, $id)
    {
        return (new Database('avasis', $tabela))->update('id = '.$id, [
            'nome' => $nome,
        ]);
    }

    public static function editPergunta($nome, $id_categoria, $id)
    {
        return (new Database('avasis', 'perguntas'))->update('id = '.$id, [
            'pergunta' => $nome,
            'id_categoria' => $id_categoria
        ]);
    }

    public static function editQuestionario($nome, $tipo, $id)
    {
        return (new Database('avasis', 'questionario'))->update('id = '.$id, [
            'nome' => $nome,
            'tipo' => $tipo
        ]);
    }

    public static function addQuestionario($nome, $tipo)
    {
        return (new Database('avasis', 'questionario'))->insert([
            "nome" => $nome,
            "tipo" => $tipo,
            "data_inicio" => date("Y-m-d H:i:s"),
            "status" => 1
        ]);
    }

    public static function puxarRespostasByPergunta($pergunta_id)
    {
        return (new Database('avasis', 'respostas r 
        inner join perguntas p
        on p.id = r.id_pergunta'))->select("r.resposta as label, count(*) as y", "p.id = ".$pergunta_id, null, 'r.resposta')->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function puxarRespostaBySetor($setor_id)
    {
        return (new Database('avasis', 'unidades u
        inner join respostas r 
        on u.id = r.id_unidade
        inner join perguntas p
        on p.id = r.id_pergunta
        inner join categorias c
        on c.id = p.id_categoria'))->select("p.id as id, concat(p.pergunta, ' - ',c.nome) as perguntaFixed", "u.id = ".$setor_id, null, "perguntaFixed")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function puxaRespostasBySetorAndPergunta($setor_id, $pergunta_id)
    {
        return (new Database('avasis', 'unidades u
        inner join respostas r 
        on u.id = r.id_unidade
        inner join perguntas p
        on p.id = r.id_pergunta
        inner join categorias c
        on c.id = p.id_categoria'))->select("r.resposta, count(*) as quantidade", "u.id = ".$setor_id." and p.id = ".$pergunta_id, null, "r.resposta")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function addPerguntasToQuestionario($id_questionario, $id_pergunta)
    {
        return (new Database('avasis', 'perguntas_questionario'))->insert([
            "id_questionario" => $id_questionario,
            'id_pergunta' => $id_pergunta
        ]);
    }

    public static function removePerguntasFromQuestionario($id_questionario, $id_pergunta)
    {
       return (new Database('avasis', 'perguntas_questionario'))->delete('id_questionario = '.$id_questionario." AND id_pergunta = ".$id_pergunta);
    }
}