<?php

namespace App\Controller\NotSis;

use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;
use App\Model\Notsis\Notificacao as NotificacaoModel;
use App\Model\Utils\Setor as SetorModel;

class Dashboard extends LayoutPage
{
  // Método responsável por retornar o agrupamento de todas as notificações enviadas por parâmetro
  private static function getNotificacoes($notificacoes)
  {
    $notificacoesR = array();
    foreach ($notificacoes as $notificacao) {
      $notificacoesR[$notificacao['id_notificacao']] = $notificacao;
    }
    return $notificacoesR;
  }
  // Método responsável por returnar o total de todos os incidentes das notificações enviadas por parâmetro
  private static function getTotalIncidentes($notificacoes)
  {
    $total = array();
    foreach ($notificacoes as $notificacao) {
      $total[$notificacao['id_incidente'] . $notificacao['id_notificacao']]++;
    }
    return count($total);
  }
  // Método responsável por retornar o total de notificações que foram respondidas
  private static function getTotalNotificacoes($notificacoes)
  {
    $total = array();
    foreach ($notificacoes as $notificacao) {
      $total[$notificacao['id_notificacao']]++;
    }
    return count($total);
  }
  // Método responsável por gerar parte o cabeçalho da página de dashboard
  public static function getDashboard($request)
  {
    $postVars = $request->getPostVars();
    if (empty($postVars['incidentes'])) {
      $notificacoes = NotificacaoModel::getAllNotificacoesByDates($postVars['dataInicio'], $postVars['dataFim']);
    } else {
      $notificacoes = NotificacaoModel::getNotificacoesByDatesAndIncidentes($postVars['dataInicio'], $postVars['dataFim'], $postVars['incidentes']);
    }
    return View::render('notsis/dashboard/notificacoes', [
      'total-notificacoes' => self::getTotalNotificacoes($notificacoes),
      'total-incidentes' => self::getTotalIncidentes($notificacoes),
      'graficos' => !empty($notificacoes) ? View::render('notsis/dashboard/graficos') : View::render('utils/alert', [
        'color' => 'danger',
        'mensagem' => 'Nenhum notificação registrada nesse período.',
      ]),
    ]);
  }
  // Método responsável por agrupar as notificações por mês
  private static function getNotificacoesByMonth($notificacoes)
  {
    $dataPoints = array();
    setlocale(LC_ALL, 'pt_BR.UTF-8', 'portuguese');
    date_default_timezone_set('America/Fortaleza');
    $meses = array();
    $media = 0;
    foreach ($notificacoes as $notificacao) {
      $mes = ucfirst(strftime('%b', strtotime($notificacao['data_criacao'])));
      $meses[$mes]++;
      $media++;
    }
    foreach ($meses as $key => $value) {
      if ($value == 0) continue;
      $dataPoints[] = array("label" => rtrim($key), "y" => $value);
    }
    $media = $media / count($meses);
    return array(
      'data' => $dataPoints,
      'media' => $media
    );
  }
  // Método responsável por agrupar as notificações por semana
  private static function getNotificacoesByWeek($notificacoes)
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
    foreach ($notificacoes as $notificacao) {
      $dia = ucfirst(strftime('%a', strtotime($notificacao['data_criacao'])));
      $dias[$dia]++;
    }
    foreach ($dias as $key => $value) {
      if ($value == 0) continue;
      $dataPoints[] = array("label" => rtrim($key), "y" => $value);
    }
    return $dataPoints;
  }
  // Método responsável por agrupar as notificações por setor
  private static function getNotificacoesBySector($notificacoes)
  {
    $result = array();
    $quantity = array();
    foreach ($notificacoes as $notificacao) {
      $setor = SetorModel::getSetorByCode($notificacao['setor_incidente']);
      if (empty($notificacao['setor_incidente'])) $setor->nome = 'Não notificado';
      $quantity[rtrim($setor->nome)]++;
    }
    foreach ($quantity as $setor => $value) {
      $result[] =  array("label" => rtrim($setor), "y" => $value);
    }
    return $result;
  }
  // Método responsável por retornar o Top N incidentes enviados
  private static function getTopIncidentes($incidentes, $quantity)
  {
    $result = array();
    $dataPoints = array();
    foreach ($incidentes as $incidente) {
      $result[$incidente['valor']]++;
    }
    array_multisort($result, SORT_DESC, array_keys($result));
    $result = array_slice($result, 0, $quantity, true);
    $total = 0;
    array_map(function ($value) use (&$total) {
      $total += $value;
      return $total;
    }, $result);
    foreach ($result as $incidente => $value) {
      $dataPoints[] =  array("label" => rtrim($incidente), "y" => round(($value / $total) * 100, 0), "percent" => round(($value / $total) * 100, 0));
    }
    return $dataPoints;
  }
  // Método responsável por retornar os gráficos do dashboard
  public static function getDataPoints($request)
  {
    $postVars = $request->getPostVars();
    if (empty($postVars['incidentes'])) {
      $notificacoesTotal = NotificacaoModel::getAllNotificacoesByDates($postVars['dataInicio'], $postVars['dataFim']);
    } else {
      $notificacoesTotal = NotificacaoModel::getNotificacoesByDatesAndIncidentes($postVars['dataInicio'], $postVars['dataFim'], $postVars['incidentes']);
    }
    $notificacoes = self::getNotificacoes($notificacoesTotal);
    $incidentes = NotificacaoModel::getAllIncidentesByDate($postVars['dataInicio'], $postVars['dataFim'], $postVars['incidentes']);

    return array(
      'notificacoes-mes' => self::getNotificacoesByMonth($notificacoes),
      'notificacoes-semana' => self::getNotificacoesByWeek($notificacoes),
      'notificacoes-setor' => self::getNotificacoesBySector($notificacoes),
      'ranking-incidentes' => self::getTopIncidentes($incidentes, 5)
    );
  }
  // Método responsável por retornar as options do select da página de dashboard
  private static function getIncidenteOptions()
  {
    $options = '';
    $incidentes = NotificacaoModel::getIncidentes();
    foreach ($incidentes as $incidente) {
      $options .= View::render('utils/option', [
        'id' => $incidente['id_incidente'],
        'nome' => $incidente['valor'],
        'selected' => '',
      ]);
    }
    return $options;
  }
  // Método responsável por retornar a página inicial do dashboard
  public static function getHome($request)
  {
    $content = View::render('notsis/dashboard', [
      'options' => self::getIncidenteOptions()
    ]);
    self::getDataPoints($request);
    return parent::getPage('NotSis', $content, 'notsis', $request);
  }
}
