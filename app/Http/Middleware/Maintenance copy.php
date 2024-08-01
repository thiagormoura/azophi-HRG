<?php

namespace App\Http\Middleware\Roles;

class Role
{
  // Método responsável por verificar a role do usuário
  public static function getRole($role, $request)
  {
    return in_array($role, $request->user->permissoes);
  }
}
