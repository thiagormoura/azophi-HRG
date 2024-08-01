<?php

namespace App\Model\CentralServicos;

use App\Db\Database;

class Permissao
{
  // Método responsável por inserir um nova permissão
  public static function insertPermissao($permissao)
  {
    return (new Database('centralservicos', 'permissao'))->insert([
      'id_sistema' => $permissao['id_sistema'],
      'codigo' => $permissao['codigo'],
      'descricao' => $permissao['descricao'],
    ]);
  }
  // Método responsável por inserir uma determinada permissão para um determinado usuário
  public static function insertPermissaoUsuario($idPermissao, $idUsuario)
  {
    return (new Database('centralservicos', 'usuario_permissao'))->insert([
      'id_permissao' => $idPermissao,
      'id_usuario' => $idUsuario,
    ]);
  }
  // Método responsável por inserir um determinada permissão em um determinado perfil
  public static function insertPermissaoPerfil($idPermissao, $idPerfil)
  {
    return (new Database('centralservicos', 'perfil_permissao'))->insert([
      'id_permissao' => $idPermissao,
      'id_perfil' => $idPerfil,
    ]);
  }
  // Método responsável por retornar todas as permissões cadastradas e com sistemas ativos
  public static function getPermissoes()
  {
    return (new Database('centralservicos', 'permissao p, sistema s'))->select('p.*, s.nome as sistema', "p.id_sistema = s.id AND s.`status` = 'A'")->fetchAll(\PDO::FETCH_CLASS, self::class);
  }
  // Método responsável por retornar os códigos das permissoes de um usuário pelo id dele
  public static function getPermissaoCodigoByUsuario($id)
  {
    return (new Database('centralservicos', 'usuario_permissao up, permissao p, sistema s'))->select('p.codigo', 'p.id = up.id_permissao and up.id_usuario = ' . $id . " AND s.`status` = 'A' AND s.id = p.id_sistema")->fetchAll(\PDO::FETCH_COLUMN);
  }
  // Método responsável por retornar as permissoes de um usuário pelo id dele
  public static function getPermissaoByUsuario($id)
  {
    return (new Database('centralservicos', 'usuario_permissao up, permissao p, sistema s'))->select('p.*', 'p.id = up.id_permissao and up.id_usuario = ' . $id . " AND s.`status` = 'A'")->fetchAll(\PDO::FETCH_CLASS, self::class);
  }
  // Método responsável por retornar as permissoes cadastradas pelo cocidgo
  public static function getPermissaoByCodigo($codigo)
  {
    return (new Database('centralservicos', 'permissao p, sistema s'))->select('p.*', "p.codigo = '" . $codigo . "' AND s.`status` = 'A'")->fetchObject(self::class);
  }
  // Método responsável por retornar as permissoes pelo id dela
  public static function getPermissaoById($id)
  {
    return (new Database('centralservicos', 'permissao p, sistema s'))->select('p.*', 'p.id  = ' . $id . " AND s.`status` = 'A'")->fetchObject(self::class);
  }
  // Método responsável por retornar as permissões pelo id do sistema a qual elas pertencem
  public static function getPermissaoBySistemaId($id)
  {
    return (new Database('centralservicos', 'permissao'))->select('*', 'id_sistema = ' . $id)->fetchAll(\PDO::FETCH_CLASS, self::class);
  }
  // Método resposnável por retornar as permissões de um determinado usuário em um determinado sistema
  public static function getPermissaoByUsuarioAndSistema($idUsuario, $idSistema)
  {
    $unionQuery = 'SELECT FALSE as vinculado, p.* FROM permissao p where p.id NOT IN (SELECT up.id_permissao FROM usuario_permissao up where up.id_usuario = ' . $idUsuario . ') and p.id_sistema = ' . $idSistema . ' GROUP BY p.codigo';

    return (new Database('centralservicos', 'permissao p, usuario_permissao up'))->unionAll('TRUE as vinculado, p.*', 'p.id_sistema = ' . $idSistema . ' and p.id = up.id_permissao and up.id_usuario = ' . $idUsuario, null, 'p.codigo', null, $unionQuery)->fetchAll(\PDO::FETCH_CLASS, self::class);
  }
  // Método responsável pore retornar as permissões de um determinado perfil em um determinado sistema
  public static function getPermissaoByPerfilAndSistema($idPerfil, $idSistema)
  {
    $unionQuery = 'SELECT FALSE as vinculado, p.* FROM permissao p where p.id NOT IN (SELECT pp.id_permissao FROM perfil_permissao pp where pp.id_perfil = ' . $idPerfil . ') and p.id_sistema = ' . $idSistema . ' GROUP BY p.codigo';

    return (new Database('centralservicos', 'permissao p, perfil_permissao pp'))->unionAll('TRUE as vinculado, p.*', 'p.id_sistema = ' . $idSistema . ' and p.id = pp.id_permissao and pp.id_perfil = ' . $idPerfil, null, 'p.codigo', null, $unionQuery)->fetchAll(\PDO::FETCH_CLASS, self::class);
  }
  // Método responsável por retornar todas as permissões de um perfil
  public static function getPermissaoByPerfil($idPerfil)
  {
    return (new Database('centralservicos', 'permissao p, perfil_permissao up'))->select('p.*', 'up.id_permissao = p.id AND up.id_perfil = ' . $idPerfil)->fetchAll(\PDO::FETCH_CLASS, self::class);
  }
  // Método responsável por retornar os ids das permissões pelo id de um perfil
  public static function getPermissaoIdByPerfil($idPerfil)
  {
    return (new Database('centralservicos', 'permissao p, perfil_permissao up'))->select('p.id', 'up.id_permissao = p.id AND up.id_perfil = ' . $idPerfil)->fetchAll(\PDO::FETCH_COLUMN);
  }
  // Método responsável por atualizar uma permissão
  public static function updatePermissao($permissao)
  {
    return (new Database('centralservicos', 'permissao'))->update('id = ' . $permissao->id,  [
      'id_sistema' => $permissao->id_sistema,
      'codigo' => $permissao->codigo,
      'descricao' => $permissao->descricao,
    ]);
  }
  // Método responsável por deletar uma permissçao por determinado usuário
  public static function deletePermissaoByUsuario($id)
  {
    return (new Database('centralservicos', 'usuario_permissao'))->delete('id_usuario = ' . $id);
  }
  // Método responsál por deletar uma permissão por determinado perfil
  public static function deletePermissaoByPerfil($id)
  {
    return (new Database('centralservicos', 'perfil_permissao'))->delete('id_perfil = ' . $id);
  }
}
