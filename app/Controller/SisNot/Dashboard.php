<?php

namespace App\Controller\SisNot;

use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;
use App\Http\Request;
use App\Model\SisNot\Incidente as IncidentModel;
use App\Model\SisNot\Notificacao as NotificationModel;
use App\Model\Utils\Setor as UnityModel;
use DateTime;
use DateTimeZone;
use IntlDateFormatter;

class Dashboard extends LayoutPage
{
  /**
   * Método responsável por retornar um array de notificações, o qual o index é seu id
   * 
   * @param array $notifications
   * @return array
   */
  private static function getNotifications(array $notifications): array
  {
    $notificationResponse = array();
    foreach ($notifications as $notification) {
      $notificationResponse[$notification->id] = $notification;
    }
    return $notificationResponse;
  }

  /**
   * Método responsável por retornar a quantidade de notificações por incidente
   * 
   * @param array $notifications
   * @return int
   */
  private static function getTotalIncidents(array $notifications): int
  {
    $total = array();
    foreach ($notifications as $notification) {
      $total[$notification->id_incidente . $notification->id]++;
    }
    return count($total);
  }
  /**
   * Método responsável por retornar o total de notificações que foram respondidas
   * 
   * @param array $notifications
   * @return array
   */
  private static function getTotalNotifications(array $notifications): int
  {
    $total = array();
    foreach ($notifications as $notification) {
      $total[$notification->id]++;
    }
    return count($total);
  }
  /**
   * Método responsável por gerar parte o cabeçalho da página de dashboard
   * 
   * @param Request $request
   * @return string
   */
  public static function getDashboard(Request $request): string
  {
    $postVars = $request->getPostVars();

    if (empty($postVars['incidentes'])) {
      $notifications = NotificationModel::getNotificationByDates($postVars['dataInicio'], $postVars['dataFim']);
    } else {
      $notifications = NotificationModel::getNotificationByDatesAndIncidents(
        $postVars['dataInicio'],
        $postVars['dataFim'],
        $postVars['incidentes']
      );
    }

    return View::render('sisnot/dashboard/notificacoes', [
      'total-notificacoes' => self::getTotalNotifications($notifications),
      'total-incidentes' => self::getTotalIncidents($notifications),
      'graficos' => !empty($notifications) ?
        View::render('sisnot/dashboard/graficos') :
        View::render('utils/alert', [
          'color' => 'danger',
          'mensagem' => 'Nenhum notificação registrada nesse período.',
        ]),
    ]);
  }

  /**
   * Método responsável por retornar as notificações por mês
   * 
   * @param array $notifications
   * @return array
   */
  private static function getNotificationsByMonth(array $notifications): array
  {
    $dataPoints = array();
    $meses = array();
    $media = 0;

    $fortalezaTimeZone = new DateTimeZone(CURRENT_TIMEZONE);
    $formatter = new IntlDateFormatter('pt_BR', IntlDateFormatter::MEDIUM, IntlDateFormatter::MEDIUM);
    $formatter->setPattern('LLL');

    foreach ($notifications as $notification) {
      $dataCriacao = new DateTime($notification->data_criacao, $fortalezaTimeZone);
      $mes = ucfirst(str_replace('.', '', $formatter->format($dataCriacao)));
      $meses[$mes]++;
      $media++;
    }

    foreach ($meses as $key => $value) {
      if ($value == 0)
        continue;

      $dataPoints[] = array("label" => rtrim($key), "y" => $value);
    }

    if (count($meses) > 0)
      $media = $media / count($meses);

    return array(
      'data' => $dataPoints,
      'media' => $media
    );
  }

