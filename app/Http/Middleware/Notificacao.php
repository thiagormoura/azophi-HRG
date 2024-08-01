<?php

namespace App\Http\Middleware;

use \App\Session\SisNot\Notificacao as NotificacaoSession;

class Notificacao
{
  // Método responsável por executar o middleware
  public function handle($request, $next)
  {
    // Verifica o estado de manunteção da página
    if (!NotificacaoSession::getNotificacaoActive()) {
      $request->getRouter()->redirect('/sisnot');
    }
    // Executa o próximo middleware
    return $next($request);
  }
}
