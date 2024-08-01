<?php

namespace App\Controller\NotSis;

use App\Communication\Email;
use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;
use App\Controller\Utils\Setor;
use App\Model\Notsis\Notificacao as NotificacaoModel;
use \App\Model\Notsis\Pergunta as PerguntaModel;
use \App\Model\Notsis\Resposta as RespostaModel;
use \App\Model\Notsis\Incidente as IncidenteModel;
use \App\Session\NotSis\Notificacao as NotificacaoSession;

class Notificacao extends LayoutPage
{
  const destinatario_email = 'pedrolira@nhc.com.br';
  // Método responsável por retornar os incidentes cadastrados
  private static function getIncidentes($notificacaoUri)
  {
    $incidentePage = '';
    $incidentes = IncidenteModel::getIncidentes();
    foreach ($incidentes as $incidente) {
      $incidentePage .= View::render('notsis/incidente_option', [
        'notificacao-uri' => $notificacaoUri,
        'id-incidente' => $incidente['id_incidente'],
        'title' => $incidente['valor'],
        'color' => 'rgba(110, 110 , 110, 1)',
        'icon' => 'fa-clipboard-list',
        'disabled' => '',
      ]);
    }
    return $incidentePage;
  }

  // Método responsável por retornar a página que contém os incidentes
  public static function getHome($request, $notificacao)
  {
    $notificacaoUri = URL . $request->getRouter()->getUri();
    $content = View::render('notsis/incidente', [
      'incidentes' => self::getIncidentes($notificacaoUri)
    ]);

    return parent::getPage('NotSis - Incidentes', $content, 'notsis', $request);
  }
  // Método responsável por retornar os radios que compõem uma pergunta
  private static function getRadioComponent($respostas, $isInfo)
  {
    $hasText = array_keys(array_column($respostas, 'TPdado'), 'TXT');
    $keysRB = array_keys(array_column($respostas, 'TPdado'), 'RB');
    $options = '';
    $info = $isInfo ? 'info-' : '';
    foreach ($keysRB as $key) {
      $options .= View::render('notsis/componentes/radio_option', [
        'id' => $info . $respostas[$key]['id_resposta'],
        'name' => $info . $respostas[$key]['id_pergunta'],
        'resposta' => $respostas[$key]['valor'],
        'value' => $respostas[$key]['id_resposta'],
        'checked' => $respostas[$key]['respondido'] ? 'checked' : '',
        'disabled' => isset($respostas[$key]['respondido']) ? 'disabled' : '',
      ]);
    }
    if (!empty($hasText)) {
      $options .= self::getAdicionalText('adicional_text', $respostas, $hasText, $info);
    }
    return View::render('notsis/componentes/radio', [
      'options' => $options
    ]);
  }
  // Método responsável por retornar os checkboxes que compõem uma pergunta
  private static function getCheckboxComponent($respostas, $isInfo)
  {
    $hasText = array_keys(array_column($respostas, 'TPdado'), 'TXT');
    $keysRB = array_keys(array_column($respostas, 'TPdado'), 'CB');
    $options = '';
    $info = $isInfo ? 'info-' : '';
    foreach ($keysRB as $key) {
      $options .= View::render('notsis/componentes/checkbox_option', [
        'id' => $info . $respostas[$key]['id_resposta'],
        'name' => $info . $respostas[$key]['id_pergunta'],
        'resposta' => $respostas[$key]['valor'],
        'value' => $respostas[$key]['id_resposta'],
        'checked' => $respostas[$key]['respondido'] ? 'checked' : '',
        'disabled' => isset($respostas[$key]['respondido']) ? 'disabled' : '',
      ]);
    }
    if (!empty($hasText)) {
      $options .= self::getAdicionalText('adicional_text_checkbox', $respostas, $hasText, $info);
    }
    return View::render('notsis/componentes/checkbox', [
      'options' => $options
    ]);
  }