  /**
   * Método responsável por retornar as notificações por semana
   * 
   * @param array $notifications
   * @return array
   */
  private static function getNotificationsByWeek($notifications)
  {
    $dias = array(
      'Seg' => 0,
      'Ter' => 0,
      'Qua' => 0,
      'Qui' => 0,
      'Sex' => 0,
      'Sab' => 0,
      'Dom' => 0,
    );
    $dataPoints = array();

    $fortalezaTimeZone = new DateTimeZone(CURRENT_TIMEZONE);
    $formatter = new IntlDateFormatter('pt_BR', IntlDateFormatter::MEDIUM, IntlDateFormatter::MEDIUM);
    $formatter->setPattern('EEE');

    foreach ($notifications as $notification) {
      $dataCriacao = new DateTime($notification->data_criacao, $fortalezaTimeZone);
      $dia = ucfirst(str_replace('.', '', $formatter->format($dataCriacao)));
      $dias[$dia]++;
    }
    foreach ($dias as $key => $value) {
      if ($value == 0) continue;
      $dataPoints[] = array("label" => rtrim($key), "y" => $value);
    }
    return $dataPoints;
  }
  /**
   * Método responsável por agrupar as notificações por setor
   * 
   * @param array $notifications
   * @return array
   */
  private static function getNotificationsBySector(array $notifications): array
  {
    $result = array();
    $quantity = array();
    foreach ($notifications as $notification) {
      $setor = UnityModel::getSetorByCode($notification->setor_origem);
      if (empty($notification->setor_origem)) $setor->nome = 'Não notificado';
      $quantity[rtrim($setor->nome)]++;
    }
    foreach ($quantity as $setor => $value) {
      $result[] =  array("label" => rtrim($setor), "y" => $value);
    }
    return $result;
  }
  /**
   * Método responsável por retornar os N incidentes mais registrados
   * 
   * @param array $incidents
   * @param int $quantity
   * @return array
   */
  private static function getTopIncidents(array $incidents, int $quantity): array
  {
    $result = array();
    $dataPoints = array();
    foreach ($incidents as $incident) {
      $result[$incident->descricao]++;
    }
    array_multisort($result, SORT_DESC, array_keys($result));
    $result = array_slice($result, 0, $quantity, true);
    $total = 0;
    array_map(function ($value) use (&$total) {
      $total += $value;
      return $total;
    }, $result);
    foreach ($result as $incident => $value) {
      $dataPoints[] =  array(
        "label" => rtrim($incident),
        "y" => round(($value / $total) * 100, 0),
        "percent" => round(($value / $total) * 100,
          0
        )
      );
    }
    return $dataPoints;
  }
  /**
   * Método responsável por retornar os datapoints dos gráficos do dashboard
   * 
   * @param Request $request
   * @return array
   */
  public static function getDataPoints(Request $request)
  {
    $postVars = $request->getPostVars();
    if (empty($postVars['incidentes'])) {
      $notificationsTotal = NotificationModel::getNotificationByDates($postVars['dataInicio'], $postVars['dataFim']);
    } else {
      $notificationsTotal = NotificationModel::getNotificationByDatesAndIncidents(
        $postVars['dataInicio'],
        $postVars['dataFim'],
        $postVars['incidentes']
      );
    }
    $notifications = self::getNotifications($notificationsTotal);
    $incidents = IncidentModel::getIncidentesByDate($postVars['dataInicio'], $postVars['dataFim'], $postVars['incidentes']);

    return array(
      'notificacoes-mes' => self::getNotificationsByMonth($notifications),
      'notificacoes-semana' => self::getNotificationsByWeek($notifications),
      'notificacoes-setor' => self::getNotificationsBySector($notifications),
      'ranking-incidentes' => self::getTopIncidents($incidents, 5)
    );
  }
  /**
   * Método responsável por retornar as options do select da página de dashboard
   * 
   * @return string
   */
  private static function getIncidentOptions(): string
  {
    $options = '';
    $incidents = IncidentModel::getIncidents();
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
   * Método responsável por retornar a página inicial do dashboard
   * 
   * @param Request $request
   * @return string
   */
  public static function getHome(Request $request): string
  {
    $content = View::render('sisnot/dashboard', [
      'options' => self::getIncidentOptions()
    ]);

    return parent::getPage('SisNot', 'sisnot', $content, $request);
  }
}
