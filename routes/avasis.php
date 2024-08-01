<?php

use \App\Http\Response;
use \App\Controller\Avasis;

$router->get('/avasis', [
  'middlewares' => [
    'jwt-auth',
    'maintenance',
    'check-permission::avasis,admin'
  ], function ($request) {
    return new Response(200, Avasis\AvasisController::getHomeDash($request));
  }
]);

$router->get('/avasis/gerenciador', [
  'middlewares' => [
    'jwt-auth',
    'maintenance',
    'check-permission::avasis-gerenciamento,admin'
  ],
  function ($request) {
    return new Response(200, Avasis\GerenciadorController::getGerenciadorPage($request));
  }
]);

$router->get('/avasis/relatorios', [
  'middlewares' => [
    'jwt-auth',
    'maintenance',
    'check-permission::avasis-relatorio,admin'
  ],
  function ($request) {
    return new Response(200, Avasis\GraphicsController::gerenciarRelatorios($request));
  }
]);

$router->post('/avasis/startQuestionario', [
  'middlewares' => [
    'jwt-auth'
  ],
  function ($request) {
    return new Response(200, Avasis\AvasisController::startQuestionarioModal($request), 'application/json');
  }
]);

$router->post('/avasis/changeStatus', [
  'middlewares' => [
    'jwt-auth'
  ],
  function ($request) {
    return new Response(200, Avasis\GerenciadorController::changeStatus($request));
  }
]);

$router->post('/avasis/addCategoria', [
  'middlewares' => [
    'jwt-auth'
  ],
  function ($request) {
    return new Response(200, Avasis\GerenciadorController::addCategoria($request));
  }
]);

$router->post('/avasis/addPergunta', [
  'middlewares' => [
    'jwt-auth'
  ],
  function ($request) {
    return new Response(200, Avasis\GerenciadorController::addPergunta($request));
  }
]);

$router->post('/avasis/modalAddCategoria', [
  'middlewares' => [
    'jwt-auth'
  ],
  function ($request) {
    return new Response(200, Avasis\GerenciadorController::modalAddCategoria($request));
  }
]);

$router->post('/avasis/editCategoria', [
  'middlewares' => [
    'jwt-auth'
  ],
  function ($request) {
    return new Response(200, Avasis\GerenciadorController::edit($request));
  }
]);

$router->post('/avasis/editQuestionario', [
  'middlewares' => [
    'jwt-auth'
  ],
  function ($request) {
    return new Response(200, Avasis\GerenciadorController::edit($request));
  }
]);

$router->post('/avasis/editPergunta', [
  'middlewares' => [
    'jwt-auth'
  ],
  function ($request) {
    return new Response(200, Avasis\GerenciadorController::edit($request));
  }
]);

$router->post('/avasis/editUnidade', [
  'middlewares' => [
    'jwt-auth'
  ],
  function ($request) {
    return new Response(200, Avasis\GerenciadorController::edit($request));
  }
]);

$router->post('/avasis/modalAddPergunta', [
  'middlewares' => [
    'jwt-auth'
  ],
  function ($request) {
    return new Response(200, Avasis\GerenciadorController::modalAddPergunta($request));
  }
]);

$router->post('/avasis/showPerguntas', [
  'middlewares' => [
    'jwt-auth'
  ],
  function ($request) {
    return new Response(200, Avasis\AvasisController::showPerguntas($request));
  }
]);

$router->post('/avasis/modalAddQuestionario', [
  'middlewares' => [
    'jwt-auth'
  ],
  function ($request) {
    return new Response(200, Avasis\GerenciadorController::modalAddQuestionario($request));
  }
]);

$router->post('/avasis/addQuestionario', [
  'middlewares' => [
    'jwt-auth'
  ],
  function ($request) {
    return new Response(200, Avasis\GerenciadorController::addQuestionario($request));
  }
]);

$router->get('/avasis/questionario/{id}', [
  'middlewares' => [
    'jwt-auth',
    'maintenance',
    'check-permission::avasis-questionario,admin'
  ],
  function ($request, $id) {
    return new Response(200, Avasis\QuestionarioController::getQuestionarioPage($request, $id));
  }
]);

$router->post('/avasis/enviarQuestionario', [
  'middlewares' => [
    'jwt-auth'
  ],
  function ($request) {
    return new Response(200, Avasis\QuestionarioController::enviarQuestionario($request));
  }
]);

$router->post('/avasis/graphics', [
  'middlewares' => [
    'jwt-auth'
  ],
  function ($request) {
    return new Response(200, Avasis\GraphicsController::getGraphicsData($request));
  }
]);

$router->post('/avasis/topTen', [
  'middlewares' => [
    'jwt-auth'
  ],
  function ($request) {
    return new Response(200, Avasis\GraphicsController::topTen($request));
  }
]);

$router->post('/avasis/getPerguntas', [
  'middlewares' => [
    'jwt-auth'
  ],
  function ($request) {
    return new Response(200, Avasis\GraphicsController::getPerguntas($request), 'application/json');
  }
]);

$router->post('/avasis/getSetores', [
  'middlewares' => [
    'jwt-auth'
  ],
  function ($request) {
    return new Response(200, Avasis\GraphicsController::respBySetor($request));
  }
]);


$router->post('/avasis/getPerguntasBySetor', [
  'middlewares' => [
    'jwt-auth'
  ],
  function ($request) {
    return new Response(200, Avasis\GraphicsController::getPerguntasBySetor($request));
  }
]);

$router->post('/avasis/getQuantidadeRespostasBySetor', [
  'middlewares' => [
    'jwt-auth'
  ],
  function ($request) {
    return new Response(200, Avasis\GraphicsController::getQuantidadeRespostasBySetor($request), 'application/json');
  }
]);

$router->post('/avasis/modalAddUnidade', [
  'middlewares' => [
    'jwt-auth'
  ],
  function ($request) {
    return new Response(200, Avasis\GerenciadorController::modalAddUnidade($request));
  }
]);

$router->post('/avasis/createUnidade', [
  'middlewares' => [
    'jwt-auth'
  ],
  function ($request) {
    return new Response(200, Avasis\GerenciadorController::createUnidade($request), 'application/json');
  }
]);

$router->post('/avasis/getPerguntasToAddInQuestionario', [
  'middlewares' => [
    'jwt-auth'
  ],
  function ($request) {
    return new Response(200, Avasis\QuestionarioController::getPerguntasToAddInQuestionario($request));
  }
]);

$router->post('/avasis/putPerguntasToAddInQuestionario', [
  'middlewares' => [
    'jwt-auth'
  ],
  function ($request) {
    return new Response(200, Avasis\QuestionarioController::putPerguntasToAddInQuestionario($request), 'application/json');
  }
]);

$router->get('/avasis/getPerguntasFromQuestionario/{id}', [
  'middlewares' => [
    'jwt-auth'
  ],
  function ($request, $id) {
    return new Response(200, Avasis\QuestionarioController::getPerguntasFromQuestionario($request, $id));
  }
]);

$router->post('/avasis/removePerguntasFromQuestionario', [
  'middlewares' => [
    'jwt-auth'
  ],
  function ($request) {
    return new Response(200, Avasis\QuestionarioController::removePerguntasFromQuestionario($request), 'application/json');
  }
]);
