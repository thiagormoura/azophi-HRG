<?php

namespace App\Controller\Avasis;

use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;
use App\Model\Avasis\Questionario;
use Error;

class GraphicsController extends LayoutPage
{

    public static function gerenciarRelatorios($request)
    {
        $MaxAndMin = Questionario::puxarDateMaxAndMinRespostas();
        $tipos = Questionario::puxarTiposRelatorios();
        $resultado = null;
        $sample = "<option value='{{id}}' data-type='{{tipo_graph}}'>{{nome}}</option>";

        foreach ($tipos as $tipo) {
            $resultado .= str_replace("{{tipo_graph}}", $tipo['tipo_graph'], str_replace("{{nome}}", $tipo['nome'], str_replace("{{id}}", $tipo['id'], $sample)));
        }

        $content = View::render('avasis/respostas', [
            "min" => $MaxAndMin['min'],
            "max" => $MaxAndMin['max'],
            "tipos" => $resultado
        ]);

        return parent::getPage('Avasis', 'avasis', $content, $request);
    }

    public static function getGraphicsData($request)
    {
        $data = $request->getPostVars();
        // var_dump($data);

        switch ($data['tipo']) {
            case "1":
                $active_perguntas = Questionario::getPerguntasToSelect();
                $options = "";
                foreach ($active_perguntas as $index => $pergunta) {
                    $content = '';
                    $nome = $pergunta['pergunta'];
                    $id = $pergunta['id'];
                    $options .= View::render('utils/option', [
                        "nome" => $nome,
                        "id" => $id,
                        "disabled" => null,
                        "selected" => null
                    ]);
                }
                $content = View::render('utils/select', [
                    "options" => $options,
                    "class" => "custom-select form-control select-pergunta",
                    "id" => "select-pergunta",
                    "name" => "perguntas",
                    "disabled" => null
                ]);
                return $content;
                break;
        }
    }

    public static function getPerguntas($request)
    {
        $data = $request->getPostVars();

        $respostas_by_pergunta = Questionario::puxarRespostasByPergunta($data['pergunta_id']);
        return $respostas_by_pergunta;
    }

    public static function topTen($request)
    {
        $res = Questionario::puxarTopTen();
        return json_encode($res);
    }

    public static function respBySetor($request)
    {
        $unidades = Questionario::puxarUnidadesAllActive();
        $options = "";
        $content = "";
        foreach ($unidades as $index => $unidade) {
            $nome = $unidade['nome'];
            $id = $unidade['id'];
            $options .= View::render('utils/option', [
                "nome" => $nome,
                "id" => $id,
                "disabled" => null,
                "selected" => null
            ]);
        }
        $content = View::render('utils/select', [
            "options" => $options,
            "class" => "custom-select form-control select-setor",
            "id" => "select-setor",
            "name" => "setores",
            "disabled" => null
        ]);
        return $content;
    }

    public static function getPerguntasBySetor($request)
    {
        $postVars = $request->getPostVars();
        $id_setor = $postVars['id_setor'];
        $respostas = Questionario::puxarRespostaBySetor($id_setor);
        $options = "";
        $content = "";
        foreach ($respostas as $index => $resposta) {
            $nome = $resposta['perguntaFixed'];
            $id = $resposta['id'];
            $options .= View::render('utils/option', [
                "nome" => $nome,
                "id" => $id,
                "disabled" => null,
                "selected" => null
            ]);
        }
        $content = View::render('utils/select', [
            "options" => $options,
            "class" => "custom-select form-control select-pergunta-setor",
            "id" => "select-pergunta-setor",
            "name" => "perguntas-setor",
            "disabled" => null
        ]);
        return $content;
    }

    public static function getQuantidadeRespostasBySetor($request)
    {
        $postVars = $request->getPostVars();
        $pergunta_id = $postVars['pergunta_id'];
        $setor_id = $postVars['setor_id'];
        $respostas_setor = Questionario::puxaRespostasBySetorAndPergunta($setor_id, $pergunta_id);
        $res = [
            ["label" => "1", "y" => 0],
            ["label" => "2", "y" => 0],
            ["label" => "3", "y" => 0],
            ["label" => "4", "y" => 0],
            ["label" => "5", "y" => 0]
        ];

        // return $respostas_setor;
        foreach ($res as $index => $resposta) {
            $key = array_search($resposta['label'], array_column($respostas_setor, 'resposta'));
            if ($key !== false) {
                $res[$index]["y"] = $respostas_setor[$key]["quantidade"];
            }
        }
        return $res;
    }
}
