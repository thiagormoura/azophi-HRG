<?php

namespace App\Http\Middleware;

use \App\Session\User\Auth as SessionUserLogin;

class ChangePassword
{
  // Método responsável por executar o middleware
  public function handle($request, $next)
  {
    if (!SessionUserLogin::getTempLogin()) {
      $request->getRouter()->redirect('/login');
    }
    return $next($request);
  }
}
