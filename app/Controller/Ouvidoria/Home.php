<?php

namespace App\Controller\Ouvidoria;

use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;
use \App\Communication\Email;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Home extends LayoutPage
{
  const ouvidoria_email = 'npti@nhc.com.br';
  // Método responsável por retornar o modal da auditoria
  public static function getModal($request)
  {
    return View::render('paciente/ouvidoria/modal');
  }
  
  // Método responsável por enviar o email para a ouvidoria
  public static function sendMessage($request)
  {
    $postVars = $request->getPostVars();
    $bodyEmail = View::render('mail/mail_body', [
      'assunto' => 'SisNot - Novo relato cadastrado.',
      'content' => View::render('mail/mail_novo_relato', [
        'date' => date('d/m/Y H:i:s'),
        'usuario' => $postVars['nome'],
        'email' => $postVars['email'],
        'contato' => $postVars['contato'],
        'mensagem' => $postVars['mensagem'],
      ]),
    ]);
    $bodyEmailUsuario = View::render('mail/mail_body', [
      'assunto' => 'SisNot - Novo relato cadastrado.',
      'content' => View::render('mail/mail_novo_relato_usuario', [
        'date' => date('d/m/Y H:i:s'),
        'usuario' => $postVars['nome'],
        'email' => $postVars['email'],
        'contato' => $postVars['contato'],
        'mensagem' => $postVars['mensagem'],
      ]),
    ]);
    $email = new Email;
    $email->sendEmail(self::ouvidoria_email, 'Ouvidoria - Novo relato', $bodyEmail);
    $email->sendEmail($postVars['email'], 'Central de Serviços - Relato enviado com sucesso!', $bodyEmailUsuario);
  }
}
