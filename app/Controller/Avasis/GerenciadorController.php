<?php

namespace App\Controller\Avasis;

use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;
use App\Model\Avasis\Questionario;
use \App\Http\Request;
use Error;

class GerenciadorController extends LayoutPage
{
  public static function gerenciarQuestionarios($request)
  {
    $questionarios = Questionario::puxarQuestionariosAll();
    $resultado = null;

    foreach ($questionarios as $questionario) {

      $selectTipo = View::render('utils/select', [
        "class" => "form-control py-1",
        "name" => "selectTipo",
        "id" => "",
        "style" => "",
        "disabled" => "disabled",
        "required" => "",
        "multiple" => "",
        "options" => "<option value='N' ".($questionario['tipo'] == 'N' ? "selected" : '')." >NPS</option>
          <option value='Q' ".($questionario['tipo'] == 'Q' ? "selected" : '').">Questionario</option>"
      ]);

      $resultado .= View::render('avasis/gerenciador/table_questionarios', [
        'id' => $questionario['id'],
        'status' => $questionario['status'],
        'nome' => $questionario['nome'],
        'status_checked' => $questionario['status'] == 1 ? 'checked' : "",
        'status_name' => $questionario['status'] == 1 ? 'Ativo' : 'Inativo',
        'tipo' => $selectTipo,
        'data_inicio' => (new \DateTime($questionario['data_inicio']))->format('Y-m-d') . 'T' . (new \DateTime($questionario['data_inicio']))->format('H:i')
      ]);
    }

    $content = View::render('avasis/gerenciador/questionarios', [
      'questionarios' => $resultado
    ]);

    return $content;
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

      $resultado .= View::render('avasis/gerenciador/table_perguntas', [
        'id' => $pergunta['id'],
        'status' => $pergunta['status'],
        'pergunta' => $pergunta['pergunta'],
        'status_check' => $pergunta['status'] == 1 ? 'checked' : "",
        'categorias' => $categorias
      ]);
    }

    $content = View::render('avasis/gerenciador/perguntas', [
      'perguntas' => $resultado
    ]);

    return $content;
  }

  public static function gerenciarCategorias($request)
  {
    $categorias = Questionario::puxarCategoriasAll();
    $resultado = null;

    foreach ($categorias as $categoria) {
      $resultado .= View::render('avasis/gerenciador/table_categorias', [
        'id' => $categoria['id'],
        'status' => $categoria['status'],
        'nome' => $categoria['nome'],
        'status_check' => $categoria['status'] == 1 ? 'checked' : ""
      ]);
    }

    $content = View::render('avasis/gerenciador/categorias', [
      'categorias' => $resultado
    ]);

    return $content;
  }

  public static function GerenciarUnidades(Request $request)
  {
    $resultado = "";
    $setores = Questionario::puxarUnidadesAll();

    foreach ($setores as $setor) {
      $resultado .= View::render('avasis/gerenciador/table_unidades', [
        'id' => $setor['id'],
        'status' => $setor['status'],
        'unidade' => $setor['nome'],
        'status_check' => $setor['status'] == 1 ? 'checked' : ""
      ]);
    }

    $content = View::render('avasis/gerenciador/unidades', [
      'unidades' => $resultado
    ]);

    return $content;
  }

  //Método responsável por retornar a página do questionário
  public static function getGerenciadorPage($request)
  {
    $content_questionarios = self::gerenciarQuestionarios($request);
    $content_categorias = self::GerenciarCategorias($request);
    $content_perguntas = self::GerenciarPerguntas($request);
    $content_unidades = self::GerenciarUnidades($request);


    $content = View::render('avasis/gerenciador/index_gerenciador', [
      'categorias' => $content_categorias,
      'perguntas' => $content_perguntas,
      'questionarios' => $content_questionarios,
      'unidades' => $content_unidades
    ]);

    return parent::getPage('Avasis', 'avasis', $content, $request);
  }
  //Dispara o modal para adicionar um questionario
  public static function modalAddQuestionario($request)
  {
    $content = View::render('avasis/gerenciador/modalAddQuestionario');
    return $content;
  }


  //Dispara o modal para adicionar perguntas
  public static function modalAddPergunta($request)
  {
    $resultado = null;
    $categorias = Questionario::puxarCategoriasAllActive();

    foreach ($categorias as $categoria) {
      $resultado .=  "<option value=" . $categoria['id'] . ">" . $categoria['nome'] . "</option>";
    }

    return View::render('avasis/gerenciador/modalAddPergunta', [
      'categorias' => $resultado
    ]);
  }

  //Dispara o modal para adicionar categorias
  public static function modalAddCategoria($request)
  {
    return View::render('avasis/gerenciador/modalAddCategoria');
  }

  //Dispara o modal para adicionar unidades
  public static function modalAddUnidade($request)
  {
    return View::render('avasis/gerenciador/modalAddUnidade');
  }


  //Cria um novo questionário
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

  //Cria a pergunta
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

  //Cria a categoria
  public static function addCategoria($request)
  {
    $post = $request->getPostVars();
    try {
      Questionario::addCategoria($post['nome-categoria']);
      return "true";
    } catch (\Exception $e) {
      return "false";
    }
  }

  //Cria Unidade
  public static function createUnidade(Request $request)
  {
    $post = $request->getPostVars();
    try {
      Questionario::addUnidade($post['nome']);
      return ["succeded" => true];
    } catch (\Exception $e) {
      return ["succeded" => false];
    }
  }

  //Muda o status da pergunta/categoria/questionário
  public static function changeStatus($request)
  {
    $post = $request->getPostVars();
    $result = null;
    switch ($post['table']) {
      case "categorias":
        $result = Questionario::changeStatus($post['table'], $post['id_categoria'], $post['status']);
        break;

      case "perguntas":
        $result = Questionario::changeStatus($post['table'], $post['id_pergunta'], $post['status']);
        break;

      case "questionario":
        $result = Questionario::changeStatus($post['table'], $post['id_questionario'], $post['status']);
        break;

      case "unidades":
        $result = Questionario::changeStatus($post['table'], $post['id_unidade'], $post['status']);
        break;
    }
    return $result ? true : false;
  }

  //Fecha e edita questionarios/perguntas/categorias
  public static function edit($request)
  {
    $post = $request->getPostVars();
    switch ($post['tipo']) {
      case "categorias":
        try {
          Questionario::editCategoriaOrUnidade("categorias", $post['nome'], $post['id']);
          return true;
        } catch (\Exception $e) {
          return false;
        }
        break;

      case "perguntas":
        try {
          Questionario::editPergunta($post['nome'], $post['categoria'], $post["id"]);
          return true;
        } catch (\Exception $e) {
          return false;
        }
        break;

      case "questionario":
        try {
          Questionario::editQuestionario($post['nome'], $post['tipo_questionario'], $post['id']);
          return true;
        } catch (\Exception $e) {
          return false;
        }
        break;

      case "unidades":
        try {
          Questionario::editCategoriaOrUnidade("unidades", $post['nome'], $post['id']);
          return true;
        } catch (\Exception $e) {
          return false;
        }
        break;
    }
  }
}
