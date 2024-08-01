<?php

namespace App\Model\SosMaqueiro;

use App\Db\Database;

class Recurso
{
  // Método responsável por retornar os recursos de uma solicitação
  public static function getRecursosByChamado($id_chamado){
    return (new Database('sosmaqueiro', 'solicitacao_recurso sr, recurso r'))->select('r.*', 'sr.id_recurso = r.id AND sr.id_solicitacao = ' . $id_chamado)->fetchAll(\PDO::FETCH_CLASS, self::class);
  }
  // Método responsável por retornar todos os recursos ativos
  public static function getRecursos(){
    return (new Database('sosmaqueiro', 'recurso'))->select('*', "`status` = 'A'")->fetchAll(\PDO::FETCH_CLASS, self::class);
  }
}