  // Método responsável por retornar os textos adicionais de uma pergunta
  private static function getAdicionalText($typeText, $respostas, $texts, $info)
  {
    $options = '';
    foreach ($texts as $key) {
      $isAnswered = isset($respostas[$key]['respondido']) ? $respostas[$key]['valor'] : '';
      $options .= View::render('notsis/componentes/' . $typeText, [
        'id' => $info . $respostas[$key]['id_resposta'],
        'name' => $info . $respostas[$key]['id_pergunta'],
        'resposta-holder' => isset($respostas[$key]['respondido']) ? '' : $respostas[$key]['valor'],
        'value' => $respostas[$key]['id_resposta'],
        'checked' => $respostas[$key]['respondido'] ? 'checked' : '',
        'disabled' => isset($respostas[$key]['respondido']) ? 'disabled' : '',
        'height' => isset($respostas[$key]['respondido']) ? '25' : '22',
        'resposta' => $respostas[$key]['respondido'] ? $respostas[$key]['valor'] . ': ' . $respostas[$key]['resposta'] : $isAnswered,
      ]);
    }
    return $options;
  }
  // Método responsável por retornar os componentes de texto que compõem uma pergunta
  private static function getTextComponent($respostas, $isInfo)
  {
    $keysTxt = array_keys(array_column($respostas, 'TPdado'), 'TXT');
    $textElements = '';
    $info = $isInfo ? 'info-' : '';
    foreach ($keysTxt as $key) {
      $textElements .= View::render('notsis/componentes/text', [
        'id' => $info . $respostas[$key]['id_resposta'],
        'name' => $info . $respostas[$key]['id_pergunta'],
        'label' => !empty($respostas[$key]['valor']) ? View::render('notsis/componentes/label', [
          'id' => $info . $respostas[$key]['id_resposta'],
          'resposta-holder' => $respostas[$key]['valor']
        ]) : '',
        'value' => $respostas[$key]['id_resposta'],
        'disabled' => isset($respostas[$key]['respondido']) ? 'disabled' : '',
        'resposta' => $respostas[$key]['respondido'] ? $respostas[$key]['resposta'] : '',
      ]);
    }
    return $textElements;
  }
  // Método responsável por retornar os textarea que compõem uma pergunta
  private static function getTextAreaComponent($respostas, $isInfo)
  {
    $keysTxt = array_keys(array_column($respostas, 'TPdado'), 'TEXT');
    $textElements = '';
    $info = $isInfo ? 'info-' : '';
    foreach ($keysTxt as $key) {
      $textElements .= View::render('notsis/componentes/textarea', [
        'id' => $info . $respostas[$key]['id_resposta'],
        'name' => $info . $respostas[$key]['id_pergunta'],
        'label' => !empty($respostas[$key]['valor']) ? View::render('notsis/componentes/label', [
          'id' => $info . $respostas[$key]['id_resposta'],
          'resposta-holder' => $respostas[$key]['valor']
        ]) : '',
        'value' => $respostas[$key]['id_resposta'],
        'disabled' => isset($respostas[$key]['respondido']) ? 'disabled' : '',
        'resposta' => $respostas[$key]['respondido'] ? $respostas[$key]['resposta'] : '',
      ]);
    }
    return $textElements;
  }
  // Método responsável por retornar o select que compõe uma pergunta
  private static function getSelectComponent($respostas, $isInfo)
  {
    $hasText = array_keys(array_column($respostas, 'TPdado'), 'TXT');
    $keysRB = array_keys(array_column($respostas, 'TPdado'), 'SL');
    $options = '';
    $info = $isInfo ? 'info-' : '';
    foreach ($keysRB as $key) {
      $options .= View::render('notsis/componentes/select_option', [
        'nome' => $respostas[$key]['valor'],
        'value' => $respostas[$key]['id_resposta'],
        'selected' => $respostas[$key]['respondido'] ? 'selected' : '',
      ]);
    }
    if (!empty($hasText)) {
      foreach ($hasText as $key) {
        $options .= View::render('notsis/componentes/select_option', [
          'nome' => $respostas[$key]['resposta'],
          'value' => $respostas[$key]['id_resposta'],
          'selected' => $respostas[$key]['respondido'] ? 'selected' : '',
        ]);
      }
    }
    return View::render('notsis/componentes/select', [
      'id' => $info . $respostas[0]['id_resposta'],
      'name' => $info . $respostas[0]['id_pergunta'],
      'disabled' => isset($respostas[0]['respondido']) ? 'disabled' : '',
      'options' => $options,
    ]);
  }
  // Método responsável por retornar as respostas de um pergunta
  private static function getRespostas($pergunta, $notificacao, $isInfo = false)
  {
    if ($notificacao == null) {
      if (!$isInfo) {
        $respostas =  RespostaModel::getRespostasByPergunta($pergunta['id_pergunta']);
      } else {
        $respostas = RespostaModel::getInfoRespostasByPergunta($pergunta['id_pergunta']);
      }
    } else {
      if (!$isInfo) {
        $respostas = RespostaModel::getRespostasAndValues($notificacao['id_notificacao'], $pergunta['id_pergunta']);
      } else {
        $respostas = RespostaModel::getInfoRespostasAndValues($notificacao['id_notificacao'], $pergunta['id_pergunta']);
      }
    }
    $contentResposta = View::render('notsis/componentes/pergunta', [
      'pergunta' => $pergunta['descricao']
    ]);
    switch ($pergunta['TPdado']) {
      case 'RB':
        $contentResposta .= self::getRadioComponent($respostas, $isInfo);
        break;
      case 'CB':
        $contentResposta .= self::getCheckboxComponent($respostas, $isInfo);
        break;
      case 'TXT':
        $contentResposta .= self::getTextComponent($respostas, $isInfo);
        break;
      case 'TEXT':
        $contentResposta .= self::getTextAreaComponent($respostas, $isInfo);
        break;
      case 'SL':
        $contentResposta .= self::getSelectComponent($respostas, $isInfo);
        break;
    }
    return $contentResposta;
  }

