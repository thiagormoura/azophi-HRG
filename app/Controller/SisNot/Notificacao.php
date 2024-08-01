<?php

namespace App\Controller\SisNot;

use App\Communication\Email;
use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;
use App\Controller\Utils\Setor as SetorModel;
use App\Http\Request;
use App\Model\Entity\Paciente;
use App\Model\SisNot\Incidente;
use App\Model\SisNot\Notificacao as NotificacaoModel;
use \App\Model\SisNot\Pergunta as PerguntaModel;
use \App\Model\SisNot\Resposta as RespostaModel;
use App\Session\SisNot\Notificacao as NotificationSession;

class Notificacao extends LayoutPage

{
  /**
   * Método responsável por buscar as subperguntas de uma determinada resposta
   * @param RespostaModel $answer
   * @param int|null $notificationId
   * @param bool $isAnswered
   * @return array 
   */
  private static function getSubquestions(RespostaModel $answer, int|null $notificationId, bool $isAnswered): array
  {
    $questions = PerguntaModel::getQuestionsByAnswer($answer->id);
    return $questions = self::getAnswersAndSubquestions($questions, $notificationId, $isAnswered);
  }

  /**
   * Método responsável por filtrar todas as respostas e encontrar suas respectivas perguntas
   * @param array $questions
   * @param int|null $notificationId
   * @param bool $isAnswered
   * @return array
   */
  private static function getAnswersAndSubquestions(array $questions, int|null $notificationId = null, bool $isAnswered = false): array
  {
    foreach ($questions as $question) {

      $answers = $isAnswered ?
        RespostaModel::getAnswersNotificationByIdQuestion($notificationId, $question->id) :
        RespostaModel::getAnswersByQuestion($question->id);

      array_filter($answers, function ($answer) use (&$question, $isAnswered, $notificationId) {

        if ($answer->possui_subpergunta == true)
          $answer->subperguntas = self::getSubquestions($answer, $notificationId, $isAnswered);

        $question->respostas[] = $answer;
      });
    }

    return $questions;
  }

  /**
   * Método responsável por buscar os componentes radio button de uma determinada pergunta
   * @param array $answers
   * @return string 
   */
  private static function getAnswerRadio(array $answers): string
  {
    $options = '';

    foreach ($answers as $answer) {
      if ($answer->tipo !== 'radio')
        continue;

      $options .= View::render('sisnot/componentes/radio_option', [
        'id' => $answer->id,
        'id-resposta' => $answer->id,
        'id-pergunta' => $answer->id_pergunta,
        'name' => $answer->id_pergunta,
        'required' => $answer->obrigatorio ? 'required' : '',
        'checked' => $answer->respondido == true ? 'checked' : '',
        'disabled' => isset($answer->respondido) ? 'disabled' : '',
        'label' => $answer->valor,
        'value' => $answer->id,
      ]);
      if (isset($answer->subperguntas))
        $options .= self::getSubquestionsComponent($answer->id, $answer->subperguntas, (bool) $answer->respondido);
    }

    $answersOther = self::getAnswerOther($answers, 'radio');

    if ($answersOther !== false)
      $options .= $answersOther;


    return $options;
  }


  /**
   * Método responsável por buscar os componentes checkbox de uma determinada pergunta
   * @param array $answers
   * @return string 
   */
  private static function getAnswerCheckbox(array $answers): string
  {
    $options = '';

    foreach ($answers as $answer) {
      if ($answer->tipo !== 'checkbox')
        continue;

      $options .= View::render('sisnot/componentes/checkbox_option', [
        'id' => $answer->id,
        'id-pergunta' => $answer->id_pergunta,
        'name' => $answer->id_pergunta,
        'required' => $answer->obrigatorio ? 'required' : '',
        'checked' => $answer->respondido == true ? 'checked' : '',
        'disabled' => isset($answer->respondido) ? 'disabled' : '',
        'label' => $answer->valor,
        'value' => $answer->id,
      ]);

      if (isset($answer->subperguntas))
        $options .= self::getSubquestionsComponent($answer->id, $answer->subperguntas, isset($answer->respondido));
    }

    $answersOther = self::getAnswerOther($answers, 'checkbox');

    if ($answersOther !== false)
      $options .= $answersOther;

    return $options;
  }

