<?php

namespace App\Controller\Auth;

use App\Communication\Email;
use \App\Utils\View;
use \App\Session\User\Auth as SessionUserLogin;
use \App\Session\Request\Request as SessionRequest;
use \App\Controller\Auth\Alert;
use \App\Controller\Layout\LayoutLogin;
use \App\Controller\Api\Auth as AuthAPI;
use App\Controller\Utils\Senha;
use App\Model\CentralServicos\Token as TokenModel;
use App\Model\Entity\User as UserModel;

class Auth extends LayoutLogin
{
  // Tempo para a solicitação de alteração de senha expirar (segundos)
  const tempoExpiracaoSenha = 1800;
  // Método responsável por retornar o conteúdo (view) da página de login
  public static function getLogin($request, $errorMessage = null, $successMessage = null)
  {
    $status = !is_null($errorMessage) ? Alert::getError($errorMessage) : (!is_null($successMessage) ? Alert::getSuccess($successMessage) : '');
    $content = View::render('login/login', [
      'status' => $status
    ]);
    return parent::getLayout('Login', $content);
  }
  // Método responsável por realizar o login do usuário
  public static function setLogin($request)
  {
    $postVars = $request->getPostVars();
    $user = UserModel::getUserByEmail($postVars['email']);
    if ($user instanceof UserModel && $user->novo && password_verify($postVars['senha'], $user->senha)) {
      SessionUserLogin::tempLogin($user);
      $request->getRouter()->redirect('/login/alterar_senha');
    }
  
    $token = AuthAPI::generateToken($request);
    SessionUserLogin::login($token);

    $requestedUrl = SessionRequest::getRequestedUrl();
    SessionRequest::dropRequestedUrl();

    $request->getRouter()->redirect($requestedUrl);
  }
  // Método responsável por deslogar o usuário
  public static function setLogout($request)
  {
    SessionUserLogin::logout();
    $request->getRouter()->redirect('/');
  }
  // Método responsável por exibir a página de alterar senha quando um novo usuário realiza login
  public static function getNewUserPassword($request, $errorMessage = null, $successMessage = null)
  {
    $status = !is_null($errorMessage) ? Alert::getError($errorMessage) : (!is_null($successMessage) ? Alert::getSuccess($successMessage) : '');
    $content = View::render('login/alterar_senha', [
      'status' => $status
    ]);
    return parent::getLayout('Login - Alterar senha', $content);
  }
  // Método responsável por alterar a senha de um novo usuário com determinada validações
  public static function setNewUserPassword($request)
  {
    $postVars = $request->getPostVars();

    if (empty($postVars['senha-antiga']) || empty($postVars['senha-antiga']) || empty($postVars['senha-confirmar'])) 
      return self::getNewUserPassword($request, 'É necessário preencher todos os campos.');

    $user = SessionUserLogin::getTempLogin();
    $obSenha = new Senha;
    $validSenha = $obSenha->validAtualSenha($postVars['senha-antiga'], $user->senha);

    if (!$validSenha['success']) 
      return self::getNewUserPassword($request, $validSenha['message']);

    $validSenha = $obSenha->validSenha($postVars['senha'], $postVars['senha-confirmar'], $postVars['senha-antiga']);

    if (!$validSenha['success']) 
      return self::getNewUserPassword($request, $validSenha['message']);

    SessionUserLogin::dropTempLogin();
    $user->senha = password_hash($postVars['senha'], PASSWORD_DEFAULT);
    $user->novo = 0;
    $user->atualizar();
    return self::getLogin($request, null, 'Alteração de senha realizada com sucesso.');
  }
  // Método responsável por retornar a página para alterar a senha caso o usuário tenha esquecido
  public static function getForgotPassword($request, $errorMessage = null, $successMessage = null)
  {
    $status = !is_null($errorMessage) ? Alert::getError($errorMessage) : (!is_null($successMessage) ? Alert::getSuccess($successMessage) : '');
    $content = View::render('login/esqueceu_senha', [
      'status' => $status
    ]);
    return parent::getLayout('Login - Esqueci minha senha', $content);
  }
  // Método responsável por gerar os pre-requesistos para validar a senha do usuário
  // e enviar o email para o usuário com as instruções para alteração de senha
  public static function setForgotPassword($request)
  {
    $postVars = $request->getPostVars();
    if (empty($postVars['email']))
      return self::getForgotPassword($request, 'É necessário preencher todos os campos.');
    $email = $postVars['email'];
    $user = UserModel::getUserByEmail($email);
    if (!$user instanceof UserModel)
      return self::getForgotPassword($request, 'Solicitação inválida. Email não cadastrado.');
    // Gera um selector em hexadecimal para ser o identificado do token na tabela
    $selector = bin2hex(random_bytes(8));
    // Gera um token de 32 bytes;
    $token = random_bytes(32);
    // Define o tempo para expiração do token gerado para alterar a senha
    $tempoExpiracao = time() + self::tempoExpiracaoSenha;
    // Define a URL a qual o usuário seria direcionado para realizar a alteração de senha
    $url = URL . '/login/esqueci_senha/novasenha?selector=' . $selector . '&validator=' . bin2hex($token);
    // Criptografa o token para armazenamento no banco
    $hashedToken = password_hash($token, PASSWORD_DEFAULT);
    $obEmail = new Email;
    $user = UserModel::getUserByEmail($email);
    setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
    date_default_timezone_set('America/Recife');
    // Define o corpo do email com as informações para alteração de senha
    $emailBody =  View::render('mail/mail_body', [
      'content' => View::render('mail/mail_alterar_senha', [
        'date' => date('d/m/Y H:i:s', time()),
        'usuario' => $user->nome . ' ' .  $user->sobrenome,
        'url-senha' => $url,
        'data-validade' => date('d/m/Y H:i:s', $tempoExpiracao),
      ]),
    ]);
    // Envia o email para o usuário com as instruções de recuperação de senha
    $obEmail->sendEmail($email, 'Central de Serviços - Alteração de senha', $emailBody);
    // Remove todos os tokens existentes no banco para aquele determinado email
    TokenModel::deleteResetSenha($user->email);
    // Insere um novo token para reset de senha
    TokenModel::insertResetSenha($email, $selector, $hashedToken, $tempoExpiracao);
    return self::getForgotPassword($request, null, 'Um email foi enviado para você com as instruções de como recuperar sua senha.');
  }
  
