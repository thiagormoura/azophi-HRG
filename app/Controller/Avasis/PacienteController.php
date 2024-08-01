<?php

namespace App\Controller\Avasis;

use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;
use App\Model\Avasis\Questionario;
use Error;

class PacienteController extends LayoutPage
{
	// Método responsável por retornar a página inicial do Avasis
	public static function getHome($request)
	{
		$questions = Questionario::puxarNPS();

		$sample = "<div class='box-questionario mr-1' data-id='{{id}}'><h3>{{nome}}</h3></div>";
		$resultado = "";

		foreach ($questions as $question) {
			$resultado .= str_replace("{{nome}}", $question['nome'], str_replace("{{id}}", $question['id'], $sample));
		}

		$content = View::render('paciente/avasis/index', [
			'url-back' => URL . '/home/paciente',
			'NPS' => $resultado
		]);

		return parent::getPage('Avasis', 'avasis', $content, $request, true);
	}

	public static function getQuestionario($request, $id)
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

		return ['perguntas' => $perguntasString, 'titulo' => $perguntas[0]['nome_questionario']];
	}

	//Método responsável por enviar a avaliação ao banco
	public static function enviarQuestionario($request)
	{
		$postVars = $request->getPostVars();
		$id_relato = '';

		if (!empty($postVars['observacoes'])) {
			$data = [];
			$data['nome'] = $postVars['observacoes']['nome'];
			$data['cont_1'] = $postVars['observacoes']['contatoPrimeiro'];
			$data['cont_2'] = $postVars['observacoes']['contatoSegundo'];
			$data['obs'] = $postVars['observacoes']['observacao'];
			$id_relato = Questionario::enviarRelato($data);
		}

		foreach ($postVars['data'] as $answer) {
			if ($answer['question_id']) {
				$data = [];
				$data['id_pergunta'] = $answer['question_id'];
				$data['resposta_valor'] = $answer['answer'];
				Questionario::enviarResp($data, $id_relato == '' ? null : $id_relato, $answer['questionario_id']);
			}
		}

		return ['result' => true];
	}


	public static function startModal($request)
	{
		$questionarios = Questionario::puxarQuestionariosAllActive();
		$resultado = null;

		if (count($questionarios) > 0) {
			foreach ($questionarios as $questionario) {
				$resultado .= View::render('paciente/avasis/startTableQuestionario', [
					"id" => $questionario['id'],
					"nome" => $questionario['nome'],
					"data_inicio" => (new \DateTime($questionario['data_inicio']))->format('d/m/Y')
				]);
			}
		} else $resultado = "<tr class='no-one'><td colspan='2'><p>Não tem questionarios</p></td></tr>";


		$content = View::render('paciente/avasis/startModal', [
			"id_questionario" => "",
			"questionarios" => $resultado
		]);

		return $content;
	}

	public static function gerenciarRelatorios($request)
	{
		$MaxAndMin = Questionario::puxarDateMaxAndMinRespostas();
		$tipos = Questionario::puxarTiposRelatorios();
		$resultado = null;
		$sample = "<option value='{{id}}' data-type='{{tipo_graph}}'>{{nome}}</option>";

		foreach ($tipos as $tipo) {
			$resultado .= str_replace("{{tipo_graph}}", $tipo['tipo_graph'], str_replace("{{nome}}", $tipo['nome'], str_replace("{{id}}", $tipo['id'], $sample)));
		}

		$content = View::render('paciente/avasis/respostas', [
			"min" => $MaxAndMin['min'],
			"max" => $MaxAndMin['max'],
			"tipos" => $resultado
		]);

		return parent::getPage('Avasis', 'avasis', $content);
	}

	public static function getHomeDash($request)
	{
		$content = View::render('paciente/avasis/dashboard');

		return parent::getPage('Avasis', 'avasis', $content) . "<script>$(document).ready(function () { $('footer').css({'position' : 'fixed', 'bottom' : 0, 'width' : 100+'%' }); });</script>";
	}

	public static function gerenciarCategorias($request)
	{
		$categorias = Questionario::puxarCategoriasAll();
		$resultado = null;

		foreach ($categorias as $categoria) {
			$resultado .= View::render('paciente/avasis/table_categorias', [
				'id' => $categoria['id'],
				'status' => $categoria['status'],
				'nome' => $categoria['nome'],
				'status_check' => $categoria['status'] == 1 ? 'checked' : ""
			]);
		}

		$content = View::render('paciente/avasis/categorias', [
			'categorias' => $resultado
		]);

		return parent::getPage('Avasis', 'avasis', $content);
	}

	// Método responsável por retornar o modal para enviar a observação
	public static function getModalEnviar($request)
	{
		$post = $request->getPostVars();

		$unidades = Questionario::puxarUnidadesAllActive();

		$sample = "<option value='{{id}}'>{{nome}}</option>";
		$resultado = "";

		foreach ($unidades as $unidade) {
			$resultado .= str_replace("{{nome}}", $unidade['nome'], str_replace("{{id}}", $unidade['id'], $sample));
		}

		$content = View::render('paciente/avasis/modalObs', [
			'id_questionario' => $post['id_questionario'],
			'tipo' => $post['type'] == null ? 0 : $post['type'],
			'unidades' => $resultado,
			'modalScript' => ""
		]);

		return $content;
	}

	public static function index_second($request)
	{
		$post = $request->getPostVars();

		$questions = Questionario::puxarPerguntasAllByQuestionario($post['id_questionario']);
		$icon = null;

		switch ($questions[0]['nome_categoria']) {
			case 'Equipe de enfermagem':
				$icon = 'user-nurse';
				break;

			case 'Equipe medica':
				$icon = 'user-md';
				break;

			case 'Nutricao':
				$icon = 'utensils';
				break;
		}
		$content = View::render('paciente/avasis/index_second', [
			'inicializador_perguntas' => "<script>var perguntas = " . json_encode($questions) . ";var perguntas_tam = perguntas.length;</script>
      <script src='" . URL . "/resources/js/customs/avasis/index_second.js'></script>",
			'id_questionario' => $post['id_questionario'],
			'nome_categoria_to_icon' => $icon,
			'nome_categoria' => $questions[0]['nome_categoria'] == "Nutricao" ? "Nutrição" : $questions[0]['nome_categoria'],
			'pergunta' => $questions[0]['pergunta'],
			'id_pergunta' => $questions[0]['id'],
		]);

		return $content;
	}

	// Método responsável por enviar a resposta
	public static function enviarResp($request)
	{
		$post = $request->getPostVars();
		$id = Questionario::enviarRelato($post);

		$objs = json_decode($post['respostas'], true);

		foreach ($objs as $rep) {
			try {
				Questionario::enviarResp($rep, $id);
			} catch (\Error $e) {
				throw new \Error("Error Processing Request", 1);
			}
		}
		return "true";
	}

	// Método responsável por retornar as categorias;
	public static function getCategorias($request)
	{
		$categorias = Questionario::puxarCategorias();
		$categorias_reserva = $categorias;
		$index = array_rand($categorias, 1);

		$categoria = $categorias[$index]['nome'];
		$pergunta = Questionario::puxarPergunta($categorias[$index]['id']);
		array_splice($categorias, $index, 1);

		return json_encode(array(
			"categorias" => $categorias,
			"pergunta_ini" => $pergunta,
			"categoria_ini" => $categoria
		), JSON_UNESCAPED_UNICODE);
	}

	public static function gerenciarPerguntas($request)
	{
		$perguntas = Questionario::puxarPerguntasAll();
		$categorias_no_limit = Questionario::puxarCategorias("no_limit");
		$resultado = null;
		$sample_categoria = "<option value='{{id_categoria}}' {{compare_id}}>{{nome}}</option>";

		foreach ($perguntas as $pergunta) {
			$categorias = null;
			foreach ($categorias_no_limit as $categ) {
				$categorias .= str_replace(
					"{{compare_id}}",
					$pergunta['id_categoria'] == $categ['id'] ? "selected" : "",
					str_replace(
						"{{nome}}",
						$categ['nome'],
						str_replace(
							"{{id_categoria}}",
							$categ['id'],
							$sample_categoria
						)
					)
				);
			}

			$resultado .= View::render('paciente/avasis/table_perguntas', [
				'id' => $pergunta['id'],
				'status' => $pergunta['status'],
				'pergunta' => $pergunta['pergunta'],
				'status_check' => $pergunta['status'] == 1 ? 'checked' : "",
				'categorias' => $categorias
			]);
		}

		$content = View::render('paciente/avasis/perguntas', [
			'perguntas' => $resultado
		]);

		return parent::getPage('Avasis', 'avasis', $content);
	}

	public static function gerenciarQuestionarios($request)
	{
		$questionarios = Questionario::puxarQuestionariosAll();
		$resultado = null;

		foreach ($questionarios as $questionario) {

			$resultado .= View::render('paciente/avasis/table_questionarios', [
				'id' => $questionario['id'],
				'status' => $questionario['status'],
				'nome' => $questionario['nome'],
				'status_checked' => $questionario['status'] == 1 ? 'checked' : "",
				'tipo' => $questionario['tipo'],
				'data_inicio' => (new \DateTime($questionario['data_inicio']))->format('Y-m-d') . 'T' . (new \DateTime($questionario['data_inicio']))->format('H:i')
			]);
		}

		$content = View::render('paciente/avasis/questionarios', [
			'questionarios' => $resultado
		]);

		return parent::getPage('Avasis', 'avasis', $content);
	}

	public static function gerenciarUnidades($request)
	{
		$unidades = Questionario::puxarUnidadesAll();
		$resultado = null;

		foreach ($unidades as $unidade) {

			$resultado .= View::render('paciente/avasis/table_unidades', [
				'id' => $unidade['id'],
				'nome' => $unidade['nome'],
				'status' => $unidade['status'],
				'status_checked' => $unidade['status'] == 1 ? 'checked' : ""
			]);
		}

		$content = View::render('paciente/avasis/unidades', [
			'unidades' => $resultado
		]);

		return parent::getPage('Avasis', 'avasis', $content);
	}

	// Método responsável pelo check se termina ou é a próxima pergunta
	public static function finalizaOuProximo($request)
	{
		$post = $request->getPostVars();
		if ($post['func'] == "return") return "return";
		$pergunta = Questionario::puxarPergunta($post['proxima_pergunta']);
		return json_encode($pergunta);
	}

	public static function changeStatus($request)
	{
		$post = $request->getPostVars();
		$result = null;
		switch ($post['type']) {
			case 0:
				$result = Questionario::changeStatus($post['table'], $post['id_categoria'], $post['status']);
				break;

			case 1:
				$result = Questionario::changeStatus($post['table'], $post['id_pergunta'], $post['status']);
				break;

			case 2:
				$result = Questionario::changeStatus($post['table'], $post['id_questionario'], $post['status']);
				break;

			case 3:
				$result = Questionario::changeStatus($post['table'], $post['id_unidade'], $post['status']);
				break;
		}
		return $result ? "true" : $result;
	}

	public static function addCategoria($request)
	{
		$post = $request->getPostVars();
		try {
			Questionario::addCategoria($post['nome-categoria']);
			return "true";
		} catch (\Exception $e) {
			// throw new \Exception("Error Processing Request", 1);
			return "false";
		}
	}

	public static function addPergunta($request)
	{
		$post = $request->getPostVars();
		try {
			Questionario::addPergunta($post['nome-pergunta'], $post['categoria-pergunta']);
			return "true";
		} catch (\Exception $e) {
			return "false";
		}
	}

	public static function modalAddCategoria($request)
	{
		return View::render('paciente/avasis/modalAddCategoria');
	}

	public static function edit($request)
	{
		$post = $request->getPostVars();
		switch ($post['tipo']) {
			case 0:
				try {
					Questionario::editCategoriaOrUnidade("categorias", $post['categoria-nome'][0], $post['categoria-id'][0]);
					return "true";
				} catch (\Exception $e) {
					return "false";
				}
				break;

			case 1:
				try {
					Questionario::editPergunta($post['pergunta-nome'][0], $post['pergunta-categoria'][0], $post["pergunta-id"][0]);
					return "true";
				} catch (\Exception $e) {
					return "false";
				}
				break;

			case 2:
				try {
					Questionario::editCategoriaOrUnidade("unidades", $post['unidade-nome'][0], $post['unidade-id'][0]);
					return "true";
				} catch (\Exception $e) {
					return "false";
				}
				break;

			case 3:
				try {
					Questionario::editQuestionario($post['nome'][0], $post['tipo'][0], $post['id'][0]);
					return "true";
				} catch (\Exception $e) {
					return "false";
				}
				break;
		}
	}

	public static function modalAddPergunta($request)
	{
		$resultado = null;
		$categorias = Questionario::puxarCategoriasAllActive();

		foreach ($categorias as $categoria) {
			$resultado .=  "<option value=" . $categoria['id'] . ">" . $categoria['nome'] . "</option>";
		}

		return View::render('paciente/avasis/modalAddPergunta', [
			'categorias' => $resultado
		]);
	}

	public static function showPerguntas($request)
	{
		$post = $request->getPostVars();
		$resultado = null;
		$perguntas = Questionario::puxarPerguntasAllByQuestionario($post['id_questionario']);

		if (count($perguntas) > 0) {
			foreach ($perguntas as $pergunta) {
				$resultado .= "<tr data-id=" . $pergunta['id'] . "><td>" . $pergunta['pergunta'] . "</td><td>" . $pergunta['nome_categoria'] . "</td></tr>";
			}
		} else {
			$resultado = "<tr class='no-one'><td colspan='2'><p>Não tem perguntas adicionadas nesse questionario</p></td></tr>";
		}

		return View::render('paciente/avasis/showPerguntas', [
			'id_questionario' => $post['id_questionario'],
			'perguntas' => $resultado
		]);
	}

	public static function modalAddQuestionario($request)
	{
		return View::render('paciente/avasis/modalAddQuestionario');
	}

	public static function addQuestionario($request)
	{
		$post = $request->getPostVars();
		try {
			Questionario::addQuestionario($post['nome'], $post['tipo']);
			return "true";
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}
}