  /**
   * Método responsável por buscar os componentes outro de determinadas respostas
   * e retornar o componente outro caso exista
   * 
   * @param array $answers
   * @param string $type
   * @return string 
   */
  private static function getAnswerOther(array $answers, string $type): string|bool
  {
    $answersOther = array_filter($answers, function ($answer) {
      return $answer->tipo == 'outro';
    });

    $answersOther = array_values($answersOther);

    if (count($answersOther) === 0)
      return false;

    $options = '';
    foreach ($answersOther as $answer) {
      if ($answer->tipo !== 'outro')
        continue;

      $options .= View::render('sisnot/componentes/adicional_text_' . $type, [
        'id' => $answer->id,
        'id-pergunta' => $answer->id_pergunta,
        'resposta' => isset($answer->resposta) ? $answer->resposta : '',
        'name' => $answer->id_pergunta,
        'required' => $answer->obrigatorio ? 'required' : '',
        'checked' => $answer->respondido == true ? 'checked' : '',
        'disabled' => isset($answer->respondido) ? 'disabled' : '',
        'label' => $answer->valor,
        'value' => $answer->id,
      ]);
    }

    return $options;
  }

  /**
   * Método responsável por buscar os componentes de texto de uma determinada pergunta
   * @param array $answers
   * @return string 
   */
  private static function getAnswerText(array $answers): string
  {
    $options = '';
    foreach ($answers as $answer) {
      $options .= View::render('sisnot/componentes/text_option', [
        'id' => $answer->id,
        'id-pergunta' => $answer->id_pergunta,
        'name' => $answer->id_pergunta,
        'required' => $answer->obrigatorio ? 'required' : '',
        'disabled' => isset($answer->respondido) ? 'disabled' : '',
        'resposta' => isset($answer->resposta) ? $answer->resposta : '',
        'label' => $answer->valor,
      ]);
    }

    return $options;
  }

  /**
   * Método responsável por buscar os componentes de texto de uma determinada pergunta
   * @param array $answers
   * @return string 
   */
  private static function getAnswerSelect(array $answers): string
  {
    $options = '';

    $firstElement = $answers[0];

    if (isset($firstElement->respondido)) {
      $correctAnswer = array_filter($answers, function ($answer) {
        if ($answer->respondido == true)
          return $answer;
      });
      $correctAnswer = array_values($correctAnswer);
      $correctAnswer = $correctAnswer[0];

      $answers = array_filter($answers, function ($answer) use ($correctAnswer) {
        if ($answer->id == $correctAnswer->resposta)
          return $answer;
      });
    }

    foreach ($answers as $answer) {
      $options .= View::render('sisnot/componentes/select_option', [
        'label' => $answer->valor,
        'value' => $answer->id,
        'selected' => $answer->respondido == true ? 'selected' : '',
      ]);
    }

    return View::render('sisnot/componentes/select', [
      'id' => $firstElement->id,
      'id-pergunta' => $firstElement->id_pergunta,
      'disabled' => isset($firstElement->respondido) ? 'disabled' : '',
      'name' => $firstElement->id_pergunta,
      'required' => $answer->obrigatorio ? 'required' : '',
      'options' => $options
    ]);
  }

  /**
   * Método responsável por buscar os componentes de texto de uma determinada pergunta
   * @param array $answers
   * @return string 
   */
  private static function getAnswerTextarea(array $answers): string
  {
    $options = '';
    foreach ($answers as $answer) {
      $options = View::render('sisnot/componentes/textarea_option', [
        'id' => $answer->id,
        'id-pergunta' => $answer->id_pergunta,
        'name' => $answer->id_pergunta,
        'required' => $answer->obrigatorio ? 'required' : '',
        'disabled' => isset($answer->respondido) ? 'disabled' : '',
        'resposta' => isset($answer->resposta) ? $answer->resposta : '',
        'label' => $answer->valor,
      ]);
    }
    return $options;
  }

  /**
   * Método responsável por buscar a subpergunta de uma determinada resposta
   * @param int $answerId
   * @param array $subquestions
   * @param bool $isAnswered
   * @return string 
   */
  private static function getSubquestionsComponent(int $answerId, array $subquestions, bool $isAnswered): string
  {
    $component = '';
    foreach ($subquestions as $subquestion) {
      $component .= self::getQuestionComponent($subquestion);
    }

    return View::render('sisnot/componentes/container_subpergunta', [
      'id-resposta' => $answerId,
      'exibir' => $isAnswered ? 'd-block' : 'd-none',
      'component' => $component,
    ]);
  }