  public static function setForgotPasswordByAdmin($request)
  {
    $postVars = $request->getPostVars();
    if (empty($postVars['id']))
      return ["success" => false, "title" => 'Id do usuário não identificado.'];
    $idUser = $postVars['id'];
    // Gera um selector em hexadecimal para ser o identificado do token na tabela
    $selector = bin2hex(random_bytes(8));
    // Gera um token de 32 bytes;
    $token = random_bytes(32);
    // Define o tempo para expiração do token gerado para alterar a senha
    $tempoExpiracao = time() + self::tempoExpiracaoSenha;
    // Define a URL a qual o usuário seria direcionado para realizar a alteração de senha
    $url = URL . '/login/esqueci_senha/novasenha?selector=' . $selector . '&validator=' . bin2hex($token);
    // Criptografa o token para armazenamento no banco
    $hashedToken = password_hash($token, PASSWORD_DEFAULT);
    $obEmail = new Email;
    $user = UserModel::getUserById($idUser);
    setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
    date_default_timezone_set('America/Recife');
    // Define o corpo do email com as informações para alteração de senha
    $emailBody =  View::render('mail/mail_body', [
      'content' => View::render('mail/mail_alterar_senha', [
        'date' => date('d/m/Y H:i:s', time()),
        'usuario' => $user->nome . ' ' .  $user->sobrenome,
        'url-senha' => $url,
        'data-validade' => date('d/m/Y H:i:s', $tempoExpiracao),
      ]),
    ]);
    // Envia o email para o usuário com as instruções de recuperação de senha
    $obEmail->sendEmail($user->email, 'Central de Serviços - Alteração de senha', $emailBody);
    // Remove todos os tokens existentes no banco para aquele determinado email
    TokenModel::deleteResetSenha($user->email);
    // Insere um novo token para reset de senha
    TokenModel::insertResetSenha($user->email, $selector, $hashedToken, $tempoExpiracao);
    return ["success" => true, "text" => 'Um email foi enviado para o usuário ('.$user->email.') com as instruções de como recuperar sua senha.'];
  }