  // Método responsável por retornar as perguntas de um determinado incidente
  private static function getPerguntas($incidente = null, $notificacao = null)
  {
    $perguntas = $incidente != null ? PerguntaModel::getPerguntasByIncidente($incidente) : NotificacaoModel::getInfoIncidentePerguntas();
    $formPage = '';
    $isInfo = $incidente != null ? false : true;
    foreach ($perguntas as $pergunta) {
      $formPage .= self::getRespostas($pergunta, $notificacao, $isInfo);
    }
    return $formPage;
  }
  // Método responsável por reotrnar as informações padrão de um determinado incidente (preenchida ou não)
  private static function getInfoIncidente($notificacao = null)
  {
    return View::render('notsis/componentes/topico', [
      'title' => "Descreva detalhes do incidente",
      'conteudo' => self::getPerguntas(null, $notificacao)
    ]);
  }

  // Método responsável por retornar a página de incidentes já com as perguntas e respostas
  private static function getFormularioPage($incidente, $notificacao = null)
  {
    return View::render('notsis/componentes/topico', [
      'title' => $incidente['valor'],
      'conteudo' => self::getPerguntas($incidente['id_incidente'], $notificacao)
    ]);
  }

  // Método responsável por retornar o formulário preenchido ou não
  public static function getFormulario($request, $notificacao, $incidente = null, $preenchido = false)
  {
    $title = '';
    if ($preenchido === false) {
      $incidente = IncidenteModel::getIncidenteById($incidente);
      $title = $incidente['valor'];
      $content = View::render('notsis/formulario', [
        'title' => $incidente['valor'],
        'notificacao' => 'notificacao/' . $notificacao,
        'info-notificacao' => '',
        'info-incidente-form' => View::render('notsis/info_incidente', [
          'hide' => 'info-incidente-hide',
          'info-incidente' => $incidente['id_incidente'] === 1 ? '' : self::getInfoIncidente(),
          'button-send' => View::render('notsis/button_send'),
        ]),
        'incidente' => $incidente['id_incidente'] === 1 ? self::getInfoIncidente() : self::getFormularioPage($incidente),
        'button-next' => View::render('notsis/button_next'),
      ]);
    } else {
      $notificacao = NotificacaoModel::getNotificacaoById($notificacao);
      $title = 'Notificação ' . $notificacao['id_notificacao'];
      $incidentes = NotificacaoModel::getIncidentesByNotificacao($notificacao['id_notificacao']);
      $dataHora = explode(' ', date('d/m/Y H:i:s', strtotime($notificacao['data_hora'])));
      $incidentePage = '';
      foreach ($incidentes as $incidente) {
        $incidentePage .= self::getFormularioPage($incidente, $notificacao);
      }
      $setorIncidente = !empty($notificacao['setor_incidente']) ? Setor::getSetorByCode($notificacao['setor_incidente']) : 'Não notificado';
      $setorNotificador = !empty($notificacao['setor_notificador']) ? Setor::getSetorByCode($notificacao['setor_notificador']) : 'Não notificado';
      $content = View::render('notsis/formulario', [
        'title' => 'Notificação ' . $notificacao['id_notificacao'],
        'notificacao' => 'notificacoes',
        'info-notificacao' => View::render('notsis/info_notificacao', [
          'data' => $dataHora[0],
          'horario' => $dataHora[1],
          'registro' => empty($notificacao['registro_paciente']) ? 'Não notificado' : $notificacao['registro_paciente'],
          'origem' => $setorIncidente,
          'notificador' => $setorNotificador,
        ]),
        'info-incidente-form' => View::render('notsis/info_incidente', [
          'hide' => '',
          'info-incidente' => self::getInfoIncidente($notificacao),
          'button-send' => '',
        ]),
        'incidente' => $incidentePage,
        'button-next' => '',
      ]);
    }
    return parent::getPage('Notsis - ' . $title, $content, 'notsis', $request);
  }

