<?php

namespace App\Controller\Monexm;

use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;
use \App\Model\Monexm\Exame as ExameModel;
use DateTime;
use DateTimeZone;

class Home extends LayoutPage
{
  // Método responsável por retonar as solicitações e resultados por semana;
  private static function getSolicAndResultByWeek($exames)
  {
    setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
    date_default_timezone_set('America/Recife');

    $dias = array();
    $dias2 = array();
    $dataPoints1 = array();
    $dataPoints2 = array();

    foreach ($exames as $exame) {
      $dia = ucfirst(strftime('%a', strtotime($exame['LANCAMENTO'])));
      $dias[$dia] += intval($exame['QTD']);
      if (array_key_exists($dia, $dias)) $dias2[$dia] += intval($exame['RESULTADOS']);
    }

    foreach ($dias as $key => $value) {
      if ($value == 0) continue;
      $dataPoints1[] = array("label" => preg_replace('/\s{1,}/', ' ', $key), "y" => $value);
    }

    foreach ($dias2 as $key => $value) {
      if ($value == 0) continue;
      $dataPoints2[] = array("label" => preg_replace('/\s{1,}/', ' ', $key), "y" => $value);
    }

    return array($dataPoints1, $dataPoints2);
  }

  // Método responsável por retornar as solicitações e resultados por dia
  private static function getSolicitAndResultByDay($exames)
  {
    setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
    date_default_timezone_set('America/Recife');

    $dias = array();
    $dias2 = array();
    $dataPoints1 = array();
    $dataPoints2 = array();

    foreach ($exames as $exame) {
      $dia = strftime('%d', strtotime($exame['LANCAMENTO'])) . PHP_EOL . ucfirst(strftime('%b', strtotime($exame['LANCAMENTO'])));
      $dias[$dia] += intval($exame['QTD']);
      if (array_key_exists($dia, $dias)) $dias2[$dia] += intval($exame['RESULTADOS']);
    }

    foreach ($dias as $key => $value) {
      if ($value == 0) continue;
      $dataPoints1[] = array("label" => preg_replace('/\s{1,}/', ' ', $key), "y" => $value);
    }

    foreach ($dias2 as $key => $value) {
      if ($value == 0) continue;
      $dataPoints2[] = array("label" => preg_replace('/\s{1,}/', ' ', $key), "y" => $value);
    }

    return array($dataPoints1, $dataPoints2);
  }

  // Método responsável por as solicitações por hora
  private static function getSolicitByHour($exames)
  {
    setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
    date_default_timezone_set('America/Recife');
    $dataPoints = array();
    $dias = array();

    foreach ($exames as $exame) {
      $dia = ucfirst(strftime('%H', strtotime($exame['LANCAMENTO'])));
      $dias[$dia] += intval($exame['QTD']);
    }
    ksort($dias);

    foreach ($dias as $key => $value) {
      $dataPoints[] = array("label" => preg_replace('/\s{1,}/', ' ', $key) . "h", "y" => $value);
    }

    return $dataPoints;
  }

  // Método responsável por as solicitações por setor
  private static function getSolicitBySetor($exames)
  {
    $dataPoints = array();
    $setores = array();
    foreach ($exames as $exame) {
      $setor = $exame['SETOR'];
      $setores[$setor] += intval($exame['QTD']);
    }

    foreach ($setores as $key => $value) {
      $dataPoints[] = array("label" => preg_replace('/\s{1,}/', ' ', $key), "y" => $value);
    }

    return $dataPoints;
  }


  // Método responsável por retornar a data do primeiro e último resultado
  private static function getFirstAndLastResult($exames)
  {
    $dateFirstExame = null;
    $dateLastExame = null;
    foreach ($exames as $exame) {
      $dateLastExame = max($dateLastExame, $exame['RESULTADO_ULTIMO']);
      if ((is_null($dateFirstExame) && is_null($exame['RESULTADO_INICAL'])) || is_null($exame['RESULTADO_INICAL'])) continue;

      if (is_null($dateFirstExame)) {
        $dateFirstExame = $exame['RESULTADO_INICAL'];
        continue;
      }

      $dateFirstExame = min($dateFirstExame, $exame['RESULTADO_INICAL']);
    }

    return  array($dateFirstExame, $dateLastExame);
  }

  // Método responsável por retornar o total de exames e resultados
  private static function getTotalExamResult($exames)
  {
    $totalExames = 0;
    $totalResultados = 0;
    foreach ($exames as $exame) {
      $totalExames += $exame['QTD'];
      $totalResultados += $exame['RESULTADOS'];
    }
    return array($totalExames, $totalResultados);
  }

  // Método responsável por retornar os datapoints dos charts
  public static function getDataPoints($request)
  {
    $postVars = $request->getPostVars();
    $exames = ExameModel::getAllExamesByDates($postVars['dataInicio'], $postVars['dataFim']);
    $solicAndResultWeek = self::getSolicAndResultByWeek($exames);
    $solicAndResultDay = self::getSolicitAndResultByDay($exames);
    $dataPoints = array(
      'solicitacao_setor' => self::getSolicitBySetor($exames),
      'solicitacao_semana' => $solicAndResultWeek[0],
      'resultado_semana' => $solicAndResultWeek[1],
      'solicitacao_hora' => self::getSolicitByHour($exames),
      'solicitacao_dia' => $solicAndResultDay[0],
      'resultado_dia' => $solicAndResultDay[1],
    );

    return json_encode($dataPoints);
  }

