<?php

namespace App\Http\Middleware;

use \App\Session\User\AuthPaciente as SessionUserLogin;

class RequireLogoutPaciente
{
  // Método responsável por executar o middleware
  public function handle($request, $next)
  {
    if (!empty(SessionUserLogin::isLogged())) {
      $request->getRouter()->redirect('/home/paciente');
    }
    return $next($request);
  }
}
