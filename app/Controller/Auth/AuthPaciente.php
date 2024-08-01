<?php

namespace App\Controller\Auth;

use App\Communication\Email;
use \App\Utils\View;
use \App\Session\User\AuthPaciente as SessionUserLogin;
use \App\Controller\Api\AuthPaciente as AuthApi;
use \App\Controller\Layout\LayoutLogin;
use App\Model\CentralServicos\Token as TokenModel;
use App\Model\Entity\Paciente as PacienteModel;
use App\Utils\ValidCPF;
use Firebase\JWT\JWT;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class AuthPaciente extends LayoutLogin
{
  const tempoExpiracaoSenha = 1800;
  // Método responsável por retornar o conteúdo (view) da página de login
  public static function getAccess($request, $errorMessage = null, $successMessage = null)
  {
    $status = !is_null($errorMessage) ? Alert::getError($errorMessage) : (!is_null($successMessage) ? Alert::getSuccess($successMessage) : '');
    $content = View::render('paciente/auth/login', [
      'status' => $status
    ]);
    return parent::getLayout('Login', $content);
  }

  // Método responsável por realizar o login do paciente a partir de uma sessão temporária
  public static function validLogin($request)
  {
    if (SessionUserLogin::getTempLogin()) SessionUserLogin::dropTempLogin();
    $postVars = $request->getPostVars();

    if (empty($postVars['registro']) or empty($postVars['cpf']) or empty($postVars['dataNascimento']) or empty($postVars['email'])) {
      return self::getAccess($request, 'Todos os campos são obrigatórios.');
    }
    if (!ValidCPF::validCPF($postVars['cpf'])) return self::getAccess($request, 'Por favor, insira um CPF válido.');;
    $paciente = PacienteModel::getPaciente($postVars['registro'], $postVars['dataNascimento'], $postVars['cpf']);
    if (!$paciente) {
      return self::getAccess($request, 'Dados inseridos estão incorretos.');
    }
    $email = $postVars['email'];
    $codigoLogin = substr(strtoupper(md5(uniqid(rand(0, 10), true))), 0, 8);
    $paciente->senha = password_hash($codigoLogin, PASSWORD_DEFAULT);
    $paciente->email = $email;
    SessionUserLogin::tempLogin($paciente, self::tempoExpiracaoSenha);
    $obEmail = new Email;
    setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
    date_default_timezone_set('America/Recife');
    $tempoExpiracao = time() + self::tempoExpiracaoSenha;
    $emailBody =  View::render('mail/mail_body', [
      'content' => View::render('mail/mail_token_paciente', [
        'date' => date('d/m/Y H:i:s', time()),
        'usuario' => $paciente->nome_social ?? $paciente->apelido,
        'senha' => $codigoLogin,
        'url-auth-paciente' => URL . '/paciente/auth',
        'data-validade' => date('d/m/Y H:i:s', $tempoExpiracao),
      ]),
    ]);
    $obEmail->sendEmail($email, 'Central de Serviços - Token de acesso', $emailBody);
    return self::getLogin($request, null, 'Um email foi enviado para você com o token para acessar o sistema.');
  }

  // Método responsável por retornar o conteúdo (view) da página de login
  public static function getLogin($request, $errorMessage = null, $successMessage = null)
  {
    $status = !is_null($errorMessage) ? Alert::getError($errorMessage) : (!is_null($successMessage) ? Alert::getSuccess($successMessage) : '');
    $content = View::render('paciente/auth/auth', [
      'status' => $status
    ]);
    return parent::getLayout('Login', $content);
  }
  // Método responsável por logar o paciente
  public static function setLogin($request)
  {
    $postVars = $request->getPostVars();
    $tempSession = SessionUserLogin::getTempLogin();
    if (!$tempSession) return self::getAccess($request, 'Desculpe, ocorreu um erro na sua solicitação de acesso ao sistema e será necessário reenviar a sua solicitação para poder acessar os sistema.');
    $paciente = $tempSession['paciente'];
    $token = $postVars['token'];
    if (!password_verify($token, $paciente->senha)) return self::getLogin($request, 'Desculpe, a senha inserida é inválida, tente novamente.');
    SessionUserLogin::dropTempLogin();
    $validAcess = array(
      'registro' => $paciente->registro,
      'dataNascimento' => $paciente->data_nascimento,
      'cpf' => $paciente->cpf,
      'email' => $paciente->email,
    );
    $token = AuthApi::generateToken($request, $validAcess);
    SessionUserLogin::login($token);
    $request->getRouter()->redirect('/home/paciente');
  }

  // Método responsável por deslogar o paciente
  public static function setLogout($request)
  {
    SessionUserLogin::logout();
    // Redireciona o usuário para a home do paciente
    $request->getRouter()->redirect('/home/paciente');
  }
}
