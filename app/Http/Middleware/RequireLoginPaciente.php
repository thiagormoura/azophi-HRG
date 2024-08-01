<?php

namespace App\Http\Middleware;

use \App\Session\User\AuthPaciente as SessionUserLogin;

class RequireLoginPaciente
{
  // Método responsável por executar o middleware
  public function handle($request, $next)
  {
    if (!SessionUserLogin::isLogged()) {
      $request->getRouter()->redirect('/paciente/login');
    }
    return $next($request);
  }
}