  // Método responsável por inserir respostas a uma determinada notificação
  private static function insertRespostaNotificacao($idNotificacao, $idIncidente, $pergunta, $resposta)
  {
    $value = null;
    if (!intval($resposta)) {
      $value = empty($resposta) ? 'Não informado' : $resposta;
      $respostas = RespostaModel::getRespostaByTypeAndPergunta($pergunta, "'TXT', 'TEXT'");
      foreach ($respostas as $respostaNotificacao) {
        $isAnswered = NotificacaoModel::checkIsAnswered($idNotificacao, $pergunta, $respostaNotificacao['id_resposta']);
        if ($isAnswered) continue;
        $resposta = $respostaNotificacao['id_resposta'];
        break;
      }
    }
    $respostaHasEmpty = empty(RespostaModel::getPerguntaByResposta($resposta));
    if (!$resposta or ($resposta and !$respostaHasEmpty)) NotificacaoModel::insertNotificacaoResposta($idNotificacao, $idIncidente, $pergunta, $resposta, $value);
  }
  // Método responsável por inserir respostas a uma determinada info de uma notificação
  private static function insertInfoIncidenteResposta($idNotificacao, $pergunta, $resposta)
  {
    $value = null;
    if (!intval($resposta)) {
      $value = empty($resposta) ? 'Não informado' : $resposta;
      $respostas = RespostaModel::getInfoRespostaByTypeAndPergunta($pergunta, "'TXT', 'TEXT'");
      foreach ($respostas as $respostaNotificacao) {
        $isAnswered = NotificacaoModel::checkInfoAnswered($idNotificacao, $pergunta, $respostaNotificacao['id_resposta']);
        if ($isAnswered) continue;
        $resposta = $respostaNotificacao['id_resposta'];
        break;
      }
    }
    $respostaHasEmpty = empty(RespostaModel::getInfoPerguntaByResposta($resposta));
    if (!$resposta or ($resposta and !$respostaHasEmpty)) NotificacaoModel::insertInfoResposta($idNotificacao, $pergunta, $resposta, $value);
  }
  // Método responsável por definir as respostas de um determinado formulário
  public static function setFormulario($request, $idNotificacao, $idIncidente)
  {
    $postVars = $request->getPostVars();
    if (empty($postVars)) return array(
      'success' => false,
      'message' => 'Desculpe, mas você não pode enviar um formulário vázio.'
    );
    setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
    date_default_timezone_set('America/Recife');
    $contentSession = NotificacaoSession::getNotificacaoContent();
    NotificacaoSession::dropNotificacao();
    $idNotificacao = NotificacaoModel::createNotificacao(
      date('Y-m-d H:i:s', strtotime($contentSession['data_hora'])), 
      $contentSession['registro_paciente'], 
      $contentSession['setor_incidente'], 
      $contentSession['setor_notificador']);
    foreach ($postVars as $pergunta => $resposta) {
      // check if question are info
      if (strpos($pergunta, 'info') !== false) {
        if (is_array($resposta)) {
          foreach ($resposta as $opcao) {
            self::insertInfoIncidenteResposta($idNotificacao, str_replace('info-', '', $pergunta), $opcao);
          }
          continue;
        }
        self::insertInfoIncidenteResposta($idNotificacao, str_replace('info-', '', $pergunta), $resposta);
        continue;
      }
      if (is_array($resposta)) {
        foreach ($resposta as $opcao) {
          self::insertRespostaNotificacao($idNotificacao, $idIncidente, $pergunta, $opcao);
        }
        continue;
      }
      self::insertRespostaNotificacao($idNotificacao, $idIncidente, $pergunta, $resposta);
    }
    $email = new Email;
    $mailBody = View::render('mail/mail_body', [
      'assunto' => 'NotSis - Novo incidente notificado.',
      'content' => View::render('mail/mail_nova_notificacao', [
        'date' => date('d/m/Y H:i:s'),
        'url-nova-notificacao' => URL . '/notsis/notificacoes/' . $idNotificacao,
      ]),
    ]);
    $email->sendEmail(self::destinatario_email, 'NotSis - Novo incidente notificado.', $mailBody);
    return array(
      'success' => true,
      'message' => 'Notificação registrada com sucesso, em instantes iremos redirecionar você.',
      'redirect' => URL . '/notsis',
    );
  }
}