  // Método responsável por retornar a página de redefinição de senha
  public static function getChangePassword($request, $errorMessage = null, $successMessage = null)
  {
    $queryParams = $request->getQueryParams();
    // Válida se o token inserido pelo usuário é valido
    if (empty($queryParams['selector']) || empty($queryParams['validator'])) return self::getLogin($request, 'Desculpe, sua solicitação não pode ser completa.');
    $selector = $queryParams['selector'];
    $validator = $queryParams['validator'];
    // Válida se o token inserido pelo usuário é valido
    if (ctype_xdigit($selector) === false && ctype_xdigit($validator) === false) return self::getLogin($request, 'Desculpe, sua solicitação não pode ser completa.');
    $status = !is_null($errorMessage) ? Alert::getError($errorMessage) : (!is_null($successMessage) ? Alert::getSuccess($successMessage) : '');
    $content = View::render('login/nova_senha', [
      'selector' => $selector,
      'validator' => $validator,
      'status' => $status
    ]);
    return parent::getLayout('Login - Nova senha', $content);
  }
  // Método responsável por validar e redefinir a senha do usuário
  public static function setChangePassword($request)
  {
    $postVars = $request->getpostVars();
    // Valida se os campos foram preenchidos
    if (empty($postVars['senha']) || empty($postVars['senha-confirmar'])) return self::getChangePassword($request, 'É necessário preencher todos os campos.');
    // Válida se as senhas são iguas  
    if ($postVars['senha'] !== $postVars['senha-confirmar']) return self::getChangePassword($request, 'As senha devem ser iguais.');
    $obSenha = new Senha;
    // Válida se as senhas possui todos os critérios para serem inseridas
    $validSenha = $obSenha->validSenha($postVars['senha'], $postVars['senha-confirmar']);
    if (!$validSenha['success']) return self::getChangePassword($request, $validSenha['message']);
    $selector = $postVars['selector'];
    $validator = $postVars['validator'];
    $dataAtual = time();
    // Obtem o token pelo selector e a data de expiração
    $resetSenha = TokenModel::getResetSenhaBySelectorAndDate($selector, $dataAtual);
    // Verifica se possui algum token com os parametros passado, caso não retorna um erro
    if (!$resetSenha) return self::getForgotPassword($request, 'Desculpe, ocorreu um erro na sua solicitação e será necessário reenviar a sua solicitação para alteração de senha.');
    // Decodifica o token
    $tokenBin = hex2bin($validator);
    // Verifica se o token bate com o cadastrado no banco
    if (!password_verify($tokenBin, $resetSenha->token))  return self::getForgotPassword($request, 'Desculpe, ocorreu um erro na sua solicitação e será necessário reenviar a sua solicitação para alteração de senha.');
    // Obtém o usuário pelo email registrado no banco
    $user = UserModel::getUserByEmail($resetSenha->usuario_email);
    if (!$user instanceof UserModel) return self::getForgotPassword($request, 'Desculpe, ocorreu um erro na sua solicitação e será necessário reenviar a sua solicitação para alteração de senha.');
    // verifica se a senha antiga é igual a atual
    $validSenha = $obSenha->validSenhaAndAntigaSenha($postVars['senha'], $user->senha);
    if (!$validSenha['success']) return self::getChangePassword($request, $validSenha['message']);
    $senha = password_hash($postVars['senha'], PASSWORD_DEFAULT);
    $user->senha = $senha;
    $user->atualizar();
    TokenModel::deleteResetSenha($user->email);
    return self::getLogin($request, null, 'Alteração de senha realizada com sucesso.');
  }

  public static function getModalViewUser($request, $idUser)
  {
    $user = UserModel::getUserById($idUser);
    if (!$user instanceof UserModel)
      return [
        "success" => false,
        "text" => "Algo deu errado na visualização!"
      ];

    return [
      "success" => true,
      "modal" => View::render('admin/usuario/view_user', [
        "cpf" => $user->cpf,
        "nome" => $user->nome." ".$user->sobrenome,
        "data" => (new \DateTime($user->data_criacao, new \DateTimeZone(CURRENT_TIMEZONE)))->format('d/m/Y H:i')
      ])
    ];
  }
}
