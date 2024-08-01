<?php

namespace App\Model\Entity;

use App\Db\Database;

class User
{
  public $id;
  public $nome;
  public $sobrenome;
  public $email;
  public $senha;
  public $matricula;
  public $cpf;
  public $status;
  public $novo;
  public $data_criacao;

  public static function updateAccess($user){

    date_default_timezone_set('America/Fortaleza');

    return (new Database("centralservicos", "usuario_acesso"))->insert([ 
        "usuario_nome" => $user->nome, 
        "usuario_cpf" => $user->cpf,
        "sistema" => 'SOS Maqueiros (Produção)',
        "dthr_login" => date("Y-m-d H:i:s")
    ]);
  }

  // Método responsável por retornar o usuário pelo email 
  public static function getUserByEmail($email)
  {
    return (new Database('centralservicos', 'usuario'))->select('*', 'email = "' . $email . '"')->fetchObject(self::class);
  }
  public static function getUsersByPermissao($permissao_codigo)
  {
    return (new Database('centralservicos', 'usuario_permissao up, usuario u, permissao p'))->select('u.*', "(p.codigo = '" . $permissao_codigo . "' OR p.codigo = 'admin') AND up.id_permissao = p.id AND up.id_usuario = u.id")->fetchAll(\PDO::FETCH_CLASS, self::class);
  }
  // Método responsável por retornar o usuário pelo cpf 
  public static function getUserByCpf($cpf)
  {
    return (new Database('centralservicos', 'usuario'))->select('*', 'cpf = "' . $cpf . '"')->fetchObject(self::class);
  }
  // Método responsável por retornar o usuário pela matricula
  public static function getUserByMatricula($matricula)
  {
    return (new Database('centralservicos', 'usuario'))->select('*', 'matricula = "' . $matricula . '"')->fetchObject(self::class);
  }
  // Método responsável por retornar o usuário pelo id
  public static function getUserById($id)
  {
    return (new Database('centralservicos', 'usuario'))->select('*', 'id = ' . $id)->fetchObject(self::class);
  }

  // Método responsável por retornar todos os usuários cadastrados no sistema
  public static function getUsers()
  {
    return (new Database('centralservicos', 'usuario'))->select('*', null, null, null,  null)->fetchAll(\PDO::FETCH_CLASS, self::class);
  }
  // Método responsável por retornar todos os usuários cadastrados no sistema que possuem a permissão de acessar o sistema da nutrição
  public static function getUsersNutricao()
  {
    return (new Database('centralservicos', 'usuario as u, usuario_permissao as up'))->select('u.*', 'up.id_permissao = 2 AND up.id_usuario = u.id')->fetchAll(\PDO::FETCH_CLASS, self::class);
  }

  // Método responsável por retornar os usuários por perfil
  public static function getUserByPerfil($id_perfil)
  {
    return (new Database('centralservicos', 'usuario'))->select("*", "id_perfil = $id_perfil", null, null, null)->fetchAll(\PDO::FETCH_CLASS, self::class);
  }

  // Método responsável por atualizar os perfis do usuário
  public static function updatePerfil($idUsuario, $idPerfil)
  {
    return (new Database('centralservicos', 'usuario'))->update('id = ' . $idUsuario,  [
      'id_perfil' => $idPerfil,
    ]);
  }

  // Método responsável por cadastrar um novo usuário
  public function cadastrar()
  {
    $this->id = (new Database('centralservicos', 'usuario'))->insert([
      'nome' => $this->nome,
      'sobrenome' => $this->sobrenome,
      'email' => $this->email,
      'senha' => $this->senha,
      'matricula' => $this->matricula,
      'cpf' => $this->cpf,
    ]);
  }

  // Método responsável por atualizar um cadastro de um sistema já existente
  public function atualizar()
  {
    return (new Database('centralservicos', 'usuario'))->update('id = ' . $this->id,  [
      "nome" => $this->nome,
      "sobrenome" => $this->sobrenome,
      "email" => $this->email,
      "senha" => $this->senha,
      "matricula" => $this->matricula,
      "cpf" => $this->cpf,
      "status" => $this->status,
      "novo" => $this->novo,
    ]);
  }

  public function excluir()
  {
    return (new Database('centralservicos', 'usuario'))->delete('id = ' . $this->id);
  }
}
