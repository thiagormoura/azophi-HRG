<?php

namespace App\Http\Middleware;

class Queue
{
  // Mapeamento de middlewares
  private static $map = [];
  // Mapeamento de middlewares que serão carregados em todas as rotas
  private static $default = [];
  // Fila de middlewares a ser executadas
  private $middlewares = [];
  // Função de execução do controlador
  private $controller;
  // Argumentos da função do controlador
  private $controllerArgs = [];
  // Middleware Args
  private $middlewareArgs = [];

  // Método responsável por construir a classe de fila de middlewares
  public function __construct($middlewares, $controller, $controllerArgs, $middleWareArgs)
  {
    $this->middlewares = array_merge(self::$default, $middlewares);
    $this->controller = $controller;
    $this->controllerArgs = $controllerArgs;
    $this->middlewareArgs = $middleWareArgs;
  }

  // Método responsável por definir o mapeamento de middlewares
  public static function setMap($map)
  {
    self::$map = $map;
  }

  public static function setDefault($default)
  {
    self::$default = $default;
  }

  // Método responsável por executar o próximo nivel da fila de middlewares
  public function next($request)
  {
    // Verifica se a fila está vázia
    if (empty($this->middlewares)) return call_user_func_array($this->controller, $this->controllerArgs);
    
    // Middleware
    $middleware = array_shift($this->middlewares);
    // Verifica o mapeamento
    if (empty(self::$map[$middleware])) {
      throw new \Exception("Problemas ao processar o middleware", 500);
    }
    
    // Função de next
    $queue = $this;
    $next = function ($request) use ($queue) {
      return $queue->next($request);
    };

    $middlewareArgs = $this->middlewareArgs ?? [];
    
    return (new self::$map[$middleware])->handle($request, $next, $middlewareArgs[$middleware] ?? []);
  }
}
