<?php

namespace App\Controller\Inutri;

use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;
use \App\Model\Inutri\Pedido as PedidoModel;
use \App\Model\Utils\Setor as SetorModel;

class Dashboard extends LayoutPage
{
  private static function getPedidosBySetor($pedidos)
  {
    $result = array();
    $quantity = array();

    foreach ($pedidos as $pedido) {
      $setor = SetorModel::getSetorByCode($pedido->unidade);
      $quantity[rtrim($setor->nome)]++;
    }

    foreach ($quantity as $setor => $value) {
      $result[] =  array("label" => rtrim($setor), "y" => $value);
    }

    return $result;
  }
  private static function getPedidosByMonth($pedidos)
  {
    $dataPoints = array();
    setlocale(LC_ALL, 'pt_BR.UTF-8', 'portuguese');
    date_default_timezone_set('America/Fortaleza');
    $meses = array();
    $media = 0;
    foreach ($pedidos as $pedido) {
      $mes = ucfirst(strftime('%b', strtotime($pedido->data_pendente)));
      $meses[$mes]++;
      $media++;
    }
    foreach ($meses as $key => $value) {
      if ($value == 0) continue;
      $dataPoints[] = array("label" => rtrim($key), "y" => $value);
    }
    $media = round($media / count($meses, 0));
    return array(
      'dataPoints' => $dataPoints,
      'media' => $media
    );
  }
  private static function getPedidosByWeek($pedidos)
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
    foreach ($pedidos as $pedido) {
      $dia = ucfirst(strftime('%a', strtotime($pedido->data_pendente)));
      $dias[$dia]++;
    }
    foreach ($dias as $key => $value) {
      if ($value == 0) continue;
      $dataPoints[] = array("label" => rtrim($key), "y" => $value);
    }
    return $dataPoints;
  }
  // Método responsável por as solicitações por hora
  private static function getSolicitByHour($pedidos)
  {
    $dataPoints = array();
    $horas = array();
    foreach ($pedidos as $pedido) {
      $hora = ucfirst(strftime('%H', strtotime($pedido->data_pendente)));
      $horas[$hora]++;
    }
    ksort($horas);
    foreach ($horas as $key => $value) {
      $dataPoints[] = array("label" => preg_replace('/\s{1,}/', ' ', $key) . "h", "y" => $value);
    }
    return $dataPoints;
  }
  // Método responsável por retornar os gráficos do dashboard
  public static function getDataPoints($request)
  {
    $postVars = $request->getPostVars();
    $pedidos = PedidoModel::getAllPedidosByDate($postVars['dataInicio'], $postVars['dataFim']);

    $pedidosByMonth = self::getPedidosByMonth($pedidos);
    return array(
      'pedidos-mes' => array(
        'pedidos' => $pedidosByMonth['dataPoints'],
        'media' => $pedidosByMonth['media']
      ),
      'pedidos-setor' => self::getPedidosBySetor($pedidos),
      'pedidos-semana' => self::getPedidosByWeek($pedidos),
      'pedidos-hora' => self::getSolicitByHour($pedidos)
    );
  }
  // Método responsável por verificar qual o tipo de usuário e qual a página redirecionada
  public static function getHome($request)
  {
    $content = View::render('inutri/dashboard', []);
    return parent::getPage('iNutri', 'inutri', $content, $request);
  }
  // Método responsável por retornar a quantidade de pedidos entregues
  private static function getPedidosEntregues($pedidos)
  {
    $entregues = 0;
    foreach ($pedidos as $pedido) if ($pedido->situacao == 'entregue') $entregues++;
    return $entregues;
  }
  // Método responsável por retornar os pedidos divididos por situações
  private static function getPedidos($pedidos)
  {
    $linhasTabela = '';
    $pedidoSetor = array();
    foreach ($pedidos as $pedido) {
      $setor = SetorModel::getSetorByCode($pedido->unidade);
      $pedidoSetor[rtrim($setor->nome)][$pedido->situacao]++;
      $pedidoSetor[rtrim($setor->nome)]['total']++;
    }

    foreach ($pedidoSetor as $setor => $pedido) {
      $linhasTabela .= View::render('inutri/dashboard/linha_tabela', [
        'setor' => $setor,
        'pendente' => $pedido['pendente'] ?? 0,
        'progresso' => $pedido['progresso'] ?? 0,
        'pronto' => $pedido['pronto'] ?? 0,
        'entregando' => $pedido['entregando'] ?? 0,
        'entregue' => $pedido['entregue'] ?? 0,
        'cancelado' => $pedido['cancelado'] ?? 0,
        'total' => $pedido['total']
      ]);
    }
    return $linhasTabela;
  }
  // Método responsável por verificar qual o tipo de usuário e qual a página redirecionada
  public static function getDashboard($request)
  {
    $postVars = $request->getPostVars();
    $pedidos = PedidoModel::getAllPedidosByDate($postVars['dataInicio'], $postVars['dataFim']);
    $totalPedidos = count($pedidos);
    return View::render('inutri/dashboard/pedidos', [
      'tabela' => $totalPedidos > 0 ? View::render('inutri/dashboard/tabela') : '',
      'total-pedidos' => $totalPedidos,
      'total-pedidos-entregues' => self::getPedidosEntregues($pedidos),
      'linhas-tabela' => self::getPedidos($pedidos),
      'graficos' => $totalPedidos > 0 ? View::render('inutri/dashboard/graficos') : View::render('utils/alert', [
        'color' => 'danger',
        'mensagem' => 'Nenhum pedido registrado nesse período.',
      ]),
    ]);
  }
}