  // Método responsável por retornar as linhas da tabela
  public static function getTableRows($request)
  {
    $postVars = $request->getPostVars();
    $exames = ExameModel::getAllExamesByDates($postVars['dataInicio'], $postVars['dataFim']);

    $rows = '';
    $examesSetores = array();
    foreach ($exames as $exame) {
      $setor = substr(preg_replace('/\s{1,}/', ' ', $exame['SETOR']), 0, -1);
      $examesSetores[$setor]['EXAMES_QTD'] += intval($exame['QTD']);
      $examesSetores[$setor]['RESULTADO'] +=  intval($exame['RESULTADOS']);
      $examesSetores[$setor]['CODE'] = $exame['SETOR_COD'];
      if ($exame['ST'] == 'X') $examesSetores[$setor]['COLETADO']++;
      if ($exame['ST'] == 'A') $examesSetores[$setor]['ABERTO']++;
    }
    ksort($examesSetores);

    foreach ($examesSetores as $setor => $dados) {
      $rows .= View::render('monexm/exames/tabela_result', [
        'setor' => $setor,
        'setor-cod' => str_replace(' ', '', $dados['CODE']),
        'qtd-exames' => $dados['EXAMES_QTD'],
        'qtd-resultados' => $dados['RESULTADO'],
        'exames-coletados' => !is_null($dados['COLETADO']) ? $dados['COLETADO'] : 0,
        'exames-aberto' => !is_null($dados['ABERTO']) ? $dados['ABERTO'] : 0,
      ]);
    }
    $examesTotal = self::getTotalExamResult($exames);
    $examesDates = self::getFirstAndLastResult($exames);

    $fortalezaTimeZone = new DateTimeZone(CURRENT_TIMEZONE);

    $dataPrimeiro = new DateTime($examesDates[0], $fortalezaTimeZone);
    $dataUltimo = new DateTime($examesDates[1], $fortalezaTimeZone);
    $tempoAgora = new DateTime('now', $fortalezaTimeZone);
    $tempoExame = new DateTime($examesDates[1], $fortalezaTimeZone);
    $diffTempoExames = $tempoAgora->diff($tempoExame);

    $colorDiffTime = '';
    if ($diffTempoExames < 0) $diffTempoExames = 0;
    if ($diffTempoExames < 30) $colorDiffTime = "bg-success";
    else if ($diffTempoExames >= 30 && $diffTempoExames < 45) $colorDiffTime = "bg-amarelo";
    else if ($diffTempoExames >= 45 && $diffTempoExames < 60) $colorDiffTime = "bg-laranja";
    else $colorDiffTime = "bg-danger";

    return View::render('monexm/exames', [
      'linhas-tabela' => $rows,
      'total-exames' => $examesTotal[0],
      'total-resultados' => $examesTotal[1],
      'data-primeiro' => $dataPrimeiro->format('d/m/Y H:i'),
      'data-ultimo' => $dataUltimo->format('d/m/Y H:i'),
      'diff-time' => $diffTempoExames->format('%I%') . ' min',
      'color-diff-time' => $colorDiffTime
    ]);
  }

  // Método responsável por retornar o modal de OS
  public static function getOs($request, $setor)
  {
    $postVars = $request->getPostVars();
    $osSetor = ExameModel::getAllExamesByDatesAndSector($postVars['dataInicio'], $postVars['dataFim'], $setor);
    $linhaOs = '';
    $osBySetor = array();
    $setor = $osSetor[0]['SETOR'];

    foreach ($osSetor as $os) {
      $osSerieNum = $os['OS_SERIE']  . " - " . $os['OS_NUMERO'];
      $osBySetor[$osSerieNum]['LANCAMENTO'] = $os['LANCAMENTO'];
      if ($os['ST'] == 'L' || $os['ST'] == 'I') $osBySetor[$osSerieNum]['RECEBIDO'] += $os['QTD'];
      if ($os['ST'] == 'A' || $os['ST'] == 'X') $osBySetor[$osSerieNum]['NRECEBIDO'] += $os['QTD'];
    }

    foreach ($osBySetor as $key => $value) {
      $linhaOs .= View::render('monexm/modals/os_info', [
        'os' => $key,
        'lancamento' => date('d/m/Y H:i', strtotime($value['LANCAMENTO'])),
        'recebido' => $value['RECEBIDO'] == null ? '0' : $value['RECEBIDO'],
        'bg-color' => $value['NRECEBIDO'] == null ? 'os-n-recebido' : '',
        'n-recebido' => $value['NRECEBIDO'] == null ? '0' : $value['NRECEBIDO'],
      ]);
    }

    return View::render('monexm/modals/os_modal', [
      'setor' => $setor,
      'linhas-os' => $linhaOs
    ]);
  }

  public static function getHome($request)
  {
    $content = View::render('monexm/home', []);
    return parent::getPage('MonExm', 'monexm', $content, $request);
  }
}
