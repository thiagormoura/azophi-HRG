<?php

namespace App\Controller\Avasis;

use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;
use App\Model\Avasis\Questionario;
use App\Model\Utils\Spy;
use Error;

class AvasisController extends LayoutPage
{
  // Método responsável por retornar a página inicial do Avasis
  public static function getHome($request)
  {
    $questions = Questionario::puxarNPS();

    $sample = "<div class='box-pergunta mr-1' data-id='{{id}}'><h3>{{nome}}</h3></div>";
    $resultado = "";

    foreach ($questions as $question) {
      $resultado .= str_replace("{{nome}}", $question['nome'], str_replace("{{id}}", $question['id'], $sample));
    }

    $content = View::render('avasis/index', [
      'url-back' => URL . '/home/funcionario',
      'NPS' => $resultado
    ]);

    return parent::getPage('Avasis', 'avasis', $content, $request);
  }

  // Função responsavel por retornar os questionarios disponíveis
  public static function startQuestionarioModal($request)
  {
    $questionarios = Questionario::puxarQuestionariosAllActive();
    $resultado = null;

    if (count($questionarios) > 0) {
      foreach ($questionarios as $questionario) {
        $resultado .= View::render('avasis/startTableQuestionario', [
          "id" => $questionario['id'],
          "nome" => $questionario['nome'],
          "data_inicio" => (new \DateTime($questionario['data_inicio']))->format('d/m/Y')
        ]);
      }
    } else $resultado = "<tr class='no-one'><td colspan='2'><p>Não tem questionarios</p></td></tr>";

    if(!parent::checkPermissao($request->user, "avasis-questionario", "admin"))
      return [
        "success" => false,
        "html" => "Você não tem permissão!"
      ];

    return [
      "success" => true,
      "html" => View::render('avasis/startModal', [
          "questionarios" => $resultado
      ])
    ];
  }

  public static function getHomeDash($request)
  {
    $infosBoxes = [
      [
        "id" => "id='start_quest'",
        "href" => "",
        "title" => "Iniciar",
        "subtitle" => "Questionário",
        "color" => "success",
        "icon" => "play",
        "perm" => "avasis-questionario"
      ],
      [
        "id" => "",
        "href" => "href='".URL."/avasis/gerenciador'",
        "title" => "Gerenciar",
        "subtitle" => "Questionários",
        "color" => "blue",
        "icon" => "clipboard-list-check",
        "perm" => "avasis-gerenciamento"
      ],
      [
        "id" => "",
        "href" => "href='".URL."/avasis/relatorios'",
        "title" => "Gerenciar",
        "subtitle" => "Relatórios",
        "color" => "teal",
        "icon" => "chart-line",
        "perm" => "avasis-relatorio"
      ]
    ];

    $infosBoxesHTML = "";
    foreach ($infosBoxes as $infoBox) {
      if(parent::checkPermissao($request->user, $infoBox['perm'], "admin"))
        $infosBoxesHTML .= View::render('avasis/info_box_dashboard', $infoBox);
    }

    $content = View::render('avasis/dashboard', [
      "info-box" => $infosBoxesHTML
    ]);

    // Atualiza o acesso do usuario nesse sistema
    Spy::updateAcess($request->user, 6, 'avasis');

    return parent::getPage('Avasis', 'avasis', $content, $request) . "<script>$(document).ready(function () { $('footer').css({'position' : 'fixed', 'bottom' : 0, 'width' : 100+'%' }); });</script>";
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

    return View::render('avasis/showPerguntas', [
      'id_questionario' => $post['id_questionario'],
      'perguntas' => $resultado
    ]);
  }
}
