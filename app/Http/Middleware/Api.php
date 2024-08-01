<?php

namespace App\Http\Middleware;

class Api
{
  // Método responsável por executar o middleware
  public function handle($request, $next)
  {
    // Altera o contentType para JSON
    $request->getRouter()->setContentType('application/json');
    // Executa o próximo middleware
    return $next($request);
  }
}
