<?php

namespace App\Controller\Pages;

use App\Communication\Email;
use \App\Utils\View;
use \App\Controller\Layout\LayoutLogin as LayoutLogin;
use App\Http\Request;
use App\Model\Utils\Project;

class Home extends LayoutLogin
{
  // Método responsável por retornar a página principal da central de serviços
  public static function getHome(Request $request)
  {
    $content = View::render('home/home', [
      'name' => $request->user->nome
    ]);

    return parent::getLayout('Central de serviços', $content, 'home');
  }

  public static function getProjects(Request $request)
  {
    $projects = Project::getProjects();
    $status = [
      'C' => 'Concluido',
      'T' => 'Em teste',
      'P' => 'Pendente'
    ];
    $color = [
      'C' => 'success',
      'T' => 'warning',
      'P' => 'secondary'
    ];
    $rows = '';

    foreach ($projects as $project) {
      $rows .= View::render('home/table_row', [
        'description' => $project->descricao,
        'link' => $project->link,
        'status' => $status[$project->status],
        'color' => $color[$project->status],
      ]);
    }


    $content = View::render('home/projetos', [
      'rows' => $rows
    ]);

    return parent::getLayout('Central de serviços | Projetos', $content, 'home');
  }
}