  /**
   * Método responsável por returnar as opções de respostas
   * @param array|RespostaModel $answers
   * @param string $type
   * @return string 
   */
  private static function getAnswersComponents(array $answers, string $type): string
  {
    $component = '';
    switch ($type) {
      case 'checkbox':
        $component = self::getAnswerCheckbox($answers);
        break;
      case 'radio':
        $component = self::getAnswerRadio($answers);
        break;
      case 'text':
        $component = self::getAnswerText($answers);
        break;
      case 'textarea':
        $component = self::getAnswerTextarea($answers);
        break;
      case 'select':
        $component = self::getAnswerSelect($answers);
        break;
    }
    return View::render('sisnot/componentes/resposta_container', [
      'component' => $component,
    ]);
  }

  /**
   * Método responsável por retornar o componente de pergunta
   * @param PerguntaModel $question
   * @return string 
   */
  private static function getQuestionComponent(PerguntaModel $question): string
  {
    $component = View::render('sisnot/componentes/pergunta', [
      'pergunta' => $question->descricao
    ]);

    $component .= self::getAnswersComponents($question->respostas, $question->tipo);

    return $component;
  }

  /**
   * Método responsável por todas as perguntas com suas respectivas respostas e subperguntas
   * @param array $questions
   * @param string $title
   * @param bool $isOpen
   * @return string
   */
  private static function getIncidentForm(array $questions, string $title, bool $isOpen = false): string
  {
    $content = '';

    foreach ($questions as $question) {
      $content .= View::render('sisnot/componentes/container_resposta', [
        'options' => self::getQuestionComponent($question),
      ]);
    }

    return View::render('sisnot/incidente_formulario', [
      'titulo' => $title,
      'conteudo' => $content,
      'botao-avancar' => $isOpen ? '' : View::render('sisnot/componentes/botao_avancar'),
    ]);
  }

  /**
   * Método responsável por todas as perguntas sobre os detalhes do incidente
   * com suas respectivas respostas e subperguntas
   * 
   * @param array $questions
   * @param bool $isDisplaying
   * @param bool $enableButton
   * @return string
   */
  private static function getDetailsForm(array $questions, bool $isDisplaying = false, bool $enableButton = true): string
  {
    $content = '';

    foreach ($questions as $question) {
      $content .= View::render('sisnot/componentes/container_resposta', [
        'options' => self::getQuestionComponent($question),
      ]);
    }

    return View::render('sisnot/incidente_detalhe_formulario', [
      'exibir' => $isDisplaying ? '' : 'info-incidente-hide',
      'conteudo' => $content,
      'botao-finalizar' => $enableButton ? View::render('sisnot/componentes/botao_finalizar') : '',
    ]);
  }

  /**
   * Método responsável por retornar o formulário não respondido
   * @param Request $request
   * @param string $notification
   * @param string $incident
   * @return string 
   */
  public static function getEmptyForm(Request $request, string $notificationId, string $incidentId): string
  {
    $incident = Incidente::getIncidentById($incidentId);

    $incidentForm = '';
    $incidentQuestions = PerguntaModel::getQuestionsByIncident($incident->id);
    if (!empty($incidentQuestions)) {
      $incidentQuestions = self::getAnswersAndSubquestions($incidentQuestions);
      $incidentForm = self::getIncidentForm($incidentQuestions, $incident->descricao);
    }

    $detailsQuestions = PerguntaModel::getDetailsQuestions();

    $detailsQuestions = self::getAnswersAndSubquestions($detailsQuestions);

    $detailsForm = empty($incidentQuestions) ?
      self::getDetailsForm($detailsQuestions, true) : self::getDetailsForm($detailsQuestions);

    $content = View::render('sisnot/incidente_page', [
      'notificacao' => 'notificacao/' . $notificationId,
      'title' => $incident->descricao,
      'forms' => $incidentForm . $detailsForm,
    ]);

    return parent::getPage('Notificação', 'sisnot', $content, $request, true);
  }

