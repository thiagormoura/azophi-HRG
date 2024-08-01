<?php

namespace App\Communication;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class Email
{

  const HOST = 'smtp.gmail.com';
  const CHARSET = 'UTF-8';
  const PORT = 465;
  const SECURE = 'ssl';
  const USER = 'naoresponder@hospitalriogrande.com.br';
  const PASS = '12345hrg';
  const FROM_EMAIL = "naoresponder@hospitalriogrande.com.br"; 
  const FROM_NAME = "Hospital Rio Grande"; 

  private $error;

  public function getError()
  {
    return $this->error;
  }

  public function sendEmail($adresses, $subject, $body, $attachments = [], $ccs = [], $bccs = [])
  {
    $this->error = '';
    $mail = new PHPMailer(true);
    try {
      $mail->isSMTP();
      $mail->Host = self::HOST;
      $mail->SMTPAuth = true;
      $mail->SMTPSecure = 'ssl';
      $mail->SMTPOptions = array(
        'ssl' => array(
          'verify_peer' => false,
          'verify_peer_name' => false,
          'allow_self_signed' => true
        )
      );
      $mail->Port = self::PORT;
      $mail->Username = self::USER;
      $mail->Password = self::PASS;
      $mail->CharSet = self::CHARSET;
      $mail->setFrom(self::FROM_EMAIL, self::FROM_NAME);
      
      $adresses = is_array($adresses) ? $adresses : [$adresses];
      foreach ($adresses as $adress) {
        $mail->addAddress($adress);
      }
      $attachments = is_array($attachments) ? $attachments : [$attachments];
      foreach ($attachments as $attachment) {
        $mail->addAttachment($attachment);
      }
      $ccs = is_array($ccs) ? $ccs : [$ccs];
      foreach ($ccs as $cc) {
        $mail->addCC($cc);
      }
      $bccs = is_array($bccs) ? $bccs : [$bccs];
      foreach ($bccs as $bcc) {
        $mail->addBCC($bcc);
      }
      
      $mail->isHTML(true);
      $mail->Subject = $subject;
      $mail->Body = $body;
      $mail->send();
      return true;
    } catch (PHPMailerException $e) {
      $this->error = $e->getMessage();
      return false;
    }
  }
}
