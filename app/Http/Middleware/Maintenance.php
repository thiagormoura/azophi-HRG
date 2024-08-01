<?php

namespace App\Http\Middleware;

class Maintenance
{
  // Método responsável por executar o middleware
  public function handle($request, $next, $args)
  {
    // Verifica o estado de manunteção da página
    if (getenv('MAINTENANCE') == 'true') {
      throw new \Exception("Página em manutenção", 200);
    }
    // Executa o próximo middleware
    return $next($request);
  }
}