  /**
   * Método responsável por retornar o formulário respondido
   * @param Request $request
   * @param string $notification
   * @param string $incident
   * @return string 
   */
  public static function getFilledForm(Request $request, string $notificationId): string
  {
    $notification = NotificacaoModel::getNotificationById($notificationId);
    $fortalezaTimeZone = new \DateTimeZone(CURRENT_TIMEZONE);
    $dateNotification = new \DateTime($notification->data_hora, $fortalezaTimeZone);
    $patient = Paciente::getPacienteByRegister($notification->registro_paciente);
    $origemSector = SetorModel::getSetorByCode($notification->setor_origem);
    $notificaterSector = SetorModel::getSetorByCode($notification->setor_notificador);
    $headerIncident = View::render('sisnot/cabecalho_incidente', [
      'data' => $dateNotification->format('d/m/Y'),
      'horario' => $dateNotification->format('H:i'),
      'registro' => $patient->registro ?
        $patient->registro . ' - ' . $patient->nome_completo : $notification->registro_paciente,
      'origem' => $origemSector,
      'notificador' => $notificaterSector,
    ]);

    $incident = Incidente::getIncidentById($notification->id_incidente);

    $incidentQuestions = PerguntaModel::getQuestionsByIncident($incident->id);
    $incidentQuestions = self::getAnswersAndSubquestions($incidentQuestions, $notificationId, true);
    $incidenteForm = self::getIncidentForm($incidentQuestions, $incident->descricao, true);

    $detailsQuestions = PerguntaModel::getDetailsQuestions();
    $detailsQuestions = self::getAnswersAndSubquestions($detailsQuestions, $notificationId, true);
    $detailsForm = self::getDetailsForm($detailsQuestions, true, false);

    $content = View::render('sisnot/incidente_page', [
      'notificacao' => 'notificacoes',
      'title' => $incident->descricao,
      'forms' => $headerIncident . $incidenteForm . $detailsForm,
    ]);

    return parent::getPage('Notificação', 'sisnot', $content, $request);
  }

  /**
   * Método responsável por registrar o formulário
   * @param Request $request
   * @param string $notification
   * @param string $incident
   * @return string 
   */
  public static function setForm(Request $request, string $notificationId, string $incidentId): array
  {
    $postVars = $request->getPostVars();

    if (empty($postVars))
      return array(
        'success' => false,
        'message' => 'Desculpe, mas é necessário preencher todos os campos obrigatórios.'
      );

    $notificationContent = NotificationSession::getNotificacaoContent();
    NotificationSession::dropNotificacao();

    $fortalezaTimeZone = new \DateTimeZone(CURRENT_TIMEZONE);
    $notificationDate = new \DateTime($notificationContent['data_hora'], $fortalezaTimeZone);

    $notification = new NotificacaoModel();
    $notification->id_incidente = $incidentId;
    $notification->data_hora = $notificationDate->format('Y-m-d H:i:s');
    $notification->registro_paciente = $notificationContent['registro_paciente'];
    $notification->setor_origem = $notificationContent['setor_incidente'];
    $notification->setor_notificador = $notificationContent['setor_notificador'];
    $notification->create();

    foreach ($postVars as $question) {
      NotificacaoModel::createNotificationAnswer($notification->id, $question['id'], $question['answer'], $question['value']);
    }

    $email = new Email;
    $mailBody = View::render('mail/mail_body', [
      'assunto' => 'SisNot - Novo incidente notificado.',
      'content' => View::render('mail/mail_nova_notificacao', [
        'date' => date('d/m/Y H:i:s'),
        'url-nova-notificacao' => URL . '/sisnot/notificacoes/' . $notification->id,
      ]),
    ]);

    $email->sendEmail(SISNOT_EMAIL, 'SisNot - Novo incidente notificado.', $mailBody);

    return array(
      'success' => true,
      'message' => 'Notificação registrada com sucesso, em instantes iremos redirecionar você.',
      'redirect' => URL . '/sisnot',
    );
  }

  /**
   * Método responsável por retornar as opções de incidentes
   * @param Request $request
   * @param string $notification
   * @param string $incident
   * @return string 
   */
  private static function getIncidentOptions(string $notificationUri): string
  {
    $incidentPage = '';
    $incidents = Incidente::getActivedIncidentes();
    foreach ($incidents as $incident) {
      $incidentPage .= View::render('sisnot/incidente_option', [
        'notificacao-uri' => $notificationUri,
        'id-incidente' => $incident->id,
        'title' => $incident->descricao,
        'icon' => 'fa-clipboard-list',
        'disabled' => '',
      ]);
    }
    return $incidentPage;
  }

  /**
   * Método responsável por os incidentes para o usuário selecionar
   * @param Request $request
   * @param string $notification
   * @return string 
   */
  public static function getIncidents(Request $request, string $notification): string
  {
    $notificationUri = URL . $request->getRouter()->getUri();

    $content = View::render('sisnot/incidente', [
      'incidentes' => self::getIncidentOptions($notificationUri)
    ]);

    return parent::getPage('SisNot - Incidentes', 'sisnot', $content, $request);
  }
}
