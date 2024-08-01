<?php

namespace App\Controller\Api;

use \App\Model\Entity\User as UserEntity;
use \App\Db\Pagination;

class User extends Api
{

  // Método responsável por retornar os detalhes da API
  public static function getUsers($request)
  {
    return [
      'user' => [self::getUserItems($request, $obPagination)],
      'paginacao' => [parent::getPagination($request, $obPagination)]
    ];
  }

  // Método responsável por retornar o usuário pelo id
  public static function getUser($request, $id)
  {

    if (!is_numeric($id)) throw new \Exception("Usuario: " . $id . " é inválido", 404);

    $obUser = UserEntity::getUserById($id);
    if (!$obUser instanceof UserEntity) {
      throw new \Exception("O usuário: " . $id . " não foi encontrado", 404);
    }

    return [
      'id' => (int)$obUser->id,
      'nome' => $obUser->nome,
      'email' => $obUser->email
    ];
  }

  // Método responsável por retornar o usuário conectado
  public static function getCurrentUser($request){
    $obUser = $request->user;
    
    return [
      'id' => (int)$obUser->id,
      'nome' => $obUser->nome,
      'email' => $obUser->email
    ];
  }

  private static function getUserItems($request, &$obPagination)
  {
    $itens = [];

    $quantidadeTotal = UserEntity::getUsers(null, null, null, 'COUNT(*) as qtd')->fetchObject()->qtd;

    $queryParams = $request->getQueryParams();
    $paginaAtual = $queryParams['page'] ?? 1;
    $obPagination = new Pagination($quantidadeTotal, $paginaAtual, 5);

    $results = UserEntity::getUsers(null, 'id DESC', $obPagination->getLimit());

    while ($obUser = $results->fetchObject(UserEntity::class)) {
      $itens[] = [
        'id' => (int)$obUser->id,
        'nome' => $obUser->nome,
        'email' => $obUser->email
      ];
    }

    return $itens;
  }

  // Método responsável por inserir um novo usuário via API
  public static function setNewUser($request)
  {
    $postVars = $request->getPostVars();

    if (!isset($postVars['nome']) or !isset($postVars['email']) or !isset($postVars['senha'])) {
      throw new \Exception("Os campos nome, email e senha são obrigatórios", 400);
    }

    $nome = $postVars['nome'];
    $email =  $postVars['email'];
    $senha = $postVars['senha'];

    $obUserEmail = UserEntity::getUserByEmail($email);
    // Verifica o email do usuario
    if ($obUserEmail instanceof UserEntity) throw new \Exception("O email: " . $email  . " já está cadastrado", 400);

    $obUser = new UserEntity;
    $obUser->nome = $nome;
    $obUser->email = $email;
    $obUser->senha = password_hash($senha, PASSWORD_DEFAULT);
    $obUser->cadastrar();

    return [
      'id' => (int)$obUser->id,
      'nome' => $obUser->nome,
      'email' => $obUser->email
    ];
  }

  // Método responsável por atualizar os dados dos usuarios via API
  public static function setEditUser($request, $id)
  {
    $postVars = $request->getPostVars();

    if (!isset($postVars['nome']) or !isset($postVars['email']) or !isset($postVars['senha'])) throw new \Exception("Os campos nome, email e senha são obrigatórios", 400);

    $obUser = UserEntity::getUserById($id);
    // Verifica o email do usuario
    if (!$obUser instanceof UserEntity) throw new \Exception("Usuário não cadastrado", 404);

    $nome = $postVars['nome'];
    $email =  $postVars['email'];
    $senha = $postVars['senha'];

    // Verifica o email do usuario
    $obUserEmail = UserEntity::getUserByEmail($email);
    if ($obUserEmail instanceof UserEntity && $obUserEmail->id != $id) throw new \Exception("O email:" . $email . " já cadastrado", 400);

    $obUser->nome = $nome;
    $obUser->email = $email;
    $obUser->senha = password_hash($senha, PASSWORD_DEFAULT);
    $obUser->atualizar();

    return [
      'id' => (int)$obUser->id,
      'nome' => $obUser->nome,
      'email' => $obUser->email
    ];
  }

  // Método responsável por remover um usuário via API
  public static function setDeleteUser($request, $id)
  {
    $obUser = UserEntity::getUserById($id);
    // Verifica o email do usuario
    if (!$obUser instanceof UserEntity) throw new \Exception("Usuário não cadastrado", 404);
    // Impede a exclusão do cadastro atualmente conectado
    if ($obUser->id == $request->user->id) throw new \Exception("Não é possivel excluir o cadastro atualmente conectado", 400);

    $obUser->excluir();

    return [
      'sucesso' => true
    ];
  }
}
