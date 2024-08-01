<?php

namespace App\Controller\Api;

class Api
{

  // Método responsável por retornar os detalhes da API
  public static function getDetails($request)
  {
    return [
      'nome' => 'API - Central de Serviços',
      'versao' => 'v1.0.0.',
      'autor' => 'TI Projetos'
    ];
  }

  // Método responsável por retornar a páginação da API
  protected static function getPagination($request, $obPagination)
  {
    $queryParams = $request->getQueryParams();
    $pages = $obPagination->getPages();
    return [
      'paginaAtual' => isset($queryParams['page']) ? (int)$queryParams['page'] : 1,
      'qtdPaginas' => !empty($pages) ? count($pages) : 1
    ];
  }
}
