<?php

namespace App\Controller\Avasis;

use App\Controller\Error\ErrorController;
use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;
use App\Model\Avasis\Questionario;
use Error;

class QuestionarioController extends LayoutPage
{
    //Método responsável por retornar a página do questionário
    public static function getQuestionarioPage($request, $id)
    {
        $perguntas = Questionario::puxarPerguntasAllByQuestionario($id);

		$perguntasByCategoria = [];
		foreach ($perguntas as $key => $pergunta) {
			$perguntasByCategoria[$pergunta['nome_categoria']][] = View::render('/paciente/avasis/cardsPerguntaTemplate', [
				'id_pergunta' => $pergunta['id'],
				'quest-number' => "Pergunta " . ($key + 1),
				'pergunta' => $pergunta['pergunta']
			]);
		}

		$perguntasString = "";
		$countCategoria = 1;
		foreach ($perguntasByCategoria as $categoria => $perguntasTemplate) {

			$perguntasCardsUnion = implode("",$perguntasTemplate);
			$perguntasString .= View::render('/paciente/avasis/perguntaTemplate', [
				'group-cards' => $perguntasCardsUnion,
				'nome-categoria' => $categoria,
				'status-collpase-button' => 'collapsed',
				'status-collapse' => 'collapse',
				'data-target' => "collapse" . ($countCategoria++)
			]);
		}

        $content = View::render('avasis/index_second', [
            'quest-cards' => $perguntasString,
            'quest-name-number' => $perguntas[0]['nome_questionario'],
            'modal' => View::render('avasis/modalObs')
        ]);

        return parent::getPage('Questionário '.$perguntas[0]['nome_questionario'], 'avasis', $content, $request);
        // return var_dump($questionario);
    }

    //Método responsável por enviar a avaliação ao banco
    public static function enviarQuestionario($request)
    {
        $postVars = $request->getPostVars()['array_respostas'];
        $id_relato = '';
        // var_dump($postVars);

        if (end($postVars)["obs"]) {
            $data = [];
            $data['nome'] = end($postVars)['name'];
            $data['cont_1'] = end($postVars)['contato1'];
            $data['cont_2'] = end($postVars)['contato2'];
            $data['obs'] = end($postVars)['obs'];
            $id_relato = Questionario::enviarRelato($data);
        }

        foreach ($postVars as $answer) {
            if ($answer['question_id']) {
                $data = [];
                $data['id_pergunta'] = $answer['question_id'];
                $data['resposta_valor'] = $answer['answer'];
                Questionario::enviarResp($data, $id_relato == '' ? null : $id_relato, $answer['id']);
            }
        }
    }

    public static function getPerguntasToAddInQuestionario($request)
    {
        $perguntasAlreadySelected = $request->getPostVars()['perguntasSelected'];

        if(!empty($perguntasAlreadySelected)){
            $perguntasAlreadySelected = array_map(function($element){
                return "'".$element."'";
            }, $perguntasAlreadySelected);
        }

        $perguntas = Questionario::puxarPerguntasAllActive($perguntasAlreadySelected);

        $tableRows = "";
        foreach ($perguntas as $pergunta) {
            $tableRows .= View::render('/avasis/addPerguntaToQuestionarioRow', [
                'id' =>  $pergunta['id'],
                'pergunta' => $pergunta['pergunta'],
                'categoria' => $pergunta['categoria_nome']
            ]);
        }

        $table = View::render('/avasis/addPerguntaToQuestionario', [
            'perguntas' => $tableRows
        ]);

        return $table;
    }

    public static function putPerguntasToAddInQuestionario($request)
    {
        $post = $request->getPostVars();
        foreach ($post['perguntas'] as $pergunta) {
            Questionario::addPerguntasToQuestionario($post['id_questionario'], $pergunta);
        }

        return [true];
    }

    public static function getPerguntasFromQuestionario($request, $id_questionario)
    {
        
        $perguntasFromQuestionario = Questionario::puxarPerguntasAllByQuestionario($id_questionario);

        $tableRows = "";
        foreach ($perguntasFromQuestionario as $pergunta) {
            $tableRows .= View::render('/avasis/addPerguntaToQuestionarioRow', [
                'id' =>  $pergunta['id'],
                'pergunta' => $pergunta['pergunta'],
                'categoria' => $pergunta['nome_categoria']
            ]);
        }

        $table = View::render('/avasis/addPerguntaToQuestionario', [
            'perguntas' => $tableRows
        ]);

        return $table;
    }

    public static function removePerguntasFromQuestionario($request)
    {
        $post = $request->getPostVars();

        foreach ($post['perguntas'] as $perguntaId) {
            Questionario::removePerguntasFromQuestionario($post['id_questionario'], $perguntaId);
        }

        return [true];
    }
}
