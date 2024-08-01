<?php

namespace App\Controller\SisNot;

use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;
use App\Model\SisNot\Notificacao as NotificationModel;
use \App\Session\SisNot\Notificacao as NotificationSession;
use App\Controller\Auth\Alert;
use App\Http\Request;
use App\Model\Entity\Paciente as PatientModel;
use App\Model\SisNot\Incidente as IncidentModel;
use App\Model\SisNot\Setor as UnityModel;
use DateInterval;
use DateTime;
use DateTimeZone;
use App\Model\SisNot\User;
use \App\Model\Utils\Spy;

class Home extends LayoutPage
{
  /**
   * Método responsável por retornar as opções de setores
   * 
   * @return string
   */
  private static function getUnityOptions(): string
  {
    $unities = UnityModel::getSetores();

    $options = '';

    foreach ($unities as $unity) {
      $options .= View::render('utils/option', [
        'id' => $unity->codigo,
        'nome' => $unity->nome,
      ]);
    }

    return $options;
  }

  /**
   * Método responsável por retornar as opções de pacientes para o select
   * 
   * @return string
   */
  private static function getPatientOptions(): string
  {
    $patients = PatientModel::getPatientDischangeByHour(72);

    $options = '';
    foreach ($patients as $patient) {
      $options .= View::render('utils/option', [
        'id' => $patient->registro,
        'nome' => $patient->registro . ' - ' . $patient->nome_completo,
      ]);
    }

    return $options;
  }

  /**
   * Método responsável por retornar a página inicial do sisnot
   * 
   * @param Request $request
   * @param array $errorMessage
   * @return string
   */
  public static function getHome(Request $request, string|null $errorMessage = null): string
  {
    $status = !is_null($errorMessage) ? Alert::getError($errorMessage) : '';
    $content = View::render('sisnot/home', [
      'status' => $status,
      'options' => self::getUnityOptions(),
      'options-patients' => self::getPatientOptions(),
    ]);

    return parent::getPage('SisNot', 'sisnot', $content, $request, true);
  }

  /**
   * Método responsável por criar uma notificação
   * 
   * @param Request $request
   * @return string
   */
  public static function createNotification(Request $request): string
  {
    $postVars = $request->getPostVars();

    if (empty($postVars['data']) || empty($postVars['hora']))
      return self::getHome($request, 'Insira a data e hora do incidente.');

    if (str_word_count($postVars['registro']) > 1)
      return self::getHome($request, 'Insira o registro do paciente.');

    $dataHora = $postVars['data'] . ' ' . $postVars['hora'] . ':00';
    $registroPaciente = $postVars['registro'] ?? null;
    $setorOrigem = $postVars['origem'] ?? null;
    $setorNotificador = $postVars['notificador'] ?? null;

    NotificationSession::createNotificacao(
      array(
        'data_hora' => $dataHora,
        'registro_paciente' => $registroPaciente,
        'setor_incidente' => $setorOrigem,
        'setor_notificador' => $setorNotificador
      )
    );
    $notification = bin2hex(random_bytes(8));
    $request->getRouter()->redirect('/sisnot/notificacao/' . $notification);
  }

  /**
   * Método responsável por retornar a página home do administrador do sistema
   * 
   * @param Request $request
   * @return string
   */
  public static function getAdminHome(Request $request): string
  {
    $content = View::render('sisnot/admin_home');

    // Atualiza o acesso do usuario nesse sistema
    Spy::updateAcess($request->user, 20, 'sisnot');

    return parent::getPage('SisNot', 'sisnot', $content, $request);
  }

  /**
   * Método responsável por retornar as notificações cadastradas
   * 
   * @param array $notifications
   * @return string
   */
  private static function getNotification(array $notifications): string
  {
    if (empty($notifications))
      return '<h5>Não há notificação nesse periodo.</h5>';

    $notificationPage = '';
    $arrayYearCheck = [];

    foreach ($notifications as $notification) {

      $ano = (new DateTime($notification->data_criacao))->format("Y");
      $id_title = $notification->id_notificacao;
      
      if(!$arrayYearCheck[$ano]['check']){
        $lastId = NotificationModel::getLastYearId($ano-1)[0];
        if(empty($lastId)) $arrayYearCheck[$ano]['check'] = false;
        elseif(empty($lastId['lastId'])) $arrayYearCheck[$ano]['check'] = false;
        else{
          $arrayYearCheck[$ano]['check'] = true;
          $arrayYearCheck[$ano]['lastId'] = $lastId['lastId'];
          $id_title -= $lastId['lastId'];
          $lastId = null;
        }
      }
      else{
        $id_title -= $arrayYearCheck[$ano]['lastId'];
      }

      $setorOrigem = !empty($notification->setor_origem) ?
        UnityModel::getSetorByCodigo($notification->setor_origem)->nome : 'Não notificado';
      $setorNotificador = !empty($notification->setor_notificador) ?

        UnityModel::getSetorByCodigo($notification->setor_notificador)->nome : 'Não notificado';
      $incident = IncidentModel::getIncidentById($notification->id_incidente);

      $notificationPage .= View::render('sisnot/notificacao', [
        'id' => $notification->id_notificacao,
        'id-title' => $id_title,
        'data-criacao' => date('d/m/Y H:i:s', strtotime($notification->data_criacao)),
        'registro' => !empty($notification->registro_paciente) ?
          $notification->registro_paciente : 'Não notificado',
        'setor-origem' => $setorOrigem,
        'setor-notificador' => $setorNotificador,
        'id-incidente' => $incident->id,
        'incidente' =>  $incident->descricao,
      ]);
    }

    return $notificationPage;
  }
  /**
   * Método responsável por retornar as opções incidentes para realizar o cadastro de uma notificação
   * 
   * @return string
   */
  private static function getIncidentOptions(): string
  {
    $options = '';
    $incidents = IncidentModel::getActivedIncidentes();
    foreach ($incidents as $incident) {
      $options .= View::render('utils/option', [
        'id' => $incident->id,
        'nome' => $incident->descricao,
        'selected' => '',
      ]);
    }
    return $options;
  }
  /**
   * Método responsável por retornar as notificações dos usuários
   * 
   * @param Request $request
   * @return string
   */
  public static function getNotifications(Request $request): string
  {
    $fortalezaTimeZone = new DateTimeZone(CURRENT_TIMEZONE);
    $dateObject = new DateTime('now', $fortalezaTimeZone);
    $todayDate = $dateObject->format('Y-m-d');
    $dateObject->sub(DateInterval::createFromDateString('6 day'));
    $lastWeekDate = $dateObject->format('Y-m-d');

    $notifications = NotificationModel::getNotificationByDates($lastWeekDate, $todayDate);

    $content = View::render('sisnot/notificacoes', [
      'notificacoes' => self::getNotification($notifications),
      'options' => self::getIncidentOptions(),
    ]);

    return parent::getPage('SisNot', 'sisnot', $content,  $request);
  }

  /**
   * Método responsável pore retornar as notificações pelas datas solicitadas
   * 
   * @param Request $request
   * @return string
   */
  public static function getNotificationsByDate(Request $request): string
  {
    $postVars = $request->getPostVars();
    $notifications = NotificationModel::getNotificationByDates($postVars['firstDate'], $postVars['secondDate']);
    return self::getNotification($notifications);
  }
}