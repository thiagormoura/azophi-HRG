<?php

namespace App\Http\Middleware;

use \App\Session\User\Auth as SessionUserLogin;

class RequireLogin
{
  // Método responsável por executar o middleware
  public function handle($request, $next)
  {
    if (!SessionUserLogin::isLogged()) {
      $request->getRouter()->redirect('/login');
    }
    return $next($request);
  }
}
