<?php

namespace App\Controller\NotSis;

use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;
use App\Model\Notsis\Notificacao as NotificacaoModel;
use \App\Session\NotSis\Notificacao as NotificacaoSession;
use App\Controller\Auth\Alert;
use App\Controller\Utils\Setor;
use App\Model\Notsis\Incidente as IncidenteModel;

class Home extends LayoutPage
{
  // Método responsável por retornar a página inicial do notsis
  public static function getHome($request, $errorMessage = null)
  {
    $status = !is_null($errorMessage) ? Alert::getError($errorMessage) : '';
    $content = View::render('notsis/home', [
      'status' => $status,
      'options' => Setor::getSetorOptions(),
    ]);

    return parent::getPage('NotSis', $content, 'notsis', $request);
  }
  // Método responsável por criar uma notificação
  public static function createNotificacao($request)
  {
    $postVars = $request->getPostVars();
    if (empty($postVars['data']) || empty($postVars['hora'])) return self::getHome($request, 'Defina a data e hora do incidente!');
    $dataHora = $postVars['data'] . ' ' . $postVars['hora'] . ':00';
    $registroPaciente = $postVars['registro'] ?? null;
    $setorIncidente = $postVars['origem'] ?? null;
    $setorNotificador = $postVars['notificador'] ?? null;
    NotificacaoSession::createNotificacao(array(
      'data_hora' => $dataHora,
      'registro_paciente' => $registroPaciente,
      'setor_incidente' => $setorIncidente,
      'setor_notificador' => $setorNotificador
    ));
    $notificacao = bin2hex(random_bytes(8));
    $request->getRouter()->redirect('/notsis/notificacao/' . $notificacao);
  }
  // Método responsável por retornar a página home do administrador do sistema
  public static function getAdminHome($request)
  {
    $content = View::render('notsis/admin_home', []);

    return parent::getPage('NotSis', $content, 'notsis', $request);
  }
  // Método responsável por retornar a página de notificações
  private static function getNotificacao($notificacoes)
  {
    if(empty($notificacoes)) return '<h5>Não há notificação nesse periodo.</h5>';
    $notificacaoPage = '';
    foreach ($notificacoes as $notificacao) {
      if($notificacao['id_incidente'] === null) continue;
      $setorIncidente = !empty($notificacao['setor_incidente']) ? Setor::getSetorByCode($notificacao['setor_incidente']) : 'Não notificado';
      $setorNotificador = !empty($notificacao['setor_notificador']) ? Setor::getSetorByCode($notificacao['setor_notificador']) : 'Não notificado';
      $incidente = IncidenteModel::getIncidenteById($notificacao['id_incidente']);
      $notificacaoPage .= View::render('notsis/notificacao', [
        'id' => $notificacao['id_notificacao'],
        'data-criacao' => date('d/m/Y H:i:s', strtotime($notificacao['data_criacao'])),
        'registro' => !empty($notificacao['registro_paciente']) ? $notificacao['registro_paciente'] : 'Não notificado',
        'setor-incidente' => $setorIncidente,
        'setor-notificador' => $setorNotificador,
        'id-incidente' => $incidente['id_incidente'],
        'incidente' =>  $incidente['valor'],
      ]);
    }
    return $notificacaoPage;
  }
  // Método responsável por retornar as opções incidentes para realizar o cadastro de uma notificação
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
  // Método responsável por retornar as notificações dos usuários
  public static function getNotificacoes($request)
  {
    $notificacoes = NotificacaoModel::getNotificacoesByDates(date('Y-m-d', strtotime("-6 day")), date('Y-m-d', strtotime('now')));
    $content = View::render('notsis/notificacoes', [
      'notificacoes' => self::getNotificacao($notificacoes),
      'options' => self::getIncidenteOptions(),
    ]);

    return parent::getPage('NotSis', $content, 'notsis', $request);
  }
  // Método responsável pore retornar as notificações pelas datas solicitadas
  public static function getNotificacoesByDate($request)
  {
    $postVars = $request->getPostVars();
    $notificacoes = NotificacaoModel::getNotificacoesByDates($postVars['firstDate'], $postVars['secondDate']);
    return self::getNotificacao($notificacoes);
  }
}
