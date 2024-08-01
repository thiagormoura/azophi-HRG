<?php

namespace App\Http\Middleware;

use \App\Session\User\Auth as SessionUserLogin;

class RequireLogout
{
  // Método responsável por executar o middleware
  public function handle($request, $next)
  {
    if (!empty(SessionUserLogin::isLogged())) {
      $request->getRouter()->redirect('/home/funcionario');
    }
    return $next($request);
  }
}
